<?php

/**
 * Contensio - The open content platform for Laravel.
 * Admin — URL Redirects (SEO-critical when content slugs change).
 * https://contensio.com
 *
 * Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * Contensio is operated by Host Server SRL.
 *
 * LICENSE:
 * Permissions of this strongest copyleft license are conditioned on making
 * available complete source code of licensed works and modifications, which
 * include larger works using a licensed work, under the same license.
 * Copyright and license notices must be preserved. Contributors provide an
 * express grant of patent rights. When a modified version is used to provide
 * a service over a network, the complete source code of the modified version
 * must be made available.
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Http\Controllers\Admin;

use Contensio\Models\Redirect as RedirectModel;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RedirectController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        $redirects = RedirectModel::query()
            ->when($q, fn ($query) => $query
                ->where('source_url', 'like', "%{$q}%")
                ->orWhere('target_url', 'like', "%{$q}%")
            )
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('contensio::admin.redirects.index', compact('redirects', 'q'));
    }

    public function create()
    {
        return view('contensio::admin.redirects.form', [
            'redirect' => new RedirectModel(['status_code' => 301]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        RedirectModel::create($data);

        return redirect()->route('contensio.account.redirects.index')->with('success', 'Redirect created.');
    }

    public function edit(int $id)
    {
        $redirect = RedirectModel::findOrFail($id);

        return view('contensio::admin.redirects.form', compact('redirect'));
    }

    public function update(Request $request, int $id)
    {
        $redirect = RedirectModel::findOrFail($id);
        $redirect->update($this->validated($request, $redirect->id));

        return redirect()->route('contensio.account.redirects.index')->with('success', 'Redirect updated.');
    }

    public function destroy(int $id)
    {
        RedirectModel::where('id', $id)->delete();

        return redirect()->route('contensio.account.redirects.index')->with('success', 'Redirect deleted.');
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'source_url'  => 'required|string|max:500|regex:/^\//',
            'target_url'  => 'required|string|max:500',
            'status_code' => 'required|in:301,302',
        ], [
            'source_url.regex' => 'Source URL must start with /',
        ]);

        // Normalize source (leading slash, no trailing slash except for "/")
        $data['source_url'] = '/' . ltrim($data['source_url'], '/');
        if ($data['source_url'] !== '/') {
            $data['source_url'] = rtrim($data['source_url'], '/');
        }

        // Reject self-redirects (would cause loops)
        if ($data['source_url'] === rtrim($data['target_url'], '/')) {
            abort(422, 'Source and target cannot be the same.');
        }

        return $data;
    }
}
