<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User permissions
            [
                'name' => 'users.view',
                'description' => 'View users',
            ],
            [
                'name' => 'users.create',
                'description' => 'Create users',
            ],
            [
                'name' => 'users.edit',
                'description' => 'Edit users',
            ],
            [
                'name' => 'users.delete',
                'description' => 'Delete users',
            ],
            // Product permissions
            [
                'name' => 'products.view',
                'description' => 'View products',
            ],
            [
                'name' => 'products.create',
                'description' => 'Create products',
            ],
            [
                'name' => 'products.edit',
                'description' => 'Edit products',
            ],
            [
                'name' => 'products.delete',
                'description' => 'Delete products',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        $this->command->info('Permissions seeded successfully!');
    }
}
