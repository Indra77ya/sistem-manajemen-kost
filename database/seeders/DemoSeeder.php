<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::create([
            'name' => 'Kost Cempaka',
            'address' => 'Jl. Cempaka No. 123, Jakarta',
            'phone' => '08123456789',
        ]);

        Room::create([
            'branch_id' => $branch->id,
            'number' => '101',
            'type' => 'Deluxe',
            'price' => 2000000,
            'capacity' => 2,
            'description' => 'Kamar luas dengan AC dan Kamar Mandi Dalam',
            'status' => 'available',
        ]);

        Room::create([
            'branch_id' => $branch->id,
            'number' => '102',
            'type' => 'Standard',
            'price' => 1500000,
            'capacity' => 1,
            'description' => 'Kamar nyaman dengan kipas angin',
            'status' => 'available',
        ]);
    }
}
