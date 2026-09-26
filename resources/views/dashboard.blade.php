<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IRFAN PA</title>
    <style>
        :root { color-scheme: dark; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
        * { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; color: #ececf1; background: #212121; }
        .app { display: grid; grid-template-columns: 262px minmax(0, 1fr); min-height: 100vh; }
        .sidebar { padding: 16px; background: #171717; border-right: 1px solid #323232; }
        .brand { display: flex; gap: 10px; align-items: center; padding: 10px; font-weight: 750; }
        .brand-mark { display: grid; width: 30px; height: 30px; place-items: center; color: #171717; background: #fff; border-radius: 50%; }
        .new-chat { display: block; width: 100%; margin: 18px 0; padding: 11px 12px; color: inherit; text-align: left; text-decoration: none; background: #2a2a2a; border: 1px solid #424242; border-radius: 9px; }
        .side-label { margin: 22px 10px 8px; font-size: .73rem; color: #a3a3a3; letter-spacing: .08em; text-transform: uppercase; }
        .device { padding: 10px; margin: 7px 0; font-size: .9rem; background: #202020; border-radius: 9px; }
        .online { color: #8ce99a; }
        .offline { color: #a3a3a3; }
        .signout { position: absolute; bottom: 18px; left: 16px; width: 230px; }
        button { font: inherit; cursor: pointer; }
        .signout button { width: 100%; padding: 10px; color: #cfcfcf; text-align: left; background: none; border: 0; }
        .main { position: relative; min-width: 0; padding-bottom: 148px; }
        .topbar { display: flex; align-items: center; justify-content: space-between; height: 58px; padding: 0 28px; border-bottom: 1px solid #323232; }
        .topbar h1 { margin: 0; font-size: 1rem; font-weight: 650; }
        .brain { padding: 5px 9px; font-size: .76rem; color: #b5f5c5; background: #173925; border-radius: 99px; }
        .brain.offline { color: #d3d3d3; background: #333; }
        .conversation { width: min(780px, calc(100% - 34px)); margin: 0 auto; padding: 30px 0; }
        .message { display: grid; grid-template-columns: 31px minmax(0, 1fr); gap: 14px; padding: 17px 4px; }
        .message.user { grid-template-columns: minmax(0, 1fr) 31px; }
        .message.user .avatar { grid-column: 2; background: #454545; }
        .message.user .copy { grid-column: 1; grid-row: 1; justify-self: end; max-width: 80%; padding: 10px 14px; background: #303030; border-radius: 18px 18px 4px 18px; }
        .avatar { display: grid; width: 31px; height: 31px; place-items: center; font-size: .78rem; font-weight: 800; color: #191919; background: #fff; border-radius: 50%; }
        .copy { line-height: 1.58; white-space: pre-wrap; }
        .welcome { padding: 70px 10px 35px; text-align: center; }
        .welcome h2 { margin: 0 0 8px; font-size: clamp(1.8rem, 5vw, 2.7rem); }
        .welcome p { margin: 0; color: #b4b4b4; }
        .draft { margin-top: 13px; overflow: hidden; background: #292929; border: 1px solid #484848; border-radius: 12px; }
        .draft-head { display: flex; align-items: center; justify-content: space-between; padding: 11px 13px; font-size: .86rem; border-bottom: 1px solid #454545; }
        .draft-body { padding: 13px; color: #cfcfcf; font-size: .9rem; }
        .pill { padding: 4px 8px; font-size: .73rem; text-transform: capitalize; border-radius: 99px; background: #414141; }
        .pill.pending { color: #ffdd91; background: #634a16; }
        .pill.approved, .pill.completed { color: #a6edb4; background: #164825; }
        .pill.failed, .pill.cancelled { color: #ffc1c1; background: #5a2525; }
        .draft-actions { display: flex; gap: 8px; padding: 0 13px 13px; }
        .draft-actions form { margin: 0; }
        .approve, .cancel { padding: 8px 12px; color: #fff; border: 0; border-radius: 7px; }
        .approve { background: #10a37f; }
        .cancel { background: #454545; }
        .result { margin-top: 8px; color: #b8c9ff; font-size: .86rem; }
        .composer-wrap { position: fixed; right: 0; bottom: 0; left: 262px; padding: 22px; background: linear-gradient(transparent, #212121 28%); }
        .composer { width: min(780px, 100%); margin: 0 auto; }
        .composer form { display: flex; gap: 8px; align-items: flex-end; padding: 9px 10px 9px 16px; background: #303030; border: 1px solid #505050; border-radius: 25px; box-shadow: 0 8px 30px #0004; }
        textarea { flex: 1; min-height: 26px; max-height: 150px; padding: 4px 0; resize: none; color: #fff; background: transparent; border: 0; outline: 0; font: inherit; }
        .send { display: grid; width: 32px; height: 32px; place-items: center; color: #151515; background: #fff; border: 0; border-radius: 50%; }
        .hint { margin: 9px 0 0; text-align: center; color: #989898; font-size: .73rem; }
        .notice { width: min(780px, calc(100% - 34px)); margin: 18px auto 0; padding: 10px 14px; color: #ffe3a3; background: #4d3a13; border-radius: 10px; }
        @media (max-width: 720px) {
            .app { display: block; }
            .sidebar { display: none; }
            .topbar { padding: 0 16px; }
            .conversation { width: min(100% - 22px, 780px); }
            .composer-wrap { left: 0; padding: 16px 11px; }
        }
    </style>
</head>
<body>
    <div class="app">
        <aside class="sidebar">
            <div class="brand"><span class="brand-mark">PA</span> IRFAN PA</div>
            <a class="new-chat" href="{{ route('dashboard') }}">＋ New chat</a>
            <p class="side-label">Paired Mac</p>
            @forelse ($devices as $device)
                <div class="device">
                    <strong>{{ $device->name }}</strong><br>
                    <span class="{{ $device->last_seen_at?->isAfter(now()->subMinutes(3)) ? 'online' : 'offline' }}">
                        {{ $device->last_seen_at?->isAfter(now()->subMinutes(3)) ? '● Connected' : '● Not connected' }}
                    </span>
                </div>
            @empty
                <div class="device">No Mac paired yet.</div>
            @endforelse
            <form class="signout" method="POST" action="{{ route('logout') }}">
                @csrf
                <button>Sign out</button>
            </form>
        </aside>

        <main class="main">
            <header class="topbar">
                <h1>IRFAN PA</h1>
                <span class="brain {{ $claudeConfigured ? '' : 'offline' }}">{{ $claudeConfigured ? 'Claude ready' : 'Claude unavailable' }}</span>
            </header>

            @if ($errors->any())
                <div class="notice">{{ $errors->first() }}</div>
            @endif

            <section class="conversation">
                @if ($messages->isEmpty())
                    <div class="welcome">
                        <h2>How can I help?</h2>
                        <p>Write naturally in English, Hindi, Gujarati, Tamil, French, or Arabic.</p>
                    </div>
                @endif

                @foreach ($messages as $message)
                    <article class="message {{ $message->role === 'user' ? 'user' : 'assistant' }}">
                        <div class="avatar">{{ $message->role === 'user' ? 'You' : 'PA' }}</div>
                        <div class="copy">
                            {{ $message->content }}
                            @if ($message->command)
                                <div class="draft">
                                    <div class="draft-head">
                                        <strong>{{ $message->command->label }}</strong>
                                        <span class="pill {{ $message->command->status }}">{{ $message->command->status }}</span>
                                    </div>
                                    <div class="draft-body">
                                        {{ $message->command->action === 'open_url' ? $message->command->payload['url'] : ($message->command->action === 'open_path' ? $message->command->payload['path'] : ($message->command->action === 'inspect_outlook_inbox' ? 'Visible Outlook Inbox only' : $message->command->payload['application'])) }}
                                        <br>For {{ $message->command->device->name }}
                                        @if ($message->command->result['message'] ?? false)
                                            <div class="result">{{ $message->command->result['message'] }}</div>
                                        @endif
                                    </div>
                                    @if ($message->command->status === 'pending')
                                        <div class="draft-actions">
                                            <form method="POST" action="{{ route('commands.approve', $message->command) }}">@csrf<button class="approve">Approve</button></form>
                                            <form method="POST" action="{{ route('commands.cancel', $message->command) }}">@csrf<button class="cancel">Cancel</button></form>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </section>
        </main>
    </div>

    <div class="composer-wrap">
        <div class="composer">
            <form method="POST" action="{{ route('chat.store') }}">
                @csrf
                <textarea name="message" rows="1" maxlength="2000" required autofocus placeholder="Message IRFAN PA…"></textarea>
                <button class="send" aria-label="Send message">↑</button>
            </form>
            <p class="hint">Safe Mac tasks start automatically. PA will ask before sending an email, WhatsApp message, or other external communication.</p>
        </div>
    </div>

    <script>
        const composer = document.querySelector('textarea');
        composer.addEventListener('input', () => { composer.style.height = 'auto'; composer.style.height = `${Math.min(composer.scrollHeight, 150)}px`; });
        composer.addEventListener('keydown', (event) => { if (event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); composer.form.requestSubmit(); } });
        if (document.querySelector('.pill.pending, .pill.approved, .pill.dispatched')) { window.setTimeout(() => window.location.reload(), 15000); }
    </script>
</body>
</html>
