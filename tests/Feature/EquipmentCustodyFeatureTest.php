<?php

namespace Tests\Feature;

use App\Mail\EquipmentCustodyAssignedMail;
use App\Models\EquipmentCustody;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EquipmentCustodyFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_systems_user_can_access_equipment_screen_and_register_custody(): void
    {
        Storage::fake('local');
        Mail::fake();

        $systemsUser = User::create([
            'name' => 'Sistemas',
            'username' => 'sistemas-equipos',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'sistemas',
            'active' => true,
        ]);

        $this->actingAs($systemsUser)
            ->get(route('equipment-custodies.index'))
            ->assertOk();

        $response = $this->actingAs($systemsUser)
            ->post(route('equipment-custodies.store'), [
                'equipment_type' => 'laptop',
                'brand' => 'Dell',
                'model' => 'Latitude 5530',
                'serial_number' => 'SN-123',
                'accessories' => 'Cargador y mochila',
                'assigned_at' => '2026-04-09',
                'responsible' => 'Juan Perez',
                'notification_email' => 'juan@example.com',
                'physical_record' => UploadedFile::fake()->create('resguardo.pdf', 200, 'application/pdf'),
            ]);

        $response->assertRedirect(route('equipment-custodies.index'));

        $this->assertDatabaseHas('equipment_custodies', [
            'equipment_type' => 'laptop',
            'brand' => 'Dell',
            'model' => 'Latitude 5530',
            'serial_number' => 'SN-123',
            'responsible' => 'Juan Perez',
            'registered_by_user_id' => $systemsUser->id,
            'registered_by_username' => 'sistemas-equipos',
            'notification_email' => 'juan@example.com',
        ]);

        $record = EquipmentCustody::query()->firstOrFail();
        $this->assertNotNull($record->physical_record_path);
        Storage::disk('local')->assertExists($record->physical_record_path);
        Mail::assertSent(EquipmentCustodyAssignedMail::class, function (EquipmentCustodyAssignedMail $mail) use ($record): bool {
            return $mail->hasTo('juan@example.com')
                && $mail->equipmentCustody->is($record);
        });

        $this->actingAs($systemsUser)
            ->get(route('equipment-custodies.physical-record', $record))
            ->assertOk();
    }

    public function test_non_systems_user_cannot_access_equipment_screen_even_with_configuration_permission(): void
    {
        $nonSystemsUser = User::create([
            'name' => 'Config Compras',
            'username' => 'config-compras',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'compras',
            'module_permissions' => ['configuracion'],
            'active' => true,
        ]);

        $this->actingAs($nonSystemsUser)
            ->get(route('equipment-custodies.index'))
            ->assertForbidden();
    }

    public function test_systems_user_does_not_send_mail_if_notification_email_is_empty(): void
    {
        Mail::fake();

        $systemsUser = User::create([
            'name' => 'Sistemas',
            'username' => 'sistemas-sin-correo',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'sistemas',
            'active' => true,
        ]);

        $response = $this->actingAs($systemsUser)
            ->post(route('equipment-custodies.store'), [
                'equipment_type' => 'laptop',
                'brand' => 'Lenovo',
                'model' => 'ThinkPad',
                'serial_number' => 'SN-EMPTY-001',
                'accessories' => 'Cargador',
                'assigned_at' => '2026-04-10',
                'responsible' => 'Ana Gomez',
                'notification_email' => '',
            ]);

        $response->assertRedirect(route('equipment-custodies.index'));
        Mail::assertNothingSent();
    }

    public function test_systems_user_can_cancel_custody_and_system_tracks_user_and_date(): void
    {
        $systemsUser = User::create([
            'name' => 'Sistemas',
            'username' => 'sistemas-cancel',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'sistemas',
            'active' => true,
        ]);

        $record = EquipmentCustody::create([
            'equipment_type' => 'laptop',
            'brand' => 'HP',
            'model' => 'ProBook',
            'serial_number' => 'SER-01',
            'accessories' => 'Cargador',
            'assigned_at' => '2026-04-09',
            'responsible' => 'Maria Lopez',
            'registered_by_user_id' => $systemsUser->id,
            'registered_by_username' => $systemsUser->username,
        ]);

        $response = $this->actingAs($systemsUser)
            ->patch(route('equipment-custodies.cancel', $record));

        $response->assertRedirect(route('equipment-custodies.index'));

        $record->refresh();
        $this->assertNotNull($record->cancelled_at);
        $this->assertSame($systemsUser->id, $record->cancelled_by_user_id);
    }

    public function test_systems_user_can_export_excel_per_equipment_type(): void
    {
        $systemsUser = User::create([
            'name' => 'Sistemas',
            'username' => 'sistemas-export',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'sistemas',
            'active' => true,
        ]);

        EquipmentCustody::create([
            'equipment_type' => 'celular',
            'brand' => 'Samsung',
            'model' => 'A55',
            'serial_number' => 'CEL-01',
            'assigned_at' => '2026-04-09',
            'responsible' => 'Luis Garcia',
            'registered_by_user_id' => $systemsUser->id,
            'registered_by_username' => $systemsUser->username,
        ]);

        $response = $this->actingAs($systemsUser)
            ->get(route('equipment-custodies.export.excel', ['type' => 'celular']));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8');
        $this->assertStringContainsString('Resguardos de Celulares', $response->streamedContent());
    }
}
