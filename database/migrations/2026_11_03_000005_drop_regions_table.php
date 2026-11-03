<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('regions');
    }

    public function down(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->string('code', 20)->primary();
            $table->string('parent_code', 20)->nullable()->index();
            $table->string('name', 150);
            $table->unsignedTinyInteger('level')->index();
            $table->string('postal_code', 10)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('has_path')->default(false);
        });
    }
};
