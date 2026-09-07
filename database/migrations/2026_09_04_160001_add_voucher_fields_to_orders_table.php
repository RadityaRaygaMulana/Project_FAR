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
            $table->integer('shipping_discount_amount')->default(0)->after('shipping_cost');
            $table->string('shipping_voucher_code')->nullable()->after('coupon_code');
            $table->string('discount_voucher_code')->nullable()->after('shipping_voucher_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_discount_amount',
                'shipping_voucher_code',
                'discount_voucher_code',
            ]);
        });
    }
};
