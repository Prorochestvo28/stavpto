<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            TestUsersSeeder::class,
        ]);

        User::query()->firstOrCreate(
            ['email' => 'admin@stav.ltd'],
            [
                'name' => 'admin',
                'password' => Hash::make('password'),
                'full_name' => 'Администратор',
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $this->call([
            TestDocumentsSeeder::class,
            TestApprovalsSeeder::class,
        ]);
    }
}
