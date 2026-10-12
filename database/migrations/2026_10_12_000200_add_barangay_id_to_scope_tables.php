<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Attach a barangay to every record that has its own geographic identity.
 *
 *   users           the resident's home barangay (a resident belongs to
 *                   exactly one barangay, enforced by registration
 *                   validation rather than the column — kept nullable so the
 *                   migration stays safe on a table that already has rows)
 *   complaints      where the complaint was filed
 *   freedom_posts   which barangay's wall the post appears on
 *
 * These are nullable at the schema level purely so the migration stays safe
 * on tables that already hold rows; registration and the complaint form both
 * require a barangay, so no new record is ever left without one.
 *
 * Content that may legitimately apply to the whole city is nullable:
 *
 *   announcements   null = city-wide notice
 *   job_postings    null = city-wide vacancy
 *   lost_found      null = city-wide report
 *
 * Resident transactions (documents, appointments, queue tickets) are scoped
 * through the owning user instead of duplicating the column, so a record never
 * disagrees with the resident it belongs to.
 */
return new class extends Migration
{
    private const SHARED = [
        // table, after column, nullable, comment
        ['users', 'role', true, 'resident home barangay'],
        ['complaints', 'user_id', true, 'where the complaint was filed'],
        ['freedom_posts', 'user_id', true, 'whose barangay wall'],
        ['announcements', 'is_published', true, 'null = city-wide'],
        ['job_postings', 'status', true, 'null = city-wide'],
        ['lost_found', 'status', true, 'null = city-wide'],
    ];

    public function up(): void
    {
        foreach (self::SHARED as [$table, $after, $nullable, $comment]) {
            Schema::table($table, function (Blueprint $blueprint) use ($after, $nullable, $comment) {
                $column = $blueprint->foreignId('barangay_id')
                    ->nullable($nullable)
                    ->after($after)
                    ->constrained()
                    ->nullOnDelete();

                $column->comment($comment);
            });
        }
    }

    public function down(): void
    {
        foreach (array_column(self::SHARED, 0) as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropConstrainedForeignId('barangay_id');
            });
        }
    }
};
