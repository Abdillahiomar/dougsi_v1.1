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
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..900;1,9..144,400..900&family=Inter:opsz,wght@14..32,400..700&display=swap" rel="stylesheet">
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

    /* ---------- COULEURS ---------- */
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

    /* ---------- HERO (Pitch principal) ---------- */
    .hero {
      background: var(--navy); color: var(--white);
      position: relative; overflow: hidden; padding: 80px 0 100px;
    }
    .hero .blob {
      position: absolute; border-radius: 50%; filter: blur(60px);
      opacity: 0.15;
    }
    .blob-1 { width: 500px; height: 500px; background: var(--primary); top: -200px; right: -100px; }
    .blob-2 { width: 400px; height: 400px; background: var(--primary); bottom: -200px; left: -100px; }
    .hero .container { position: relative; z-index: 2; display: grid; grid-template-columns: 1.1fr 1fr; gap: 60px; align-items: center; }
    .hero-badge {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.3);
      color: var(--primary-soft); padding: 6px 18px; border-radius: 999px;
      font-size: 13px; font-weight: 600; margin-bottom: 24px;
    }
    .hero h1 {
      font-family: 'Fraunces', serif; font-weight: 900; font-size: 48px;
      line-height: 1.1; letter-spacing: -1px;
    }
    .hero h1 em { color: var(--primary); font-style: normal; }
    .hero p {
      font-size: 18px; color: rgba(255,255,255,0.85);
      max-width: 520px; margin: 20px 0 32px;
    }
    .hero-cta { display: flex; gap: 14px; flex-wrap: wrap; }
    .hero-stats {
      display: flex; gap: 40px; margin-top: 38px;
      padding-top: 28px; border-top: 1px solid rgba(255,255,255,0.08);
    }
    .hero-stats .num {
      font-family: 'Fraunces', serif; font-weight: 700; font-size: 30px;
      color: var(--white); line-height: 1.1;
    }
    .hero-stats .lbl { font-size: 13px; color: rgba(255,255,255,0.6); }

    /* Hero visuel (mockup avec overlay play) */
    .hero-visual { perspective: 1200px; }
    .hero-mock {
      border-radius: 18px; overflow: hidden;
      box-shadow: 0 30px 70px rgba(0,0,0,0.5);
      transform: rotateY(-6deg) rotateX(4deg);
      transition: transform 0.5s ease;
      position: relative;
    }
    .hero-mock:hover { transform: rotateY(-2deg) rotateX(2deg); }
    .hero-mock img { display: block; width: 100%; height: auto; }
    .play-overlay {
      position: absolute; inset: 0;
      background: rgba(15,23,42,0.5);
      display: flex; align-items: center; justify-content: center;
      opacity: 0; transition: opacity 0.3s ease;
      cursor: pointer;
    }
    .hero-mock:hover .play-overlay { opacity: 1; }
    .play-btn {
      width: 70px; height: 70px; border-radius: 50%;
      background: var(--primary); display: flex; align-items: center; justify-content: center;
      font-size: 30px; color: white; box-shadow: 0 0 0 12px rgba(16,185,129,0.3);
      transition: transform 0.2s;
    }
    .play-btn:hover { transform: scale(1.08); }

    /* ---------- PREUVE SOCIALE ---------- */
    .proof {
      background: var(--white); padding: 32px 0;
      border-bottom: 1px solid var(--border);
    }
    .proof .container { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; }
    .proof .label { color: var(--slate-text); font-size: 13px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; }
    .pilot-card {
      display: flex; align-items: center; gap: 16px;
      background: var(--slate); border: 1px solid var(--border); border-radius: 16px;
      padding: 12px 24px;
    }
    .pilot-card .avatar {
      width: 48px; height: 48px; border-radius: 12px;
      background: var(--primary); display: flex; align-items: center; justify-content: center;
      font-family: 'Fraunces', serif; font-weight: 900; font-size: 22px; color: white;
    }
    .pilot-card strong { display: block; font-size: 15px; }
    .pilot-card span { font-size: 13px; color: var(--slate-text); }
    .pilot-badge {
      background: var(--primary-soft); color: var(--primary-dark);
      font-size: 11px; font-weight: 700; padding: 2px 12px; border-radius: 999px;
    }

    /* ---------- SECTIONS ---------- */
    .section { padding: 90px 0; }
    .section-header {
      text-align: center; max-width: 700px; margin: 0 auto 56px;
    }
    .eyebrow {
      color: var(--primary); font-weight: 700; font-size: 14px;
      letter-spacing: 1px; text-transform: uppercase;
    }
    .section-header h2 {
      font-family: 'Fraunces', serif; font-weight: 700; font-size: 38px;
      margin-top: 8px; line-height: 1.15;
    }
    .section-header p {
      color: var(--slate-text); font-size: 17px; margin-top: 12px;
    }

    /* Grille fonctionnalités */
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

    /* ---------- DÉMONSTRATION VIDÉO (remplace la 2e capture) ---------- */
    .video-section { background: var(--slate); }
    .video-wrapper {
      max-width: 900px; margin: 0 auto;
      background: var(--navy); border-radius: 24px; overflow: hidden;
      box-shadow: 0 24px 60px rgba(15,23,42,0.15);
      position: relative;
    }
    .video-wrapper img { display: block; width: 100%; height: auto; }
    .video-play {
      position: absolute; inset: 0;
      background: rgba(15,23,42,0.4);
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      cursor: pointer; transition: background 0.3s;
    }
    .video-play:hover { background: rgba(15,23,42,0.25); }
    .video-play .big-play {
      width: 80px; height: 80px; border-radius: 50%;
      background: var(--primary); display: flex; align-items: center; justify-content: center;
      font-size: 36px; color: white; box-shadow: 0 0 0 16px rgba(16,185,129,0.25);
      transition: transform 0.2s;
    }
    .video-play .big-play:hover { transform: scale(1.06); }
    .video-play p { color: white; font-weight: 600; margin-top: 20px; font-size: 18px; }

    /* ---------- ROI / CHIFFRES CLÉS ---------- */
    .roi-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 24px; }
    .roi-item {
      text-align: center; padding: 24px; background: var(--slate); border-radius: 20px;
      border: 1px solid var(--border);
    }
    .roi-item .number {
      font-family: 'Fraunces', serif; font-weight: 900; font-size: 36px;
      color: var(--primary);
    }
    .roi-item .label { font-size: 14px; color: var(--slate-text); margin-top: 4px; }

    /* ---------- TÉMOIGNAGE ---------- */
    .testimonial {
      background: var(--navy); color: var(--white); border-radius: 24px;
      padding: 48px 56px; max-width: 800px; margin: 0 auto;
      position: relative;
    }
    .testimonial .quote {
      font-family: 'Fraunces', serif; font-size: 24px; font-weight: 600;
      line-height: 1.4;
    }
    .testimonial .author { margin-top: 20px; display: flex; align-items: center; gap: 16px; }
    .testimonial .author .photo {
      width: 52px; height: 52px; border-radius: 50%;
      background: var(--primary); display: flex; align-items: center; justify-content: center;
      font-weight: 700; font-size: 20px; color: white;
    }
    .testimonial .author .name { font-weight: 700; }
    .testimonial .author .title { font-size: 14px; color: rgba(255,255,255,0.7); }

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
    details summary::after {
      content: "+"; font-size: 24px; color: var(--primary);
      transition: transform 0.2s;
    }
    details[open] summary::after { content: "−"; }
    details .answer { padding: 0 24px 20px; color: var(--slate-text); font-size: 14.5px; }

    /* ---------- CTA FINAL ---------- */
    .cta-final {
      background: var(--navy); color: var(--white); text-align: center;
      border-radius: 28px; padding: 72px 40px; position: relative; overflow: hidden;
    }
    .cta-final h2 {
      font-family: 'Fraunces', serif; font-weight: 700; font-size: 38px;
      line-height: 1.15;
    }
    .cta-final h2 em { color: var(--primary); font-style: normal; }
    .cta-final p { font-size: 18px; color: rgba(255,255,255,0.82); margin: 16px auto 30px; max-width: 520px; }

    /* ---------- FOOTER ---------- */
    footer {
      background: var(--navy); color: rgba(255,255,255,0.7);
      padding: 48px 0 28px; border-top: 1px solid var(--navy-light);
    }
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
    .footer-bottom {
      border-top: 1px solid var(--navy-light); margin-top: 32px;
      padding-top: 20px; text-align: center; font-size: 13px; opacity: 0.6;
    }

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
      .hero-mock { transform: none; }
      .grid-3, .roles-grid, .steps, .roi-grid { grid-template-columns: 1fr 1fr; }
      .nav-links { display: none; }
    }
    @media (max-width: 640px) {
      .hero h1 { font-size: 32px; }
      .section-header h2 { font-size: 28px; }
      .grid-3, .roles-grid, .steps, .roi-grid { grid-template-columns: 1fr; }
      .testimonial { padding: 28px 20px; }
      .cta-final { padding: 40px 20px; }
      .cta-final h2 { font-size: 28px; }
      .hero-stats { gap: 20px; flex-wrap: wrap; }
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
      <a href="#demo">Démo</a>
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
        <div class="stat"><div class="num" id="stat1">0</div><div class="lbl">écoles pilotes</div></div>
        <div class="stat"><div class="num" id="stat2">0</div><div class="lbl">francs djiboutiens</div></div>
        <div class="stat"><div class="num" id="stat3">0</div><div class="lbl">modules intégrés</div></div>
      </div>
    </div>
    <div class="hero-visual">
      <div class="hero-mock">
        <img src="{{ asset('images/dashboard.png') }}" alt="Tableau de bord Dugsi">
        <div class="play-overlay" onclick="alert('🎬 Vidéo de démonstration — contactez-nous pour une présentation personnalisée.')">
          <div class="play-btn">▶</div>
        </div>
      </div>
    </div>
  </div>
</header>

<!-- ====== PREUVE SOCIALE ====== -->
<section class="proof">
  <div class="container">
    <span class="label">Déjà adopté sur le terrain</span>
    <div class="pilot-card">
      <div class="avatar">E</div>
      <div>
        <strong>École Les Petits Futés</strong>
        <span>École pilote — Djibouti</span>
        <div class="pilot-badge">✓ Utilise Dougsi au quotidien</div>
      </div>
    </div>
    <span style="font-size:14px; color:var(--slate-text);">+ d'autres écoles nous rejoignent chaque mois</span>
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
      <div class="card reveal"><div class="ico">🎓</div><h3>Inscriptions & élèves</h3><p>Gérez les dossiers élèves, les classes et les années scolaires en quelques clics.</p></div>
      <div class="card reveal d1"><div class="ico">📄</div><h3>Bulletins automatiques</h3><p>Saisissez les notes, Dougsi génère les bulletins et calcule les moyennes automatiquement.</p></div>
      <div class="card reveal d2"><div class="ico">💰</div><h3>Paiements & impayés</h3><p>Suivez chaque encaissement, identifiez les impayés et éditez les reçus en temps réel.</p></div>
      <div class="card reveal"><div class="ico">📅</div><h3>Absences & emploi du temps</h3><p>Enregistrez les présences et gardez le planning des classes toujours à jour.</p></div>
      <div class="card reveal d1"><div class="ico">👨‍👩‍👧</div><h3>Espace parents</h3><p>Les parents consultent notes, absences et annonces depuis leur propre espace.</p></div>
      <div class="card reveal d2"><div class="ico">🔐</div><h3>Rôles & sécurité</h3><p>Chaque membre du personnel accède uniquement à ce qui le concerne. Vos données sont protégées.</p></div>
    </div>
  </div>
</section>

<!-- ====== DÉMONSTRATION VIDÉO (remplace la 2e capture) ====== -->
<section class="section video-section" id="demo">
  <div class="container">
    <div class="section-header reveal">
      <div class="eyebrow">Voir Dougsi en action</div>
      <h2>Une démonstration vidéo de 2 minutes</h2>
      <p>Découvrez comment Dougsi transforme la gestion quotidienne de votre école.</p>
    </div>
    <div class="video-wrapper reveal">
      <img src="{{ asset('images/paiements.png') }}" alt="Aperçu de la gestion des paiements">
      <div class="video-play" onclick="alert('🎬 Vidéo de démonstration — Contactez-nous pour une présentation personnalisée en direct.')">
        <div class="big-play">▶</div>
        <p>▶ Regarder la démo (2 min)</p>
      </div>
    </div>
  </div>
</section>

<!-- ====== ROI / CHIFFRES CLÉS ====== -->
<section class="section" style="background:var(--slate);">
  <div class="container">
    <div class="section-header reveal">
      <div class="eyebrow">Résultats concrets</div>
      <h2>Ce que Dougsi apporte à votre école</h2>
    </div>
    <div class="roi-grid">
      <div class="roi-item reveal"><div class="number">70%</div><div class="label">de temps gagné sur la gestion</div></div>
      <div class="roi-item reveal d1"><div class="number">100%</div><div class="label">de visibilité sur les impayés</div></div>
      <div class="roi-item reveal d2"><div class="number">5x</div><div class="label">plus rapide pour les bulletins</div></div>
      <div class="roi-item reveal d3"><div class="number">24/7</div><div class="label">accès pour les parents</div></div>
    </div>
  </div>
</section>

<!-- ====== TÉMOIGNAGE ====== -->
<section class="section">
  <div class="container">
    <div class="testimonial reveal">
      <div class="quote">“Dougsi nous a permis de passer de la paperasse à une gestion fluide. Les parents adorent l'espace en ligne et nous avons réduit de 80 % le temps passé sur les relevés de notes.”</div>
      <div class="author">
        <div class="photo">M</div>
        <div>
          <div class="name">M. Hassan Ali</div>
          <div class="title">Directeur, École Les Petits Futés — Djibouti</div>
        </div>
      </div>
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
    <div style="max-width:680px; margin:0 auto; background:var(--navy); color:white; border-radius:24px; padding:48px 36px; text-align:center; position:relative; overflow:hidden;">
      <h3 style="font-family:'Fraunces',serif; font-size:28px;">Sur devis, selon vos besoins</h3>
      <p style="margin:16px 0 24px; color:rgba(255,255,255,0.8);">Nous établissons ensemble une offre adaptée au nombre d'élèves et aux fonctionnalités qui comptent pour vous.</p>
      <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap; margin-bottom:28px;">
        <span style="background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.3); color:var(--primary-soft); padding:6px 16px; border-radius:999px; font-size:13px; font-weight:600;">✓ Mise en place incluse</span>
        <span style="background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.3); color:var(--primary-soft); padding:6px 16px; border-radius:999px; font-size:13px; font-weight:600;">✓ Formation du personnel</span>
        <span style="background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.3); color:var(--primary-soft); padding:6px 16px; border-radius:999px; font-size:13px; font-weight:600;">✓ Support en français</span>
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
        <a href="#" class="social-icon">
          <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073Z"/></svg>
        </a>
        <a href="#" class="social-icon">
          <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
        </a>
      </div>
    </div>
    <div class="footer-contact">
      <strong style="color:var(--white);">Contact</strong><br>
      📞 <a href="tel:+25377825892">77 33 40 40</a><br>
      ✉️ <a href="mailto:contact@dougsi.tech">contact@dougsi.tech</a><br>
      🌐 www.dougsi.tech
    </div>
  </div>
  <div class="footer-bottom">© 2025 DOUGSI — Tous droits réservés.</div>
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
        if (el.id === 'stat1') animateCounter(el, 1, ' école');
        else if (el.id === 'stat2') animateCounter(el, 100, '%');
        else if (el.id === 'stat3') animateCounter(el, 5, ' modules');
        obs.unobserve(el);
      }
    });
  }, { threshold: 0.5 });

  document.querySelectorAll('.stat .num').forEach(el => obs.observe(el));

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
      if (el.id === 'stat1') el.textContent = '1 école';
      else if (el.id === 'stat2') el.textContent = '100%';
      else if (el.id === 'stat3') el.textContent = '5 modules';
    });
  }
</script>
</body>
</html>