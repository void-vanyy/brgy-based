<?php

namespace App\Support;

use Illuminate\Database\QueryException;
use Throwable;

/**
 * Recognises the failures that mean "the database is unreachable" rather than
 * "this request was bad".
 *
 * The exception handler uses this to turn a raw 500 into a friendly, cacheable
 * 503 during an outage. That is the failure mode that took the whole site down
 * on 8 Oct 2026 when mysqld died: every DB-backed page returned 500 and the
 * only hint was a stack trace.
 *
 * Deliberately narrow: a missing table or a bad column is a schema/code bug
 * and must stay visible as a normal error. Only transport-level failures —
 * the server is not there, or is refusing us — count as an outage.
 */
final class DbOutage
{
    /** Transport-level symptoms of an unreachable database server. */
    private const PATTERNS = [
        'No connection could be made',      // port not listening (mysqld down)
        'Connection refused',
        'Connection could not be established',
        'Timed out while trying to connect',
        'Too many connections',
        'server has gone away',
        'Lost connection',
        'Access denied for user',
        'Unknown database',
    ];

    private function __construct()
    {
    }

    public static function isConnectionFailure(Throwable $e): bool
    {
        if (! $e instanceof QueryException) {
            return false;
        }

        $message = $e->getMessage();

        foreach (self::PATTERNS as $pattern) {
            if (stripos($message, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }
}
