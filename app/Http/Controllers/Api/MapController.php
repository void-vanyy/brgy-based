<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\JsonResponse;

class MapController extends Controller
{
    /** Barangay centre used by the public and admin maps. */
    public const CENTER_LAT = 14.6042;

    public const CENTER_LNG = 121.0410;

    /**
     * GET /api/map/complaints
     *
     * Plottable complaints only — records without coordinates are skipped and
     * resident e-mail addresses are never exposed.
     */
    public function complaints(): JsonResponse
    {
        $complaints = Complaint::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('resident:id,name')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Complaint $complaint): array => [
                'id' => $complaint->id,
                'reference_no' => $complaint->reference_no,
                'title' => $complaint->title,
                'category' => $complaint->category,
                'priority' => $complaint->priority,
                'status' => $complaint->status,
                'location' => $complaint->location,
                'latitude' => (float) $complaint->latitude,
                'longitude' => (float) $complaint->longitude,
                'created_at' => $complaint->created_at,
                'resident_name' => $complaint->resident?->name ?? 'Resident',
            ])
            ->values();

        return response()->json($complaints);
    }

    /**
     * GET /api/map/points
     *
     * Static barangay landmarks rendered as the "places" layer on every map.
     */
    public function points(): JsonResponse
    {
        $points = [
            ['label' => 'Barangay Hall', 'latitude' => 14.6042, 'longitude' => 121.0410, 'type' => 'hall'],
            ['label' => 'Health Center', 'latitude' => 14.6058, 'longitude' => 121.0424, 'type' => 'health'],
            ['label' => 'Police Sub-station', 'latitude' => 14.6027, 'longitude' => 121.0395, 'type' => 'peace'],
            ['label' => 'Public Market', 'latitude' => 14.6071, 'longitude' => 121.0382, 'type' => 'market'],
            ['label' => 'Sigla Elementary School', 'latitude' => 14.6005, 'longitude' => 121.0438, 'type' => 'school'],
            ['label' => 'Public Plaza', 'latitude' => 14.6049, 'longitude' => 121.0447, 'type' => 'plaza'],
            ['label' => 'Our Lady of Sigla Chapel', 'latitude' => 14.6018, 'longitude' => 121.0459, 'type' => 'chapel'],
            ['label' => 'Covered Court', 'latitude' => 14.6066, 'longitude' => 121.0456, 'type' => 'court'],
        ];

        return response()->json($points);
    }
}
