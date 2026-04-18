<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Room;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup roles
        Role::create(['name' => 'tenant']);
    }

    public function test_user_can_initiate_booking()
    {
        $branch = Branch::create([
            'name' => 'Test Branch',
            'default_booking_fee' => 50000,
            'booking_expiration_hours' => 24
        ]);

        $user = User::create([
            'name' => 'Test Tenant',
            'email' => 'tenant@test.com',
            'password' => bcrypt('password')
        ]);
        $user->assignRole('tenant');
        $user->branches()->attach($branch->id);

        $room = Room::create([
            'branch_id' => $branch->id,
            'number' => '101',
            'price' => 1000000,
            'status' => 'available'
        ]);

        $this->actingAs($user);

        $response = $this->get(route('booking.create', $room->id));
        $response->assertStatus(200);
        $response->assertSee('Konfirmasi Booking Kamar 101');
    }

    public function test_user_can_submit_booking()
    {
        $branch = Branch::create([
            'name' => 'Test Branch',
            'default_booking_fee' => 50000,
            'booking_expiration_hours' => 24
        ]);

        $user = User::create([
            'name' => 'Test Tenant',
            'email' => 'tenant@test.com',
            'password' => bcrypt('password')
        ]);
        $user->assignRole('tenant');
        $user->branches()->attach($branch->id);

        $room = Room::create([
            'branch_id' => $branch->id,
            'number' => '101',
            'price' => 1000000,
            'status' => 'available'
        ]);

        $this->actingAs($user);

        $response = $this->post(route('booking.store', $room->id), [
            'check_in_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('bookings', [
            'room_id' => $room->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'booking_fee' => 50000,
        ]);

        $room->refresh();
        $this->assertEquals('reserved', $room->status);
    }
}
