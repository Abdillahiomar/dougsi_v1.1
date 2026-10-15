<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié — Dugsi</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,600;0,9..144,700;1,9..144,400&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy: #0F172A; --navy2: #1E293B; --gold: #10B981; --gold2: #059669;
            --ink: #0F172A; --paper: #F1F5F9; --muted: #64748B; --line: #E2E8F0;
            --red: #EF4444; --white: #FFFFFF;
        }

        html, body { height: 100%; font-family: 'Inter', sans-serif; background: var(--paper); color: var(--ink); }

        .auth-shell { display: grid; grid-template-columns: 1fr 1fr; min-height: 100vh; }

        .auth-left {
            background: var(--navy); display: flex; flex-direction: column; justify-content: space-between;
            padding: 3rem; position: relative; overflow: hidden;
        }

        .auth-left::before {
            content: ''; position: absolute; inset: 0; opacity: .05;
            background-image:
                repeating-linear-gradient(45deg, rgba(255,255,255,.4) 0px, rgba(255,255,255,.4) 1px, transparent 1px, transparent 48px),
                repeating-linear-gradient(-45deg, rgba(255,255,255,.4) 0px, rgba(255,255,255,.4) 1px, transparent 1px, transparent 48px);
        }

        .auth-left::after {
            content: ''; position: absolute; bottom: -120px; right: -120px; width: 400px; height: 400px;
            border-radius: 50%; border: 60px solid rgba(16,185,129,.12);
        }

        .auth-brand { position: relative; z-index: 1; display: flex; align-items: center; gap: .75rem; }
        .auth-brand-logo { width: 40px; height: 40px; background: rgba(16,185,129,.15); border-radius: 10px; display: flex; align-items: center; justify-content: center; }
        .auth-brand-logo svg { width: 22px; height: 22px; color: var(--gold); }
        .auth-brand-name { font-family: 'Fraunces', serif; font-size: 1.5rem; font-weight: 700; color: white; letter-spacing: -.02em; }

        .auth-tagline { position: relative; z-index: 1; }
        .auth-tagline-title { font-family: 'Fraunces', serif; font-size: 2rem; font-weight: 600; color: white; line-height: 1.2; letter-spacing: -.03em; margin-bottom: 1rem; }
        .auth-tagline-title em { font-style: italic; color: var(--gold); }
        .auth-tagline-desc { font-size: .9375rem; color: rgba(255,255,255,.55); line-height: 1.65; }

        .auth-features { position: relative; z-index: 1; display: flex; flex-direction: column; gap: .75rem; }
        .auth-feature { display: flex; align-items: center; gap: .75rem; font-size: .875rem; color: rgba(255,255,255,.6); }
        .auth-feature-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--gold); flex-shrink: 0; }

        .auth-right { display: flex; align-items: center; justify-content: center; padding: 3rem 2rem; background: var(--white); }
        .auth-form-wrap { width: 100%; max-width: 400px; }

        .form-title { font-family: 'Fraunces', serif; font-size: 1.75rem; font-weight: 700; color: var(--ink); letter-spacing: -.03em; margin-bottom: .35rem; }
        .form-subtitle { font-size: .9375rem; color: var(--muted); margin-bottom: 2.25rem; line-height: 1.5; }

        .session-status {
            padding: .75rem 1rem; border-radius: 8px; background: rgba(16,185,129,.1); border: 1px solid rgba(16,185,129,.3);
            color: var(--gold2); font-size: .875rem; font-weight: 500; margin-bottom: 1.25rem; line-height: 1.5;
        }

        .notice-box {
            padding: .875rem 1rem; border-radius: 8px; background: rgba(239,68,68,.06); border: 1px solid rgba(239,68,68,.25);
            color: #991B1B; font-size: .875rem; font-weight: 500; margin-bottom: 1.25rem; line-height: 1.55;
        }

        .field { display: flex; flex-direction: column; gap: .4rem; margin-bottom: 1.25rem; }
        .field:last-of-type { margin-bottom: 0; }

        .field-label { font-family: 'JetBrains Mono', monospace; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: .08em; color: var(--ink); opacity: .55; }

        .field-input {
            padding: .65rem .875rem; border-radius: 9px; border: 1.5px solid var(--line); background: var(--paper);
            font-size: .9375rem; font-family: 'Inter', sans-serif; color: var(--ink); outline: none; width: 100%;
            transition: border-color .15s, box-shadow .15s;
        }

        .field-input:focus { border-color: var(--gold); background: var(--white); box-shadow: 0 0 0 3px rgba(16,185,129,.12); }
        .field-input::placeholder { color: var(--muted); opacity: .6; }

        .field-error { font-size: .8125rem; color: var(--red); display: flex; align-items: center; gap: .35rem; }
        .field-error::before {
            content: '!'; width: 14px; height: 14px; border-radius: 50%; background: var(--red); color: white;
            font-size: 9px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
        }

        .btn-submit {
            width: 100%; padding: .75rem 1.5rem; border-radius: 10px; background: var(--gold); color: white;
            font-size: 1rem; font-weight: 600; font-family: 'Inter', sans-serif; border: none; cursor: pointer;
            transition: background .15s, transform .1s, box-shadow .15s; display: flex; align-items: center; justify-content: center; gap: .5rem;
        }
        .btn-submit:hover { background: var(--gold2); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(16,185,129,.3); }
        .btn-submit:active { transform: translateY(0); }
        .btn-submit svg { width: 17px; height: 17px; }

        .auth-back { text-align: center; font-size: .875rem; color: var(--muted); margin-top: 1.5rem; }
        .auth-back a { color: var(--gold2); font-weight: 600; text-decoration: none; }
        .auth-back a:hover { text-decoration: underline; }

        @media (max-width: 768px) {
            .auth-shell { grid-template-columns: 1fr; }
            .auth-left  { display: none; }
            .auth-right { padding: 2rem 1.25rem; }
        }
    </style>
