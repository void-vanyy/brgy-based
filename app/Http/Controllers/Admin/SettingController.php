<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    /** Human-friendly label + hint for every key stored in the settings table. */
    public const FIELDS = [
        'barangay_name' => [
            'label' => 'Barangay name',
            'hint' => 'Shown in the brand mark, page titles and printed documents.',
            'placeholder' => 'Barangay Sigla',
        ],
        'barangay_address' => [
            'label' => 'Barangay address',
            'hint' => 'Complete mailing address used on certificates.',
            'placeholder' => 'Barangay Hall, Sigla Street, Quezon City',
        ],
        'barangay_contact' => [
            'label' => 'Contact number / email',
            'hint' => 'Hotline or e-mail printed on public pages.',
            'placeholder' => '(02) 8123-4567 / barangay.sigla@mail.gov.ph',
        ],
        'barangay_capain' => [
            'label' => 'Punong Barangay',
            'hint' => 'Caption of the Punong Barangay shown beside the seal.',
            'placeholder' => 'Hon. Juan Dela Cruz, Punong Barangay',
        ],
        'barangay_motto' => [
            'label' => 'Barangay motto',
            'hint' => 'Short tagline for the landing page hero.',
            'placeholder' => 'Makabuluhan, mapagkalinga, magkaisa.',
        ],
    ];

    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => Setting::kv(),
            'fields' => self::FIELDS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'barangay_name' => ['required', 'string', 'max:120'],
            'barangay_address' => ['nullable', 'string', 'max:220'],
            'barangay_contact' => ['nullable', 'string', 'max:120'],
            'barangay_capain' => ['nullable', 'string', 'max:140'],
            'barangay_motto' => ['nullable', 'string', 'max:220'],
        ]);

        foreach (array_keys(self::FIELDS) as $key) {
            Setting::set($key, trim($data[$key] ?? ''));
        }

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', 'Barangay settings saved and published across the site.');
    }
}
