<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Room;
use App\Models\User;
use App\Models\Service;
use App\Models\ExpenseCategory;
use App\Models\MaintenanceCategory;
use App\Models\PaymentMethod;
use App\Models\Asset;
use App\Models\Inventory;
use App\Models\Lease;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Booking;
use App\Models\BookingInvitation;
use App\Models\MaintenanceRequest;
use App\Models\Expense;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Setup Roles (assuming RolePermissionSeeder already run, but let's be safe)
        $roles = ['super_admin', 'owner', 'admin_cabang', 'technician', 'tenant'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // 2. Branches
        $branch1 = Branch::create([
            'name' => 'Kost Kita - Kemang',
            'address' => 'Jl. Kemang Raya No. 10, Jakarta Selatan',
            'phone' => '021-7190001',
            'penalty_type' => 'daily',
            'penalty_amount' => 10000,
            'penalty_grace_period' => 3,
            'booking_expiration_hours' => 24,
            'default_booking_fee' => 100000,
        ]);

        $branch2 = Branch::create([
            'name' => 'Kost Kita - Tebet',
            'address' => 'Jl. Tebet Utara Dalam No. 5, Jakarta Selatan',
            'phone' => '021-8310002',
            'penalty_type' => 'flat',
            'penalty_amount' => 50000,
            'penalty_grace_period' => 5,
            'booking_expiration_hours' => 12,
            'default_booking_fee' => 150000,
        ]);

        // 3. Users
        $dev = User::create([
            'name' => 'Developer',
            'email' => 'dev@admin.com',
            'password' => Hash::make('password'),
        ]);
        $dev->assignRole('super_admin');

        $owner = User::create([
            'name' => 'Bapak Budi (Owner)',
            'email' => 'owner@admin.com',
            'password' => Hash::make('password'),
        ]);
        $owner->assignRole('owner');

        $admin1 = User::create([
            'name' => 'Siti Admin Kemang',
            'email' => 'admin1@admin.com',
            'password' => Hash::make('password'),
        ]);
        $admin1->assignRole('admin_cabang');
        $admin1->branches()->attach($branch1->id);

        $tech1 = User::create([
            'name' => 'Agus Teknisi',
            'email' => 'tech@admin.com',
            'password' => Hash::make('password'),
        ]);
        $tech1->assignRole('technician');
        $tech1->branches()->attach($branch1->id);

        $tenant1 = User::create([
            'name' => 'Rizky Ramadhan',
            'email' => 'tenant1@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $tenant1->assignRole('tenant');
        $tenant1->branches()->attach($branch1->id);

        $tenant2 = User::create([
            'name' => 'Ani Wijaya',
            'email' => 'tenant2@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $tenant2->assignRole('tenant');
        $tenant2->branches()->attach($branch1->id);

        // 4. Master Data
        $wifi = Service::create([
            'branch_id' => $branch1->id,
            'name' => 'High Speed Wifi',
            'price' => 50000,
            'is_recurring' => true,
        ]);

        $laundry = Service::create([
            'branch_id' => $branch1->id,
            'name' => 'Laundry Kiloan (10kg)',
            'price' => 75000,
            'is_recurring' => true,
        ]);

        $expCat1 = ExpenseCategory::create(['name' => 'Listrik & Air']);
        $expCat2 = ExpenseCategory::create(['name' => 'Gaji Karyawan']);
        $expCat3 = ExpenseCategory::create(['name' => 'Perbaikan']);

        $maintCat1 = MaintenanceCategory::create([
            'name' => 'AC',
            'default_priority' => 'high',
            'default_technician_id' => $tech1->id
        ]);
        $maintCat2 = MaintenanceCategory::create([
            'name' => 'Kelistrikan',
            'default_priority' => 'urgent',
            'default_technician_id' => $tech1->id
        ]);

        PaymentMethod::create([
            'name' => 'Bank BCA',
            'account_number' => '8820011223',
            'account_holder' => 'PT Kost Kita Indonesia',
            'is_active' => true
        ]);

        $assetAC = Asset::create(['name' => 'AC Split 1/2 PK', 'category' => 'Elektronik']);
        $assetBed = Asset::create(['name' => 'Kasur Springbed Single', 'category' => 'Furniture']);

        // 5. Rooms
        $room101 = Room::create([
            'branch_id' => $branch1->id,
            'number' => '101',
            'type' => 'Deluxe',
            'price' => 2500000,
            'capacity' => 1,
            'status' => 'occupied',
            'description' => 'Lantai 1, Jendela menghadap taman'
        ]);

        $room102 = Room::create([
            'branch_id' => $branch1->id,
            'number' => '102',
            'type' => 'Standard',
            'price' => 1800000,
            'capacity' => 1,
            'status' => 'occupied',
            'description' => 'Lantai 1'
        ]);

        $room201 = Room::create([
            'branch_id' => $branch1->id,
            'number' => '201',
            'type' => 'Deluxe',
            'price' => 2600000,
            'capacity' => 1,
            'status' => 'available',
            'description' => 'Lantai 2, Balkon'
        ]);

        Inventory::create(['room_id' => $room101->id, 'asset_id' => $assetAC->id, 'quantity' => 1, 'condition' => 'good']);
        Inventory::create(['room_id' => $room101->id, 'asset_id' => $assetBed->id, 'quantity' => 1, 'condition' => 'good']);

        // 6. Leases & Invoices
        $lease1 = Lease::create([
            'branch_id' => $branch1->id,
            'room_id' => $room101->id,
            'user_id' => $tenant1->id,
            'start_date' => Carbon::now()->subMonths(2)->startOfMonth(),
            'billing_date' => 1,
            'deposit_amount' => 2500000,
            'status' => 'active'
        ]);
        $lease1->services()->attach([$wifi->id, $laundry->id]);

        $lease2 = Lease::create([
            'branch_id' => $branch1->id,
            'room_id' => $room102->id,
            'user_id' => $tenant2->id,
            'start_date' => Carbon::now()->subMonth()->startOfMonth(),
            'billing_date' => 5,
            'deposit_amount' => 1800000,
            'status' => 'active'
        ]);

        // Paid Invoice
        $inv1 = Invoice::create([
            'branch_id' => $branch1->id,
            'lease_id' => $lease1->id,
            'invoice_number' => 'INV/2026/01/001',
            'amount' => 2625000,
            'due_date' => Carbon::now()->subMonths(2)->startOfMonth()->addDays(3),
            'status' => 'paid'
        ]);
        InvoiceItem::create(['invoice_id' => $inv1->id, 'description' => 'Sewa Kamar 101 - Jan 2026', 'amount' => 2500000, 'type' => 'rent']);
        InvoiceItem::create(['invoice_id' => $inv1->id, 'description' => 'Layanan Wifi', 'amount' => 50000, 'type' => 'service']);
        InvoiceItem::create(['invoice_id' => $inv1->id, 'description' => 'Layanan Laundry', 'amount' => 75000, 'type' => 'service']);

        // Overdue Invoice
        $inv2 = Invoice::create([
            'branch_id' => $branch1->id,
            'lease_id' => $lease2->id,
            'invoice_number' => 'INV/2026/02/002',
            'amount' => 1800000,
            'due_date' => Carbon::now()->subMonth()->startOfMonth()->addDays(5),
            'status' => 'overdue'
        ]);
        InvoiceItem::create(['invoice_id' => $inv2->id, 'description' => 'Sewa Kamar 102 - Feb 2026', 'amount' => 1800000, 'type' => 'rent']);

        // 7. Bookings & Invitations
        $booking = Booking::create([
            'branch_id' => $branch1->id,
            'room_id' => $room201->id,
            'user_id' => $tenant1->id, // Tenant 1 booking another room
            'check_in_date' => Carbon::now()->addDays(10),
            'booking_fee' => 100000,
            'status' => 'confirmed',
            'expires_at' => Carbon::now()->addDays(1)
        ]);
        $room201->update(['status' => 'reserved']);

        BookingInvitation::generateForRoom($room201); // Another invitation just to show

        // 8. Maintenance & Expenses
        $req = MaintenanceRequest::create([
            'branch_id' => $branch1->id,
            'room_id' => $room101->id,
            'user_id' => $tenant1->id,
            'maintenance_category_id' => $maintCat1->id,
            'title' => 'AC Kurang Dingin',
            'description' => 'Sudah 3 hari AC tidak dingin mohon dicek',
            'priority' => 'high',
            'status' => 'resolved',
            'technician_id' => $tech1->id
        ]);

        Expense::create([
            'branch_id' => $branch1->id,
            'expense_category_id' => $expCat3->id,
            'maintenance_request_id' => $req->id,
            'amount' => 150000,
            'date' => Carbon::now()->subDays(2),
            'description' => 'Service AC Kamar 101 - Isi Freon',
            'is_tenant_chargeable' => false
        ]);
    }
}
