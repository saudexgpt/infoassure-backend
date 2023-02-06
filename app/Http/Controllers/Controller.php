<?php

namespace App\Http\Controllers;

use App\Events\ClassEvent;
use App\Events\AuditTrailEvent;
use App\Events\SubjectEvent;
use App\Http\Resources\UserResource;
use App\Models\ActivatedModule;
use App\Notifications\AuditTrail;
use App\Models\ClassTeacher;
use App\Models\Client;
use App\Models\CurriculumLevelGroup;
use App\Models\Gallery;
use App\Models\Grade;
use App\Models\Guardian;
use App\Models\Level;
use App\Models\LocalGovernmentArea;
use App\Models\News;
use App\Models\Partner;
use App\Models\Permission;
use App\Models\Project;
use App\Models\ResultDisplaySetting;
use App\Models\Role;
use App\Models\School;
use App\Models\SSession;
use App\Models\Staff;
use App\Models\StaffRole;
use App\Models\Student;
use App\Models\StudentsInClass;
use App\Models\Subject;
use App\Models\Term;
use App\Models\UniqNumGen;
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
use Illuminate\Support\Facades\Notification;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $user;
    protected $client;
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
        $this->user  = Auth::user();
    }

    public function getUser()
    {
        $this->setUser();

        return $this->user;
    }
    public function setClient()
    {
        $user  = Auth::user();
        $client_user = DB::table('client_user')->where('user_id', $user->id)->first();
        $client_id = $client_user->client_id;
        $this->client = Client::find($client_id);
    }

    public function getClient()
    {
        $this->setClient();

        return $this->client;
    }
    public function getCurrency()
    {
        return $this->currency;
    }

    public function setColorCode(Request $request)
    {
        //return $request->color_code;
        if ($request->option == 'grade') {
            $grade = Grade::find($request->id);
            $grade->color_code = '#' . $request->color_code;

            if ($grade->save()) {
                return 'success';
            }
        } else {
            if ($request->option == 'subject') {
                $subject = Subject::find($request->id);
                $subject->color_code = '#' . $request->color_code;

                if ($subject->save()) {
                    return 'success';
                }
            }
        }
        return 'failed';
    }


    public function uploadFile($media, $file_name, $folder_key)
    {
        $folder = "clients/" . $folder_key;

        $media->storeAs($folder, $file_name, 'public');

        return $folder . '/' . $file_name;
    }
    public function auditTrailEvent($title, $action, $user = null)
    {

        // $user = $this->getUser();
        $users = User::whereHas('roles', function ($query) {
            $query->where('name', '=', 'super')
                ->orWhere('name', '=', 'admin'); // this is the role id inside of this callback
        })->get();
        if ($user != null) {
            $users = $users->push($user);
        }
        $notification = new AuditTrail($title, $action);
        // if ($class_teacher_id !== null) {
        //     $class = [ClassTeacher::find($class_teacher_id)];

        //     Notification::send($class, $notification);
        // }
        // broadcast(new AuditTrailEvent($title, $action));
        return Notification::send($users->unique(), $notification);
    }
}
