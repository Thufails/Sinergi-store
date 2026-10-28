<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('courier_name', 100)->nullable()->after('payment_due_at');
            $table->string('tracking_number', 100)->nullable()->after('courier_name');
            $table->timestamp('shipped_at')->nullable()->after('tracking_number');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['courier_name', 'tracking_number', 'shipped_at']);
        });
    }
};
