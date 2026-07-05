<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = ['admin', 'manager', 'serveur'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        $accounts = [
            ['email' => 'test@example.com', 'name' => 'Test User', 'password' => 'password', 'role' => 'admin'],
            ['email' => 'admin@example.com', 'name' => 'Admin User', 'password' => 'password123', 'role' => 'admin'],
            ['email' => 'manager@test.com', 'name' => 'Manager User', 'password' => 'motdepasse123', 'role' => 'manager'],
            ['email' => 'serveur@essai.com', 'name' => 'Serveur User', 'password' => 'code123', 'role' => 'serveur'],
        ];

        foreach ($accounts as $account) {
            $user = User::firstOrCreate(
                ['email' => $account['email']],
                [
                    'name'     => $account['name'],
                    'password' => Hash::make($account['password']),
                ]
            );

            $user->syncRoles($account['role']);

            $this->command->info("✅  Utilisateur prêt : {$account['email']} / {$account['password']}");
        }

        // Données de démonstration du module restaurant
        $this->call(RestaurantSeeder::class);
    }
}
