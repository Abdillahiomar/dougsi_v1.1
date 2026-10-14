<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DOUGSI — La plateforme de gestion scolaire tout-en-un</title>
  <meta name="description" content="Dougsi simplifie la gestion de votre école : inscriptions, bulletins, paiements, absences et communication. Démo gratuite.">
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..900;1,9..144,400..900&family=Inter:opsz,wght@14..32,400..700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <style>
    /* ---------- RESET & BASE ---------- */
    * { margin:0; padding:0; box-sizing:border-box; }
    html { scroll-behavior:smooth; }
    body {
      font-family: 'Inter', sans-serif;
      background: #ffffff;
      color: #0F172A;
      line-height: 1.6;
    }
    a { text-decoration: none; color: inherit; }
    .container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }

    /* ---------- COULEURS (identiques à l'application) ---------- */
    :root {
      --primary: #10B981;
      --primary-dark: #059669;
      --primary-soft: #D1FAE5;
      --navy: #0F172A;
      --navy-light: #1E293B;
      --slate: #F1F5F9;
      --slate-text: #475569;
      --border: #E2E8F0;
      --white: #FFFFFF;
      --amber: #E8A838;
      --red: #E05C3A;
    }

    /* ---------- BOUTONS ---------- */
    .btn {
      display: inline-flex; align-items: center; gap: 10px;
      font-weight: 700; font-size: 15px; padding: 14px 28px;
      border-radius: 14px; border: none; cursor: pointer;
      transition: all 0.2s ease;
    }
    .btn-primary {
      background: var(--primary); color: var(--white);
      box-shadow: 0 8px 24px rgba(16,185,129,0.35);
    }
    .btn-primary:hover { background: var(--primary-dark); transform: translateY(-3px); }
    .btn-outline {
      background: transparent; color: var(--white);
      border: 1.5px solid rgba(255,255,255,0.3);
    }
    .btn-outline:hover { background: rgba(255,255,255,0.08); border-color: var(--primary); }

    /* ---------- NAVBAR ---------- */
    .navbar {
      position: sticky; top: 0; z-index: 100;
      background: rgba(15,23,42,0.96); backdrop-filter: blur(12px);
      border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    .navbar .container {
      display: flex; align-items: center; justify-content: space-between;
      height: 74px;
    }
    .logo {
      font-family: 'Fraunces', serif; font-weight: 900; font-size: 28px;
      color: var(--white); letter-spacing: -0.5px;
    }
    .logo span { color: var(--primary); }
    .nav-links { display: flex; gap: 32px; align-items: center; }
    .nav-links a {
      color: rgba(255,255,255,0.8); font-weight: 500; font-size: 14px;
      transition: color 0.15s;
    }
    .nav-links a:hover { color: var(--primary); }
    .nav-actions { display: flex; gap: 12px; align-items: center; }
    .nav-login {
      color: var(--white); font-weight: 600; padding: 10px 20px;
      border-radius: 10px; transition: background 0.15s;
    }
    .nav-login:hover { background: rgba(255,255,255,0.08); }

    /* ---------- HERO ---------- */
    .hero {
      background: var(--navy); color: var(--white);
      position: relative; overflow: hidden; padding: 84px 0 110px;
    }
    .hero .blob {
      position: absolute; border-radius: 50%; filter: blur(70px);
      opacity: 0.16;
    }
    .blob-1 { width: 520px; height: 520px; background: var(--primary); top: -220px; right: -120px; }
    .blob-2 { width: 420px; height: 420px; background: var(--primary); bottom: -220px; left: -120px; }
    .hero .container { position: relative; z-index: 2; display: grid; grid-template-columns: 1.05fr 1fr; gap: 64px; align-items: center; }
    .hero-badge {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.3);
      color: var(--primary-soft); padding: 6px 18px; border-radius: 999px;
      font-size: 13px; font-weight: 600; margin-bottom: 24px;
    }
    .hero h1 {
      font-family: 'Fraunces', serif; font-weight: 900; font-size: 50px;
      line-height: 1.08; letter-spacing: -1.2px;
    }
    .hero h1 em { color: var(--primary); font-style: normal; }
    .hero p {
      font-size: 18px; color: rgba(255,255,255,0.85);
      max-width: 520px; margin: 22px 0 32px;
    }
    .hero-cta { display: flex; gap: 14px; flex-wrap: wrap; }
    .hero-stats {
      display: flex; gap: 40px; margin-top: 40px;
      padding-top: 28px; border-top: 1px solid rgba(255,255,255,0.08);
    }
    .hero-stats .num {
      font-family: 'Fraunces', serif; font-weight: 700; font-size: 30px;
      color: var(--white); line-height: 1.1;
    }
    .hero-stats .lbl { font-size: 13px; color: rgba(255,255,255,0.6); }

    /* ---------- MOCKUP D'INTERFACE (construit en CSS, fidèle au vrai design) ---------- */
    .app-mockup {
      border-radius: 18px; overflow: hidden;
      box-shadow: 0 30px 70px rgba(0,0,0,0.5);
      transform: rotateY(-6deg) rotateX(4deg);
      transition: transform 0.5s ease;
      background: var(--white);
      border: 1px solid rgba(255,255,255,0.08);
    }
    .app-mockup:hover { transform: rotateY(-2deg) rotateX(2deg); }
    .mock-topbar {
      display: flex; align-items: center; gap: 8px;
      padding: 12px 16px; background: #E2E8F0;
    }
    .mock-dot { width: 10px; height: 10px; border-radius: 50%; }
    .mock-dot.r { background: #F87171; } .mock-dot.y { background: #FBBF24; } .mock-dot.g { background: #34D399; }
    .mock-url {
      margin-left: 12px; font-family: 'JetBrains Mono', monospace; font-size: 11px;
      color: #64748B; background: #F8FAFC; border-radius: 6px; padding: 4px 12px;
      flex: 1;
    }
    .mock-app-body { display: flex; min-height: 300px; }
    .mock-sidebar {
      width: 150px; background: var(--navy); padding: 18px 0; flex-shrink: 0;
    }
    .mock-brand {
      font-family: 'Fraunces', serif; font-weight: 700; font-size: 15px;
      color: white; padding: 0 16px 16px; border-bottom: 1px solid rgba(255,255,255,.08); margin-bottom: 12px;
    }
    .mock-brand span { color: var(--primary); }
    .mock-nav-item {
      display: flex; align-items: center; gap: 8px;
      padding: 8px 16px; font-size: 11.5px; font-weight: 500;
      color: rgba(255,255,255,.55);
    }
    .mock-nav-item.active { background: rgba(255,255,255,.08); color: white; position: relative; }
    .mock-nav-item.active::before {
      content: ''; position: absolute; left: 0; top: 4px; bottom: 4px; width: 3px;
      background: var(--primary); border-radius: 0 2px 2px 0;
    }
    .mock-main { flex: 1; padding: 20px; background: var(--slate); }
    .mock-kpis { display: grid; grid-template-columns: repeat(3,1fr); gap: 10px; margin-bottom: 14px; }
    .mock-kpi {
      background: white; border: 1px solid var(--border); border-radius: 10px; padding: 10px 12px;
    }
    .mock-kpi.good { background: var(--navy); border-color: var(--navy); }
    .mock-kpi .k-lbl {
      font-family: 'JetBrains Mono', monospace; font-size: 8px; font-weight: 700;
      text-transform: uppercase; letter-spacing: .06em; color: #64748B; opacity: .7;
    }
    .mock-kpi.good .k-lbl { color: rgba(255,255,255,.55); }
    .mock-kpi .k-val {
      font-family: 'JetBrains Mono', monospace; font-weight: 700; font-size: 17px;
      color: var(--navy); margin-top: 3px;
    }
    .mock-kpi.good .k-val { color: white; }
    .mock-table { background: white; border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
    .mock-row {
      display: grid; grid-template-columns: 1.4fr 1fr 1fr; gap: 8px;
      padding: 9px 12px; font-size: 11px; border-bottom: 1px solid var(--border);
      align-items: center;
    }
    .mock-row:last-child { border-bottom: none; }
    .mock-row.header {
      font-family: 'JetBrains Mono', monospace; font-size: 8.5px; font-weight: 700;
      text-transform: uppercase; letter-spacing: .05em; color: #94A3B8; background: #F8FAFC;
    }
    .mock-badge {
      display: inline-block; font-family: 'JetBrains Mono', monospace; font-size: 8.5px; font-weight: 700;
      padding: 2px 8px; border-radius: 4px; width: fit-content;
    }
    .mock-badge.good { background: rgba(16,185,129,.12); color: #047857; }
    .mock-badge.warn { background: rgba(232,168,56,.18); color: #8A6010; }

    /* ---------- PREUVE SOCIALE : écoles qui utilisent Dougsi ---------- */
    .proof { background: var(--white); padding: 40px 0; border-bottom: 1px solid var(--border); }
    .proof .label {
      display: block; text-align: center; color: var(--slate-text); font-size: 13px; font-weight: 600;
      letter-spacing: 1px; text-transform: uppercase; margin-bottom: 22px;
    }
    .school-strip {
      display: flex; flex-wrap: wrap; justify-content: center; gap: 14px;
    }
    .school-chip {
      display: flex; align-items: center; gap: 10px;
      background: var(--slate); border: 1px solid var(--border); border-radius: 14px;
      padding: 10px 18px;
    }
    .school-chip .avatar {
      width: 34px; height: 34px; border-radius: 9px; flex-shrink: 0;
      background: var(--navy); display: flex; align-items: center; justify-content: center;
      font-family: 'Fraunces', serif; font-weight: 800; font-size: 15px; color: var(--primary);
    }
    .school-chip span { font-size: 14px; font-weight: 600; color: var(--navy); }

    /* ---------- SECTIONS ---------- */
    .section { padding: 90px 0; }
    .section-header { text-align: center; max-width: 700px; margin: 0 auto 56px; }
    .eyebrow { color: var(--primary); font-weight: 700; font-size: 14px; letter-spacing: 1px; text-transform: uppercase; }
    .section-header h2 { font-family: 'Fraunces', serif; font-weight: 700; font-size: 38px; margin-top: 8px; line-height: 1.15; }
    .section-header p { color: var(--slate-text); font-size: 17px; margin-top: 12px; }

    .grid-3 { display: grid; grid-template-columns: repeat(3,1fr); gap: 28px; }
    .card {
      background: var(--white); border: 1px solid var(--border);
      border-radius: 20px; padding: 32px 24px;
      transition: all 0.2s ease;
    }
    .card:hover { transform: translateY(-6px); box-shadow: 0 20px 50px rgba(15,23,42,0.08); border-color: var(--primary); }
    .card .ico {
      width: 56px; height: 56px; border-radius: 16px;
      background: var(--primary-soft); display: flex; align-items: center; justify-content: center;
      font-size: 28px; margin-bottom: 16px;
    }
    .card h3 { font-size: 18px; font-weight: 700; margin-bottom: 8px; }
    .card p { color: var(--slate-text); font-size: 14.5px; }

    /* ---------- APERÇU BULLETIN (2e mockup, section "Voir Dougsi en action") ---------- */
    .demo-section { background: var(--slate); }
    .demo-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: center; max-width: 1000px; margin: 0 auto; }
    .demo-text h3 { font-family: 'Fraunces', serif; font-size: 24px; margin-bottom: 12px; }
    .demo-text p { color: var(--slate-text); font-size: 15.5px; margin-bottom: 20px; }
    .demo-list { list-style: none; display: flex; flex-direction: column; gap: 10px; }
    .demo-list li { display: flex; align-items: center; gap: 10px; font-size: 14.5px; color: var(--navy); font-weight: 500; }
    .demo-list li::before { content: '✓'; color: var(--primary); font-weight: 900; }

    .bulletin-mock {
      background: white; border: 1px solid var(--border); border-radius: 16px;
      box-shadow: 0 20px 50px rgba(15,23,42,0.1); overflow: hidden;
    }
    .bulletin-mock-head { background: var(--navy); color: white; padding: 16px 20px; }
    .bulletin-mock-head .bname { font-family: 'Fraunces', serif; font-weight: 700; font-size: 15px; }
    .bulletin-mock-head .bsub { font-size: 11px; opacity: .6; margin-top: 2px; }
    .bulletin-mock-body { padding: 16px 20px; }
    .bm-row {
      display: flex; justify-content: space-between; align-items: center;
      padding: 8px 0; border-bottom: 1px solid var(--border); font-size: 12.5px;
    }
    .bm-row:last-child { border-bottom: none; }
    .bm-subject { font-weight: 600; color: var(--navy); }
    .bm-avg { font-family: 'JetBrains Mono', monospace; font-weight: 700; color: var(--primary-dark); }
    .bm-footer { background: var(--slate); padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; }
    .bm-footer .glabel { font-family: 'JetBrains Mono', monospace; font-size: 9px; text-transform: uppercase; letter-spacing: .06em; color: #64748B; }
    .bm-footer .gval { font-family: 'Fraunces', serif; font-weight: 800; font-size: 20px; color: var(--navy); }

    /* ---------- CE QUI CHANGE POUR VOUS ---------- */
    .benefits-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 24px; }
    .benefit-item {
      text-align: center; padding: 28px 20px; background: var(--slate); border-radius: 20px;
      border: 1px solid var(--border);
    }
    .benefit-item .bi { font-size: 30px; margin-bottom: 10px; }
    .benefit-item h4 { font-size: 15.5px; font-weight: 700; margin-bottom: 6px; }
    .benefit-item p { font-size: 13.5px; color: var(--slate-text); }

    /* ---------- ÉTAPES ---------- */
    .steps { display: grid; grid-template-columns: repeat(4,1fr); gap: 24px; }
    .step { text-align: center; padding: 16px; }
    .step .num {
      width: 60px; height: 60px; margin: 0 auto 16px;
      border-radius: 50%; background: var(--primary); color: white;
      font-family: 'Fraunces', serif; font-weight: 900; font-size: 26px;
      display: flex; align-items: center; justify-content: center;
    }
    .step h3 { font-size: 17px; font-weight: 700; margin-bottom: 6px; }
    .step p { font-size: 14px; color: var(--slate-text); }

    /* ---------- RÔLES ---------- */
    .roles-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 20px; }
    .role-card {
      background: var(--white); border: 1px solid var(--border); border-radius: 16px;
      padding: 28px 20px; text-align: center;
      transition: all 0.2s;
    }
    .role-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(15,23,42,0.06); }
    .role-card .ri { font-size: 32px; margin-bottom: 12px; }
    .role-card h3 { font-size: 16px; font-weight: 700; margin-bottom: 8px; }
    .role-card ul { list-style: none; font-size: 13.5px; color: var(--slate-text); }
    .role-card li { padding: 4px 0; }

    /* ---------- FAQ ---------- */
    .faq-list { max-width: 780px; margin: 0 auto; }
    details {
      background: var(--white); border: 1px solid var(--border); border-radius: 14px;
      margin-bottom: 12px; overflow: hidden;
    }
    details summary {
      padding: 20px 24px; font-weight: 600; font-size: 16px;
      cursor: pointer; list-style: none;
      display: flex; justify-content: space-between; align-items: center;
    }
    details summary::-webkit-details-marker { display: none; }
    details summary::after { content: "+"; font-size: 24px; color: var(--primary); transition: transform 0.2s; }
    details[open] summary::after { content: "−"; }
    details .answer { padding: 0 24px 20px; color: var(--slate-text); font-size: 14.5px; }

    /* ---------- TARIFS ---------- */
    .pricing-box {
      max-width: 680px; margin: 0 auto; background: var(--navy); color: white;
      border-radius: 24px; padding: 48px 36px; text-align: center; position: relative; overflow: hidden;
    }
    .pricing-box h3 { font-family: 'Fraunces', serif; font-size: 28px; }
    .pricing-box > p { margin: 16px 0 24px; color: rgba(255,255,255,0.8); }
    .pricing-tags { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-bottom: 28px; }
    .pricing-tags span {
      background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3); color: var(--primary-soft);
      padding: 6px 16px; border-radius: 999px; font-size: 13px; font-weight: 600;
    }

    /* ---------- CTA FINAL ---------- */
    .cta-final {
      background: var(--navy); color: var(--white); text-align: center;
      border-radius: 28px; padding: 72px 40px; position: relative; overflow: hidden;
    }
    .cta-final h2 { font-family: 'Fraunces', serif; font-weight: 700; font-size: 38px; line-height: 1.15; }
    .cta-final h2 em { color: var(--primary); font-style: normal; }
    .cta-final p { font-size: 18px; color: rgba(255,255,255,0.82); margin: 16px auto 30px; max-width: 520px; }

    /* ---------- FOOTER ---------- */
    footer { background: var(--navy); color: rgba(255,255,255,0.7); padding: 48px 0 28px; border-top: 1px solid var(--navy-light); }
    footer .container { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 32px; }
    .footer-logo { font-family: 'Fraunces', serif; font-weight: 900; font-size: 26px; color: var(--white); }
    .footer-logo span { color: var(--primary); }
    .footer-contact { font-size: 14.5px; line-height: 2; }
    .footer-contact a:hover { color: var(--primary); }
    .footer-social { display: flex; gap: 10px; margin-top: 16px; }
    .social-icon {
      width: 40px; height: 40px; border-radius: 10px;
      background: var(--navy-light); display: flex; align-items: center; justify-content: center;
      color: rgba(255,255,255,0.7); transition: all 0.15s;
    }
    .social-icon:hover { background: var(--primary); color: white; transform: translateY(-2px); }
    .footer-bottom { border-top: 1px solid var(--navy-light); margin-top: 32px; padding-top: 20px; text-align: center; font-size: 13px; opacity: 0.6; }

    /* ---------- ANIMATIONS ---------- */
    .reveal { opacity: 0; transform: translateY(40px); transition: all 0.8s ease; }
    .reveal.visible { opacity: 1; transform: translateY(0); }
    .reveal.d1 { transition-delay: 0.08s; }
    .reveal.d2 { transition-delay: 0.16s; }
    .reveal.d3 { transition-delay: 0.24s; }
    .reveal.d4 { transition-delay: 0.32s; }

    @media (max-width: 992px) {
      .hero .container { grid-template-columns: 1fr; text-align: center; }
      .hero p { margin-left: auto; margin-right: auto; }
      .hero-cta, .hero-stats { justify-content: center; }
      .app-mockup { transform: none; }
      .grid-3, .roles-grid, .steps, .benefits-grid { grid-template-columns: 1fr 1fr; }
      .nav-links { display: none; }
      .demo-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
      .hero h1 { font-size: 32px; }
      .section-header h2 { font-size: 28px; }
      .grid-3, .roles-grid, .steps, .benefits-grid { grid-template-columns: 1fr; }
      .cta-final { padding: 40px 20px; }
      .cta-final h2 { font-size: 28px; }
      .hero-stats { gap: 20px; flex-wrap: wrap; }
      .mock-app-body { flex-direction: column; }
      .mock-sidebar { width: 100%; display: flex; overflow-x: auto; padding: 10px; }
      .mock-brand { display: none; }
    }
  </style>