</head>
<body>

<div class="auth-shell">
    <div class="auth-left">
        <div class="auth-brand">
            <div class="auth-brand-logo">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                </svg>
            </div>
            <span class="auth-brand-name">Dougsi</span>
        </div>

        <div class="auth-tagline">
            <div class="auth-tagline-title">Mot de passe<br><em>oublié ?</em></div>
            <div class="auth-tagline-desc">
                Indiquez votre adresse email de connexion. Si un lien de
                réinitialisation peut être envoyé, vous le recevrez à cette adresse.
            </div>
        </div>

        <div class="auth-features">
            @foreach ([
                'Lien de réinitialisation valable 60 minutes',
                'Envoyé par votre établissement',
                'Aucune donnée partagée entre écoles',
            ] as $feature)
                <div class="auth-feature">
                    <div class="auth-feature-dot"></div>
                    {{ $feature }}
                </div>
            @endforeach
        </div>
    </div>

    <div class="auth-right">
        <div class="auth-form-wrap">
            <div class="form-title">Mot de passe oublié</div>
            <div class="form-subtitle">Entrez votre adresse email pour recevoir un lien de réinitialisation.</div>

            @if (session('status'))
                <div class="session-status">
                    {{ session('status') }}
                </div>
            @elseif (session('contactAdmin'))
                <div class="notice-box">
                    Votre établissement n'a pas encore activé l'envoi d'emails automatiques.
                    Merci de contacter l'administrateur de votre établissement pour réinitialiser votre mot de passe.
                </div>
            @else
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="field">
                        <label for="email" class="field-label">Adresse email</label>
                        <input id="email" name="email" type="email" class="field-input"
                               value="{{ old('email') }}"
                               placeholder="votre@email.dj" required autofocus autocomplete="email">
                        @error('email') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <button type="submit" class="btn-submit">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Envoyer le lien
                    </button>
                </form>
            @endif

            <div class="auth-back">
                <a href="{{ route('login') }}" wire:navigate>← Retour à la connexion</a>
            </div>
        </div>
    </div>
</div>

@livewireScripts
</body>
</html>
