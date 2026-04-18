<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // No changes needed to schema, just a reminder that 'status' is a string and can accept 'reserved'
        // But let's make sure it's documented in the migration if it was an enum (it's a string in our case)
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
