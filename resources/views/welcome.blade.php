<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>DUGSI — La plateforme de gestion pour votre école</title>
<meta name="description" content="Dugsi simplifie la gestion de votre école : inscriptions, bulletins, paiements, absences et communication avec les parents. Démo gratuite.">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;0,9..144,700;0,9..144,900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
  :root{
    --emerald:#10B981; --emerald-dark:#059669; --emerald-soft:#D1FAE5;
    --navy:#0F172A; --navy-soft:#1E293B; --navy2:#1E293B;
    --slate:#F1F5F9; --slate-text:#475569; --slate-border:#E2E8F0;
    --white:#FFFFFF;
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  html{scroll-behavior:smooth;}
  body{font-family:'Inter',sans-serif;color:var(--navy);background:var(--white);line-height:1.6;}
  a{text-decoration:none;color:inherit;}
  .wrap{max-width:1140px;margin:0 auto;padding:0 24px;}

  .btn{display:inline-flex;align-items:center;gap:8px;font-weight:700;font-size:15px;
       padding:13px 24px;border-radius:12px;transition:transform .15s,box-shadow .15s;cursor:pointer;border:none;}
  .btn:hover{transform:translateY(-2px);}
  .btn-primary{background:var(--emerald);color:var(--white);box-shadow:0 8px 24px rgba(16,185,129,.35);}
  .btn-primary:hover{background:var(--emerald-dark);}
  .btn-ghost{background:rgba(255,255,255,.08);color:var(--white);border:1px solid rgba(255,255,255,.25);}

  /* Navbar */
  .nav{position:sticky;top:0;z-index:50;background:rgba(15,23,42,.96);backdrop-filter:blur(10px);}
  .nav .wrap{display:flex;align-items:center;justify-content:space-between;height:70px;}
  .logo{font-family:'Fraunces',serif;font-weight:900;font-size:28px;color:var(--white);letter-spacing:-.5px;}
  .logo span{color:var(--emerald);}
  .nav-links{display:flex;gap:26px;align-items:center;}
  .nav-links a.link{color:rgba(255,255,255,.85);font-weight:500;font-size:14.5px;transition:color .15s;}
  .nav-links a.link:hover{color:var(--emerald);}
  .nav-actions{display:flex;gap:12px;align-items:center;}
  .nav-login{color:var(--white);font-weight:600;font-size:15px;padding:10px 18px;border-radius:10px;transition:background .15s;}
  .nav-login:hover{background:rgba(255,255,255,.1);}

  /* Hero */
  .hero{background:var(--navy);color:var(--white);position:relative;overflow:hidden;padding:80px 0 88px;}
  .blob{position:absolute;border-radius:50%;opacity:.12;filter:blur(8px);}
  .b1{width:420px;height:420px;background:var(--emerald);top:-140px;right:-120px;opacity:.18;}
  .b2{width:300px;height:300px;background:var(--emerald);bottom:-140px;left:-120px;opacity:.1;}
  .hero .wrap{position:relative;z-index:2;display:grid;grid-template-columns:1.1fr 1fr;gap:48px;align-items:center;}
  .badge{display:inline-flex;align-items:center;gap:7px;background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.35);
         color:var(--emerald-soft);padding:7px 16px;border-radius:999px;font-size:13px;font-weight:600;margin-bottom:24px;}
  .hero h1{font-family:'Fraunces',serif;font-weight:900;font-size:46px;line-height:1.1;letter-spacing:-1px;}
  .hero h1 em{color:var(--emerald);font-style:normal;}
  .hero p{font-size:18px;color:rgba(255,255,255,.82);max-width:560px;margin:20px 0 32px;}
  .hero-cta{display:flex;gap:14px;flex-wrap:wrap;}

  /* Compteurs */
  .hero-stats{display:flex;gap:32px;margin-top:34px;padding-top:24px;border-top:1px solid rgba(255,255,255,.12);}
  .hero-stat .num{font-family:'Fraunces',serif;font-weight:700;font-size:28px;color:var(--white);line-height:1.1;}
  .hero-stat .lbl{font-size:13px;color:rgba(255,255,255,.6);margin-top:2px;}

  /* Capture inclinée */
  .hero-visual{perspective:1400px;}
  .hero-shot{border-radius:16px;overflow:hidden;box-shadow:0 30px 70px rgba(0,0,0,.45);
             transform:rotateY(-11deg) rotateX(5deg);transition:transform .5s ease;}
  .hero-shot:hover{transform:rotateY(-4deg) rotateX(2deg);}
  .hero-shot img{display:block;width:100%;height:auto;}
  .hero-shot .placeholder{background:var(--navy-soft);color:rgba(255,255,255,.5);
                          padding:70px 30px;text-align:center;font-size:14px;font-style:italic;}

  /* Preuve sociale */
  .proof{background:var(--white);padding:40px 0;border-bottom:1px solid var(--slate-border);}
  .proof .wrap{text-align:center;}
  .proof .label{color:var(--slate-text);font-size:13px;font-weight:600;letter-spacing:1px;text-transform:uppercase;margin-bottom:22px;}
  .proof-logos{display:flex;gap:20px;justify-content:center;align-items:center;flex-wrap:wrap;}
  .pilot{display:flex;align-items:center;gap:14px;background:var(--slate);border:1px solid var(--slate-border);border-radius:16px;padding:16px 26px;}
  .pilot .plogo{width:48px;height:48px;border-radius:12px;background:var(--emerald);display:flex;align-items:center;justify-content:center;color:var(--white);font-family:'Fraunces',serif;font-weight:900;font-size:22px;}
  .pilot .pinfo{text-align:left;}
  .pilot .pinfo strong{display:block;font-size:15px;}
  .pilot .pinfo span{font-size:13px;color:var(--slate-text);}
  .pilot-badge{display:inline-block;background:var(--emerald-soft);color:var(--emerald-dark);font-size:11px;font-weight:700;padding:3px 10px;border-radius:999px;margin-top:4px;}

  .section{padding:84px 0;}
  .section-head{text-align:center;max-width:640px;margin:0 auto 54px;}
  .eyebrow{color:var(--emerald);font-weight:700;font-size:14px;letter-spacing:1px;text-transform:uppercase;}
  .section-head h2{font-family:'Fraunces',serif;font-weight:700;font-size:38px;line-height:1.15;margin-top:10px;color:var(--navy);}
  .section-head p{color:var(--slate-text);font-size:17px;margin-top:14px;}

  /* Fonctionnalités */
  .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px;}
  .card{background:var(--white);border:1px solid var(--slate-border);border-radius:18px;padding:28px;
        transition:transform .18s,box-shadow .18s,border-color .18s;}
  .card:hover{transform:translateY(-4px);box-shadow:0 14px 40px rgba(15,23,42,.08);border-color:var(--emerald);}
  .card .ico{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:26px;margin-bottom:16px;background:var(--emerald-soft);transition:transform .18s;}
  .card:hover .ico{transform:translateY(-3px) scale(1.06);}
  .card h3{font-size:18px;font-weight:700;margin-bottom:8px;}
  .card p{color:var(--slate-text);font-size:14.5px;}

  /* Aperçu produit */
  .preview{background:var(--slate);}
  .mock{background:var(--white);border:1px solid var(--slate-border);border-radius:20px;overflow:hidden;box-shadow:0 20px 60px rgba(15,23,42,.1);}
  .mock-bar{background:var(--slate);padding:12px 16px;display:flex;gap:7px;align-items:center;border-bottom:1px solid var(--slate-border);}
  .mock-bar span{width:11px;height:11px;border-radius:50%;}
  .mock-bar .r{background:#fca5a5;} .mock-bar .y{background:#fde047;} .mock-bar .g{background:var(--emerald);}
  .mock img{display:block;width:100%;height:auto;transition:transform .4s ease;}
  .mock:hover img{transform:scale(1.02);}
  .mock-body{padding:60px 34px;text-align:center;color:var(--slate-text);}
  .preview-note{text-align:center;color:var(--slate-text);font-size:14px;margin-top:18px;font-style:italic;}

  /* Comment ça marche */
  .steps{display:grid;grid-template-columns:repeat(4,1fr);gap:22px;}
  .step{text-align:center;padding:12px;}
  .step .n{width:54px;height:54px;margin:0 auto 16px;border-radius:50%;background:var(--emerald);color:var(--white);font-family:'Fraunces',serif;font-weight:900;font-size:24px;display:flex;align-items:center;justify-content:center;}
  .step h3{font-size:16px;font-weight:700;margin-bottom:6px;}
  .step p{font-size:13.5px;color:var(--slate-text);}

  /* Rôles */
  .roles{background:var(--slate);}
  .role-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;}
  .role{background:var(--white);border:1px solid var(--slate-border);border-radius:16px;padding:24px;transition:transform .18s,box-shadow .18s;}
  .role:hover{transform:translateY(-3px);box-shadow:0 12px 32px rgba(15,23,42,.07);}
  .role .ri{font-size:30px;margin-bottom:12px;}
  .role h3{font-size:16px;font-weight:700;margin-bottom:10px;}
  .role ul{list-style:none;}
  .role li{font-size:13.5px;color:var(--slate-text);padding-left:18px;position:relative;margin-bottom:6px;}
  .role li::before{content:"✓";position:absolute;left:0;color:var(--emerald);font-weight:700;}

  /* Tarifs */
  .pricing .box{max-width:640px;margin:0 auto;background:var(--navy);color:var(--white);border-radius:24px;padding:52px 40px;text-align:center;position:relative;overflow:hidden;}
  .pricing .box h3{font-family:'Fraunces',serif;font-size:28px;font-weight:700;margin-bottom:14px;}
  .pricing .box p{font-size:16px;color:rgba(255,255,255,.82);max-width:440px;margin:0 auto 26px;}
  .pricing .feat{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-bottom:28px;}
  .pricing .feat span{background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.35);color:var(--emerald-soft);padding:6px 14px;border-radius:999px;font-size:13px;font-weight:600;}

  /* FAQ */
  .faq-list{max-width:760px;margin:0 auto;}
  details{background:var(--white);border:1px solid var(--slate-border);border-radius:14px;margin-bottom:12px;overflow:hidden;}
  details summary{padding:20px 24px;font-weight:600;font-size:16px;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center;}
  details summary::-webkit-details-marker{display:none;}
  details summary::after{content:"+";font-size:24px;color:var(--emerald);font-weight:400;transition:transform .2s;}
  details[open] summary::after{content:"−";}
  details .ans{padding:0 24px 20px;color:var(--slate-text);font-size:14.5px;}

  /* CTA final */
  .cta-final{background:var(--navy);color:var(--white);text-align:center;border-radius:28px;padding:64px 32px;position:relative;overflow:hidden;}
  .cta-final h2{font-family:'Fraunces',serif;font-weight:700;font-size:36px;line-height:1.15;}
  .cta-final h2 em{color:var(--emerald);font-style:normal;}
  .cta-final p{font-size:18px;color:rgba(255,255,255,.82);margin:16px auto 30px;max-width:520px;}

  /* Footer */
  footer{background:var(--navy);color:rgba(255,255,255,.72);padding:48px 0 32px;border-top:1px solid var(--navy-soft);}
  footer .wrap{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:24px;}
  .foot-logo{font-family:'Fraunces',serif;font-weight:900;font-size:26px;color:var(--white);}
  .foot-logo span{color:var(--emerald);}
  .foot-contact{font-size:14.5px;line-height:1.9;}
  .foot-contact a:hover{color:var(--emerald);}
  .foot-bottom{border-top:1px solid var(--navy-soft);margin-top:32px;padding-top:20px;font-size:13px;opacity:.6;text-align:center;}

  .foot-social{display:flex;gap:10px;margin-top:18px;}
  .social-btn{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;
              background:var(--navy2);color:rgba(255,255,255,.75);transition:background .15s,color .15s,transform .15s;}
  .social-btn:hover{background:var(--emerald);color:var(--white);transform:translateY(-2px);}

  /* ── Animations ── */
  @keyframes fadeUp{from{opacity:0;transform:translateY(28px);}to{opacity:1;transform:translateY(0);}}
  @keyframes floatBlob{0%,100%{transform:translateY(0);}50%{transform:translateY(-30px);}}

  .hero .badge{animation:fadeUp .7s ease both;}
  .hero h1{animation:fadeUp .7s ease .12s both;}
  .hero p{animation:fadeUp .7s ease .24s both;}
  .hero-cta{animation:fadeUp .7s ease .36s both;}
  .hero-stats{animation:fadeUp .7s ease .48s both;}
  .hero-visual{animation:fadeUp .8s ease .3s both;}

  .b1{animation:floatBlob 8s ease-in-out infinite;}
  .b2{animation:floatBlob 10s ease-in-out infinite;}

  .reveal{opacity:0;transform:translateY(34px);transition:opacity .7s ease,transform .7s ease;}
  .reveal.visible{opacity:1;transform:translateY(0);}
  .reveal.d1{transition-delay:.08s;} .reveal.d2{transition-delay:.16s;} .reveal.d3{transition-delay:.24s;}

  @media(max-width:900px){
    .grid,.role-grid{grid-template-columns:1fr 1fr;}
    .steps{grid-template-columns:1fr 1fr;}
    .nav-links{display:none;}
  }
  @media(max-width:820px){
    .hero .wrap{grid-template-columns:1fr;text-align:center;}
    .hero p{margin-left:auto;margin-right:auto;}
    .hero-cta,.hero-stats{justify-content:center;}
    .hero-shot{transform:none;}
  }
  @media(max-width:640px){
    .hero h1{font-size:34px;} .hero p{font-size:16px;}
    .section-head h2{font-size:30px;}
    .grid,.role-grid,.steps{grid-template-columns:1fr;}
    .nav-login{display:none;} .cta-final h2{font-size:28px;}
    .hero-stats{gap:22px;} .hero-stat .num{font-size:24px;}
  }

  @media(prefers-reduced-motion:reduce){
    *,.b1,.b2{animation:none !important;transition:none !important;}
    .reveal{opacity:1;transform:none;}
    .hero-shot{transform:none;}
  }
