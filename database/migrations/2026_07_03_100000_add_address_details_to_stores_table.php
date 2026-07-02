<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Street line (jalan / RT / RW) and postal code for the store profile, so a
     * store can show a full origin address alongside its region text (§7). The
     * region columns (province/city/district/village) already exist; the fee is
     * still computed from the lat/lng, not this text. All nullable.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('full_address', 500)->nullable()->after('description');
            $table->string('postal_code', 20)->nullable()->after('village');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['full_address', 'postal_code']);
        });
    }
};
