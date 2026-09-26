<?php

namespace App\Http\Controllers;

use App\Models\MacAgentCommand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MacAgentCommandController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['open_application', 'open_path', 'open_url'])],
            'application' => ['nullable', 'string', 'max:100', 'regex:/^[\pL\pN .\'-]+$/u', 'required_if:action,open_application'],
            'device_id' => ['required', 'integer', Rule::exists('mac_devices', 'id')],
            'label' => ['required', 'string', 'max:120'],
            'path' => ['nullable', 'string', 'max:1000', 'required_if:action,open_path'],
            'url' => ['nullable', 'url', 'max:2048', 'required_if:action,open_url'],
        ]);

        $payload = match ($validated['action']) {
            'open_application' => ['application' => $validated['application']],
            'open_path' => ['path' => $validated['path']],
            'open_url' => ['url' => $validated['url']],
        };

        if (($payload['url'] ?? null) !== null && (parse_url($payload['url'], PHP_URL_SCHEME) !== 'https' || parse_url($payload['url'], PHP_URL_USER) !== null)) {
            return back()->withErrors(['url' => 'Only HTTPS URLs without embedded credentials can be approved.'])->withInput();
        }

        MacAgentCommand::query()->create([
            'action' => $validated['action'],
            'label' => $validated['label'],
            'mac_device_id' => $validated['device_id'],
            'payload' => $payload,
        ]);

        return back()->with('status', 'Draft command created. Review it, then approve it when ready.');
    }

    public function approve(MacAgentCommand $command): RedirectResponse
    {
        if ($command->status !== 'pending') {
            return back()->withErrors(['command' => 'Only draft commands can be approved.']);
        }

        $command->forceFill(['status' => 'approved'])->save();

        return back()->with('status', 'Command approved for the paired Mac.');
    }

    public function cancel(MacAgentCommand $command): RedirectResponse
    {
        if (! in_array($command->status, ['pending', 'approved'], true)) {
            return back()->withErrors(['command' => 'This command can no longer be cancelled.']);
        }

        $command->forceFill(['status' => 'cancelled'])->save();

        return back()->with('status', 'Command cancelled.');
    }
}