</style>
</head>
<body>

<nav class="nav">
  <div class="wrap">
    <div class="logo">DUG<span>SI</span></div>
    <div class="nav-links">
      <a href="#features" class="link">Fonctionnalités</a>
      <a href="#how" class="link">Comment ça marche</a>
      <a href="#faq" class="link">FAQ</a>
    </div>
    <div class="nav-actions">
      <a href="{{ url('/login') }}" class="nav-login">Connexion</a>
      <a href="https://wa.me/25377825892?text=Bonjour%2C%20je%20suis%20directeur%20d%27%C3%A9cole%20et%20je%20souhaite%20une%20d%C3%A9mo%20de%20Dugsi." target="_blank" class="btn btn-primary">Demander une démo</a>
    </div>
  </div>
</nav>

<header class="hero">
  <div class="blob b1"></div><div class="blob b2"></div>
  <div class="wrap">
    <div class="hero-text">
      <span class="badge">✦ Démonstration gratuite — sans engagement</span>
      <h1>La plateforme qui simplifie la gestion de <em>votre école</em>.</h1>
      <p>Inscriptions, bulletins, paiements, absences et communication avec les parents — tout au même endroit, accessible depuis votre téléphone.</p>
      <div class="hero-cta">
        <a href="https://wa.me/25377825892?text=Bonjour%2C%20je%20suis%20directeur%20d%27%C3%A9cole%20et%20je%20souhaite%20une%20d%C3%A9mo%20de%20Dugsi." target="_blank" class="btn btn-primary">📲 Demander une démo gratuite</a>
        <a href="{{ url('/login') }}" class="btn btn-ghost">Se connecter</a>
      </div>
      <div class="hero-stats">
        <div class="hero-stat">
          <div class="num counter" data-target="1" data-suffix=" école pilote">0</div>
          <div class="lbl">active à Djibouti</div>
        </div>
        <div class="hero-stat">
          <div class="num counter" data-target="100" data-suffix="%">0</div>
          <div class="lbl">en franc djiboutien</div>
        </div>
        <div class="hero-stat">
          <div class="num counter" data-target="5" data-suffix=" modules">0</div>
          <div class="lbl">tout-en-un</div>
        </div>
      </div>
    </div>

    <div class="hero-visual">
      <div class="hero-shot">
        {{-- Remplace le bloc ci-dessous par ta vraie capture --}}
        {{-- <img src="{{ asset('images/dashboard.png') }}" alt="Tableau de bord Dugsi"> --}}
        <div class="placeholder">📊 [ Capture de votre tableau de bord ]</div>
      </div>
    </div>
  </div>
