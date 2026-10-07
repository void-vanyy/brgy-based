<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('resident.profile.edit', [
            'user' => auth()->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'purok' => ['nullable', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:190'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'bio' => ['nullable', 'string', 'max:500'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();
        $password = $data['password'] ?? null;
        unset($data['password']);

        // Only profile fields are ever mass-assigned here — role/status stay untouched.
        $user->fill($data);

        if ($password !== null) {
            $user->password = $password;
        }

        $user->save();

        return redirect()
            ->route('resident.profile.edit')
            ->with('success', $password !== null
                ? 'Profile and password updated successfully.'
                : 'Your profile has been updated.');
    }
}
