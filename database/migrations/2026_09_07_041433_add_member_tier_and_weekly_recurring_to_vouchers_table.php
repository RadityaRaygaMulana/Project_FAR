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
        Schema::table('vouchers', function (Blueprint $table) {
            $table->string('member_tier', 20)->default('all')->after('type');
            $table->boolean('is_weekly_recurring')->default(false)->after('quota');
            $table->string('weekly_day_rule', 30)->default('all')->after('is_weekly_recurring');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn(['member_tier', 'is_weekly_recurring', 'weekly_day_rule']);
        });
    }
};