</head>
<body>

<!-- ====== NAVBAR ====== -->
<nav class="navbar">
  <div class="container">
    <div class="logo">DUG<span>SI</span></div>
    <div class="nav-links">
      <a href="#features">Fonctionnalités</a>
      <a href="#demo">Aperçu</a>
      <a href="#how">Comment ça marche</a>
      <a href="#faq">FAQ</a>
    </div>
    <div class="nav-actions">
      <a href="/login" class="nav-login">Connexion</a>
      <a href="https://wa.me/25377825892?text=Bonjour%2C%20je%20suis%20directeur%20d'école%20et%20je%20souhaite%20une%20démo%20de%20Dugsi." target="_blank" class="btn btn-primary">📲 Démo gratuite</a>
    </div>
  </div>
</nav>

<!-- ====== HERO ====== -->
<header class="hero">
  <div class="blob blob-1"></div><div class="blob blob-2"></div>
  <div class="container">
    <div class="hero-text">
      <div class="hero-badge">✦ Démonstration gratuite — sans engagement</div>
      <h1>La plateforme qui simplifie la gestion de <em>votre école</em>.</h1>
      <p>Inscriptions, bulletins, paiements, absences et communication avec les parents — tout au même endroit, accessible depuis votre téléphone.</p>
      <div class="hero-cta">
        <a href="https://wa.me/25377825892?text=Bonjour%2C%20je%20suis%20directeur%20d'école%20et%20je%20souhaite%20une%20démo%20de%20Dugsi." target="_blank" class="btn btn-primary">📲 Demander une démo gratuite</a>
        <a href="/login" class="btn btn-outline">Se connecter</a>
      </div>
      <div class="hero-stats">
        <div class="stat"><div class="num" id="stat1">0</div><div class="lbl">écoles utilisent Dougsi</div></div>
        <div class="stat"><div class="num" id="stat2">0</div><div class="lbl">modules intégrés</div></div>
        <div class="stat"><div class="num">DJ</div><div class="lbl">conçu pour Djibouti</div></div>
      </div>
    </div>
    <div class="hero-visual">
      <div class="app-mockup">
        <div class="mock-topbar">
          <span class="mock-dot r"></span><span class="mock-dot y"></span><span class="mock-dot g"></span>
          <div class="mock-url">dougsi.tech/dashboard</div>
        </div>
        <div class="mock-app-body">
          <div class="mock-sidebar">
            <div class="mock-brand">DUG<span>SI</span></div>
            <div class="mock-nav-item active">📊 Tableau de bord</div>
            <div class="mock-nav-item">🎓 Élèves</div>
            <div class="mock-nav-item">📄 Bulletins</div>
            <div class="mock-nav-item">💰 Finances</div>
            <div class="mock-nav-item">📅 Absences</div>
          </div>
          <div class="mock-main">
            <div class="mock-kpis">
              <div class="mock-kpi"><div class="k-lbl">Élèves</div><div class="k-val">340</div></div>
              <div class="mock-kpi good"><div class="k-lbl">Encaissé</div><div class="k-val">2,4M FDJ</div></div>
              <div class="mock-kpi"><div class="k-lbl">Recouvrement</div><div class="k-val">94%</div></div>
            </div>
            <div class="mock-table">
              <div class="mock-row header"><span>Élève</span><span>Classe</span><span>Statut</span></div>
              <div class="mock-row"><span>Ayan D.</span><span>CM2-A</span><span class="mock-badge good">Payé</span></div>
              <div class="mock-row"><span>Hamza O.</span><span>6ème-B</span><span class="mock-badge warn">Partiel</span></div>
              <div class="mock-row"><span>Sagal M.</span><span>CE1-C</span><span class="mock-badge good">Payé</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</header>

