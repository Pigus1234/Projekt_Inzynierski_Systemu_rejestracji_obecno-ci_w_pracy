<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdministratorUserSeeder extends Seeder
{
    public function run(): void
    {
        $administratorName = (string) config('administrator_account.name');
        $administratorEmail = (string) config('administrator_account.email');
        $administratorPassword = (string) config('administrator_account.password');

        if ($administratorName === '' || $administratorEmail === '' || $administratorPassword === '') {
            throw new RuntimeException('Brak ADMINISTRATOR_USER_NAME / ADMINISTRATOR_USER_EMAIL / ADMINISTRATOR_USER_PASSWORD w konfiguracji.');
        }

        $administratorRole = Role::where('name', 'Administrator')->first();

        if ($administratorRole === null) {
            throw new RuntimeException('Brak roli Administrator w tabeli roles.');
        }

        User::updateOrCreate(
            ['email' => $administratorEmail],
            [
                'name' => $administratorName,
                'password' => Hash::make($administratorPassword),
                'role_id' => $administratorRole->id,
                'email_verified_at' => now(),
            ]
        );
    }
}
