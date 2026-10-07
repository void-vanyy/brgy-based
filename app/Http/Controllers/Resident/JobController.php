<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $category = (string) $request->query('category', '');
        $type = (string) $request->query('employment_type', '');

        if (! array_key_exists($category, Job::CATEGORIES)) {
            $category = '';
        }
        if (! array_key_exists($type, Job::TYPES)) {
            $type = '';
        }

        $query = Job::where('status', 'open')
            ->orderByDesc('is_featured')
            ->latest();

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                    ->orWhere('company', 'like', "%{$q}%")
                    ->orWhere('location', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if ($category !== '') {
            $query->where('category', $category);
        }

        if ($type !== '') {
            $query->where('employment_type', $type);
        }

        return view('resident.jobs.index', [
            'jobs' => $query->paginate(9)->withQueryString(),
            'categories' => Job::CATEGORIES,
            'types' => Job::TYPES,
            'q' => $q,
            'category' => $category,
            'type' => $type,
            'openCount' => Job::where('status', 'open')->count(),
        ]);
    }

    public function show(Job $job): View
    {
        abort_unless($job->status === 'open', 404);

        return view('resident.jobs.show', [
            'job' => $job,
        ]);
    }
}
