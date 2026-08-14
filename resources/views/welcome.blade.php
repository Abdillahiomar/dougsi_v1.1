{{-- ============================================================
     resources/views/welcome.blade.php
     Landing commerciale Dugsi — pour directeurs d'écoles privées
     Autonome (CSS inline) — indépendante du build Tailwind
     ============================================================ --}}
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dugsi — La plateforme de gestion pour votre école</title>
<meta name="description" content="Dugsi simplifie la gestion de votre école : inscriptions, bulletins, paiements, absences et communication avec les parents. Essai gratuit 1 mois.">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;0,9..144,700;0,9..144,900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
  :root{
    --indigo:#4338ca; --violet:#6d28d9; --rose:#db2777;
    --amber:#fbbf24; --cyan:#22d3ee; --ink:#1e1b4b; --slate:#475569;
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  html{scroll-behavior:smooth;}
  body{font-family:'Inter',sans-serif;color:var(--ink);background:#fff;line-height:1.6;}
  a{text-decoration:none;color:inherit;}
  .wrap{max-width:1140px;margin:0 auto;padding:0 24px;}
  .serif{font-family:'Fraunces',serif;}

  /* Boutons */
  .btn{display:inline-flex;align-items:center;gap:8px;font-weight:700;font-size:15px;
       padding:13px 24px;border-radius:12px;transition:transform .15s,box-shadow .15s;cursor:pointer;border:none;}
  .btn:hover{transform:translateY(-2px);}
  .btn-primary{background:var(--amber);color:#4c1d95;box-shadow:0 8px 24px rgba(251,191,36,.4);}
  .btn-ghost{background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.3);}
  .btn-outline{background:#fff;color:var(--violet);border:2px solid var(--violet);}

  /* Navbar */
  .nav{position:sticky;top:0;z-index:50;background:rgba(67,56,202,.95);backdrop-filter:blur(10px);}
  .nav .wrap{display:flex;align-items:center;justify-content:space-between;height:70px;}
  .logo{font-family:'Fraunces',serif;font-weight:900;font-size:28px;color:#fff;letter-spacing:-.5px;}
  .logo span{color:var(--amber);}
  .nav-actions{display:flex;gap:12px;align-items:center;}
  .nav-login{color:#fff;font-weight:600;font-size:15px;padding:10px 18px;border-radius:10px;transition:background .15s;}
  .nav-login:hover{background:rgba(255,255,255,.12);}

  /* Hero */
  .hero{background:linear-gradient(160deg,var(--indigo) 0%,var(--violet) 48%,var(--rose) 100%);
        color:#fff;position:relative;overflow:hidden;padding:80px 0 96px;}
  .blob{position:absolute;border-radius:50%;opacity:.3;}
  .b1{width:360px;height:360px;background:var(--cyan);top:-120px;right:-100px;}
  .b2{width:280px;height:280px;background:var(--amber);bottom:-120px;left:-120px;opacity:.22;}
  .hero .wrap{position:relative;z-index:2;text-align:center;}
  .badge{display:inline-block;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);
         padding:7px 16px;border-radius:999px;font-size:13px;font-weight:600;margin-bottom:24px;}
  .hero h1{font-family:'Fraunces',serif;font-weight:900;font-size:52px;line-height:1.1;letter-spacing:-1px;max-width:820px;margin:0 auto;}
  .hero h1 em{color:var(--amber);font-style:normal;}
  .hero p{font-size:19px;opacity:.92;max-width:600px;margin:22px auto 34px;}
  .hero-cta{display:flex;gap:14px;justify-content:center;flex-wrap:wrap;}

  /* Sections */
  .section{padding:88px 0;}
  .section-head{text-align:center;max-width:620px;margin:0 auto 56px;}
  .eyebrow{color:var(--rose);font-weight:700;font-size:14px;letter-spacing:1px;text-transform:uppercase;}
  .section-head h2{font-family:'Fraunces',serif;font-weight:700;font-size:38px;line-height:1.15;margin-top:10px;color:var(--ink);}
  .section-head p{color:var(--slate);font-size:17px;margin-top:14px;}

  /* Grille fonctionnalités */
  .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px;}
  .card{background:#fff;border:1px solid #eef0f6;border-radius:18px;padding:28px;
        box-shadow:0 4px 20px rgba(30,27,75,.04);transition:transform .18s,box-shadow .18s;}
  .card:hover{transform:translateY(-4px);box-shadow:0 14px 40px rgba(109,40,217,.12);}
  .card .ico{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:26px;margin-bottom:16px;}
  .card h3{font-size:18px;font-weight:700;margin-bottom:8px;}
  .card p{color:var(--slate);font-size:14.5px;}
  .i1{background:linear-gradient(135deg,#e0e7ff,#c7d2fe);}
  .i2{background:linear-gradient(135deg,#fce7f3,#fbcfe8);}
  .i3{background:linear-gradient(135deg,#fef3c7,#fde68a);}
  .i4{background:linear-gradient(135deg,#cffafe,#a5f3fc);}
  .i5{background:linear-gradient(135deg,#ede9fe,#ddd6fe);}
  .i6{background:linear-gradient(135deg,#dcfce7,#bbf7d0);}

  /* Bénéfices */
  .benefits{background:#faf9ff;}
  .ben-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:28px;}
  .ben{text-align:center;padding:20px;}
  .ben .num{font-family:'Fraunces',serif;font-weight:900;font-size:40px;
            background:linear-gradient(135deg,var(--violet),var(--rose));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
  .ben h3{font-size:18px;font-weight:700;margin:10px 0 8px;}
  .ben p{color:var(--slate);font-size:14.5px;}

  /* CTA final */
  .cta-final{background:linear-gradient(135deg,var(--violet),var(--rose));color:#fff;text-align:center;border-radius:28px;padding:64px 32px;position:relative;overflow:hidden;}
  .cta-final h2{font-family:'Fraunces',serif;font-weight:700;font-size:36px;line-height:1.15;}
  .cta-final p{font-size:18px;opacity:.92;margin:16px auto 30px;max-width:520px;}

  /* Footer */
  footer{background:var(--ink);color:rgba(255,255,255,.75);padding:48px 0 32px;}
  footer .wrap{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:24px;}
  .foot-logo{font-family:'Fraunces',serif;font-weight:900;font-size:26px;color:#fff;}
  .foot-logo span{color:var(--amber);}
  .foot-contact{font-size:14.5px;line-height:1.9;}
  .foot-contact a:hover{color:#fff;}
  .foot-bottom{border-top:1px solid rgba(255,255,255,.1);margin-top:32px;padding-top:20px;font-size:13px;opacity:.6;text-align:center;}

  /* Responsive */
  @media(max-width:900px){
    .grid,.ben-grid{grid-template-columns:1fr 1fr;}
  }
  @media(max-width:640px){
    .hero h1{font-size:36px;}
    .hero p{font-size:17px;}
    .section-head h2{font-size:30px;}
    .grid,.ben-grid{grid-template-columns:1fr;}
    .nav-login{display:none;}
    .cta-final h2{font-size:28px;}
  }
</style>
</head>
<body>

{{-- ── Navbar ── --}}
<nav class="nav">
  <div class="wrap">
    <div class="logo">Dug<span>si</span></div>
    <div class="nav-actions">
      <a href="{{ url('/login') }}" class="nav-login">Connexion</a>
      <a href="https://wa.me/25377825892?text=Bonjour%2C%20je%20suis%20directeur%20d%27%C3%A9cole%20et%20je%20souhaite%20une%20d%C3%A9mo%20de%20Dugsi."
         target="_blank" class="btn btn-primary">Demander une démo</a>
    </div>
  </div>
</nav>

{{-- ── Hero ── --}}
<header class="hero">
  <div class="blob b1"></div>
  <div class="blob b2"></div>
  <div class="wrap">
    <span class="badge">✨ 1 mois d'essai gratuit — sans engagement</span>
    <h1>La plateforme qui simplifie la gestion de <em>votre école</em>.</h1>
    <p>Inscriptions, bulletins, paiements, absences et communication avec les parents — tout au même endroit, accessible depuis votre téléphone.</p>
    <div class="hero-cta">
      <a href="https://wa.me/25377825892?text=Bonjour%2C%20je%20suis%20directeur%20d%27%C3%A9cole%20et%20je%20souhaite%20une%20d%C3%A9mo%20de%20Dugsi."
         target="_blank" class="btn btn-primary">📲 Demander une démo gratuite</a>
      <a href="{{ url('/login') }}" class="btn btn-ghost">Se connecter</a>
    </div>
  </div>
</header>

{{-- ── Fonctionnalités ── --}}
<section class="section">
  <div class="wrap">
    <div class="section-head">
      <div class="eyebrow">Tout-en-un</div>
      <h2>Une seule plateforme pour toute votre école</h2>
      <p>Fini les cahiers, les fichiers Excel éparpillés et les calculs manuels. Dugsi centralise votre gestion quotidienne.</p>
    </div>
    <div class="grid">
      <div class="card"><div class="ico i1">🎓</div><h3>Inscriptions & élèves</h3><p>Gérez les dossiers élèves, les classes et les années scolaires en quelques clics.</p></div>
      <div class="card"><div class="ico i2">📄</div><h3>Bulletins automatiques</h3><p>Saisissez les notes, Dugsi génère les bulletins et calcule les moyennes automatiquement.</p></div>
      <div class="card"><div class="ico i3">💰</div><h3>Paiements & impayés</h3><p>Suivez chaque encaissement, identifiez les impayés et éditez les reçus en temps réel.</p></div>
      <div class="card"><div class="ico i4">📅</div><h3>Absences & emploi du temps</h3><p>Enregistrez les présences et gardez le planning des classes toujours à jour.</p></div>
      <div class="card"><div class="ico i5">👨‍👩‍👧</div><h3>Espace parents</h3><p>Les parents consultent notes, absences et annonces depuis leur propre espace.</p></div>
      <div class="card"><div class="ico i6">🔐</div><h3>Rôles & sécurité</h3><p>Chaque membre du personnel accède uniquement à ce qui le concerne. Vos données sont protégées.</p></div>
    </div>
  </div>
</section>

{{-- ── Bénéfices directeur ── --}}
<section class="section benefits">
  <div class="wrap">
    <div class="section-head">
      <div class="eyebrow">Pourquoi Dugsi ?</div>
      <h2>Pensé pour les directeurs d'école</h2>
    </div>
    <div class="ben-grid">
      <div class="ben"><div class="num">−70%</div><h3>de temps administratif</h3><p>Automatisez les bulletins, les reçus et les rapports qui vous prenaient des journées entières.</p></div>
      <div class="ben"><div class="num">100%</div><h3>de vos finances sous contrôle</h3><p>Visualisez vos encaissements et vos impayés à tout moment, sans ressaisir une seule ligne.</p></div>
      <div class="ben"><div class="num">+</div><h3>d'image moderne</h3><p>Offrez aux parents une expérience numérique qui valorise le sérieux de votre établissement.</p></div>
    </div>
  </div>
</section>

{{-- ── CTA final ── --}}
<section class="section" style="padding-top:40px;">
  <div class="wrap">
    <div class="cta-final">
      <div class="blob b1" style="opacity:.18;"></div>
      <h2>Prêt à moderniser votre école ?</h2>
      <p>Contactez-nous dès aujourd'hui pour une démonstration gratuite et personnalisée de Dugsi.</p>
      <a href="https://wa.me/25377825892?text=Bonjour%2C%20je%20suis%20directeur%20d%27%C3%A9cole%20et%20je%20souhaite%20une%20d%C3%A9mo%20de%20Dugsi."
         target="_blank" class="btn btn-primary" style="font-size:16px;padding:15px 30px;">📲 Demander ma démo gratuite</a>
    </div>
  </div>
</section>

{{-- ── Footer ── --}}
<footer>
  <div class="wrap">
    <div>
      <div class="foot-logo">Dug<span>si</span></div>
      <p style="font-size:14px;margin-top:8px;opacity:.7;">Plateforme de gestion scolaire<br>conçue pour les écoles de Djibouti.</p>
    </div>
    <div class="foot-contact">
      <strong style="color:#fff;">Contact</strong><br>
      📞 <a href="tel:+25377825892">77 82 58 92</a><br>
      ✉️ <a href="mailto:abdillahiomar96@gmail.com">abdillahiomar96@gmail.com</a><br>
      🌐 www.dugsi.tech
    </div>
  </div>
  <div class="foot-bottom">© {{ date('Y') }} Dugsi — Tous droits réservés.</div>
</footer>

</body>
</html>