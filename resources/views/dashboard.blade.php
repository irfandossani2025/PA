<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IRFAN PA</title>
    <style>
        body { margin: 0; background: #08111f; color: #eaf2ff; font: 16px system-ui; }
        .shell { max-width: 1050px; margin: auto; padding: 28px; }
        .top, .row { display: flex; justify-content: space-between; gap: 14px; }
        .panel { margin: 18px 0; padding: 20px; background: #101d31; border: 1px solid #243a59; border-radius: 16px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        label { display: block; margin-top: 10px; color: #b7cbe4; }
        input, select, button { box-sizing: border-box; width: 100%; margin-top: 5px; padding: 10px; color: #fff; background: #0a1525; border: 1px solid #355475; border-radius: 9px; }
        button { font-weight: 700; background: #2476bd; border: 0; }
        .muted { color: #a2b7d0; }
        .status { padding: 5px 9px; font-size: .8rem; background: #193353; border-radius: 20px; }
        .approved { background: #174c3c; }
        .cancelled, .failed { background: #542735; }
        .row { padding: 13px 0; border-top: 1px solid #243a59; }
        .actions { display: flex; gap: 8px; align-items: flex-start; }
        .actions form { margin: 0; }
        @media (max-width: 760px) { .grid { grid-template-columns: 1fr; } .top, .row { flex-direction: column; } }
    </style>
</head>
<body>
    <main class="shell">
        <header class="top">
            <div>
                <strong>IRFAN PA</strong>
                <p class="muted">Private Mac command centre</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button>Sign out</button>
            </form>
        </header>

        @if (session('status'))
            <div class="panel">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="panel">{{ $errors->first() }}</div>
        @endif

        <section class="grid">
            <article class="panel">
                <h2>Paired Macs</h2>
                @forelse ($devices as $device)
                    <div class="row">
                        <div>
                            <strong>{{ $device->name }}</strong>
                            <div class="muted">Last heartbeat: {{ $device->last_seen_at?->diffForHumans() ?? 'Not seen yet' }}</div>
                        </div>
                        <span class="status">{{ $device->last_seen_at?->isAfter(now()->subMinutes(3)) ? 'Online' : 'Offline' }}</span>
                    </div>
                @empty
                    <p class="muted">No paired Mac yet.</p>
                @endforelse
            </article>
            <article class="panel">
                <h2>Assistant brain</h2>
                <p class="muted">Claude: {{ $claudeConfigured ? 'Connected privately on PA.' : 'Not configured.' }}</p>
                <p class="muted">Only approved safe commands are delivered to your Mac.</p>
            </article>
        </section>

        <section class="panel">
            <h2>Create safe Mac command</h2>
            <form method="POST" action="{{ route('commands.store') }}">
                @csrf
                <label>Mac
                    <select name="device_id" required>
                        @foreach ($devices as $device)
                            <option value="{{ $device->id }}">{{ $device->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Task<input name="label" required maxlength="120" placeholder="Open my sales folder"></label>
                <label>Action
                    <select name="action" required>
                        <option value="open_url">Open an HTTPS website</option>
                        <option value="open_path">Open a file or folder</option>
                        <option value="open_application">Open an application</option>
                    </select>
                </label>
                <label>HTTPS URL<input name="url" type="url" placeholder="https://example.com"></label>
                <label>Mac path<input name="path" placeholder="/Users/irfandossani/Documents"></label>
                <label>Application name<input name="application" placeholder="Safari"></label>
                <button>Create draft for review</button>
            </form>
        </section>

        <section class="panel">
            <h2>Command review</h2>
            @forelse ($commands as $command)
                <div class="row">
                    <div>
                        <strong>{{ $command->label ?? $command->action }}</strong>
                        <div class="muted">{{ $command->device->name }} · {{ $command->action }} · {{ $command->created_at->diffForHumans() }}</div>
                    </div>
                    <div class="actions">
                        <span class="status {{ $command->status }}">{{ $command->status }}</span>
                        @if ($command->status === 'pending')
                            <form method="POST" action="{{ route('commands.approve', $command) }}">
                                @csrf
                                <button>Approve</button>
                            </form>
                        @endif
                        @if (in_array($command->status, ['pending', 'approved'], true))
                            <form method="POST" action="{{ route('commands.cancel', $command) }}">
                                @csrf
                                <button>Cancel</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <p class="muted">No commands yet.</p>
            @endforelse
        </section>
    </main>
</body>
</html>
