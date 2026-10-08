<?php

namespace Database\Seeders;

use App\Models\CostCenter;
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
        $superAdmin = User::updateOrCreate(
            ['username' => 'SuperAdmin'],
            ['name' => 'Super Admin', 'password' => Hash::make('SistemasCLF'), 'role' => 'superadmin', 'department' => 'sistemas', 'active' => true]
        );

        $administrator = User::updateOrCreate(
            ['username' => 'admin'],
            ['name' => 'Administrador', 'password' => Hash::make('CLF.2025'), 'role' => 'admin', 'department' => 'gerencia', 'active' => true]
        );

        $warehouseUser = User::updateOrCreate(
            ['username' => 'usuario'],
            ['name' => 'Usuario', 'password' => Hash::make('123456'), 'role' => 'user', 'department' => 'almacen', 'active' => true]
        );

        $defaultCostCenterId = CostCenter::where('code', 'INDIRECTOS-MATRIZ')->value('id');
        if ($defaultCostCenterId) {
            foreach ([$superAdmin, $administrator, $warehouseUser] as $user) {
                $user->costCenters()->syncWithoutDetaching([$defaultCostCenterId]);
            }
        }

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
            'license' => 'A',
            'license_expires_at' => now()->addYears(2)->toDateString(),
            'active' => true,
        ]);

        Driver::updateOrCreate(['name' => 'María López'], [
            'employee_number' => 'E002',
            'license' => 'B',
            'license_expires_at' => now()->addYears(2)->toDateString(),
            'active' => true,
        ]);
    }
}
