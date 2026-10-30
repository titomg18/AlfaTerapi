<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => '43499180504'],
            [
                'name'     => 'Tito Muhammad Gafa',
                'password' => 'tito1234',
            ]
        );
    }
}