<?php

namespace App\Http\Controllers;

use App\Jobs\SyncSourceAccountJob;
use App\Models\SourceAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SourceAccountController extends Controller
{
    public function index(): Response
    {
        $accounts = SourceAccount::where('user_id', auth()->id())
            ->withCount(['messages', 'tasks'])
            ->latest()->get();

        return Inertia::render('Sources/Index', [
            'accounts' => $accounts,
            'types' => SourceAccount::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => 'required|in:' . implode(',', SourceAccount::TYPES),
            'name' => 'required|string|max:200',
            'identifier' => 'nullable|string|max:200',
            'credentials' => 'nullable|array',
            'settings' => 'nullable|array',
            'enabled' => 'sometimes|boolean',
        ]);
        $data['user_id'] = auth()->id();
        $account = SourceAccount::create($data);
        return redirect()->route('sources.show', $account);
    }

    public function show(SourceAccount $source): Response
    {
        $this->authorizeAccount($source);
        $source->loadCount(['messages', 'tasks']);
        return Inertia::render('Sources/Show', [
            'account' => $source,
            'recent_logs' => $source->syncLogs()->latest()->limit(20)->get(),
        ]);
    }

    public function update(Request $request, SourceAccount $source): RedirectResponse
    {
        $this->authorizeAccount($source);
        $data = $request->validate([
            'name' => 'sometimes|string|max:200',
            'identifier' => 'nullable|string|max:200',
            'credentials' => 'nullable|array',
            'settings' => 'nullable|array',
            'enabled' => 'sometimes|boolean',
        ]);
        $source->update($data);
        return back();
    }

    public function destroy(SourceAccount $source): RedirectResponse
    {
        $this->authorizeAccount($source);
        $source->delete();
        return redirect()->route('sources.index');
    }

    public function sync(SourceAccount $source): RedirectResponse
    {
        $this->authorizeAccount($source);
        SyncSourceAccountJob::dispatch($source->id);
        return back()->with('status', 'Sync queued.');
    }

    public function toggle(SourceAccount $source): RedirectResponse
    {
        $this->authorizeAccount($source);
        $source->update(['enabled' => ! $source->enabled]);
        return back();
    }

    protected function authorizeAccount(SourceAccount $account): void
    {
        abort_if($account->user_id !== auth()->id(), 403);
    }
}
