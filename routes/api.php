<?php

use App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public JSON endpoints — consumed by the Leaflet map and the queue board.
|--------------------------------------------------------------------------
*/

Route::get('/announcements', [Api\AnnouncementController::class, 'index']);
Route::get('/map/complaints', [Api\MapController::class, 'complaints']);
Route::get('/map/points', [Api\MapController::class, 'points']);
Route::get('/queue/status', [Api\QueueController::class, 'status']);
Route::get('/jobs', [Api\JobController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Health probe — GET /api/health
|--------------------------------------------------------------------------
| Answers "is this install actually working?" in one request. Returns 200 when
| the database answers and 503 when it does not, so it can be polled by a
| browser tab, a uptime check, or the group's own testing script. Never cached.
*/
Route::get('/health', function () {
    $database = 'ok';
    $status = 200;

    try {
        DB::connection()->getPdo();
        DB::select('select 1');
    } catch (\Throwable $e) {
        $database = 'unreachable';
        $status = 503;
    }

    return response()->json([
        'status' => $status === 200 ? 'ok' : 'degraded',
        'database' => $database,
        'environment' => config('app.env'),
        'checked_at' => now()->toIso8601String(),
    ], $status)->header('Cache-Control', 'no-store');
});
