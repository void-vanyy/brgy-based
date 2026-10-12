<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The city-wide barangay dimension.
 *
 * Santa Rosa City has 18 barangays. Each row carries its official PSGC
 * (Philippine Standard Geographic Code) correspondence code and the
 * approximate centre coordinate, so the map can be positioned per barangay
 * instead of a single hard-coded point.
 *
 * Seed data lives in Database\Seeders\BarangaySeeder.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barangays', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();                 // official barangay name
            $table->string('alias')->nullable();              // e.g. "Poblacion Uno"
            $table->string('psgc_code', 12)->unique();        // PSGC correspondence code
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedInteger('population_2020')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barangays');
    }
};
