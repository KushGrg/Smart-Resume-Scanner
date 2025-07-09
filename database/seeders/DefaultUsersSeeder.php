<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'previously_verified' => true,
        ]);
        $admin->assignRole('admin');

        // Create regular user
        $job_seeker = User::create([
            'name' => 'Regular User',
            'email' => 'user@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'previously_verified' => true,
        ]);
        $job_seeker->assignRole('job_seeker');

        $hr = User::create(attributes: [
            'name' => 'Hr',
            'email' => 'hr@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'previously_verified' => true,
        ]);
        $hr->assignRole('hr');

        //  $job_seeker = User::create(attributes: [
        //     'name' => 'Job_seeker',
        //     'email' => 'jobseeker@gmail.com',
        //     'password' => Hash::make('password'),
        //     'email_verified_at' => now(),
        //     'previously_verified' => true,
        // ]);
        // $job_seeker->assignRole('job_seeker');
    }
}