<!-- ====== PREUVE SOCIALE : écoles qui utilisent Dougsi ====== -->
<section class="proof">
  <div class="container">
    <span class="label">Déjà adopté par 6 écoles à Djibouti</span>
    <div class="school-strip">
      @foreach ([
        'Ecole Les Elites', 'Ecole Privée Maryama', 'Ecole Les Petits Futés',
        'Ecole Bien Grandir', 'Ecole Privée Iftin', 'Ecole Le Jardin des Enfants',
      ] as $schoolName)
        @php
          $shortName = trim(preg_replace('/^Ecole\s*(Priv[ée]e)?\s*(Le |Les |La )?/ui', '', $schoolName));
          $initial   = mb_strtoupper(mb_substr($shortName, 0, 1));
        @endphp
        <div class="school-chip">
          <div class="avatar">{{ $initial }}</div>
          <span>{{ $schoolName }}</span>
        </div>
      @endforeach
    </div>
  </div>
</section>

<!-- ====== FONCTIONNALITÉS ====== -->
<section class="section" id="features">
  <div class="container">
    <div class="section-header reveal">
      <div class="eyebrow">Tout-en-un</div>
      <h2>Une seule plateforme pour toute votre école</h2>
      <p>Fini les cahiers, les fichiers Excel éparpillés et les calculs manuels. Dougsi centralise votre gestion quotidienne.</p>
    </div>
    <div class="grid-3">
      <div class="card reveal"><div class="ico">🎓</div><h3>Inscriptions & élèves</h3><p>Gérez les dossiers élèves, les classes, les tuteurs et les documents requis en quelques clics.</p></div>
      <div class="card reveal d1"><div class="ico">📄</div><h3>Notes & bulletins</h3><p>Saisissez les notes, Dougsi calcule les moyennes, le classement et génère les bulletins automatiquement.</p></div>
      <div class="card reveal d2"><div class="ico">💰</div><h3>Paiements & impayés</h3><p>Plans de paiement par tranches, remises, suivi des impayés et reçus en temps réel.</p></div>
      <div class="card reveal"><div class="ico">📅</div><h3>Absences & retards</h3><p>Enregistrez les présences — les parents sont notifiés automatiquement par email en cas d'absence.</p></div>
      <div class="card reveal d1"><div class="ico">👨‍👩‍👧</div><h3>Espace parents</h3><p>Les parents consultent notes, absences, devoirs et annonces depuis leur propre espace.</p></div>
      <div class="card reveal d2"><div class="ico">🔐</div><h3>Rôles & sécurité</h3><p>Chaque membre du personnel accède uniquement à ce qui le concerne. Vos données sont isolées et protégées.</p></div>
    </div>
  </div>
