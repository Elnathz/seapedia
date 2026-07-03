<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Square (1:1) store profile photo, stored on the public disk. Nullable —
     * a store without a logo falls back to its initials monogram.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('logo_path');
        });
    }
};
