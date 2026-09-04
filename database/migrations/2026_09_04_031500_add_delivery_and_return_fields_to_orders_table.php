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
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('delivered_at')->nullable()->after('status')->index();
            $table->timestamp('completed_at')->nullable()->after('delivered_at');
            $table->string('return_status')->nullable()->after('cancellation_response_note')->index();
            $table->string('return_reason')->nullable()->after('return_status');
            $table->text('return_description')->nullable()->after('return_reason');
            $table->string('return_proof_image')->nullable()->after('return_description');
            $table->timestamp('return_requested_at')->nullable()->after('return_proof_image');
            $table->timestamp('return_responded_at')->nullable()->after('return_requested_at');
            $table->text('return_response_note')->nullable()->after('return_responded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivered_at',
                'completed_at',
                'return_status',
                'return_reason',
                'return_description',
                'return_proof_image',
                'return_requested_at',
                'return_responded_at',
                'return_response_note',
            ]);
        });
    }
};
