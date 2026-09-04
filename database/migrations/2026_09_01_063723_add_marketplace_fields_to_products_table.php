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
            $table->string('brand')->nullable()->after('name');
            $table->string('badge')->default('Official')->after('brand'); // Official, Mall, Star+
            $table->decimal('rating', 2, 1)->default(4.8)->after('stock');
            $table->unsignedInteger('sold_count')->default(150)->after('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['brand', 'badge', 'rating', 'sold_count']);
        });
    }
};
