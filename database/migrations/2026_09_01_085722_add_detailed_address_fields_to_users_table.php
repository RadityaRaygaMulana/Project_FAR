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
        Schema::table('users', function (Blueprint $table) {
            $table->string('address_label')->nullable()->default('Rumah')->after('avatar');
            $table->string('recipient_name')->nullable()->after('address_label');
            $table->string('recipient_phone')->nullable()->after('recipient_name');
            $table->string('province')->nullable()->after('recipient_phone');
            $table->string('city')->nullable()->after('province');
            $table->string('district')->nullable()->after('city');
            $table->string('postal_code')->nullable()->after('district');
            $table->text('address_detail')->nullable()->after('postal_code');
            $table->string('map_notes')->nullable()->after('address_detail');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'address_label',
                'recipient_name',
                'recipient_phone',
                'province',
                'city',
                'district',
                'postal_code',
                'address_detail',
                'map_notes',
            ]);
        });
    }
};
