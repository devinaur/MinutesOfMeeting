<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::updateOrCreate(
            ['email' => 'devina@yusen.co.id'],
            [
                'name' => 'Devina Rahma',
                'username' => 'devina',
                'department' => 'IT',
                'password' => bcrypt('password123'),
                'is_active' => true,
            ]
        );

        \App\Models\User::updateOrCreate(
            ['email' => 'admin@yusen.co.id'],
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'department' => 'IT',
                'password' => bcrypt('password123'),
                'is_active' => true,
            ]
        );
    }
}
