<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion Superadmin — Dugsi</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,600;0,9..144,700;1,9..144,400&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy    : #0F172A;
            --navy2   : #1E293B;
            --gold    : #10B981;
            --gold2   : #059669;
            --ink     : #0F172A;
            --paper   : #F1F5F9;
            --muted   : #64748B;
            --line    : #E2E8F0;
            --red     : #EF4444;
            --white   : #FFFFFF;
        }

        html, body {
            height: 100%;
            font-family: 'Inter', sans-serif;
            background: var(--paper);
            color: var(--ink);
        }

        .auth-shell {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
        }

        .auth-left {
            background: var(--navy);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }

        .auth-left::before {
            content: '';
            position: absolute;
            inset: 0;
            opacity: .05;
            background-image:
                repeating-linear-gradient(45deg, rgba(255,255,255,.4) 0px, rgba(255,255,255,.4) 1px, transparent 1px, transparent 48px),
                repeating-linear-gradient(-45deg, rgba(255,255,255,.4) 0px, rgba(255,255,255,.4) 1px, transparent 1px, transparent 48px);
        }

        .auth-left::after {
            content: '';
            position: absolute;
            bottom: -120px;
            right: -120px;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            border: 60px solid rgba(16,185,129,.12);
        }

        .auth-brand {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .auth-brand-logo {
            width: 40px;
            height: 40px;
            background: rgba(16,185,129,.15);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-brand-logo svg { width: 22px; height: 22px; color: var(--gold); }

        .auth-brand-name {
            font-family: 'Fraunces', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            letter-spacing: -.02em;
        }

        .auth-brand-badge {
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--gold);
            background: rgba(16,185,129,.12);
            border: 1px solid rgba(16,185,129,.25);
            padding: 2px 8px;
            border-radius: 5px;
        }

        .auth-tagline { position: relative; z-index: 1; }

        .auth-tagline-title {
            font-family: 'Fraunces', serif;
            font-size: 2rem;
            font-weight: 600;
            color: white;
            line-height: 1.2;
            letter-spacing: -.03em;
            margin-bottom: 1rem;
        }

        .auth-tagline-title em {
            font-style: italic;
            color: var(--gold);
        }

        .auth-tagline-desc {
            font-size: .9375rem;
            color: rgba(255,255,255,.55);
            line-height: 1.65;
        }

        .auth-features {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            gap: .75rem;
        }

        .auth-feature {
            display: flex;
            align-items: center;
            gap: .75rem;
            font-size: .875rem;
            color: rgba(255,255,255,.6);
        }

        .auth-feature-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--gold);
            flex-shrink: 0;
        }

        .auth-right {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 2rem;
            background: var(--white);
        }

        .auth-form-wrap { width: 100%; max-width: 400px; }

        .form-title {
            font-family: 'Fraunces', serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--ink);
            letter-spacing: -.03em;
            margin-bottom: .35rem;
        }

        .form-subtitle {
            font-size: .9375rem;
            color: var(--muted);
            margin-bottom: 2.25rem;
        }

        .session-status {
            padding: .75rem 1rem;
            border-radius: 8px;
            background: rgba(16,185,129,.1);
            border: 1px solid rgba(16,185,129,.3);
            color: var(--gold2);
            font-size: .875rem;
            font-weight: 500;
            margin-bottom: 1.25rem;
        }

        .field { display: flex; flex-direction: column; gap: .4rem; margin-bottom: 1.25rem; }
        .field:last-of-type { margin-bottom: 0; }

        .field-label {
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--ink);
            opacity: .55;
        }

        .field-input {
            padding: .65rem .875rem;
            border-radius: 9px;
            border: 1.5px solid var(--line);
            background: var(--paper);
            font-size: .9375rem;
            font-family: 'Inter', sans-serif;
            color: var(--ink);
            outline: none;
            width: 100%;
            transition: border-color .15s, box-shadow .15s;
        }

        .field-input:focus {
            border-color: var(--gold);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(16,185,129,.12);
        }

        .field-input::placeholder { color: var(--muted); opacity: .6; }

        .field-error {
            font-size: .8125rem;
            color: var(--red);
            display: flex;
            align-items: center;
            gap: .35rem;
        }

        .field-error::before {
            content: '!';
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: var(--red);
            color: white;
            font-size: 9px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .pw-wrap { position: relative; }
        .pw-toggle {
            position: absolute;
            right: .75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--muted);
            display: flex;
            align-items: center;
            padding: 0;
            transition: color .12s;
        }
        .pw-toggle:hover { color: var(--gold); }
        .pw-toggle svg { width: 16px; height: 16px; }

        .auth-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 1.25rem 0;
        }

        .checkbox-wrap { display: flex; align-items: center; gap: .5rem; cursor: pointer; }

        .checkbox-input {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            border: 1.5px solid var(--line);
            cursor: pointer;
            accent-color: var(--gold);
        }

        .checkbox-label { font-size: .875rem; color: var(--muted); cursor: pointer; }

        .btn-submit {
            width: 100%;
            padding: .75rem 1.5rem;
            border-radius: 10px;
            background: var(--gold);
            color: white;
            font-size: 1rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            border: none;
            cursor: pointer;
            transition: background .15s, transform .1s, box-shadow .15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
        }

        .btn-submit:hover {
            background: var(--gold2);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(16,185,129,.3);
        }

        .btn-submit:active { transform: translateY(0); }
        .btn-submit:disabled { opacity: .6; cursor: not-allowed; transform: none; }
        .btn-submit svg { width: 17px; height: 17px; }

        @media (max-width: 768px) {
            .auth-shell { grid-template-columns: 1fr; }
            .auth-left  { display: none; }
            .auth-right { padding: 2rem 1.25rem; }
        }
    </style>
</head>
<body>

<div class="auth-shell">

    {{-- ── Panneau gauche ── --}}
    <div class="auth-left">
        <div class="auth-brand">
            <div class="auth-brand-logo">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"/>
                </svg>
            </div>
            <span class="auth-brand-name">Dougsi</span>
            <span class="auth-brand-badge">Superadmin</span>
        </div>

        <div class="auth-tagline">
            <div class="auth-tagline-title">
                Administrez<br>
                <em>toute la plateforme</em><br>
                Dugsi.
            </div>
            <div class="auth-tagline-desc">
                Un espace unique pour piloter l'ensemble des écoles
                clientes, leurs abonnements et leurs accès.
            </div>
        </div>

        <div class="auth-features">
            @foreach ([
                'Gestion des écoles clientes',
                'Suivi des abonnements et factures',
                'Contrôle des accès et des utilisateurs',
                'Journal d\'activité complet',
            ] as $feature)
                <div class="auth-feature">
                    <div class="auth-feature-dot"></div>
                    {{ $feature }}
                </div>
            @endforeach
        </div>
    </div>

    {{-- ── Panneau droit — formulaire ── --}}
    <div class="auth-right">
        {{ $slot }}
    </div>
</div>

</body>
</html>
