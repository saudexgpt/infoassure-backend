<?php

namespace App\Http\Controllers;

use App\Models\ActivatedModule;
use App\Models\AvailableModule;
use App\Models\Project;
use App\Notifications\AuditTrail;
use App\Models\Client;
use App\Models\Partner;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Notification;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $user;
    protected $client;
    protected $partner;
    protected $myProjects;
    protected $role;
    protected $roles = [];
    protected $data = [];
    protected $currency = '₦'; //'&#x20A6;';
    protected $this_year;

    public function __construct(Request $httpRequest)
    {
        //->paginate(10);
        $this->middleware(function ($request, $next) {
            return $next($request);
        });
    }
    public function render($data = [])
    {
        $this->data = array_merge($this->data, $data);
        $this->data['currency'] = '₦';

        //print_r($data['class']->class->name);exit;*/
        return response()->json($this->data, 200);
    }
    // public function toggleStudentNonPaymentSuspension(Request $request)
    // {
    //     $student_ids = $request->student_ids;
    //     foreach ($student_ids as $student_id) {
    //         $student = Student::find($student_id);
    //         $status = $student->studentship_status;
    //         if ($status == 1) {
    //             $student->studentship_status = 0;
    //         } else {
    //             $student->studentship_status = 1;
    //         }
    //         $student->save();
    //     }
    //     return 'success';
    // }
    public function setYear()
    {

        $this->this_year = (int) date('Y', strtotime('now'));
    }
    public function getYear()
    {
        $this->setYear();
        return $this->this_year;
    }
    public function setRoles()
    {
        $school_id = $this->getSchool()->id;
        $roles = Role::where('school_id', 0)->orWhere('school_id', $school_id)->get();
        foreach ($roles as $role) {
            $role_permissions = [];
            foreach ($role->permissions as $permission) {
                $role_permissions[] = $permission->id;
            }
            $role->role_permissions = $role_permissions;
        }
        $this->roles = $roles;
    }
    public function getRoles()
    {
        $this->setRoles();
        return $this->roles;
    }
    public function getPermissions()
    {
        $permissions = Permission::orderBy('name')->get();
        return $permissions;
    }
    public function getSoftwareName()
    {
        return env("APP_NAME");
    }

    public function setUser()
    {
        $this->user = User::find(Auth::user()->id);
    }

    public function getUser()
    {
        $this->setUser();

        return $this->user;
    }
    public function setMyProjects($client_id = NULL)
    {
        $user = $this->getUser();
        if ($client_id == NULL) {

            $client_id = $user->client_id;
        }
        if ($user->haRole('client') || $user->haRole('admin') || $user->haRole('super')) {
            $this->myProjects = $user->projects()
                ->with('client', 'availableModule', 'standard')
                ->where(['client_id' => $client_id, 'year' => $this->getYear()])
                ->orderBy('id', 'DESC')
                ->get();
        } else {

            $this->myProjects = Project::with('client', 'availableModule', 'standard')
                ->where(['client_id' => $client_id, 'year' => $this->getYear()])
                ->orderBy('id', 'DESC')
                ->get();
        }
    }

    public function getMyProjects($client_id)
    {
        $this->setMyProjects($client_id);

        return $this->myProjects;
    }
    public function setClient()
    {
        $user = $this->getUser();
        // $client_user = DB::table('client_user')->where('user_id', $user->id)->first();
        // $client_id = $client_user->client_id;
        $this->client = Client::find($user->client_id);
    }

    public function getClient()
    {
        $this->setClient();

        return $this->client;
    }
    public function setPartner()
    {
        $user = $this->getUser();
        // $partner_user = DB::table('partner_user')->where('user_id', $user->id)->first();
        // $partner_id = $partner_user->partner_id;
        $this->partner = Partner::find($user->partner_id);
    }

    public function getPartner()
    {
        $this->setPartner();

        return $this->partner;
    }
    public function getCurrency()
    {
        return $this->currency;
    }

    public function fetchCountries()
    {
        $countries = DB::table('countries')->orderBy('country_name')->pluck('country_name');
        return response()->json(compact('countries'), 200);
    }
    public function uploadFile($media, $file_name, $folder_key)
    {
        $folder = "clients/" . $folder_key;

        $media->storeAs($folder, $file_name, 'public');

        return $folder . '/' . $file_name;
    }
    public function auditTrailEvent($title, $action, $clients = null)
    {

        // $user = $this->getUser();
        $users = User::whereHas('roles', function ($query) {
            $query->where('name', '=', 'super');
            // ->orWhere('name', '=', 'admin'); // this is the role id inside of this callback
        })->get();
        if ($clients != null) {
            $users = $users->merge($clients);
        }
        $notification = new AuditTrail($title, $action);
        return Notification::send($users->unique(), $notification);
    }



    public function fetchAvailableModules()
    {
        $modules = AvailableModule::orderBy('name')->get();
        return response()->json(compact('modules'));
    }

    // public function fetchClientActivatedModules(Request $request, Client $client)
    // {
    //     $partner_id = $client->partner_id;
    //     $activated_modules = ActivatedModule::with('availableModule')->where('partner_id', $partner_id)->where('client_ids', '!=', NULL)->get();
    //     $modules = [];
    //     foreach ($activated_modules as $activated_module) {
    //         $client_ids_array = explode('~', $activated_module->client_ids);
    //         if (in_array($client->id, $client_ids_array)) {
    //             $modules[] = $activated_module->availableModule;
    //         }
    //     }

    //     return response()->json(compact('modules'));
    // }
}
