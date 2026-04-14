<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Usuarios
            'users.view', 'users.create', 'users.edit', 'users.delete',
            // Roles
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            // Geografía
            'geography.view', 'geography.manage',
            // Campañas
            'campaigns.view', 'campaigns.create', 'campaigns.edit', 'campaigns.delete',
            // Cupones
            'coupons.view', 'coupons.create', 'coupons.edit', 'coupons.cancel',
            'coupons.redeem', 'coupons.reverse',
            // Clientes
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
            // Documentos legales
            'legal.view', 'legal.create', 'legal.edit', 'legal.publish',
            // SMS
            'sms.view', 'sms.create', 'sms.send',
            // API Clients
            'api_clients.view', 'api_clients.create', 'api_clients.revoke',
            // Auditoría
            'audit.view',
            // Dashboard
            'dashboard.view',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // SUPER ADMIN - todos los permisos
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // ADMIN - gestión operativa completa
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'users.view', 'users.create', 'users.edit',
            'geography.view', 'geography.manage',
            'campaigns.view', 'campaigns.create', 'campaigns.edit', 'campaigns.delete',
            'coupons.view', 'coupons.create', 'coupons.edit', 'coupons.cancel', 'coupons.reverse',
            'customers.view', 'customers.create', 'customers.edit',
            'legal.view', 'legal.create', 'legal.edit', 'legal.publish',
            'sms.view', 'sms.create', 'sms.send',
            'api_clients.view', 'api_clients.create',
            'audit.view', 'dashboard.view',
        ]);

        // OPERADOR - operación diaria
        $operator = Role::firstOrCreate(['name' => 'operador', 'guard_name' => 'web']);
        $operator->syncPermissions([
            'campaigns.view', 'coupons.view', 'coupons.redeem',
            'customers.view', 'customers.create', 'customers.edit',
            'dashboard.view',
        ]);

        // ANALISTA - solo consulta
        $analyst = Role::firstOrCreate(['name' => 'analista', 'guard_name' => 'web']);
        $analyst->syncPermissions([
            'campaigns.view', 'coupons.view',
            'customers.view', 'audit.view', 'dashboard.view',
        ]);

        $this->command->info('Roles y permisos creados correctamente.');
    }
}