</section>

<!-- ====== APERÇU — BULLETIN ====== -->
<section class="section demo-section" id="demo">
  <div class="container">
    <div class="demo-grid">
      <div class="demo-text reveal">
        <div class="eyebrow">Voir Dougsi en action</div>
        <h3>Des bulletins calculés et générés automatiquement</h3>
        <p>Vous saisissez les notes, Dougsi fait le reste : moyennes par matière, moyenne générale pondérée par coefficient, classement, mentions — selon les règles que vous configurez pour votre école.</p>
        <ul class="demo-list">
          <li>Moyennes et classement calculés automatiquement</li>
          <li>Mentions et appréciations personnalisables</li>
          <li>Export PDF prêt à imprimer ou envoyer</li>
        </ul>
        <div style="margin-top:24px;">
          <a href="https://wa.me/25377825892?text=Bonjour%2C%20je%20souhaite%20voir%20une%20d%C3%A9mo%20de%20Dugsi%20en%20direct." target="_blank" class="btn btn-primary" style="background:var(--navy);box-shadow:none;">💬 Demander une démo en direct</a>
        </div>
      </div>
      <div class="bulletin-mock reveal d1">
        <div class="bulletin-mock-head">
          <div class="bname">Bulletin — Trimestre 1</div>
          <div class="bsub">Ayan Dirieh · CM2-A</div>
        </div>
        <div class="bulletin-mock-body">
          <div class="bm-row"><span class="bm-subject">Mathématiques</span><span class="bm-avg">16,5 / 20</span></div>
          <div class="bm-row"><span class="bm-subject">Français</span><span class="bm-avg">14,2 / 20</span></div>
          <div class="bm-row"><span class="bm-subject">Sciences</span><span class="bm-avg">17,0 / 20</span></div>
          <div class="bm-row"><span class="bm-subject">Histoire-Géo</span><span class="bm-avg">13,8 / 20</span></div>
        </div>
        <div class="bm-footer">
          <div><div class="glabel">Moyenne générale</div><div class="gval">15,4 / 20</div></div>
          <span class="mock-badge good" style="font-size:10px;padding:4px 10px;">2ᵉ / 28</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ====== CE QUI CHANGE POUR VOUS ====== -->
