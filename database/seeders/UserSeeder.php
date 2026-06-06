<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Branch Manager
        User::create([
            'name'       => 'Sara Hassan',
            'email'      => 'manager@iti.test',
            'password'   => bcrypt('password'),
            'role'       => 'branch_manager',
            'expires_at' => now()->addYears(2),
        ]);

        // Track Admin (internal — gets salary + hourly)
        User::create([
            'name'              => 'Ahmed Nour',
            'email'             => 'trackadmin@iti.test',
            'password'          => bcrypt('password'),
            'role'              => 'track_admin',
            'compensation_type' => 'internal',
            'fixed_salary'      => 12000,
            'hourly_rate'       => 80,
            'expires_at'        => now()->addYear(),
        ]);

        // External Instructor
        User::create([
            'name'              => 'Mona Samir',
            'email'             => 'instructor@iti.test',
            'password'          => bcrypt('password'),
            'role'              => 'instructor',
            'compensation_type' => 'external',
            'hourly_rate'       => 150,
            'expires_at'        => now()->addMonths(3),
        ]);

        // Students (seed at least 5 to fill a lab group)
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name'       => "Student $i",
                'email'      => "student$i@iti.test",
                'password'   => bcrypt('password'),
                'role'       => 'student',
                'expires_at' => now()->addMonths(9),
            ]);
        }
    }
}
