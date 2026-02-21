<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Services;

class ServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'Corte',
                'description' => 'Corte de cabello profesional',
                'price' => 120,
                'duration_minutes' => 35,
                'is_active' => true,
            ],
            [
                'name' => 'Barba',
                'description' => 'Perfilado y arreglo de barba',
                'price' => 120,
                'duration_minutes' => 25,
                'is_active' => true,
            ],
            [
                'name' => 'Corte y barba',
                'description' => 'Servicio completo de corte y barba',
                'price' => 240,
                'duration_minutes' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'Limpieza facial profunda',
                'description' => 'Tratamiento facial profundo',
                'price' => 350,
                'duration_minutes' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Exfoliación',
                'description' => 'Exfoliación facial rápida',
                'price' => 60,
                'duration_minutes' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Mascarilla negra',
                'description' => 'Mascarilla para eliminar puntos negros',
                'price' => 60,
                'duration_minutes' => 15,
                'is_active' => true,
            ],
            [
                'name' => 'Tinte platinado en toda la cabeza',
                'description' => 'Coloración platinada completa',
                'price' => 700,
                'duration_minutes' => 120,
                'is_active' => true,
            ],
            [
                'name' => 'Mechas platinadas o de color',
                'description' => 'Mechas personalizadas platinadas o de color',
                'price' => 550,
                'duration_minutes' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'Alisado express',
                'description' => 'Alisado rápido',
                'price' => 120,
                'duration_minutes' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Keratina cabello de hombre',
                'description' => 'Tratamiento de keratina',
                'price' => 550,
                'duration_minutes' => 120,
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            Services::create($service);
        }
    }
}
