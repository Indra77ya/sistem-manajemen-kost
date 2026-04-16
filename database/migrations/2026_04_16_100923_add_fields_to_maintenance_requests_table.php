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
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->string('attachment_before')->nullable();
            $table->string('attachment_after')->nullable();
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->boolean('is_charged_to_tenant')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropForeign(['technician_id']);
            $table->dropColumn([
                'technician_id',
                'started_at',
                'resolved_at',
                'attachment_before',
                'attachment_after',
                'total_cost',
                'is_charged_to_tenant',
            ]);
        });
    }
};
