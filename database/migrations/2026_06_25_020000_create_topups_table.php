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
        Schema::create('topups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->enum('status', ['pending', 'paid', 'failed', 'expired'])->default('pending');
            $table->enum('gateway', ['ipaymu', 'fake']);
            $table->string('gateway_session_id')->nullable();
            $table->string('gateway_reference')->nullable()->unique();
            $table->json('raw_response')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topups');
    }
};