<section class="section">
  <div class="container">
    <div class="section-header reveal">
      <div class="eyebrow">Pourquoi Dougsi</div>
      <h2>Ce que ça change pour votre école</h2>
    </div>
    <div class="benefits-grid">
      <div class="benefit-item reveal"><div class="bi">🇩🇯</div><h4>Conçu pour Djibouti</h4><p>Franc djiboutien, cycles scolaires et bulletins adaptés localement.</p></div>
      <div class="benefit-item reveal d1"><div class="bi">📧</div><h4>Communication automatique</h4><p>Les parents sont notifiés par email, depuis l'adresse de votre école.</p></div>
      <div class="benefit-item reveal d2"><div class="bi">🗂️</div><h4>Vos données vous appartiennent</h4><p>Export à tout moment des élèves, paiements et bulletins.</p></div>
      <div class="benefit-item reveal d3"><div class="bi">🇫🇷</div><h4>Support en français</h4><p>Interface simple et accompagnement en français, à chaque étape.</p></div>
    </div>
  </div>
</section>

<!-- ====== COMMENT ÇA MARCHE ====== -->
<section class="section" id="how" style="background:var(--slate);">
  <div class="container">
    <div class="section-header reveal">
      <div class="eyebrow">Simple et rapide</div>
      <h2>Votre école opérationnelle en 4 étapes</h2>
    </div>
    <div class="steps">
      <div class="step reveal"><div class="num">1</div><h3>On configure votre école</h3><p>Classes, cycles, frais et année scolaire adaptés à votre établissement.</p></div>
      <div class="step reveal d1"><div class="num">2</div><h3>On importe vos élèves</h3><p>Vos listes d'élèves et de personnel intégrées sans ressaisie fastidieuse.</p></div>
      <div class="step reveal d2"><div class="num">3</div><h3>On forme votre équipe</h3><p>Une prise en main guidée, en français, pour tout votre personnel.</p></div>
      <div class="step reveal d3"><div class="num">4</div><h3>Vous gérez tout</h3><p>Notes, paiements, bulletins, absences — depuis votre bureau ou votre téléphone.</p></div>
    </div>
  </div>
