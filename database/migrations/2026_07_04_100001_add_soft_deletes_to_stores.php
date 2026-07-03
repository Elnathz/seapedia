<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * When a seller resigns their role we soft-hide the store instead of
     * deleting it: `stores.orders` (orders.store_id) is cascadeOnDelete, so a
     * hard delete would erase completed orders + histories + deliveries that
     * buyers, the seller's income report, and drivers still reference.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
