<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'permissions' => $this->when($this->relationLoaded('permissions'), function () {
                return $this->permissions->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'display_name' => $permission->display_name,
                        'description' => $permission->description,
                    ];
                });
            }),
            'users_count' => $this->when($this->relationLoaded('users'), function () {
                return $this->users->count();
            }),
            'permissions_count' => $this->when($this->relationLoaded('permissions'), function () {
                return $this->permissions->count();
            })
        ];
    }
}
