<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DocumentRequestController extends Controller
{
    /** Documents that carry the ₱50.00 fee; everything else is ₱30.00. */
    private const FEE_50 = ['barangay_clearance', 'business_permit_endorsement'];

    public function index(Request $request): View
    {
        $status = (string) $request->query('status', '');
        if (! array_key_exists($status, DocumentRequest::STATUSES)) {
            $status = '';
        }

        $query = DocumentRequest::where('user_id', auth()->id())->latest();

        if ($status !== '') {
            $query->where('status', $status);
        }

        $counts = ['all' => DocumentRequest::where('user_id', auth()->id())->count()];
        foreach (array_keys(DocumentRequest::STATUSES) as $key) {
            $counts[$key] = DocumentRequest::where('user_id', auth()->id())->where('status', $key)->count();
        }

        return view('resident.documents.index', [
            'requests' => $query->paginate(10)->withQueryString(),
            'counts' => $counts,
            'statuses' => DocumentRequest::STATUSES,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('resident.documents.create', [
            'types' => DocumentRequest::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'doc_type' => ['required', 'string', Rule::in(array_keys(DocumentRequest::TYPES))],
            'purpose' => ['required', 'string', 'min:3', 'max:190'],
            'copies' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $fee = in_array($data['doc_type'], self::FEE_50, true) ? 50.00 : 30.00;

        $document = DocumentRequest::create([
            'reference_no' => 'DOC-'.str_pad((string) (DocumentRequest::max('id') + 1), 4, '0', STR_PAD_LEFT),
            'user_id' => $request->user()->id,
            'doc_type' => $data['doc_type'],
            'purpose' => trim($data['purpose']),
            'copies' => (int) $data['copies'],
            'fee' => $fee,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('resident.documents.show', $document)
            ->with('success', 'Request '.$document->reference_no.' received. Present this reference number at the records desk.');
    }

    public function show(DocumentRequest $documentRequest): View
    {
        abort_unless($documentRequest->user_id === auth()->id(), 403);

        return view('resident.documents.show', [
            'document' => $documentRequest,
        ]);
    }
}
