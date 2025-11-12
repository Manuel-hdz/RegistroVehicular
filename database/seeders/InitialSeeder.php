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
            ['username' => 'SuperAdmin'],
            ['name' => 'Super Admin', 'password' => Hash::make('SistemasCLF'), 'role' => 'superadmin', 'active' => true]
        );

        User::updateOrCreate(
            ['username' => 'admin'],
            ['name' => 'Administrador', 'password' => Hash::make('CLF.2025'), 'role' => 'admin', 'active' => true]
        );

        User::updateOrCreate(
            ['username' => 'usuario'],
            ['name' => 'Usuario', 'password' => Hash::make('123456'), 'role' => 'user', 'active' => true]
        );

        // Vehicles
        Vehicle::updateOrCreate(['plate' => 'ABC-123'], [
            'identifier' => 'CLF130',
            'model' => 'Toyota Hylux',
            'year' => 2024,
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
