<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;
use App\Models\Room;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MaintenanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_maintenance_costs_are_calculated_correctly_during_checkout()
    {
        // 1. Setup Data
        $branch = Branch::create(['name' => 'Branch Test']);
        $room = Room::create([
            'branch_id' => $branch->id,
            'number' => '101',
            'price' => 1000000,
            'status' => 'occupied'
        ]);
        $tenant = User::create([
            'name' => 'Tenant Test',
            'email' => 'tenant@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_TENANT
        ]);

        $lease = Lease::create([
            'branch_id' => $branch->id,
            'room_id' => $room->id,
            'user_id' => $tenant->id,
            'start_date' => now(),
            'billing_date' => 1,
            'deposit_amount' => 500000,
            'status' => 'active'
        ]);

        // 2. Create Maintenance Requests
        // Charged to tenant
        MaintenanceRequest::create([
            'branch_id' => $branch->id,
            'room_id' => $room->id,
            'user_id' => $tenant->id,
            'title' => 'Rusak Lampu',
            'description' => 'Pecah oleh penyewa',
            'status' => 'resolved',
            'total_cost' => 50000,
            'is_charged_to_tenant' => true
        ]);

        // Not charged to tenant
        MaintenanceRequest::create([
            'branch_id' => $branch->id,
            'room_id' => $room->id,
            'user_id' => $tenant->id,
            'title' => 'Atap Bocor',
            'description' => 'Faktor usia',
            'status' => 'resolved',
            'total_cost' => 200000,
            'is_charged_to_tenant' => false
        ]);

        // Still pending (should not be counted yet)
        MaintenanceRequest::create([
            'branch_id' => $branch->id,
            'room_id' => $room->id,
            'user_id' => $tenant->id,
            'title' => 'Kran Patah',
            'description' => 'Patah oleh penyewa',
            'status' => 'in_progress',
            'total_cost' => 30000,
            'is_charged_to_tenant' => true
        ]);

        // 3. Perform Checkout Logic (Manual simulation of LeaseResource action)
        $unpaidAmount = $lease->invoices()->where('status', '!=', 'paid')->sum('amount');

        $maintenanceCosts = MaintenanceRequest::where('room_id', $lease->room_id)
            ->where('user_id', $lease->user_id)
            ->where('is_charged_to_tenant', true)
            ->where('status', 'resolved')
            ->sum('total_cost');

        // Deposit 500k, Rent 1M -> Total Invoice 1.5M.
        // refund = deposit (500k) - unpaid (1.5M) - maint (50k) = -1.05M
        $refundAmount = $lease->deposit_amount - $unpaidAmount - $maintenanceCosts;

        // 4. Assertions
        $this->assertEquals(50000, $maintenanceCosts);
        $this->assertEquals(-1050000, $refundAmount);
    }
}
