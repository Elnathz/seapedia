<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The store's origin/pickup region. Used to compute the region-tier
     * delivery surcharge (§5.4) by comparing it against the buyer's shipping
     * address, and to sort driver jobs by nearest. Nullable: a store without a
     * recorded origin is charged base fee only (no surprise surcharge).
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('province')->nullable()->after('description');
            $table->string('city')->nullable()->after('province');
            $table->string('district')->nullable()->after('city');
            $table->string('village')->nullable()->after('district');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['province', 'city', 'district', 'village']);
        });
    }
};
