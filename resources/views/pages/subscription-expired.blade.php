<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abonnement expiré — Dugsi</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,600;0,9..144,700;1,9..144,400&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy: #0F172A; --gold: #10B981; --gold2: #059669;
            --ink: #0F172A; --paper: #F1F5F9; --muted: #64748B; --line: #E2E8F0; --white: #FFFFFF;
        }

        html, body { height: 100%; font-family: 'Inter', sans-serif; background: var(--paper); color: var(--ink); }

        .wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 1.25rem; }

        .card {
            width: 100%; max-width: 440px; background: var(--white); border-radius: 16px;
            border: 1px solid var(--line); padding: 2.5rem; text-align: center;
            box-shadow: 0 20px 50px rgba(15,23,42,.08);
        }

        .icon-wrap {
            width: 56px; height: 56px; border-radius: 14px; background: rgba(224,92,58,.1);
            display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;
        }
        .icon-wrap svg { width: 28px; height: 28px; color: #C04020; }

        .title { font-family: 'Fraunces', serif; font-size: 1.375rem; font-weight: 700; color: var(--ink); margin-bottom: .65rem; }
        .desc { font-size: .9375rem; color: var(--muted); line-height: 1.6; margin-bottom: 2rem; }

        .btn-logout {
            display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
            width: 100%; padding: .75rem 1.5rem; border-radius: 10px;
            background: var(--navy); color: white; font-size: .9375rem; font-weight: 600;
            font-family: 'Inter', sans-serif; border: none; cursor: pointer;
            transition: background .15s;
        }
        .btn-logout:hover { background: #1E293B; }
        .btn-logout svg { width: 17px; height: 17px; }
    </style>
</head>
<body>

<div class="wrap">
    <div class="card">
        <div class="icon-wrap">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
        </div>

        <div class="title">Abonnement expiré</div>
        <div class="desc">
            L'abonnement de votre établissement à Dugsi est arrivé à échéance.
            Merci de contacter l'administration de votre école, ou directement le support Dugsi, pour régulariser la situation.
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Se déconnecter
            </button>
        </form>
    </div>
</div>

</body>
</html>
