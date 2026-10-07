<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JobController extends Controller
{
    public function index(Request $request): View
    {
        $query = Job::query()->with('poster:id,name');

        if (in_array($request->input('status'), ['open', 'closed'], true)) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('q')) {
            $term = trim($request->string('q')->toString());
            $query->where(fn ($q) => $q->where('title', 'like', '%'.$term.'%')
                ->orWhere('company', 'like', '%'.$term.'%')
                ->orWhere('location', 'like', '%'.$term.'%'));
        }

        if ($request->input('category') && array_key_exists($request->input('category'), Job::CATEGORIES)) {
            $query->where('category', $request->input('category'));
        }

        $jobs = $query->orderByDesc('is_featured')->latest()->get();

        return view('admin.jobs.index', [
            'jobs' => $jobs,
            'openCount' => Job::where('status', 'open')->count(),
            'closedCount' => Job::where('status', 'closed')->count(),
            'total' => Job::count(),
            'categories' => Job::CATEGORIES,
            'badge' => [
                'open' => 'badge-green',
                'closed' => 'badge-rose',
            ],
            'active' => in_array($request->input('status'), ['open', 'closed'], true)
                ? $request->input('status')
                : 'all',
        ]);
    }

    public function create(): View
    {
        return view('admin.jobs.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $job = Job::create([
            ...$data,
            'status' => $data['status'] ?? 'open',
            'is_featured' => $request->boolean('is_featured'),
            'posted_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route('admin.jobs.index')
            ->with('success', '"'.$job->title.'" was posted to the livelihood board.');
    }

    public function edit(Job $job): View
    {
        return view('admin.jobs.edit', ['job' => $job]);
    }

    public function update(Request $request, Job $job): RedirectResponse
    {
        $data = $this->validated($request);

        $job->update([
            ...$data,
            'status' => $data['status'] ?? $job->status,
            'is_featured' => $request->boolean('is_featured'),
        ]);

        return redirect()
            ->route('admin.jobs.index')
            ->with('success', '"'.$job->title.'" was updated.');
    }

    public function destroy(Job $job): RedirectResponse
    {
        $title = $job->title;
        $job->delete();

        return redirect()
            ->route('admin.jobs.index')
            ->with('success', '"'.$title.'" was removed from the board.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'company' => ['required', 'string', 'max:140'],
            'category' => ['required', Rule::in(array_keys(Job::CATEGORIES))],
            'location' => ['required', 'string', 'max:140'],
            'employment_type' => ['required', Rule::in(array_keys(Job::TYPES))],
            'salary' => ['nullable', 'string', 'max:80'],
            'description' => ['required', 'string', 'min:10', 'max:6000'],
            'requirements' => ['nullable', 'string', 'max:4000'],
            'contact_person' => ['nullable', 'string', 'max:120'],
            'contact_number' => ['nullable', 'string', 'max:40'],
            'contact_email' => ['nullable', 'email', 'max:190'],
            'deadline' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['open', 'closed'])],
            'is_featured' => ['nullable', 'boolean'],
        ]);
    }
}
