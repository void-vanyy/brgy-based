<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\FreedomPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FreedomWallController extends Controller
{
    public function index(): View
    {
        return view('resident.freedom-wall.index', [
            'posts' => FreedomPost::with('author')->latest()->paginate(10),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'min:3', 'max:1000'],
            'topic' => ['nullable', 'string', 'max:60'],
        ]);

        FreedomPost::create([
            'user_id' => $request->user()->id,
            'message' => trim($data['message']),
            'topic' => ($data['topic'] ?? null) !== null && trim($data['topic']) !== '' ? trim($data['topic']) : null,
            'is_anonymous' => true,
            'reactions' => 0,
        ]);

        return back()->with('success', 'Your message is now live on the Freedom Wall — posted anonymously.');
    }

    public function react(FreedomPost $post): RedirectResponse
    {
        $post->increment('reactions', 1);

        return back();
    }

    public function destroy(FreedomPost $post): RedirectResponse
    {
        abort_unless($post->user_id === auth()->id(), 403);

        $post->delete();

        return back()->with('success', 'Your post was removed from the wall.');
    }
}
