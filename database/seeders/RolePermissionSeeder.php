<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $adminRole = Role::where('name', 'Admin')->first();
        $editorRole = Role::where('name', 'Editor')->first();
        $viewerRole = Role::where('name', 'Viewer')->first();

        // Get all permissions
        $allPermissions = Permission::all();

        // Admin gets all permissions
        $adminRole->permissions()->attach($allPermissions->pluck('id'));

        // Editor gets view, create, edit permissions (no delete)
        $editorPermissions = Permission::whereIn('name', [
            'users.view', 'users.create',
            'products.view', 'products.create', 'products.edit'
        ])->get();
        $editorRole->permissions()->attach($editorPermissions->pluck('id'));

        // Viewer gets only view permissions
        $viewerPermissions = Permission::whereIn('name', [
            'users.view', 'products.view'
        ])->get();
        $viewerRole->permissions()->attach($viewerPermissions->pluck('id'));

        $this->command->info('Role permissions attached successfully!');
    }
}
