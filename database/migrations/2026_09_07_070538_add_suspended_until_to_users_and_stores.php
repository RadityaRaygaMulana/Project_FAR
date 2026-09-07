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
        if (! Schema::hasColumn('users', 'suspended_until')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('suspended_until')->nullable()->after('suspended_at');
            });
        }

        if (! Schema::hasColumn('stores', 'suspended_until')) {
            Schema::table('stores', function (Blueprint $table) {
                $table->timestamp('suspended_until')->nullable()->after('suspended_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('suspended_until');
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('suspended_until');
        });
    }
};
