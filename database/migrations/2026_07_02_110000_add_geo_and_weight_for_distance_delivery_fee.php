<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Geo + weight for the distance-based delivery fee: the buyer address and
     * the store origin each get a lat/lng (picked on a Leaflet map), and every
     * sellable unit gets a shipping weight in grams. The fee is computed from
     * the Haversine distance (origin → destination) and the order weight, plus
     * the per-method base/rate (§5.4). All nullable so existing rows keep
     * working; a null weight bills as 0 and a missing coordinate as 0 km.
     */
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('village');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->decimal('origin_latitude', 10, 7)->nullable()->after('village');
            $table->decimal('origin_longitude', 10, 7)->nullable()->after('origin_latitude');
        });

        Schema::table('products', function (Blueprint $table) {
            // Grams. Used for non-variant products (a variant carries its own).
            $table->unsignedInteger('weight')->nullable()->after('stock');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->unsignedInteger('weight')->nullable()->after('stock');
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['origin_latitude', 'origin_longitude']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('weight');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('weight');
        });
    }
};
