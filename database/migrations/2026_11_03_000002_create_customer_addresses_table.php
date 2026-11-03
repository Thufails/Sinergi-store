<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('label', 60);
            $table->string('recipient_name', 150);
            $table->string('recipient_phone', 30);
            $table->string('province', 100);
            $table->string('rajaongkir_province_id', 30);
            $table->string('city', 100);
            $table->string('rajaongkir_city_id', 30);
            $table->string('district', 100);
            $table->string('rajaongkir_district_id', 30);
            $table->string('subdistrict', 100)->nullable();
            $table->string('rajaongkir_subdistrict_id', 30)->nullable();
            $table->string('postal_code', 10);
            $table->text('address_line');
            $table->text('notes')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['customer_id', 'is_default'], 'customer_addresses_owner_default_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
