<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('penalty_type')->default('none'); // none, flat, daily
            $table->decimal('penalty_amount', 15, 2)->default(0);
            $table->integer('penalty_grace_period')->default(0); // days after due date
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['penalty_type', 'penalty_amount', 'penalty_grace_period']);
        });
    }
};
