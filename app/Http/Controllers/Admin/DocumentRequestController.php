<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RendersStatusPatches;
use App\Http\Controllers\Concerns\ScopesAdminQueries;
use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DocumentRequestController extends Controller
{
    use RendersStatusPatches, ScopesAdminQueries;

    public function index(Request $request): View
    {
        $query = $this->scopedByResident(DocumentRequest::query())
            ->with(['resident:id,name,email,avatar,purok', 'processor:id,name']);

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

        $counts = $this->scopedByResident(DocumentRequest::query())
            ->select('status', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.requests.index', [
            'requests' => $requests,
            'counts' => $counts,
            'statuses' => DocumentRequest::STATUSES,
            'scopeLabel' => $this->scopeLabel(),
        ]);
    }

    public function show(DocumentRequest $documentRequest): View
    {
        $this->guardInScope($documentRequest);

        $documentRequest->load(['resident:id,name,email,phone,purok,address,avatar', 'processor:id,name']);

        return view('admin.requests.show', [
            'docRequest' => $documentRequest,
            'statuses' => DocumentRequest::STATUSES,
            'types' => DocumentRequest::TYPES,
        ]);
    }

    public function update(Request $request, DocumentRequest $documentRequest): RedirectResponse|JsonResponse
    {
        $this->guardInScope($documentRequest);

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

        $fresh = $documentRequest->fresh(['processor:id,name']);

        return $this->savedResponse(
            $request,
            'admin.requests.show',
            $documentRequest->reference_no.' is now "'.$documentRequest->statusLabel().'".',
            [
                /* The header badge. */
                [
                    'selector' => '[data-status-cell]',
                    'badge' => DocumentRequest::STATUS_BADGES[$fresh->status] ?? 'badge-neutral',
                    'label' => $fresh->statusLabel(),
                ],
                /* Remarks, release stamp and processor - all move with status. */
                ['selector' => '#requestDetails', 'view' => 'admin.requests.partials.details', 'data' => ['docRequest' => $fresh]],
                /* The progress steps. */
                ['selector' => '#requestTrail', 'view' => 'admin.requests.partials.trail', 'data' => ['docRequest' => $fresh]],
            ],
            ['documentRequest' => $documentRequest],
        );
    }

    /**
     * Document requests carry no barangay_id of their own — the resident who
     * filed them already does — so the check runs through that resident.
     */
    private function guardInScope(DocumentRequest $documentRequest): void
    {
        $id = $this->scopeBarangayId();

        if ($id !== null && $documentRequest->resident?->barangay_id !== $id) {
            abort(404);
        }
    }
}
