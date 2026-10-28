<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('paid_at')->nullable()->after('status');
            $table->index('paid_at');
        });

        DB::table('orders')
            ->whereIn('status', ['paid', 'packing', 'shipped'])
            ->whereNull('paid_at')
            ->update(['paid_at' => DB::raw('updated_at')]);

        $paymentHistories = DB::table('order_status_histories')
            ->select('order_id')
            ->selectRaw('MIN(created_at) as paid_at')
            ->where('to_status', 'paid')
            ->groupBy('order_id')
            ->get();

        foreach ($paymentHistories as $paymentHistory) {
            DB::table('orders')
                ->where('id', $paymentHistory->order_id)
                ->update(['paid_at' => $paymentHistory->paid_at]);
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['paid_at']);
            $table->dropColumn('paid_at');
        });
    }
};
