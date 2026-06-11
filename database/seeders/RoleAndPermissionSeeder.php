<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run()
    {
        // Nettoyer le cache des rôles et permissions Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Créer les rôles
        $adminRole   = Role::create(['name' => 'Admin']);
        $managerRole = Role::create(['name' => 'Manager']);
        $memberRole  = Role::create(['name' => 'Membre']);

        // 2. Créer des permissions de base à titre d'exemple
        Permission::create(['name' => 'manage projects']);
        Permission::create(['name' => 'edit tasks']);

        // Associer des permissions aux rôles si nécessaire
        $adminRole->givePermissionTo(Permission::all());
        $managerRole->givePermissionTo(['manage projects', 'edit tasks']);
    }
}
