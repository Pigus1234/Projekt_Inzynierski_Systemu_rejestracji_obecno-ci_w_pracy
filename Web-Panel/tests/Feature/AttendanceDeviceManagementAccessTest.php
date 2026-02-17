<?php

namespace Tests\Feature;

use App\Models\AttendanceDevice;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDeviceManagementAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_opening_attendance_devices_index(): void
    {
        $response = $this->get(route('administrator.attendance-devices.index'));

        $response->assertRedirectToRoute('login');
        $this->assertGuest();
    }

    public function test_non_administrator_is_forbidden_when_opening_attendance_devices_index(): void
    {
        $user = $this->createStandardUser();

        $response = $this->actingAs($user)->get(route('administrator.attendance-devices.index'));

        $response->assertForbidden();
    }

    public function test_administrator_can_open_index_and_sees_status_badges_for_devices(): void
    {
        $administrator = $this->createAdministratorUser();

        AttendanceDevice::query()->create([
            'name' => 'RFID-ONLINE',
            'api_token_hash' => hash('sha256', 'token-1'),
            'is_active' => true,
            'last_seen_at' => now()->subMinute(),
        ]);

        AttendanceDevice::query()->create([
            'name' => 'RFID-OFFLINE',
            'api_token_hash' => hash('sha256', 'token-2'),
            'is_active' => true,
            'last_seen_at' => now()->subMinutes(10),
        ]);

        AttendanceDevice::query()->create([
            'name' => 'RFID-INACTIVE',
            'api_token_hash' => hash('sha256', 'token-3'),
            'is_active' => false,
            'last_seen_at' => now()->subMinute(),
        ]);

        $response = $this->actingAs($administrator)->get(route('administrator.attendance-devices.index'));

        $response->assertOk();

        $response->assertSeeText('Urządzenia odbić');
        $response->assertSeeText('Dodaj urządzenie');

        $response->assertSeeText('RFID-ONLINE');
        $response->assertSeeText('Online');

        $response->assertSeeText('RFID-OFFLINE');
        $response->assertSeeText('Offline');

        $response->assertSeeText('RFID-INACTIVE');
        $response->assertSeeText('Nieaktywne');
    }

    public function test_administrator_can_open_create_page(): void
    {
        $administrator = $this->createAdministratorUser();

        $response = $this->actingAs($administrator)->get(route('administrator.attendance-devices.create'));

        $response->assertOk();
        $response->assertSeeText('Dodaj urządzenie');
        $response->assertSeeText('Utwórz i pokaż token');
    }

    public function test_administrator_can_store_device_and_receives_plain_token_while_hash_is_saved(): void
    {
        $administrator = $this->createAdministratorUser();

        $response = $this->actingAs($administrator)->post(route('administrator.attendance-devices.store'), [
            'name' => 'RFID-NEW',
            'isActive' => '1',
        ]);

        $response->assertOk();

        $response->assertViewHas('attendanceDevice');
        $response->assertViewHas('plainToken');

        $plainToken = (string) $response->viewData('plainToken');

        $this->assertNotSame('', $plainToken);

        $this->assertDatabaseHas('attendance_devices', [
            'name' => 'RFID-NEW',
            'is_active' => 1,
            'api_token_hash' => hash('sha256', $plainToken),
        ]);

        $response->assertSeeText('X-Attendance-Device-Token:');
        $response->assertSeeText($plainToken);
    }

    public function test_store_defaults_to_active_true_when_isActive_is_missing(): void
    {
        $administrator = $this->createAdministratorUser();

        $response = $this->actingAs($administrator)->post(route('administrator.attendance-devices.store'), [
            'name' => 'RFID-DEFAULT-ACTIVE',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('attendance_devices', [
            'name' => 'RFID-DEFAULT-ACTIVE',
            'is_active' => 1,
        ]);
    }

    public function test_administrator_can_update_device_name_and_is_redirected_to_index(): void
    {
        $administrator = $this->createAdministratorUser();

        $device = AttendanceDevice::query()->create([
            'name' => 'RFID-OLD',
            'api_token_hash' => hash('sha256', 'token-x'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($administrator)->put(route('administrator.attendance-devices.update', $device), [
            'name' => 'RFID-RENAMED',
        ]);

        $response->assertRedirectToRoute('administrator.attendance-devices.index');

        $this->assertDatabaseHas('attendance_devices', [
            'id' => $device->id,
            'name' => 'RFID-RENAMED',
        ]);
    }

    public function test_administrator_can_rotate_token_and_hash_changes(): void
    {
        $administrator = $this->createAdministratorUser();

        $device = AttendanceDevice::query()->create([
            'name' => 'RFID-ROTATE',
            'api_token_hash' => hash('sha256', 'old-token'),
            'is_active' => true,
        ]);

        $oldHash = $device->api_token_hash;

        $response = $this->actingAs($administrator)->post(route('administrator.attendance-devices.rotate-token', $device));

        $response->assertOk();
        $response->assertViewHas('plainToken');

        $plainToken = (string) $response->viewData('plainToken');

        $device->refresh();

        $this->assertNotSame($oldHash, $device->api_token_hash);
        $this->assertSame(hash('sha256', $plainToken), $device->api_token_hash);

        $response->assertSeeText('X-Attendance-Device-Token:');
        $response->assertSeeText($plainToken);
    }

    public function test_administrator_can_deactivate_and_activate_device(): void
    {
        $administrator = $this->createAdministratorUser();

        $device = AttendanceDevice::query()->create([
            'name' => 'RFID-ACTIVE',
            'api_token_hash' => hash('sha256', 'token-y'),
            'is_active' => true,
        ]);

        $deactivateResponse = $this->actingAs($administrator)->patch(route('administrator.attendance-devices.deactivate', $device));
        $deactivateResponse->assertRedirectToRoute('administrator.attendance-devices.index');

        $device->refresh();
        $this->assertFalse($device->is_active);

        $activateResponse = $this->actingAs($administrator)->patch(route('administrator.attendance-devices.activate', $device));
        $activateResponse->assertRedirectToRoute('administrator.attendance-devices.index');

        $device->refresh();
        $this->assertTrue($device->is_active);
    }

    private function createAdministratorUser(): User
    {
        $administratorRole = Role::query()->firstOrCreate(['name' => 'Administrator']);

        return User::factory()->create([
            'role_id' => $administratorRole->id,
        ]);
    }

    private function createStandardUser(): User
    {
        $standardRole = Role::query()->firstOrCreate(['name' => 'Standard']);

        return User::factory()->create([
            'role_id' => $standardRole->id,
        ]);
    }
}