</header>

<section class="proof">
  <div class="wrap">
    <div class="label">Déjà adopté sur le terrain</div>
    <div class="proof-logos">
      <div class="pilot">
        <div class="plogo">E</div>
        <div class="pinfo">
          <strong>École Les Petits Futés</strong>
          <span>École pilote — Djibouti</span>
          <div class="pilot-badge">✓ Utilise Dugsi au quotidien</div>
        </div>
      </div>
    </div>
    <p style="color:var(--slate-text);font-size:14px;margin-top:20px;">Rejoignez les premières écoles de Djibouti qui modernisent leur gestion.</p>
  </div>
</section>

<section class="section" id="features">
  <div class="wrap">
    <div class="section-head reveal">
      <div class="eyebrow">Tout-en-un</div>
      <h2>Une seule plateforme pour toute votre école</h2>
      <p>Fini les cahiers, les fichiers Excel éparpillés et les calculs manuels. Dugsi centralise votre gestion quotidienne.</p>
    </div>
    <div class="grid">
      <div class="card reveal"><div class="ico">🎓</div><h3>Inscriptions & élèves</h3><p>Gérez les dossiers élèves, les classes et les années scolaires en quelques clics.</p></div>
      <div class="card reveal d1"><div class="ico">📄</div><h3>Bulletins automatiques</h3><p>Saisissez les notes, Dugsi génère les bulletins et calcule les moyennes automatiquement.</p></div>
      <div class="card reveal d2"><div class="ico">💰</div><h3>Paiements & impayés</h3><p>Suivez chaque encaissement, identifiez les impayés et éditez les reçus en temps réel.</p></div>
      <div class="card reveal"><div class="ico">📅</div><h3>Absences & emploi du temps</h3><p>Enregistrez les présences et gardez le planning des classes toujours à jour.</p></div>
      <div class="card reveal d1"><div class="ico">👨‍👩‍👧</div><h3>Espace parents</h3><p>Les parents consultent notes, absences et annonces depuis leur propre espace.</p></div>
      <div class="card reveal d2"><div class="ico">🔐</div><h3>Rôles & sécurité</h3><p>Chaque membre du personnel accède uniquement à ce qui le concerne. Vos données sont protégées.</p></div>
    </div>
  </div>
