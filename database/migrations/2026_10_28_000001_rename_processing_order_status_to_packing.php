<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('orders')
            ->where('status', 'processing')
            ->update(['status' => 'packing']);

        DB::table('order_status_histories')
            ->where('from_status', 'processing')
            ->update(['from_status' => 'packing']);

        DB::table('order_status_histories')
            ->where('to_status', 'processing')
            ->update(['to_status' => 'packing']);
    }

    public function down(): void
    {
        DB::table('orders')
            ->where('status', 'packing')
            ->update(['status' => 'processing']);

        DB::table('order_status_histories')
            ->where('from_status', 'packing')
            ->update(['from_status' => 'processing']);

        DB::table('order_status_histories')
            ->where('to_status', 'packing')
            ->update(['to_status' => 'processing']);
    }
};
