<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * phpunit.xml pins this suite to an in-memory SQLite database, but its
     * <env> entries are only read while the configuration is being built. When
     * bootstrap/cache/config.php exists, the values from .env are already baked
     * into it, so phpunit.xml is ignored entirely: the suite runs against the
     * live MySQL database named in the cache instead.
     *
     * That is not a hypothetical. On 19 Nov 2026 a plain `php artisan test`
     * started the stock Feature\ExampleTest, whose RefreshDatabase trait issued
     * migrate:fresh against brgy_based - emptying users, barangays, complaints
     * and every other table while the tests reported "2 passed".
     *
     * So drop the cache before the application boots and let phpunit.xml apply
     * as written. The cache is rebuildable (`php artisan config:cache`) and the
     * next web request simply re-reads .env, which holds the same values.
     */
    public function createApplication()
    {
        $cachedConfig = dirname(__DIR__).'/bootstrap/cache/config.php';

        if (is_file($cachedConfig)) {
            unlink($cachedConfig);
        }

        return parent::createApplication();
    }
}