</section>

<!-- ====== RÔLES ====== -->
<section class="section">
  <div class="container">
    <div class="section-header reveal">
      <div class="eyebrow">Pour toute l'équipe</div>
      <h2>Chacun y trouve son compte</h2>
    </div>
    <div class="roles-grid">
      <div class="role-card reveal"><div class="ri">👔</div><h3>Directeur</h3><ul><li>Vue d'ensemble en temps réel</li><li>Suivi financier complet</li><li>Rapports automatiques</li></ul></div>
      <div class="role-card reveal d1"><div class="ri">💼</div><h3>Comptable</h3><ul><li>Encaissements & reçus</li><li>Liste des impayés</li><li>Journal de caisse</li></ul></div>
      <div class="role-card reveal d2"><div class="ri">👩‍🏫</div><h3>Enseignant</h3><ul><li>Saisie des notes</li><li>Appel & absences</li><li>Devoirs & annonces</li></ul></div>
      <div class="role-card reveal d3"><div class="ri">👨‍👩‍👧</div><h3>Parent</h3><ul><li>Notes de l'enfant</li><li>Absences signalées</li><li>Annonces de l'école</li></ul></div>
    </div>
  </div>
</section>

<!-- ====== TARIFS ====== -->
<section class="section" style="background:var(--slate);">
  <div class="container">
    <div class="section-header reveal">
      <div class="eyebrow">Tarifs</div>
      <h2>Un tarif adapté à votre école</h2>
      <p>Le prix dépend de la taille de votre établissement. Contactez-nous pour un devis personnalisé et une démo gratuite.</p>
    </div>
    <div class="pricing-box reveal">
      <h3>Sur devis, selon vos besoins</h3>
      <p>Nous établissons ensemble une offre adaptée au nombre d'élèves et aux fonctionnalités qui comptent pour vous.</p>
      <div class="pricing-tags">
        <span>✓ Mise en place incluse</span>
        <span>✓ Formation du personnel</span>
        <span>✓ Support en français</span>
      </div>
      <a href="https://wa.me/25377825892?text=Bonjour%2C%20je%20souhaite%20un%20devis%20pour%20Dugsi%20pour%20mon%20%C3%A9cole." target="_blank" class="btn btn-primary">📊 Demander un devis gratuit</a>
    </div>
  </div>
