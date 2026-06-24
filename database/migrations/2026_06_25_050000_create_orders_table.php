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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();

            // Address snapshot (§7) — survives the buyer later editing or
            // deleting the saved address.
            $table->string('ship_recipient');
            $table->string('ship_phone');
            $table->string('ship_address', 500);

            $table->enum('delivery_method', ['instant', 'next_day', 'regular']);

            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('discount_total')->default(0);
            $table->unsignedBigInteger('delivery_fee');
            $table->unsignedBigInteger('tax_amount');
            $table->unsignedBigInteger('grand_total');
            $table->unsignedBigInteger('seller_income_amount');

            $table->enum('status', [
                'sedang_dikemas', 'menunggu_pengirim', 'sedang_dikirim', 'pesanan_selesai', 'dikembalikan',
            ]);

            $table->timestamp('created_sim_at');
            $table->timestamp('sla_due_at');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            $table->index('buyer_id');
            $table->index('store_id');
            $table->index('status');
            $table->index('sla_due_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
