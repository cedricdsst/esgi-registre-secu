<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions CRUD pour les entités principales
        $crudPermissions = [
            // Sites
            'view_sites',
            'create_sites',
            'edit_sites',
            'delete_sites',
            
            // Bâtiments
            'view_batiments',
            'create_batiments',
            'edit_batiments',
            'delete_batiments',
            
            // Niveaux
            'view_niveaux',
            'create_niveaux',
            'edit_niveaux',
            'delete_niveaux',
            
            // Parties
            'view_parties',
            'create_parties',
            'edit_parties',
            'delete_parties',
            
            // Droits d'accès
            'manage_permissions',
            'view_all_data',
            
            // Utilisateurs
            'manage_users',
            'view_users',
        ];

        // Permissions métier spécifiques AXIGNIS
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

        // Permissions pour l'application Base d'équipements
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

        $allPermissions = array_merge($crudPermissions, $securityPermissions, $equipmentPermissions, $adminPermissions);

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Créer les rôles
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $clientAdmin = Role::firstOrCreate(['name' => 'client-admin']);
        $user = Role::firstOrCreate(['name' => 'user']);
        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $userEntreprise = Role::firstOrCreate(['name' => 'user-entreprise']);
        $userIntervenant = Role::firstOrCreate(['name' => 'user-intervenant']);

        // Nettoyer les permissions existantes des rôles
        $superAdmin->permissions()->detach();
        $admin->permissions()->detach();
        $clientAdmin->permissions()->detach();
        $user->permissions()->detach();
        $viewer->permissions()->detach();
        $userEntreprise->permissions()->detach();
        $userIntervenant->permissions()->detach();

        // Assigner toutes les permissions au super-admin
        $superAdmin->givePermissionTo(Permission::all());

        // Permissions pour l'admin AXIGNIS
        $admin->givePermissionTo([
            // CRUD de base
            'view_sites',
            'create_sites',
            'edit_sites',
            'view_batiments',
            'create_batiments',
            'edit_batiments',
            'view_niveaux',
            'create_niveaux',
            'edit_niveaux',
            'view_parties',
            'create_parties',
            'edit_parties',
            'view_users',
            
            // Permissions métier
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

        // Permissions pour l'admin client
        $clientAdmin->givePermissionTo([
            // CRUD limité
            'view_sites',
            'view_batiments',
            'view_niveaux',
            'view_parties',
            'edit_sites',
            'edit_batiments',
            'edit_niveaux',
            'edit_parties',
            
            // Permissions métier
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

        // Permissions pour l'utilisateur standard
        $user->givePermissionTo([
            // CRUD de base
            'view_sites',
            'view_batiments',
            'view_niveaux',
            'view_parties',
            
            // Permissions métier
            'security_register.view',
            'security_register.create',
            'security_register.edit',
            'sites.manage',
            'buildings.manage',
            'parts.manage',
            'interventions.manage',
            'reports.manage',
        ]);

        // Permissions pour le viewer (lecture seule)
        $viewer->givePermissionTo([
            'view_sites',
            'view_batiments',
            'view_niveaux',
            'view_parties',
            'security_register.view',
            'equipment_base.view',
        ]);

        // Permissions pour l'utilisateur entreprise
        $userEntreprise->givePermissionTo([
            // CRUD de base
            'view_sites',
            'view_batiments',
            'view_niveaux',
            'view_parties',
            'edit_sites',
            'edit_batiments',
            'edit_niveaux',
            'edit_parties',
            
            // Permissions métier
            'security_register.view',
            'security_register.create',
            'security_register.edit',
            'security_register.export',
            'sites.manage',
            'buildings.manage',
            'parts.manage',
            'interventions.manage',
            'reports.manage',
            'equipment_base.view',
            'equipment_base.create',
            'equipment_base.edit',
        ]);

        // Permissions pour l'utilisateur intervenant
        $userIntervenant->givePermissionTo([
            // CRUD limité (pas de création/suppression)
            'view_sites',
            'view_batiments',
            'view_niveaux',
            'view_parties',
            
            // Permissions métier limitées
            'security_register.view',
            'security_register.create',
            'security_register.edit',
            'interventions.manage',
            'reports.manage',
            'equipment_base.view',
        ]);

        $this->command->info('Rôles et permissions créés avec succès !');
        $this->command->info('- 7 rôles créés : super-admin, admin, client-admin, user, viewer, user-entreprise, user-intervenant');
        $this->command->info('- ' . count($allPermissions) . ' permissions créées (CRUD + métier)');
    }
}