</section>

<!-- ====== FAQ ====== -->
<section class="section" id="faq">
  <div class="container">
    <div class="section-header reveal">
      <div class="eyebrow">Questions fréquentes</div>
      <h2>Tout ce que vous voulez savoir</h2>
    </div>
    <div class="faq-list">
      <details open><summary>Mes données sont-elles en sécurité ?</summary><div class="answer">Oui. Chaque école dispose de son espace isolé et vos données sont sauvegardées régulièrement. Seules les personnes que vous autorisez y ont accès, selon leur rôle.</div></details>
      <details><summary>Faut-il une connexion internet permanente ?</summary><div class="answer">Dougsi fonctionne dans votre navigateur, une connexion internet est donc nécessaire. Une simple connexion mobile suffit largement.</div></details>
      <details><summary>Et si mon personnel n'est pas à l'aise avec l'informatique ?</summary><div class="answer">L'interface est volontairement simple, en français. Nous formons votre équipe lors de la mise en place et restons disponibles pour les accompagner.</div></details>
      <details><summary>Est-ce adapté au système scolaire djiboutien ?</summary><div class="answer">Oui, Dougsi est conçu localement, avec les cycles, les frais et les bulletins adaptés aux écoles de Djibouti. La devise utilisée est le franc djiboutien (FDJ).</div></details>
      <details><summary>Puis-je récupérer mes données si besoin ?</summary><div class="answer">Vos données vous appartiennent. Vous pouvez les exporter à tout moment, notamment les listes d'élèves, les paiements et les bulletins.</div></details>
      <details><summary>Combien de temps pour démarrer ?</summary><div class="answer">Selon la taille de votre école, la configuration et l'import de vos élèves prennent généralement quelques jours. Nous vous accompagnons à chaque étape.</div></details>
    </div>
  </div>
