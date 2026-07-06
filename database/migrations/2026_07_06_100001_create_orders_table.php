<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('product_link');
            $table->text('product_note')->nullable();
            $table->enum('status', [
                'submitted',
                'quoted',
                'payment_pending',
                'payment_confirmed',
                'ordered',
                'shipped',
                'delivered',
                'cancelled',
            ])->default('submitted');
            $table->string('batch_id')->nullable()->index();
            $table->decimal('item_cost', 10, 2)->nullable();
            $table->decimal('service_fee_pct', 5, 2)->nullable();
            $table->decimal('shipping_fee', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->string('delivery_address')->nullable();
            $table->text('tracking_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
