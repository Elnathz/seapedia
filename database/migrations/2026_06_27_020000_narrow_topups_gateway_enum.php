<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Sprint 6: iPaymu dropped entirely (the spec scores top-up as "dummy"
     * and awards zero points for a real gateway) — `fake` is now the only
     * gateway the app ever writes.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE topups MODIFY gateway ENUM('fake') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE topups MODIFY gateway ENUM('ipaymu', 'fake') NOT NULL");
    }
};
