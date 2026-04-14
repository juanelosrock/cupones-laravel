<?php
namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@cuponeshub.com'],
            [
                'name' => 'Administrador',
                'password' => bcrypt('Admin@2026!'),
                'phone' => '+573000000000',
                'document_type' => 'CC',
                'document_number' => '1000000001',
                'status' => 'active',
            ]
        );

        $admin->assignRole('super-admin');

        UserProfile::firstOrCreate(
            ['user_id' => $admin->id],
            ['department' => 'TI', 'position' => 'Super Administrador']
        );

        $this->command->info("Admin creado: admin@cuponeshub.com / Admin@2026!");
    }
}