</section>

<section class="section preview">
  <div class="wrap">
    <div class="section-head reveal">
      <div class="eyebrow">Aperçu</div>
      <h2>Une interface claire, pensée pour aller vite</h2>
      <p>Un tableau de bord qui vous donne l'essentiel d'un coup d'œil : élèves, encaissements, impayés et présence.</p>
    </div>
    <div class="mock reveal">
      <div class="mock-bar"><span class="r"></span><span class="y"></span><span class="g"></span></div>
      {{-- Remplace le bloc ci-dessous par une 2e capture (ex: paiements ou bulletin) --}}
      {{-- <img src="{{ asset('images/paiements.png') }}" alt="Écran de collecte des paiements Dugsi"> --}}
      <div class="mock-body">
        <p>📊 [ Remplacez ce bloc par une capture d'écran de votre tableau de bord ]</p>
      </div>
    </div>
    <p class="preview-note">Astuce : une vraie capture de votre dashboard convaincra bien plus qu'un texte.</p>
  </div>
</section>

<section class="section" id="how">
  <div class="wrap">
    <div class="section-head reveal">
      <div class="eyebrow">Simple et rapide</div>
      <h2>Votre école opérationnelle en 4 étapes</h2>
    </div>
    <div class="steps">
      <div class="step reveal"><div class="n">1</div><h3>On configure votre école</h3><p>Classes, cycles, frais et année scolaire adaptés à votre établissement.</p></div>
      <div class="step reveal d1"><div class="n">2</div><h3>On importe vos élèves</h3><p>Vos listes d'élèves et de personnel intégrées sans ressaisie fastidieuse.</p></div>
      <div class="step reveal d2"><div class="n">3</div><h3>On forme votre équipe</h3><p>Une prise en main guidée, en français, pour tout votre personnel.</p></div>
      <div class="step reveal d3"><div class="n">4</div><h3>Vous gérez tout</h3><p>Notes, paiements, bulletins, absences — depuis votre bureau ou votre téléphone.</p></div>
    </div>
  </div>
</section>

<section class="section roles">
  <div class="wrap">
    <div class="section-head reveal">
      <div class="eyebrow">Pour toute l'équipe</div>
      <h2>Chacun y trouve son compte</h2>
    </div>
    <div class="role-grid">
      <div class="role reveal"><div class="ri">👔</div><h3>Directeur</h3><ul><li>Vue d'ensemble en temps réel</li><li>Suivi financier complet</li><li>Rapports automatiques</li></ul></div>
      <div class="role reveal d1"><div class="ri">💼</div><h3>Comptable</h3><ul><li>Encaissements & reçus</li><li>Liste des impayés</li><li>Journal de caisse</li></ul></div>
      <div class="role reveal d2"><div class="ri">👩‍🏫</div><h3>Enseignant</h3><ul><li>Saisie des notes</li><li>Appel & absences</li><li>Devoirs & annonces</li></ul></div>
      <div class="role reveal d3"><div class="ri">👨‍👩‍👧</div><h3>Parent</h3><ul><li>Notes de l'enfant</li><li>Absences signalées</li><li>Annonces de l'école</li></ul></div>
    </div>
  </div>
</section>

<section class="section pricing">
  <div class="wrap">
    <div class="section-head reveal">
      <div class="eyebrow">Tarifs</div>
      <h2>Un tarif adapté à votre école</h2>
      <p>Le prix dépend de la taille de votre établissement. Contactez-nous pour un devis personnalisé et une démo gratuite.</p>
    </div>
    <div class="box reveal">
      <h3>Sur devis, selon vos besoins</h3>
      <p>Nous établissons ensemble une offre adaptée au nombre d'élèves et aux fonctionnalités qui comptent pour vous.</p>
      <div class="feat">
        <span>✓ Mise en place incluse</span>
        <span>✓ Formation du personnel</span>
        <span>✓ Support en français</span>
      </div>
      <a href="https://wa.me/25377825892?text=Bonjour%2C%20je%20souhaite%20un%20devis%20pour%20Dugsi%20pour%20mon%20%C3%A9cole." target="_blank" class="btn btn-primary">Demander un devis gratuit</a>
    </div>
  </div>
</section>

<section class="section" id="faq" style="background:var(--slate);">
  <div class="wrap">
    <div class="section-head reveal">
      <div class="eyebrow">Questions fréquentes</div>
      <h2>Tout ce que vous voulez savoir</h2>
    </div>
    <div class="faq-list">
      <details open><summary>Mes données sont-elles en sécurité ?</summary><div class="ans">Oui. Chaque école dispose de son espace isolé et vos données sont sauvegardées régulièrement. Seules les personnes que vous autorisez y ont accès, selon leur rôle.</div></details>
      <details><summary>Faut-il une connexion internet permanente ?</summary><div class="ans">Dugsi fonctionne dans votre navigateur, une connexion internet est donc nécessaire pour l'utiliser. Une simple connexion mobile suffit largement.</div></details>
      <details><summary>Et si mon personnel n'est pas à l'aise avec l'informatique ?</summary><div class="ans">L'interface est volontairement simple, en français. Nous formons votre équipe lors de la mise en place et restons disponibles pour les accompagner.</div></details>
      <details><summary>Est-ce adapté au système scolaire djiboutien ?</summary><div class="ans">Oui, Dugsi est conçu localement, avec les cycles, les frais et les bulletins adaptés aux écoles de Djibouti. La devise utilisée est le franc djiboutien (FDJ).</div></details>
      <details><summary>Puis-je récupérer mes données si besoin ?</summary><div class="ans">Vos données vous appartiennent. Vous pouvez les exporter à tout moment, notamment les listes d'élèves, les paiements et les bulletins.</div></details>
      <details><summary>Combien de temps pour démarrer ?</summary><div class="ans">Selon la taille de votre école, la configuration et l'import de vos élèves prennent généralement quelques jours. Nous vous accompagnons à chaque étape.</div></details>
    </div>
  </div>
</section>

<section class="section" style="padding-top:40px;">
  <div class="wrap">
    <div class="cta-final reveal">
      <div class="blob b1" style="opacity:.15;"></div>
      <h2>Prêt à moderniser <em>votre école</em> ?</h2>
      <p>Contactez-nous dès aujourd'hui pour une démonstration gratuite et personnalisée de Dugsi.</p>
      <a href="https://wa.me/25377825892?text=Bonjour%2C%20je%20suis%20directeur%20d%27%C3%A9cole%20et%20je%20souhaite%20une%20d%C3%A9mo%20de%20Dugsi." target="_blank" class="btn btn-primary" style="font-size:16px;padding:15px 30px;">📲 Demander ma démo gratuite</a>
    </div>
  </div>
</section>

<footer>
  <div class="wrap">
    <div>
      <div class="foot-logo">DUG<span>SI</span></div>
      <p style="font-size:14px;margin-top:8px;opacity:.7;">Plateforme de gestion scolaire<br>conçue pour les écoles de Djibouti.</p>

      <div class="foot-social">
        <a href="https://wa.me/25377825892" target="_blank" aria-label="WhatsApp" class="social-btn">
          <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.71.306 1.263.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
          </svg>
        </a>
        <a href="#" target="_blank" aria-label="Facebook" class="social-btn">
          <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073Z"/>
          </svg>
        </a>
        <a href="#" target="_blank" aria-label="LinkedIn" class="social-btn">
          <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
          </svg>
        </a>
        <a href="#" target="_blank" aria-label="Instagram" class="social-btn">
          <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
          </svg>
        </a>
      </div>
    </div>

    <div class="foot-contact">
      <strong style="color:var(--white);">Contact</strong><br>
     📞 <a href="tel:+25377825892">77 33 40 40</a><br>
      ✉️ <a href="mailto:contact@dougsi.tech">contact@dougsi.tech</a><br>
      🌐 www.dougsi.tech
    </div>
  </div>
  <div class="foot-bottom">© {{ date('Y') }} DUGSI — Tous droits réservés.</div>
</footer>

<script>
  const prefersReduced = matchMedia('(prefers-reduced-motion: reduce)').matches;

  const runCounter = (el) => {
    const target = parseInt(el.dataset.target, 10);
    const suffix = el.dataset.suffix || '';
    if (prefersReduced) { el.textContent = target.toLocaleString('fr-FR') + suffix; return; }
    let start = null;
    const step = (ts) => {
      if (!start) start = ts;
      const p = Math.min((ts - start) / 1400, 1);
      const eased = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.round(target * eased).toLocaleString('fr-FR') + suffix;
      if (p < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  };

  const counterObs = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) { runCounter(e.target); counterObs.unobserve(e.target); }
    });
  }, { threshold: 0.5 });
  document.querySelectorAll('.counter').forEach(el => counterObs.observe(el));

  const revealObs = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) { e.target.classList.add('visible'); revealObs.unobserve(e.target); }
    });
  }, { threshold: 0.15 });
  document.querySelectorAll('.reveal').forEach(el => revealObs.observe(el));
</script>

</body>
</html>