<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MacAgentCommand;
use App\Models\MacDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MacAgentHeartbeatController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'array'],
            'status.agent_version' => ['nullable', 'string', 'max:50'],
            'status.hostname' => ['nullable', 'string', 'max:255'],
            'status.platform' => ['nullable', 'string', 'max:100'],
        ]);

        /** @var MacDevice $device */
        $device = $request->attributes->get('mac_device');

        $device->forceFill([
            'last_seen_at' => now(),
            'last_status' => $validated['status'] ?? $device->last_status,
        ])->save();

        $commands = DB::transaction(function () use ($device) {
            $device->commands()
                ->where('status', 'dispatched')
                ->where('claimed_at', '<=', now()->subMinutes(5))
                ->get()
                ->each(function (MacAgentCommand $command): void {
                    $command->forceFill([
                        'status' => 'failed',
                        'result' => [
                            'message' => 'No completion receipt was received within five minutes. Create a new draft if you still want this action.',
                        ],
                        'completed_at' => now(),
                    ])->save();
                });

            return $device->commands()
                ->where('status', 'approved')
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->each(function (MacAgentCommand $command): void {
                    $command->forceFill([
                        'status' => 'dispatched',
                        'claimed_at' => now(),
                    ])->save();
                })
                ->map(fn (MacAgentCommand $command): array => [
                    'id' => $command->id,
                    'action' => $command->action,
                    'payload' => $command->payload,
                ])
                ->values();
        });

        return response()->json([
            'device' => [
                'id' => $device->id,
                'name' => $device->name,
            ],
            'commands' => $commands,
        ]);
    }
}
