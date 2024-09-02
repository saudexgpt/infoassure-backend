<?php

namespace App\Http\Resources;

use App\Models\ActivatedModule;
use App\Models\AvailableModule;
use App\Models\Client;
use App\Models\Partner;
use App\Models\Role;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $this_year = (int) date('Y', strtotime('now'));
        $permissions = [];
        if ($this->login_as !== NULL) {

            $role = Role::with('permissions')->where('name', $this->login_as)->first();
            $permissions = $role->permissions()->pluck('name');
        }
        $modules = [];
        $partner = '';
        if ($this->haRole('client')) {
            $client_id = $this->client_id;
            $client = Client::find($client_id);
            $partner_id = $client->partner_id;

            $projects = $this->projects()
                ->with('availableModule')
                ->where(['client_id' => $client_id, 'year' => $this_year])
                ->orderBy('id', 'DESC')
                ->get();
            foreach ($projects as $project) {

                $modules[] = $project->availableModule->slug;
            }
            $partner = Partner::find($partner_id);
        }
        if ($this->haRole('partner')) {
            $partner_id = $this->partner_id;
            $partner = Partner::with('activatedModules')->find($partner_id);
            $activated_modules = $partner->activatedModules;
            foreach ($activated_modules as $activated_module) {

                $modules[] = $activated_module->availableModule->slug;
            }
        }
        if ($this->haRole('super') || $this->haRole('admin')) {
            $modules = AvailableModule::pluck('slug');
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'password_status' => $this->password_status,
            'notifications' => [],
            'login_as' => $this->login_as,
            'partner_id' => $this->partner_id,
            'client_id' => $this->client_id,
            'modules' => $modules,
            // 'activity_logs' => $this->notifications()->orderBy('created_at', 'DESC')->get(),
            'roles' => [$this->login_as],
            'all_roles' => array_map(
                function ($role) {
                    return $role['name'];
                },
                $this->roles->toArray()
            ),
            // 'role' => 'admin',
            'permissions' => $permissions,
            // 'role' => 'admin',
            'all_permissions' => array_map(
                function ($permission) {
                    return $permission['name'];
                },
                $this->allPermissions()->toArray()
            ),
            'photo' => $this->photo,
            'logo' => $this->logo,
            'navbar_bg' => $this->navbar_bg,
            'sidebar_bg' => $this->sidebar_bg,

        ];
    }
}
