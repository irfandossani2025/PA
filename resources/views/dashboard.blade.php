<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Irfan Dossani's private personal assistant control centre.">
        <title>IRFAN PA — Control Centre</title>
        <style>
            :root { color-scheme: dark; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
            * { box-sizing: border-box; }
            body { min-height: 100vh; margin: 0; color: #eef5ff; background: radial-gradient(circle at 10% 0%, #1c365c 0, transparent 33rem), linear-gradient(135deg, #07101f 0%, #0b1730 54%, #0b1d2d 100%); }
            .shell { width: min(1120px, calc(100% - 32px)); margin: 0 auto; padding: 32px 0 56px; }
            .topbar, .section-heading, .task, .timeline-entry { display: flex; align-items: center; }
            .topbar { justify-content: space-between; gap: 24px; padding-bottom: 38px; }
            .brand { display: flex; align-items: center; gap: 12px; font-size: .85rem; font-weight: 800; letter-spacing: .14em; }
            .brand-mark { display: grid; width: 38px; height: 38px; place-items: center; border: 1px solid #5ab8ff; border-radius: 12px; color: #7ed0ff; background: #102c4b; box-shadow: 0 0 34px #2298ed33; }
            .private { padding: 8px 12px; border: 1px solid #ffffff20; border-radius: 999px; color: #bcd0e8; font-size: .78rem; }
            .eyebrow { margin: 0 0 12px; color: #73c8ff; font-size: .76rem; font-weight: 800; letter-spacing: .15em; text-transform: uppercase; }
            h1 { max-width: 780px; margin: 0; font-size: clamp(2.4rem, 6vw, 4.75rem); line-height: .98; letter-spacing: -.06em; }
            .intro { max-width: 655px; margin: 20px 0 0; color: #b8c9df; font-size: 1.08rem; line-height: 1.65; }
            .status { display: inline-flex; align-items: center; gap: 8px; margin: 25px 0 44px; padding: 9px 13px; border: 1px solid #50d9ac55; border-radius: 999px; color: #9ff0d2; background: #0c2b2b; font-size: .83rem; font-weight: 700; }
            .dot { width: 8px; height: 8px; border-radius: 50%; background: #4ee5a9; box-shadow: 0 0 10px #4ee5a9; }
            .grid { display: grid; grid-template-columns: 1.3fr .7fr; gap: 18px; }
            .panel { border: 1px solid #ffffff16; border-radius: 20px; background: linear-gradient(135deg, #ffffff11, #ffffff05); box-shadow: 0 18px 60px #00000024; backdrop-filter: blur(14px); }
            .tasks { padding: 25px; }
            .section-heading { justify-content: space-between; gap: 12px; }
            h2 { margin: 0; font-size: 1.08rem; letter-spacing: -.02em; }
            .count { color: #9db4d1; font-size: .8rem; }
            .task-list { display: grid; gap: 2px; margin-top: 18px; }
            .task { gap: 13px; padding: 15px 0; border-top: 1px solid #ffffff12; }
            .check { width: 21px; height: 21px; flex: 0 0 auto; border: 1px solid #6d8db4; border-radius: 7px; }
            .task strong { display: block; font-size: .94rem; }
            .task span { display: block; margin-top: 3px; color: #91a6c2; font-size: .8rem; }
            .focus { padding: 25px; background: linear-gradient(160deg, #1573a333, #0e213f 74%); }
            .focus-label { color: #73c8ff; font-size: .78rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
            .focus h2 { margin-top: 16px; font-size: 1.55rem; line-height: 1.15; }
            .focus p { color: #b5c9e3; line-height: 1.6; font-size: .92rem; }
            .ready { margin-top: 22px; padding: 14px; border-radius: 13px; background: #071c31aa; color: #c1d5eb; font-size: .82rem; line-height: 1.5; }
            .ready b { color: #ffffff; }
            .timeline { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-top: 18px; }
            .timeline-entry { min-height: 112px; gap: 13px; padding: 20px; }
            .step { display: grid; width: 28px; height: 28px; flex: 0 0 auto; place-items: center; border-radius: 50%; color: #74caff; background: #1a4164; font-size: .76rem; font-weight: 800; }
            .timeline-entry strong { display: block; font-size: .9rem; }
            .timeline-entry span { display: block; margin-top: 4px; color: #99acc5; font-size: .78rem; line-height: 1.45; }
            footer { margin-top: 32px; color: #7188a6; font-size: .75rem; }
            @media (max-width: 760px) { .shell { width: min(100% - 24px, 1120px); padding-top: 18px; } .topbar { padding-bottom: 28px; } .grid, .timeline { grid-template-columns: 1fr; } .private { display: none; } }
        </style>
    </head>
    <body>
        <main class="shell">
            <header class="topbar">
                <div class="brand"><span class="brand-mark">ID</span> IRFAN PA</div>
                <span class="private">PRIVATE CONTROL CENTRE</span>
            </header>

            <p class="eyebrow">Personal operations, in one calm place</p>
            <h1>Your assistant is taking shape.</h1>
            <p class="intro">A secure, always-available workspace for the work you decide to delegate: requests, follow-ups, approvals, and the record of what happened.</p>
            <div class="status"><span class="dot"></span> Foundation online locally</div>

            <section class="grid" aria-label="Assistant overview">
                <article class="panel tasks">
                    <div class="section-heading"><h2>First workspace</h2><span class="count">3 setup items</span></div>
                    <div class="task-list">
                        <div class="task"><span class="check"></span><div><strong>Protect the workspace</strong><span>Owner sign-in and access rules come before external connections.</span></div></div>
                        <div class="task"><span class="check"></span><div><strong>Connect your work channels</strong><span>Only services you approve will be connected, one at a time.</span></div></div>
                        <div class="task"><span class="check"></span><div><strong>Define approval limits</strong><span>Your assistant can prepare work; actions with impact stay reviewable.</span></div></div>
                    </div>
                </article>

                <aside class="panel focus">
                    <div class="focus-label">Design principle</div>
                    <h2>Useful autonomy, clear control.</h2>
                    <p>Every future task will show its status, source, and next action—so you always know what your assistant is doing for you.</p>
                    <div class="ready"><b>Ready for the next build step:</b><br>owner access and a private task inbox.</div>
                </aside>
            </section>

            <section class="timeline" aria-label="Build plan">
                <article class="panel timeline-entry"><span class="step">01</span><div><strong>Private access</strong><span>One owner account, secure sessions, and an audit trail.</span></div></article>
                <article class="panel timeline-entry"><span class="step">02</span><div><strong>Task inbox</strong><span>Create, review, prioritise, and approve assistant work.</span></div></article>
                <article class="panel timeline-entry"><span class="step">03</span><div><strong>Approved integrations</strong><span>Connect email, calendar, and other work tools with scoped access.</span></div></article>
            </section>

            <footer>IRFAN PA · Built for Irfan Dossani · Local foundation only — not yet deployed</footer>
        </main>
    </body>
</html>
