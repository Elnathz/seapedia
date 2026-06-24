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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['percentage', 'fixed']);
            // Holds a percentage (0–100) or a fixed IDR amount; money is
            // BIGINT UNSIGNED per §7 / golden rule 5.
            $table->unsignedBigInteger('value');
            $table->unsignedBigInteger('max_discount')->nullable();
            $table->unsignedBigInteger('min_spend')->nullable();
            $table->timestamp('expiry_date');
            $table->unsignedInteger('usage_limit');
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
