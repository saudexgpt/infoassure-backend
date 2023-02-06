<?php

namespace App\Http\Resources;

use App\Models\SSession;
use App\Models\Term;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'password_status' => $this->password_status,
            'notifications' => [],
            // 'activity_logs' => $this->notifications()->orderBy('created_at', 'DESC')->get(),
            'roles' => array_map(
                function ($role) {
                    return $role['name'];
                },
                $this->roles->toArray()
            ),
            // 'role' => 'admin',
            'permissions' => array_map(
                function ($permission) {
                    return $permission['name'];
                },
                $this->allPermissions()->toArray()
            ),
            'photo' => $this->photo
        ];
    }
}
