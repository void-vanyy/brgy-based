<?php

namespace App\Support;

use App\Models\DocumentRequest;

/**
 * Single source of truth for document processing fees.
 *
 * Every document type carries a base rate for the first copy plus a smaller
 * surcharge for each additional copy. The controller computes the fee here,
 * the request form mirrors the same table for its live preview via
 * schedule(), the fee tables render describe(), and the seeder builds its
 * rows from calculate() — so the amounts cannot drift apart across the app.
 *
 * If a rate changes, change it once in SCHEDULE or DEFAULT below.
 */
final class DocumentFee
{
    /**
     * doc_type => [base => first copy, extra => each additional copy].
     *
     * Document types not listed here fall back to DEFAULT.
     */
    private const SCHEDULE = [
        'barangay_clearance' => ['base' => 50.00, 'extra' => 20.00],
        'business_permit_endorsement' => ['base' => 50.00, 'extra' => 20.00],
    ];

    /** Rate applied to every document type not named in SCHEDULE. */
    private const DEFAULT = ['base' => 30.00, 'extra' => 15.00];

    /** Hard ceiling on copies — must match the copies validation rule. */
    public const MAX_COPIES = 5;

    private function __construct()
    {
    }

    /** [base, extra] rates for one document type. */
    public static function rateFor(string $docType): array
    {
        return self::SCHEDULE[$docType] ?? self::DEFAULT;
    }

    /**
     * Total fee for a document type at a given copy count.
     *
     * Copies below 1 are clamped so a bad call can never return a negative
     * fee.
     */
    public static function calculate(string $docType, int $copies): float
    {
        $copies = max(1, $copies);
        $rate = self::rateFor($docType);

        return round($rate['base'] + (($copies - 1) * $rate['extra']), 2);
    }

    /**
     * doc_type => ['base' => , 'extra' => ] for every known document type.
     *
     * Used by the request form so its live preview reads the same numbers the
     * server will charge instead of hard-coding them a second time.
     */
    public static function schedule(): array
    {
        $schedule = [];

        foreach (array_keys(DocumentRequest::TYPES) as $type) {
            $schedule[$type] = self::rateFor($type);
        }

        return $schedule;
    }

    /** Short human breakdown, e.g. "₱50.00 + 2 × ₱20.00". */
    public static function breakdown(string $docType, int $copies): string
    {
        $copies = max(1, $copies);
        $rate = self::rateFor($docType);

        if ($copies === 1) {
            return sprintf('₱%.2f (1 copy)', $rate['base']);
        }

        return sprintf('₱%.2f + %d × ₱%.2f', $rate['base'], $copies - 1, $rate['extra']);
    }

    /** Fee-table line, e.g. "₱50.00 first copy + ₱20.00 each extra". */
    public static function describe(string $docType): string
    {
        $rate = self::rateFor($docType);

        return sprintf('₱%.2f first copy + ₱%.2f each extra', $rate['base'], $rate['extra']);
    }
}
