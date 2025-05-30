<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Créer les permissions pour l'application Registre de sécurité
        $securityPermissions = [
            'security_register.view',
            'security_register.create',
            'security_register.edit',
            'security_register.delete',
            'security_register.export',
            'sites.manage',
            'buildings.manage',
            'parts.manage',
            'interventions.manage',
            'reports.manage',
        ];

        // Créer les permissions pour l'application Base d'équipements
        $equipmentPermissions = [
            'equipment_base.view',
            'equipment_base.create',
            'equipment_base.edit',
            'equipment_base.delete',
            'products.manage',
            'documents.manage',
            'brands.manage',
        ];

        // Permissions administratives
        $adminPermissions = [
            'users.manage',
            'clients.manage',
            'settings.manage',
        ];

        $allPermissions = array_merge($securityPermissions, $equipmentPermissions, $adminPermissions);

        foreach ($allPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Créer les rôles
        $superAdmin = Role::create(['name' => 'super-admin']);
        $admin = Role::create(['name' => 'admin']);
        $clientAdmin = Role::create(['name' => 'client-admin']);
        $user = Role::create(['name' => 'user']);
        $viewer = Role::create(['name' => 'viewer']);

        // Attribuer les permissions aux rôles
        $superAdmin->givePermissionTo(Permission::all());
        
        $admin->givePermissionTo([
            'security_register.view',
            'security_register.create',
            'security_register.edit',
            'security_register.export',
            'equipment_base.view',
            'equipment_base.create',
            'equipment_base.edit',
            'sites.manage',
            'buildings.manage',
            'parts.manage',
            'interventions.manage',
            'reports.manage',
            'products.manage',
            'documents.manage',
        ]);

        $clientAdmin->givePermissionTo([
            'security_register.view',
            'security_register.create',
            'security_register.edit',
            'security_register.export',
            'sites.manage',
            'buildings.manage',
            'parts.manage',
            'interventions.manage',
            'reports.manage',
            'users.manage', // Peut gérer les utilisateurs de son client
        ]);

        $user->givePermissionTo([
            'security_register.view',
            'security_register.create',
            'security_register.edit',
            'sites.manage',
            'buildings.manage',
            'parts.manage',
            'interventions.manage',
            'reports.manage',
        ]);

        $viewer->givePermissionTo([
            'security_register.view',
            'equipment_base.view',
        ]);
    }
}