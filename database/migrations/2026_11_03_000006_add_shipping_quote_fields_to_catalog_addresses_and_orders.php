<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('weight_grams')->default(0)->after('stock');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->unsignedInteger('weight_grams')->nullable()->after('stock');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('customer_address_id')
                ->nullable()
                ->after('customer_id')
                ->constrained('customer_addresses')
                ->nullOnDelete();
            $table->decimal('subtotal_amount', 12, 2)->default(0)->after('total_amount');
            $table->decimal('shipping_fee', 12, 2)->default(0)->after('subtotal_amount');
            $table->unsignedInteger('shipping_weight_grams')->default(0)->after('shipping_fee');
            $table->string('shipping_recipient_name', 150)->nullable()->after('shipping_weight_grams');
            $table->string('shipping_recipient_phone', 30)->nullable()->after('shipping_recipient_name');
            $table->string('shipping_province', 100)->nullable()->after('shipping_recipient_phone');
            $table->string('shipping_city', 100)->nullable()->after('shipping_province');
            $table->string('shipping_district', 100)->nullable()->after('shipping_city');
            $table->string('shipping_village', 100)->nullable()->after('shipping_district');
            $table->string('shipping_postal_code', 10)->nullable()->after('shipping_village');
            $table->text('shipping_address_line')->nullable()->after('shipping_postal_code');
            $table->text('shipping_notes')->nullable()->after('shipping_address_line');
            $table->decimal('shipping_latitude', 10, 7)->nullable()->after('shipping_notes');
            $table->decimal('shipping_longitude', 10, 7)->nullable()->after('shipping_latitude');
            $table->string('payment_proof_path')->nullable()->after('payment_due_at');
            $table->timestamp('payment_proof_uploaded_at')->nullable()->after('payment_proof_path');
            $table->text('payment_review_note')->nullable()->after('payment_proof_uploaded_at');
            $table->string('courier_service', 150)->nullable()->after('courier_name');
            $table->string('shipping_estimated_delivery', 80)->nullable()->after('courier_service');
        });

        DB::table('orders')->update([
            'subtotal_amount' => DB::raw('total_amount'),
        ]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_address_id');
            $table->dropColumn([
                'subtotal_amount',
                'shipping_fee',
                'shipping_weight_grams',
                'shipping_recipient_name',
                'shipping_recipient_phone',
                'shipping_province',
                'shipping_city',
                'shipping_district',
                'shipping_village',
                'shipping_postal_code',
                'shipping_address_line',
                'shipping_notes',
                'shipping_latitude',
                'shipping_longitude',
                'payment_proof_path',
                'payment_proof_uploaded_at',
                'payment_review_note',
                'courier_service',
                'shipping_estimated_delivery',
            ]);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('weight_grams');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('weight_grams');
        });
    }
};
