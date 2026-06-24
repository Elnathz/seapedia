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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            // Nullable + nullOnDelete: order history must survive a seller
            // later deleting the product — the snapshot columns below are
            // exactly what keeps it displayable without the live row.
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name_snapshot');
            $table->unsignedBigInteger('price_snapshot');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('line_subtotal');
            $table->timestamps();

            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
