<?php

namespace App\Http\Controllers\VendorDueDiligence;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Country;
use App\Models\VendorDueDiligence\BankDetail;
use App\Models\VendorDueDiligence\DocumentUpload;
use App\Models\VendorDueDiligence\User;
use App\Models\VendorDueDiligence\Vendor;
use Illuminate\Http\Request;
use App\Mail\ConfirmNewRegistration;
use Illuminate\Support\Facades\Mail;

class VendorsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $user = $this->getUser();
        // $condition = [];
        // $partner_with_clients = [];
        // if ($user->haRole('client') || $user->haRole('admin')) {
        //     $id = $this->getClient()->id;
        //     $condition = ['id' => $id];
        // }
        // if ($user->haRole('partner')) {
        //     $partner_id = $this->getPartner()->id;
        //     $condition = ['partner_id' => $partner_id];
        // }

        // if ($user->haRole('super')) {
        //     $partner_with_clients = Partner::with('clients')->get();
        // }

        // if (isset($request->option) && $request->option === 'all') {
        //     $clients = Client::where($condition)->orderBy('name')->get();

        // } else {

        //     $clients = Client::with('users')->where($condition)->orderBy('name')->paginate($request->limit);
        //     return response()->json(compact('clients'), 200);
        // }
        // return response()->json(compact('clients', 'partner_with_clients'), 200);
    }


    public function showVendor(Request $request, Vendor $vendor)
    {
        $vendor = Vendor::with('bankDetail', 'documentUploads')->find($vendor->id);
        $business_types = $this->businessTypes();
        $countries = Country::get();
        return response()->json(compact('vendor', 'business_types', 'countries'), 200);
    }

    public function businessTypes()
    {
        return ['Cloud Provider', 'Consultancy', 'Cybersecurity Services', 'Financial Services', 'IT Services'];
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

    public function registerClient(Request $request)
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
                $request->client_id = $client->id;
                $request->role = 'client';
                $request->roles = ['admin', 'client'];
                $request->login_as = 'admin';
                $this->registerClientUser($request);
                $title = "New Client Registered";
                //log this event
                $description = "$client->name was registered";
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
        $user_obj = new User();
        $user = $user_obj->createUser($request);
        // make this user the client admin
        $roles = [$request->role];
        if (isset($request->roles)) {
            if (in_array('admin', $request->roles)) {

                $client->admin_user_id = $user->id;
                $client->save();
            }
            $roles = $request->roles;
        }
        // sync user to client
        $client->users()->syncWithoutDetaching($user->id);
        $roleIds = Role::whereIn('name', $roles)->pluck('id');
        $user->roles()->sync($roleIds); // role id 3 is client

        $this->sendLoginCredentials($user);
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
     * @return string
     */
    public function updateVendor(Request $request)
    {
        $data = $request->toArray();
        $vendor_id = $request->id;
        Vendor::find($vendor_id)->update($data);
        BankDetail::updateOrCreate([
            'vendor_id' => $request->id,
        ], [
            'bank_name' => $request->bank_name,
            'account_name' => $request->account_name,
            'account_no' => $request->account_no,
        ]);
        $files = $request->file('uploadable_files');
        $file_titles = $request->uploadable_files_titles;
        $titles = [];
        $counter = 0;
        $extra_message = '';
        foreach ($files as $file) {
            $title = $file_titles[$counter]; //$file->getClientOriginalName();
            if ($file->isValid()) {
                $formated_name = str_replace(' ', '_', ucwords($title));
                $file_name = $formated_name . '_' . $vendor_id . "." . $file->guessClientExtension();
                $link = $file->storeAs('vendors/' . $vendor_id . '/documents', $file_name, 'public');
                DocumentUpload::updateOrCreate([
                    'vendor_id' => $vendor_id,
                    'title' => $title
                ], ['link' => $link]);

                $extra_message = 'Some required documents were also uploaded.';

                // $this->auditTrailEvent($title, $description, $users);
            }

            $counter++;
        }
        $vendor = Vendor::find($vendor_id);
        $client = Client::with('users')->find($vendor->client_id);
        $token = $request->bearerToken();
        $user = User::where('api_token', $token)->first();
        $name = $user->name;// . ' (' . $user->email . ')';
        $title = "Vendor profile updated";
        $userIds = $client->users()->pluck('id')->toArray();
        //log this event
        $description = "The vendor profile for $vendor->name was updated by $user->name. <br>" . $extra_message;
        $this->sendNotification($title, $description, $userIds);

        return 'success';
    }

    public function deleteUploadedDocument(Request $rquest, DocumentUpload $document)
    {
        $document_link = $document->link;
        unlink(portalPulicPath($document_link));
        $document->delete();
        return 'success';
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

    public function assignUserAsClientAdmin(Request $request, Client $client)
    {
        $client->admin_user_id = $request->user_id;
        $client->save();
        $client = $client->with('users')->find($client->id);
        return response()->json(compact('client'), 200);
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
