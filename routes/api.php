<?php

use App\Http\Controllers\Api;
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
