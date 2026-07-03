<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Snapshot the delivery destination (buyer coordinates) and the store→buyer
 * distance on the order at checkout. The buyer address can later change or be
 * deleted, so drivers need a frozen copy to sort jobs by proximity (D1) and to
 * draw the pickup→dropoff route (D2).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('ship_latitude', 10, 7)->nullable()->after('ship_address');
            $table->decimal('ship_longitude', 10, 7)->nullable()->after('ship_latitude');
            $table->decimal('delivery_distance_km', 6, 2)->nullable()->after('delivery_fee');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['ship_latitude', 'ship_longitude', 'delivery_distance_km']);
        });
    }
};
