<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DocumentRequestController extends Controller
{
    public function index(Request $request): View
    {
        $query = DocumentRequest::query()->with(['resident:id,name,email,avatar,purok', 'processor:id,name']);

        if (in_array($request->input('status'), array_keys(DocumentRequest::STATUSES), true)) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('q')) {
            $term = trim($request->string('q')->toString());
            $query->where(function ($q) use ($term) {
                $q->where('reference_no', 'like', '%'.$term.'%')
                    ->orWhere('doc_type', 'like', '%'.$term.'%')
                    ->orWhereHas('resident', fn ($r) => $r->where('name', 'like', '%'.$term.'%'));
            });
        }

        $requests = $query->latest()->paginate(12)->withQueryString();

        $counts = DocumentRequest::query()
            ->select('status', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.requests.index', [
            'requests' => $requests,
            'counts' => $counts,
            'statuses' => DocumentRequest::STATUSES,
        ]);
    }

    public function show(DocumentRequest $documentRequest): View
    {
        $documentRequest->load(['resident:id,name,email,phone,purok,address,avatar', 'processor:id,name']);

        return view('admin.requests.show', [
            'docRequest' => $documentRequest,
            'statuses' => DocumentRequest::STATUSES,
            'types' => DocumentRequest::TYPES,
        ]);
    }

    public function update(Request $request, DocumentRequest $documentRequest): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(DocumentRequest::STATUSES))],
            'remarks' => ['nullable', 'string', 'max:4000'],
        ]);

        $documentRequest->update([
            'status' => $data['status'],
            'remarks' => ($data['remarks'] ?? null) ?: null,
            'processed_by' => $request->user()?->id,
            'released_at' => $data['status'] === 'released'
                ? ($documentRequest->released_at ?? now())
                : $documentRequest->released_at,
        ]);

        return redirect()
            ->route('admin.requests.show', $documentRequest)
            ->with('success', $documentRequest->reference_no.' is now "'.$documentRequest->statusLabel().'".');
    }
}
