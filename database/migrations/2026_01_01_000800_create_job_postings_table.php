<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('company');
            $table->string('category')->default('general'); // general|construction|food_service|retail|healthcare|agriculture|bpo|domestic|technical
            $table->string('location');
            $table->string('employment_type')->default('full_time'); // full_time|part_time|contract|temporary|freelance
            $table->string('salary')->nullable();
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('contact_email')->nullable();
            $table->date('deadline')->nullable();
            $table->string('status')->default('open'); // open|closed
            $table->boolean('is_featured')->default(false);
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
