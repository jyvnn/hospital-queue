<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class AdminSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create an admin user if it doesn't already exist
        $email = env('ADMIN_EMAIL', 'admin@local.test');
        $password = env('ADMIN_PASSWORD', 'AdminPass123');

        User::firstOrCreate(
            ['email' => $email],
            ['name' => 'Administrator', 'password' => bcrypt($password), 'is_admin' => true]
        );
    }
}
