<?php

namespace Database\Seeders;

use App\Models\Personnel;
use Illuminate\Database\Seeder;

class PersonnelSeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            [
                'employee_number' => 'RH-001',
                'first_name' => 'Luis',
                'last_name' => 'Hernandez',
                'middle_name' => 'Garcia',
                'department' => 'Operaciones',
                'position' => 'Operador',
                'hire_date' => '2024-01-15',
                'phone' => '4931000001',
                'email' => 'luis.hernandez@empresa.local',
                'curp' => 'HEGL900101HZSRRS01',
                'rfc' => 'HEGL900101AB1',
                'nss' => '12345678901',
                'address' => 'Col. Centro, Fresnillo, Zac.',
                'emergency_contact_name' => 'Maria Hernandez',
                'emergency_contact_phone' => '4931000101',
                'active' => true,
                'terminated_at' => null,
            ],
            [
                'employee_number' => 'RH-002',
                'first_name' => 'Carla',
                'last_name' => 'Lopez',
                'middle_name' => 'Ramirez',
                'department' => 'Mantenimiento',
                'position' => 'Mecanica',
                'hire_date' => '2023-09-03',
                'phone' => '4931000002',
                'email' => 'carla.lopez@empresa.local',
                'curp' => 'LORC910205MZSPRM02',
                'rfc' => 'LORC910205AB2',
                'nss' => '12345678902',
                'address' => 'Col. Arboledas, Fresnillo, Zac.',
                'emergency_contact_name' => 'Diego Lopez',
                'emergency_contact_phone' => '4931000102',
                'active' => true,
                'terminated_at' => null,
            ],
            [
                'employee_number' => 'RH-003',
                'first_name' => 'Jorge',
                'last_name' => 'Santos',
                'middle_name' => 'Vega',
                'department' => 'Almacen',
                'position' => 'Auxiliar de almacen',
                'hire_date' => '2022-05-12',
                'phone' => '4931000003',
                'email' => 'jorge.santos@empresa.local',
                'curp' => 'SAVJ920330HZSNGR03',
                'rfc' => 'SAVJ920330AB3',
                'nss' => '12345678903',
                'address' => 'Col. Industrial, Fresnillo, Zac.',
                'emergency_contact_name' => 'Ana Vega',
                'emergency_contact_phone' => '4931000103',
                'active' => false,
                'terminated_at' => '2026-02-20',
            ],
            [
                'employee_number' => 'RH-004',
                'first_name' => 'Paola',
                'last_name' => 'Mendez',
                'middle_name' => 'Ruiz',
                'department' => 'Recursos Humanos',
                'position' => 'Generalista RH',
                'hire_date' => '2025-02-11',
                'phone' => '4931000004',
                'email' => 'paola.mendez@empresa.local',
                'curp' => null,
                'rfc' => null,
                'nss' => null,
                'address' => 'Col. Benito Juarez, Fresnillo, Zac.',
                'emergency_contact_name' => 'Daniel Mendez',
                'emergency_contact_phone' => '4931000104',
                'active' => true,
                'terminated_at' => null,
            ],
        ];

        foreach ($records as $record) {
            Personnel::updateOrCreate(
                ['employee_number' => $record['employee_number']],
                $record
            );
        }
    }
}
