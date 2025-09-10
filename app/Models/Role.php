<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    /**
     * Get the users for the role.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user');
    }

    /**
     * Get the permissions for the role.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    /**
     * Check if role has a specific permission
     */
    public function hasPermission($permission)
    {
        return $this->permissions()->where('name', $permission)->exists();
    }
}
