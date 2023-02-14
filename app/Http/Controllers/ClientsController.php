<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use App\Jobs\SendQueuedConfirmationEmailJob;
use App\Mail\ConfirmNewRegistration;
use App\Models\Role;
use Illuminate\Support\Facades\Mail;

class ClientsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (isset($request->option) && $request->option === 'all') {
            $clients = Client::orderBy('name')->get();
        } else {

            $clients = Client::with('users')->orderBy('name')->paginate($request->limit);
        }
        return response()->json(compact('clients'), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $contact_email = $request->contact_email;
        $client = Client::where('contact_email', $contact_email)->first();
        if (!$client) {
            $client = new Client();
            $client->name = $request->organization_name;
            $client->contact_email = $request->contact_email;
            $client->contact_phone = $request->contact_phone;
            $client->contact_address = $request->contact_address;
            if ($client->save()) {

                // we have to save the user info for this client
                $request->name = $request->admin_first_name . ' ' . $request->admin_last_name;
                $request->email = $request->admin_email;
                $request->password = $request->admin_email;
                $request->phone = $request->admin_phone;
                $request->role = 'client';
                $user_obj = new User();
                $user = $user_obj->createUser($request);
                // sync user to client
                $client->users()->sync($user->id);
                $role = Role::where('name', 'client')->first();
                $user->roles()->sync($role->id); // role id 3 is client

                // send confirmation email to user
                //email will be sent later containing login credentials
                // SendQueuedConfirmationEmailJob::dispatch($user);
                $actor = $this->getUser();
                $title = "New Client Registered";
                //log this event
                $description = "$client->name was registered by $actor->name";
                $this->auditTrailEvent($title, $description);


                return response()->json('success', 200);
            }
            return response()->json(['message' => 'Unable to register'], 500);
        }
        return response()->json(['message' => 'Company already exists'], 401);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function sendLoginCredentials(User $user)
    {
        $password = randomPassword();
        $user->password = $password;
        $user->save();
        //email will be sent later containing login credentials
        // SendQueuedConfirmationEmailJob::dispatch($user, $password);
        Mail::to($user)->send(new ConfirmNewRegistration($user, $password));
        // \Illuminate\Support\Facades\Artisan::call('queue:work --queue=high,default');
        return response()->json([], 204);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Client $client)
    {
        //
        $client->name = $request->organization_name;
        $client->contact_email = $request->contact_email;
        $client->contact_phone = $request->contact_phone;
        $client->contact_address = $request->contact_address;
        if ($client->save()) {
            $user = User::find($request->admin_id);
            if (!$user) {
                $user = new User();
                $user->password = $request->admin_email;
                $user->role = 'client';
                $user->confirm_hash = hash('sha256', time() . $request->admin_email);
            }

            $user->name = $request->admin_name;
            $user->email = $request->admin_email;
            $user->phone = $request->admin_phone;
            $user->designation = $request->designation;
            $user->save();

            $client->users()->sync($user->id);
            $user->roles()->sync(2); // role id 2 is client
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function toggleClientSuspension(Request $request, Client $client)
    {
        $value = $request->value;
        $client->is_active = $value;
        $client->save();
    }
}
