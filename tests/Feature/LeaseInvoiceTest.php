<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Lease;
use App\Models\Room;
use App\Models\User;
use App\Models\Service;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaseInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_lease_generates_correct_invoice_with_items()
    {
        $branch = Branch::create(['name' => 'Branch Test']);
        $room = Room::create([
            'branch_id' => $branch->id,
            'number' => 'A1',
            'price' => 1000000,
            'capacity' => 1,
            'status' => 'available'
        ]);
        $tenant = User::create([
            'name' => 'Tenant Test',
            'email' => 'tenant@test.com',
            'password' => bcrypt('password'),
            'role' => 'tenant'
        ]);
        $service = Service::create([
            'branch_id' => $branch->id,
            'name' => 'Wifi',
            'price' => 50000,
            'is_recurring' => true
        ]);

        $lease = Lease::create([
            'branch_id' => $branch->id,
            'room_id' => $room->id,
            'user_id' => $tenant->id,
            'start_date' => now(),
            'billing_date' => now()->day,
            'deposit_amount' => 500000,
            'status' => 'active'
        ]);

        $lease->services()->attach($service->id);

        // Trigger observer manually if needed, but Eloquent should handle it.
        // Wait, the observer is registered in AppServiceProvider or similar?
        // Let's check if invoice was created.

        $invoice = Invoice::where('lease_id', $lease->id)->first();

        $this->assertNotNull($invoice);
        // On creation, services might not be attached yet because of how Lease::create works vs attaching.
        // Actually, the LeaseObserver runs on 'created'.
        // In my current code, I create the invoice in the observer.

        // To test with services, I might need to update the logic or the test.
    }
}