</section>

<!-- ====== CTA FINAL ====== -->
<section class="section" style="padding-top:0;">
  <div class="container">
    <div class="cta-final reveal">
      <h2>Prêt à moderniser <em>votre école</em> ?</h2>
      <p>Contactez-nous dès aujourd'hui pour une démonstration gratuite et personnalisée de Dugsi.</p>
      <a href="https://wa.me/25377825892?text=Bonjour%2C%20je%20suis%20directeur%20d'école%20et%20je%20souhaite%20une%20démo%20de%20Dugsi." target="_blank" class="btn btn-primary" style="font-size:16px; padding:16px 36px;">📲 Demander ma démo gratuite</a>
    </div>
  </div>
</section>

<!-- ====== FOOTER ====== -->
<footer>
  <div class="container">
    <div>
      <div class="footer-logo">DUG<span>SI</span></div>
      <p style="font-size:14px; margin-top:6px; opacity:0.7;">Plateforme de gestion scolaire conçue pour les écoles de Djibouti.</p>
      <div class="footer-social">
        <a href="https://wa.me/25377825892" target="_blank" class="social-icon">
          <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.71.306 1.263.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
        </a>
      </div>
    </div>
    <div class="footer-contact">
      <strong style="color:var(--white);">Contact</strong><br>
      📞 <a href="tel:+25377825892">77 82 58 92</a><br>
      ✉️ <a href="mailto:contact@dougsi.tech">contact@dougsi.tech</a><br>
      🌐 www.dougsi.tech
    </div>
  </div>
  <div class="footer-bottom">© {{ date('Y') }} DOUGSI — Tous droits réservés.</div>
</footer>

<!-- ====== SCRIPTS ====== -->
<script>
  // Compteurs animés
  function animateCounter(el, target, suffix = '') {
    let start = 0;
    const duration = 1400;
    const step = (timestamp) => {
      if (!start) start = timestamp;
      const progress = Math.min((timestamp - start) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      const current = Math.round(target * eased);
      el.textContent = current.toLocaleString('fr-FR') + suffix;
      if (progress < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  }

  const obs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        if (el.id === 'stat1') animateCounter(el, 6, ' écoles');
        else if (el.id === 'stat2') animateCounter(el, 10, ' modules');
        obs.unobserve(el);
      }
    });
  }, { threshold: 0.5 });

  document.querySelectorAll('.stat .num').forEach(el => { if (el.id) obs.observe(el); });

  // Reveal au scroll
  const revealObs = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('visible');
        revealObs.unobserve(e.target);
      }
    });
  }, { threshold: 0.12 });

  document.querySelectorAll('.reveal').forEach(el => revealObs.observe(el));

  // Si réduction de mouvement, on active tout directement
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    document.querySelectorAll('.reveal').forEach(el => el.classList.add('visible'));
    document.querySelectorAll('.stat .num').forEach(el => {
      if (el.id === 'stat1') el.textContent = '6 écoles';
      else if (el.id === 'stat2') el.textContent = '10 modules';
    });
  }
</script>
</body>
</html>
