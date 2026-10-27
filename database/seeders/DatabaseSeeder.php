<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
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
        /*
         * Demo accounts, one per role. They share the password "password",
         * which is also shown on the local sign-in page.
         */
        $accounts = [
            ['name' => 'Администратор', 'email' => 'admin@cinema.test', 'role' => UserRole::Admin],
            ['name' => 'Менеджер', 'email' => 'manager@cinema.test', 'role' => UserRole::Manager],
            ['name' => 'Покупатель', 'email' => 'buyer@cinema.test', 'role' => UserRole::Buyer],
        ];

        foreach ($accounts as $account) {
            User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'role' => $account['role'],
                    'password' => 'password',
                ],
            );
        }
    }
}
