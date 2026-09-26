<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MacAgentCommand;
use App\Models\MacDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class MacAgentCommandCompletionController extends Controller
{
    public function store(Request $request, int $command): JsonResponse
    {
        $validated = $request->validate([
            'success' => ['required', 'boolean'],
            'result' => ['nullable', 'array'],
            'result.message' => ['nullable', 'string', 'max:500'],
        ]);

        /** @var MacDevice $device */
        $device = $request->attributes->get('mac_device');

        /** @var MacAgentCommand $macAgentCommand */
        $macAgentCommand = $device->commands()
            ->whereKey($command)
            ->where('status', 'dispatched')
            ->firstOrFail();

        $macAgentCommand->forceFill([
            'status' => $validated['success'] ? 'completed' : 'failed',
            'result' => Arr::only($validated['result'] ?? [], ['message']),
            'completed_at' => now(),
        ])->save();

        return response()->json([
            'command' => [
                'id' => $macAgentCommand->id,
                'status' => $macAgentCommand->status,
            ],
        ]);
    }
}
