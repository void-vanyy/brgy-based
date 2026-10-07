<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name_on_ticket');
            $table->string('service'); // document_request|complaint|payment|consultation|general
            $table->string('window')->nullable();
            $table->string('status')->default('waiting'); // waiting|called|serving|done|skipped|no_show
            $table->date('queue_date');
            $table->timestamp('called_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamps();

            $table->unique(['queue_date', 'ticket_no']);
            $table->index(['queue_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_tickets');
    }
};
