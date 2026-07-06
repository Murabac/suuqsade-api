<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::query()->updateOrCreate(
            ['email' => 'admin@suuqsade.com'],
            [
                'name' => 'Suuqsade Admin',
                'password' => 'password',
                'role' => 'super_admin',
            ],
        );
    }
}
