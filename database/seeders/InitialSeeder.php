<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialSeeder extends Seeder
{
    public function run(): void
    {
        // Users
        User::updateOrCreate(
            ['email' => 'guard@example.com'],
            ['name' => 'Vigilante', 'password' => Hash::make('password'), 'role' => 'guard']
        );

        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Administrador', 'password' => Hash::make('password'), 'role' => 'admin']
        );

        // Vehicles
        Vehicle::updateOrCreate(['plate' => 'ABC-123'], [
            'identifier' => 'Pickup 4x4',
            'model' => 'Nissan NP300',
            'year' => 2020,
            'active' => true,
        ]);

        Vehicle::updateOrCreate(['plate' => 'XYZ-987'], [
            'identifier' => 'Sedán',
            'model' => 'VW Vento',
            'year' => 2019,
            'active' => true,
        ]);

        // Drivers
        Driver::updateOrCreate(['name' => 'Juan Pérez'], [
            'employee_number' => 'E001',
            'license' => 'A1',
            'active' => true,
        ]);

        Driver::updateOrCreate(['name' => 'María López'], [
            'employee_number' => 'E002',
            'license' => 'A2',
            'active' => true,
        ]);
    }
}
