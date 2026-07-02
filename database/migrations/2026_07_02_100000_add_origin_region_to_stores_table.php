<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The store's origin/pickup region as human-readable text, shown on the
     * store profile and as the driver's pickup label. (The delivery fee itself
     * is computed from lat/lng added in a later migration, §5.4a.) Nullable.
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
