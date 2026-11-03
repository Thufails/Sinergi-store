<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->string('province_code', 20)->nullable()->after('province');
            $table->string('regency_code', 20)->nullable()->after('city');
            $table->string('district_code', 20)->nullable()->after('district');
            $table->string('village_code', 20)->nullable()->after('subdistrict');
            $table->dropColumn([
                'rajaongkir_province_id',
                'rajaongkir_city_id',
                'rajaongkir_district_id',
                'rajaongkir_subdistrict_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->string('rajaongkir_province_id', 30)->nullable();
            $table->string('rajaongkir_city_id', 30)->nullable();
            $table->string('rajaongkir_district_id', 30)->nullable();
            $table->string('rajaongkir_subdistrict_id', 30)->nullable();
            $table->dropColumn([
                'province_code',
                'regency_code',
                'district_code',
                'village_code',
            ]);
        });
    }
};
