<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columnsToAdd = array_filter([
            ! Schema::hasColumn('customer_addresses', 'province_code') ? 'province_code' : null,
            ! Schema::hasColumn('customer_addresses', 'regency_code') ? 'regency_code' : null,
            ! Schema::hasColumn('customer_addresses', 'district_code') ? 'district_code' : null,
            ! Schema::hasColumn('customer_addresses', 'village_code') ? 'village_code' : null,
        ]);

        if ($columnsToAdd === []) {
            return;
        }

        Schema::table('customer_addresses', function (Blueprint $table) use ($columnsToAdd): void {
            foreach ($columnsToAdd as $column) {
                $table->string($column, 20)->nullable();
            }
        });
    }

    public function down(): void
    {
        $columnsToDrop = array_filter([
            Schema::hasColumn('customer_addresses', 'province_code') ? 'province_code' : null,
            Schema::hasColumn('customer_addresses', 'regency_code') ? 'regency_code' : null,
            Schema::hasColumn('customer_addresses', 'district_code') ? 'district_code' : null,
            Schema::hasColumn('customer_addresses', 'village_code') ? 'village_code' : null,
        ]);

        if ($columnsToDrop === []) {
            return;
        }

        Schema::table('customer_addresses', function (Blueprint $table) use ($columnsToDrop): void {
            $table->dropColumn($columnsToDrop);
        });
    }
};
