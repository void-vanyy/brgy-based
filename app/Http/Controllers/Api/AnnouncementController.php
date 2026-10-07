<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;

class AnnouncementController extends Controller
{
    /**
     * GET /api/announcements
     *
     * The ten most recently published notices (pinned items first).
     */
    public function index(): JsonResponse
    {
        $announcements = Announcement::query()
            ->published()
            ->take(10)
            ->get([
                'id',
                'title',
                'body',
                'category',
                'event_date',
                'location',
                'is_pinned',
                'published_at',
            ]);

        return response()->json($announcements->values());
    }
}
