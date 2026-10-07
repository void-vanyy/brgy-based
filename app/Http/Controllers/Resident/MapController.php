<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class MapController extends Controller
{
    public function index(): View
    {
        return view('resident.map.index');
    }
}
