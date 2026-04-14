<?php
namespace Database\Seeders;

use App\Models\ApiClient;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ApiClientSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@cuponeshub.com')->first();

        $secret = 'demo_secret_' . Str::random(32);

        ApiClient::firstOrCreate(
            ['client_id' => 'ch_demo_client'],
            [
                'user_id' => $admin?->id,
                'name' => 'Cliente Demo',
                'client_secret' => bcrypt($secret),
                'allowed_ips' => null,
                'rate_limit_per_minute' => 60,
                'permissions' => ['validate', 'redeem', 'customers', 'legal'],
                'status' => 'active',
            ]
        );

        $this->command->info("API Client demo creado: client_id=ch_demo_client | secret={$secret}");
        $this->command->warn("IMPORTANTE: Guarde el secret, no se puede recuperar.");
    }
}