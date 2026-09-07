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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_free_shipping')->default(false)->after('allowed_payment_methods');
            $table->unsignedInteger('free_shipping_min_spend')->default(0)->after('is_free_shipping');
            $table->boolean('allow_vouchers')->default(true)->after('free_shipping_min_spend');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_free_shipping', 'free_shipping_min_spend', 'allow_vouchers']);
        });
    }
};
