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
        $user = $this->getUser();
        $condition = [];
        if ($user->haRole('client')) {
            $id = $this->getClient()->id;
            $condition = ['id' => $id];
        }
        if ($user->haRole('partner')) {
            $partner_id = $this->getPartner()->id;
            $condition = ['partner_id' => $partner_id];
        }
        if (isset($request->option) && $request->option === 'all') {
            $clients = Client::where($condition)->orderBy('name')->get();
        } else {

            $clients = Client::with('users')->where($condition)->orderBy('name')->paginate($request->limit);
        }
        return response()->json(compact('clients'), 200);
    }


    public function fetchUserClients()
    {
        $user_id = $this->getUser()->id;
        $user = User::find($user_id);
        $clients = $user->clients;
        return response()->json(compact('clients'), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = $this->getUser();
        if (!$user->haRole('partner')) {
            return response()->json(['message' => 'Clients registration is restricted to Partners only'], 500);
        }
        $partner_id = $this->getPartner()->id;

        $contact_email = $request->contact_email;
        $client = Client::where('contact_email', $contact_email)->first();
        if (!$client) {
            $client = new Client();
            $client->partner_id = $partner_id;
            $client->name = $request->organization_name;
            $client->contact_email = $request->contact_email;
            $client->contact_phone = $request->contact_phone;
            $client->contact_address = $request->contact_address;
            if ($client->save()) {
                $actor = $this->getUser();
                $title = "New Client Registered";
                //log this event
                $description = "$client->name was registered by $actor->name";
                $this->auditTrailEvent($title, $description);


                return response()->json(compact('client'), 200);
            }
            return response()->json(['message' => 'Unable to register'], 500);
        }
        return response()->json(['message' => 'Company already exists'], 401);
    }


    public function registerClientUser(Request $request)
    {
        $client = Client::find($request->client_id);
        $request->name = $request->admin_first_name . ' ' . $request->admin_last_name;
        $request->email = $request->admin_email;
        $request->password = $request->admin_email;
        $request->phone = $request->admin_phone;
        $request->role = 'client';
        $user_obj = new User();
        $user = $user_obj->createUser($request);
        // sync user to client
        $client->users()->syncWithoutDetaching($user->id);
        $role = Role::where('name', 'client')->first();
        $user->roles()->sync($role->id); // role id 3 is client

        return response()->json('success', 200);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function sendLoginCredentials(User $user)
    {
        $password = $user->email; // randomPassword();
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
    public function update(Request $request)
    {
        $client = Client::find($request->id);
        $client->name = $request->name;
        $client->contact_email = $request->contact_email;
        $client->contact_phone = $request->contact_phone;
        $client->contact_address = $request->contact_address;
        $client->navbar_bg = $request->navbar_bg;
        $client->sidebar_bg = $request->sidebar_bg;
        $client->save();
        $this->changeClientLogo($request, $client);
    }
    private function changeClientLogo($data, $client)
    {
        if ($data->file('logo') != null && $data->file('logo')->isValid()) {

            $name = time() . '_' . $data->file('logo')->hashName();
            // $file_name = $name . "." . $request->file('file_uploaded')->extension();
            $link = $data->file('logo')->storeAs('client-logos', $name, 'public');
            $client->logo = $link;
            $client->save();
        }
    }
    public function updateClientUser(Request $request, User $user)
    {

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->designation = $request->designation;
        $user->save();

        // $client->users()->sync($user->id);
        // $role = Role::where('name', 'client')->first();
        // $user->roles()->sync($role->id); // role id 3 is client

    }
    public function attachClientUser(Request $request, Client $client)
    {
        $actor = $this->getUser();
        $user = User::find($request->user_id);
        $title = "Client User Attached";
        //log this event
        $description = "$user->name was attached to $client->name by $actor->name";
        $this->auditTrailEvent($title, $description);

        $client->users()->syncWithoutDetaching($user->id);
        // $user->delete();
        return response()->json([], 204);
        // $role = Role::where('name', 'partner')->first();
        // $partner->roles()->sync($role->id); // role id 3 is partner

    }


    public function removeClientUser(Request $request, Client $client)
    {
        $actor = $this->getUser();
        if (!$actor->haRole('partner')) {
            return response()->json(['message' => 'Clients are managed by Partners only'], 500);
        }
        $user = User::find($request->user_id);
        $title = "Client User Deletion";
        //log this event
        $description = "$user->name was removed from $client->name by $actor->name";
        $this->auditTrailEvent($title, $description);

        $client->users()->detach($user->id);
        // $user->delete();
        return response()->json([], 204);
        // $role = Role::where('name', 'partner')->first();
        // $partner->roles()->sync($role->id); // role id 3 is partner

    }
    // public function deleteClientUser(Request $request, User $user)
    // {
    //     $actor = $this->getUser();
    //     if (!$user->haRole('partner')) {
    //         return response()->json(['message' => 'Clients are managed by Partners only'], 500);
    //     }
    //     $title = "Client User Deletion";
    //     //log this event
    //     $description = "$user->name was deleted by $actor->name";
    //     $this->auditTrailEvent($title, $description);
    //     $user->forceDelete();
    //     return response()->json([], 204);
    //     // $client->users()->sync($user->id);
    //     // $role = Role::where('name', 'client')->first();
    //     // $user->roles()->sync($role->id); // role id 3 is client

    // }

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
