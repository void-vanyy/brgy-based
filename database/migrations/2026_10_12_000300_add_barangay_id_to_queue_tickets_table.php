<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The queue board is the one place a resident's barangay cannot be derived.
 *
 * A ticket booked online belongs to a resident who already carries a
 * barangay_id, so duplicating it there would be redundant. A walk-in ticket
 * has no owning user at all — it is written on a slip of paper at the counter
 * — and the only way to know which barangay's counter issued it is to record
 * that at the moment of issue.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queue_tickets', function (Blueprint $table) {
            $table->foreignId('barangay_id')
                ->nullable()
                ->after('user_id')
                ->constrained()
                ->nullOnDelete();

            $table->index('barangay_id');
        });
    }

    public function down(): void
    {
        Schema::table('queue_tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('barangay_id');
        });
    }
};
