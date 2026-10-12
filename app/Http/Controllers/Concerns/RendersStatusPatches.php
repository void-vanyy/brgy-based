<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Answers an admin action the way its submitter expects.
 *
 * A plain form post wants a redirect and a flash message. An ajax submit
 * (form[data-ajax] in app.js) wants JSON describing what changed, so the
 * affected pieces can be rewritten in place instead of reloading the page.
 * One controller method serves both, which is why the two can never
 * disagree about what was saved.
 */
trait RendersStatusPatches
{
    /**
     * @param  array  $patches  one entry per DOM node to rewrite, each shaped
     *                           as one of:
     *
     *        status cell   ['selector' => ..., 'badge' => ..., 'label' => ..., 'remark' => ...]
     *                      -> renders admin.partials.status-cell into selector
     *
     *        blade view    ['selector' => ..., 'view' => 'admin.partials.x', 'data' => [...]]
     *                      -> renders that view's inner HTML into selector
     *
     *        plain html    ['selector' => ..., 'html' => ...]
     *
     *        no-op         ['selector' => ...]  (just close the modal and toast)
     *
     *        attributes    ['selector' => ..., 'attrs' => ['data-status' => 'approved']]
     *                      -> sets data-* on every match instead of replacing
     *                         HTML, so a button that prefills the next modal
     *                         does not keep offering yesterday's value
     */
    protected function savedResponse(
        Request $request,
        string $redirectRoute,
        string $message,
        array $patches,
        array $routeParams = [],
    ): RedirectResponse|JsonResponse {
        if (! $request->expectsJson()) {
            return redirect()
                ->route($redirectRoute, $routeParams)
                ->with('success', $message);
        }

        return response()->json([
            'message' => $message,
            'patches' => array_map(fn (array $patch) => $this->compilePatch($patch), $patches),
        ]);
    }

    /**
     * @return array{selector: string, html?: string, attrs?: array<string, string>}
     */
    private function compilePatch(array $patch): array
    {
        $out = ['selector' => $patch['selector']];

        if (isset($patch['attrs'])) {
            return $out + ['attrs' => $patch['attrs']];
        }

        if (array_key_exists('badge', $patch) || array_key_exists('label', $patch)) {
            $out['html'] = view('admin.partials.status-cell', [
                'badge' => $patch['badge'] ?? 'badge-neutral',
                'label' => $patch['label'] ?? '',
                'remark' => $patch['remark'] ?? null,
            ])->render();
        } elseif (isset($patch['view'])) {
            $out['html'] = view($patch['view'], $patch['data'] ?? [])->render();
        } elseif (array_key_exists('html', $patch)) {
            $out['html'] = $patch['html'];
        }

        return $out;
    }

    /**
     * @param  int|string  $id
     */
    protected function statusCellSelector($id): string
    {
        return sprintf('[data-record-id="%s"] [data-status-cell]', $id);
    }
}
