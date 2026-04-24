<?php

namespace Database\Seeders;

use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\User;
use Illuminate\Database\Seeder;

class InitialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Priorities
        $priorities = [
            ['name' => 'Low', 'level' => 1, 'color' => '#22C55E'],
            ['name' => 'Medium', 'level' => 2, 'color' => '#F59E0B'],
            ['name' => 'High', 'level' => 3, 'color' => '#EF4444'],
        ];

        foreach ($priorities as $priority) {
            TicketPriority::query()->firstOrCreate(
                ['level' => $priority['level']],
                $priority,
            );
        }

        // 2. Categories
        $categories = [
            ['name' => 'Hardware', 'description' => 'Kendala perangkat keras (PC, laptop, printer, dll)'],
            ['name' => 'Software', 'description' => 'Kendala aplikasi atau software'],
            ['name' => 'Network', 'description' => 'Kendala jaringan internet atau LAN'],
            ['name' => 'Email', 'description' => 'Kendala email atau akun'],
            ['name' => 'Lainnya', 'description' => 'Kendala TI lainnya'],
        ];

        foreach ($categories as $category) {
            TicketCategory::query()->firstOrCreate(
                ['name' => $category['name']],
                $category,
            );
        }

        // 3. Super Admin User
        $superAdmin = User::query()->firstOrCreate(['username' => 'superadmin'], [
            'username' => 'superadmin',
            'name' => 'Super Administrator',
            'email' => 'admin@helpdesk.test',
            'password' => 'password',
            'is_active' => true,
        ]);

        if (! $superAdmin->hasRole('super_admin')) {
            $superAdmin->assignRole('super_admin');
        }
    }
}
