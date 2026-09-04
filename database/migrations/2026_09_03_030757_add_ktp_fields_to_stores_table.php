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
        Schema::table('stores', function (Blueprint $table) {
            $table->string('ktp_nik', 16)->nullable()->after('phone');
            $table->string('ktp_name')->nullable()->after('ktp_nik');
            $table->string('ktp_photo_path')->nullable()->after('ktp_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['ktp_nik', 'ktp_name', 'ktp_photo_path']);
        });
    }
};
