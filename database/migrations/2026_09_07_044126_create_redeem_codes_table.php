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
        Schema::create('redeem_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name')->comment('Nama deskriptif kode redeem ini');
            $table->string('description')->nullable()->comment('Penjelasan singkat untuk admin');

            // Jenis diskon yang diberikan
            $table->enum('discount_type', ['fixed', 'percentage'])->default('fixed')->comment('fixed = nominal tetap, percentage = persen');
            $table->unsignedInteger('discount_amount')->default(0)->comment('Nominal potongan harga (Rp) atau persen (%)');
            $table->unsignedInteger('max_discount')->nullable()->comment('Maksimal potongan bila tipe percentage');
            $table->unsignedInteger('shipping_discount')->default(0)->comment('Potongan ongkir langsung (Rp)');

            // Syarat penggunaan
            $table->unsignedInteger('min_spend')->default(0)->comment('Minimum belanja agar kode bisa dipakai');
            $table->unsignedInteger('quota')->nullable()->comment('Batas total penggunaan (null = tak terbatas)');
            $table->unsignedInteger('used_count')->default(0)->comment('Total kali kode sudah dipakai');

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redeem_codes');
    }
};
