<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'System administrator with full access'
            ],
            [
                'name' => 'Customer',
                'slug' => 'customer',
                'description' => 'Regular customer with limited access'
            ]
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['slug' => $role['slug']],
                $role
            );
        }

        // Assign admin role to the test user
        $testUser = User::where('email', 'test@example.com')->first();
        if ($testUser) {
            $adminRole = Role::where('slug', 'admin')->first();
            $testUser->roles()->attach($adminRole);
        }
    }
} 