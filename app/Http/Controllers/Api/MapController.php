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
     * City landmarks rendered as the "places" layer on every map.
     *
     * Coordinates sit in the poblacion, where Santa Rosa's civic buildings
     * actually are — City Hall, the public market and the parish are all
     * within a few hundred metres of each other between Kanluran, Malusak and
     * Market Area. Complaint markers, by contrast, are placed in whichever
     * barangay filed them (see ComplaintSeeder).
     */
    public function points(): JsonResponse
    {
        $points = [
            ['label' => 'Santa Rosa City Hall', 'latitude' => 14.3162, 'longitude' => 121.1128, 'type' => 'hall'],
            ['label' => 'City Health Office', 'latitude' => 14.3148, 'longitude' => 121.1141, 'type' => 'health'],
            ['label' => 'Santa Rosa Police Station', 'latitude' => 14.3139, 'longitude' => 121.1109, 'type' => 'peace'],
            ['label' => 'Santa Rosa Public Market', 'latitude' => 14.3171, 'longitude' => 121.1155, 'type' => 'market'],
            ['label' => 'Santa Rosa Central Elementary School', 'latitude' => 14.3125, 'longitude' => 121.1122, 'type' => 'school'],
            ['label' => 'Rizal Park', 'latitude' => 14.3155, 'longitude' => 121.1136, 'type' => 'plaza'],
            ['label' => 'Our Lady of Rosa Mystica Parish', 'latitude' => 14.3144, 'longitude' => 121.1116, 'type' => 'chapel'],
            ['label' => 'Santa Rosa Sports Complex', 'latitude' => 14.3180, 'longitude' => 121.1101, 'type' => 'court'],
        ];

        return response()->json($points);
    }
}
