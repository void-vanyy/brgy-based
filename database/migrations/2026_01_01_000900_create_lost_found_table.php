<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lost_found', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->string('category')->default('others'); // gadget|jewelry|documents|clothing|accessory|pet|others
            $table->text('description');
            $table->string('status')->default('lost'); // lost|found|claimed
            $table->string('location');
            $table->date('date_occurred')->nullable();
            $table->string('contact_info')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lost_found');
    }
};
