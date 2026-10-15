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
            $table->string('order_number', 40)->unique();
            $table->string('customer_name', 150);
            $table->string('customer_phone', 30);
            $table->string('status', 30)->default('pending_confirmation');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->timestamp('payment_due_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'payment_due_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
