<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\JsonResponse;

class JobController extends Controller
{
    /**
     * GET /api/jobs
     *
     * The twenty most recent open livelihood listings.
     */
    public function index(): JsonResponse
    {
        $jobs = Job::query()
            ->where('status', 'open')
            ->orderByDesc('is_featured')
            ->latest()
            ->take(20)
            ->get([
                'id',
                'title',
                'company',
                'category',
                'location',
                'employment_type',
                'salary',
                'deadline',
                'is_featured',
            ]);

        return response()->json($jobs->values());
    }
}
