<?php
namespace Database\Seeders;

use App\Models\Country;
use App\Models\Department;
use App\Models\City;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class GeographySeeder extends Seeder
{
    public function run(): void
    {
        $colombia = Country::firstOrCreate(
            ['code' => 'CO'],
            ['name' => 'Colombia', 'phone_code' => '+57', 'is_active' => true]
        );

        $departments = [
            ['name' => 'Cundinamarca', 'code' => 'CUN'],
            ['name' => 'Antioquia', 'code' => 'ANT'],
            ['name' => 'Valle del Cauca', 'code' => 'VAC'],
            ['name' => 'Atlántico', 'code' => 'ATL'],
            ['name' => 'Santander', 'code' => 'SAN'],
            ['name' => 'Bolívar', 'code' => 'BOL'],
            ['name' => 'Nariño', 'code' => 'NAR'],
            ['name' => 'Córdoba', 'code' => 'COR'],
        ];

        $cities = [
            'CUN' => [
                ['name' => 'Bogotá D.C.', 'code' => 'BOG'],
                ['name' => 'Soacha', 'code' => 'SOA'],
                ['name' => 'Chía', 'code' => 'CHI'],
            ],
            'ANT' => [
                ['name' => 'Medellín', 'code' => 'MED'],
                ['name' => 'Bello', 'code' => 'BEL'],
                ['name' => 'Envigado', 'code' => 'ENV'],
            ],
            'VAC' => [
                ['name' => 'Cali', 'code' => 'CAL'],
                ['name' => 'Palmira', 'code' => 'PAL'],
            ],
            'ATL' => [
                ['name' => 'Barranquilla', 'code' => 'BAQ'],
                ['name' => 'Soledad', 'code' => 'SOL'],
            ],
            'SAN' => [
                ['name' => 'Bucaramanga', 'code' => 'BGA'],
                ['name' => 'Floridablanca', 'code' => 'FLO'],
            ],
            'BOL' => [
                ['name' => 'Cartagena', 'code' => 'CTG'],
            ],
            'NAR' => [
                ['name' => 'Pasto', 'code' => 'PST'],
            ],
            'COR' => [
                ['name' => 'Montería', 'code' => 'MTR'],
            ],
        ];

        foreach ($departments as $deptData) {
            $dept = Department::firstOrCreate(
                ['country_id' => $colombia->id, 'code' => $deptData['code']],
                ['name' => $deptData['name'], 'is_active' => true]
            );

            foreach ($cities[$deptData['code']] ?? [] as $cityData) {
                $city = City::firstOrCreate(
                    ['department_id' => $dept->id, 'code' => $cityData['code']],
                    ['name' => $cityData['name'], 'is_active' => true]
                );

                // Zonas por defecto para Bogotá
                if ($cityData['code'] === 'BOG') {
                    $zones = ['Norte', 'Sur', 'Centro', 'Oriente', 'Occidente', 'Usaquén', 'Chapinero', 'Kennedy', 'Suba'];
                    foreach ($zones as $zoneName) {
                        Zone::firstOrCreate(
                            ['city_id' => $city->id, 'name' => $zoneName],
                            ['is_active' => true]
                        );
                    }
                }
            }
        }

        $this->command->info('Datos geográficos de Colombia cargados.');
    }
}