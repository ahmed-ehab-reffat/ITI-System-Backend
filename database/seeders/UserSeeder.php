<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Branch Manager ──────────────────────────────────────────────────────
        User::create([
            'name'       => 'Sara Hassan',
            'email'      => 'manager@iti.test',
            'password'   => bcrypt('password'),
            'role'       => 'branch_manager',
            'expires_at' => now()->addYears(2),
        ]);

        // ── Track Admins (internal — salary + hourly) ───────────────────────────
        User::create([
            'name'              => 'Ahmed Nour',
            'email'             => 'admin.web@iti.test',
            'password'          => bcrypt('password'),
            'role'              => 'track_admin',
            'compensation_type' => 'internal',
            'fixed_salary'      => 15000.00,
            'hourly_rate'       => 100.00,
            'expires_at'        => now()->addYears(1),
        ]);

        User::create([
            'name'              => 'Heba Mansour',
            'email'             => 'admin.mobile@iti.test',
            'password'          => bcrypt('password'),
            'role'              => 'track_admin',
            'compensation_type' => 'internal',
            'fixed_salary'      => 15000.00,
            'hourly_rate'       => 100.00,
            'expires_at'        => now()->addYears(1),
        ]);

        // ── Instructors ─────────────────────────────────────────────────────────
        // Web Dev: 1 lecture instructor + 3 lab instructors
        // Mobile Dev: 1 lecture instructor + 2 lab instructors

        $instructors = [
            // Web Dev
            ['name' => 'Dr. Hossam Eldin',    'email' => 'hossam@iti.test',   'type' => 'internal', 'salary' => 12000, 'rate' => 80],
            ['name' => 'Eng. Tarek Naguib',   'email' => 'tarek@iti.test',    'type' => 'external', 'salary' => null,  'rate' => 150],
            ['name' => 'Eng. Rania Kamel',    'email' => 'rania@iti.test',    'type' => 'external', 'salary' => null,  'rate' => 150],
            ['name' => 'Eng. Sherif Abdallah','email' => 'sherif@iti.test',   'type' => 'external', 'salary' => null,  'rate' => 150],
            // Mobile Dev
            ['name' => 'Dr. Amal Farouk',     'email' => 'amal@iti.test',     'type' => 'internal', 'salary' => 12000, 'rate' => 80],
            ['name' => 'Eng. Nadia Fouad',    'email' => 'nadia@iti.test',    'type' => 'external', 'salary' => null,  'rate' => 150],
            ['name' => 'Eng. Bassem Wahid',   'email' => 'bassem@iti.test',   'type' => 'external', 'salary' => null,  'rate' => 150],
        ];

        foreach ($instructors as $ins) {
            User::create([
                'name'              => $ins['name'],
                'email'             => $ins['email'],
                'password'          => bcrypt('password'),
                'role'              => 'instructor',
                'compensation_type' => $ins['type'],
                'fixed_salary'      => $ins['salary'],
                'hourly_rate'       => $ins['rate'],
                'expires_at'        => now()->addMonths(8),
            ]);
        }

        // ── Students ────────────────────────────────────────────────────────────
        // 30 for Web Dev (3 groups × 10), 16 for Mobile Dev (2 groups × 8)
        $maleFirst   = ['Ahmed','Mohamed','Omar','Khaled','Youssef','Amr','Hassan','Ali','Mahmoud','Mostafa',
                         'Karim','Tarek','Sherif','Bassem','Ibrahim','Marwan','Ziad','Adham','Samir','Nabil'];
        $femaleFirst = ['Sara','Fatima','Nour','Mariam','Dina','Rania','Heba','Aya','Mona','Yasmine',
                         'Salma','Nadia','Amal','Hana','Layla','Doaa','Ghada','Eman','Reem','Shaimaa'];
        $family      = ['Mohamed','Ali','Hassan','Ibrahim','Mahmoud','Salem','Khalil','Sayed','Mostafa',
                         'Ahmed','Kamal','Naguib','Farouk','Wahid','Mansour','Abdallah','Fouad','Kamel','Zaki','Fathy'];

        $allFirst = array_merge($maleFirst, $femaleFirst);
        shuffle($allFirst);
        shuffle($family);

        for ($i = 1; $i <= 46; $i++) {
            $firstName = $allFirst[($i - 1) % count($allFirst)];
            $lastName  = $family[($i - 1) % count($family)];
            $track     = $i <= 30 ? 'web' : 'mobile';

            User::create([
                'name'       => "$firstName $lastName",
                'email'      => "student{$i}@iti.test",
                'password'   => bcrypt('password'),
                'role'       => 'student',
                'expires_at' => now()->addMonths(9),
            ]);
        }
    }
}