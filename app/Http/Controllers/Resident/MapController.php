<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use Illuminate\View\View;

class MapController extends Controller
{
    public function index(): View
    {
        /* The resident board spans the whole city, so it opens on Santa Rosa. */
        [$latitude, $longitude] = Barangay::CITY_CENTER;

        return view('resident.map.index', [
            'latitude'  => $latitude,
            'longitude' => $longitude,
            'zoom'      => Barangay::CITY_ZOOM,
        ]);
    }
}
