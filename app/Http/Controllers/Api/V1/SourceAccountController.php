<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SourceAccountResource;
use App\Jobs\SyncSourceAccountJob;
use App\Models\SourceAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SourceAccountController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return SourceAccountResource::collection(
            SourceAccount::where('user_id', $request->user()->id)->get()
        );
    }

    public function store(Request $request): SourceAccountResource
    {
        $data = $request->validate([
            'type' => 'required|in:' . implode(',', SourceAccount::TYPES),
            'name' => 'required|string|max:200',
            'identifier' => 'nullable|string|max:200',
            'credentials' => 'nullable|array',
            'settings' => 'nullable|array',
            'enabled' => 'sometimes|boolean',
        ]);
        $data['user_id'] = $request->user()->id;
        return new SourceAccountResource(SourceAccount::create($data));
    }

    public function show(Request $request, SourceAccount $source): SourceAccountResource
    {
        $this->authorize($request, $source);
        return new SourceAccountResource($source);
    }

    public function update(Request $request, SourceAccount $source): SourceAccountResource
    {
        $this->authorize($request, $source);
        $data = $request->validate([
            'name' => 'sometimes|string|max:200',
            'identifier' => 'nullable|string|max:200',
            'credentials' => 'nullable|array',
            'settings' => 'nullable|array',
            'enabled' => 'sometimes|boolean',
        ]);
        $source->update($data);
        return new SourceAccountResource($source);
    }

    public function destroy(Request $request, SourceAccount $source): JsonResponse
    {
        $this->authorize($request, $source);
        $source->delete();
        return response()->json(['ok' => true]);
    }

    public function sync(Request $request, SourceAccount $source): JsonResponse
    {
        $this->authorize($request, $source);
        SyncSourceAccountJob::dispatch($source->id);
        return response()->json(['queued' => true]);
    }

    public function enable(Request $request, SourceAccount $source): SourceAccountResource
    {
        $this->authorize($request, $source);
        $source->update(['enabled' => true]);
        return new SourceAccountResource($source);
    }

    public function disable(Request $request, SourceAccount $source): SourceAccountResource
    {
        $this->authorize($request, $source);
        $source->update(['enabled' => false]);
        return new SourceAccountResource($source);
    }

    protected function authorize(Request $request, SourceAccount $source): void
    {
        abort_if($source->user_id !== $request->user()->id, 403);
    }
}
