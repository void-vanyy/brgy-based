<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('doc_type'); // barangay_clearance|certificate_of_residency|certificate_of_indigency|...
            $table->text('purpose');
            $table->unsignedInteger('copies')->default(1);
            $table->decimal('fee', 10, 2)->default(0);
            $table->string('status')->default('pending'); // pending|under_review|approved|ready_for_release|released|rejected
            $table->text('remarks')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_requests');
    }
};
