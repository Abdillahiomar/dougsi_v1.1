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
    --navy:#0F172A; --navy-soft:#1E293B;
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
  .nav-links a.link{color:rgba(255,255,255,.85);font-weight:500;font-size:14.5px;}
  .nav-links a.link:hover{color:var(--emerald);}
  .nav-actions{display:flex;gap:12px;align-items:center;}
  .nav-login{color:var(--white);font-weight:600;font-size:15px;padding:10px 18px;border-radius:10px;transition:background .15s;}
  .nav-login:hover{background:rgba(255,255,255,.1);}

  /* Hero */
  .hero{background:var(--navy);color:var(--white);position:relative;overflow:hidden;padding:88px 0 96px;}
  .blob{position:absolute;border-radius:50%;opacity:.12;filter:blur(8px);}
  .b1{width:420px;height:420px;background:var(--emerald);top:-140px;right:-120px;opacity:.18;}
  .b2{width:300px;height:300px;background:var(--emerald);bottom:-140px;left:-120px;opacity:.1;}
  .hero .wrap{position:relative;z-index:2;text-align:center;}
  .badge{display:inline-flex;align-items:center;gap:7px;background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.35);
         color:var(--emerald-soft);padding:7px 16px;border-radius:999px;font-size:13px;font-weight:600;margin-bottom:24px;}
  .hero h1{font-family:'Fraunces',serif;font-weight:900;font-size:52px;line-height:1.1;letter-spacing:-1px;max-width:820px;margin:0 auto;}
  .hero h1 em{color:var(--emerald);font-style:normal;}
  .hero p{font-size:19px;color:rgba(255,255,255,.82);max-width:600px;margin:22px auto 34px;}
  .hero-cta{display:flex;gap:14px;justify-content:center;flex-wrap:wrap;}

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
        transition:transform .18s,box-shadow .18s;}
  .card:hover{transform:translateY(-4px);box-shadow:0 14px 40px rgba(15,23,42,.08);border-color:var(--emerald);}
  .card .ico{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:26px;margin-bottom:16px;background:var(--emerald-soft);}
  .card h3{font-size:18px;font-weight:700;margin-bottom:8px;}
  .card p{color:var(--slate-text);font-size:14.5px;}

  /* Aperçu produit */
  .preview{background:var(--slate);}
  .mock{background:var(--white);border:1px solid var(--slate-border);border-radius:20px;overflow:hidden;box-shadow:0 20px 60px rgba(15,23,42,.1);}
  .mock-bar{background:var(--slate);padding:12px 16px;display:flex;gap:7px;align-items:center;border-bottom:1px solid var(--slate-border);}
  .mock-bar span{width:11px;height:11px;border-radius:50%;}
  .mock-bar .r{background:#fca5a5;} .mock-bar .y{background:#fde047;} .mock-bar .g{background:var(--emerald);}
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
  .role{background:var(--white);border:1px solid var(--slate-border);border-radius:16px;padding:24px;}
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
  details summary::after{content:"+";font-size:24px;color:var(--emerald);font-weight:400;}
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

  @media(max-width:900px){
    .grid,.role-grid{grid-template-columns:1fr 1fr;}
    .steps{grid-template-columns:1fr 1fr;}
    .nav-links{display:none;}
  }
  @media(max-width:640px){
    .hero h1{font-size:36px;} .hero p{font-size:17px;}
    .section-head h2{font-size:30px;}
    .grid,.role-grid,.steps{grid-template-columns:1fr;}
    .nav-login{display:none;} .cta-final h2{font-size:28px;}
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
    <span class="badge">✦ Démonstration gratuite — sans engagement</span>
    <h1>La plateforme qui simplifie la gestion de <em>votre école</em>.</h1>
    <p>Inscriptions, bulletins, paiements, absences et communication avec les parents — tout au même endroit, accessible depuis votre téléphone.</p>
    <div class="hero-cta">
      <a href="https://wa.me/25377825892?text=Bonjour%2C%20je%20suis%20directeur%20d%27%C3%A9cole%20et%20je%20souhaite%20une%20d%C3%A9mo%20de%20Dugsi." target="_blank" class="btn btn-primary">📲 Demander une démo gratuite</a>
      <a href="{{ url('/login') }}" class="btn btn-ghost">Se connecter</a>
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
    <div class="section-head">
      <div class="eyebrow">Tout-en-un</div>
      <h2>Une seule plateforme pour toute votre école</h2>
      <p>Fini les cahiers, les fichiers Excel éparpillés et les calculs manuels. Dugsi centralise votre gestion quotidienne.</p>
    </div>
    <div class="grid">
      <div class="card"><div class="ico">🎓</div><h3>Inscriptions & élèves</h3><p>Gérez les dossiers élèves, les classes et les années scolaires en quelques clics.</p></div>
      <div class="card"><div class="ico">📄</div><h3>Bulletins automatiques</h3><p>Saisissez les notes, Dugsi génère les bulletins et calcule les moyennes automatiquement.</p></div>
      <div class="card"><div class="ico">💰</div><h3>Paiements & impayés</h3><p>Suivez chaque encaissement, identifiez les impayés et éditez les reçus en temps réel.</p></div>
      <div class="card"><div class="ico">📅</div><h3>Absences & emploi du temps</h3><p>Enregistrez les présences et gardez le planning des classes toujours à jour.</p></div>
      <div class="card"><div class="ico">👨‍👩‍👧</div><h3>Espace parents</h3><p>Les parents consultent notes, absences et annonces depuis leur propre espace.</p></div>
      <div class="card"><div class="ico">🔐</div><h3>Rôles & sécurité</h3><p>Chaque membre du personnel accède uniquement à ce qui le concerne. Vos données sont protégées.</p></div>
    </div>
  </div>
</section>

<section class="section preview">
  <div class="wrap">
    <div class="section-head">
      <div class="eyebrow">Aperçu</div>
      <h2>Une interface claire, pensée pour aller vite</h2>
      <p>Un tableau de bord qui vous donne l'essentiel d'un coup d'œil : élèves, encaissements, impayés et présence.</p>
    </div>
    <div class="mock">
      <div class="mock-bar"><span class="r"></span><span class="y"></span><span class="g"></span></div>
      <div class="mock-body">
        <p>📊 [ Remplacez ce bloc par une capture d'écran de votre tableau de bord ]</p>
      </div>
    </div>
    <p class="preview-note">Astuce : une vraie capture de votre dashboard convaincra bien plus qu'un texte.</p>
  </div>
</section>

<section class="section" id="how">
  <div class="wrap">
    <div class="section-head">
      <div class="eyebrow">Simple et rapide</div>
      <h2>Votre école opérationnelle en 4 étapes</h2>
    </div>
    <div class="steps">
      <div class="step"><div class="n">1</div><h3>On configure votre école</h3><p>Classes, cycles, frais et année scolaire adaptés à votre établissement.</p></div>
      <div class="step"><div class="n">2</div><h3>On importe vos élèves</h3><p>Vos listes d'élèves et de personnel intégrées sans ressaisie fastidieuse.</p></div>
      <div class="step"><div class="n">3</div><h3>On forme votre équipe</h3><p>Une prise en main guidée, en français, pour tout votre personnel.</p></div>
      <div class="step"><div class="n">4</div><h3>Vous gérez tout</h3><p>Notes, paiements, bulletins, absences — depuis votre bureau ou votre téléphone.</p></div>
    </div>
  </div>
</section>

<section class="section roles">
  <div class="wrap">
    <div class="section-head">
      <div class="eyebrow">Pour toute l'équipe</div>
      <h2>Chacun y trouve son compte</h2>
    </div>
    <div class="role-grid">
      <div class="role"><div class="ri">👔</div><h3>Directeur</h3><ul><li>Vue d'ensemble en temps réel</li><li>Suivi financier complet</li><li>Rapports automatiques</li></ul></div>
      <div class="role"><div class="ri">💼</div><h3>Comptable</h3><ul><li>Encaissements & reçus</li><li>Liste des impayés</li><li>Journal de caisse</li></ul></div>
      <div class="role"><div class="ri">👩‍🏫</div><h3>Enseignant</h3><ul><li>Saisie des notes</li><li>Appel & absences</li><li>Devoirs & annonces</li></ul></div>
      <div class="role"><div class="ri">👨‍👩‍👧</div><h3>Parent</h3><ul><li>Notes de l'enfant</li><li>Absences signalées</li><li>Annonces de l'école</li></ul></div>
    </div>
  </div>
</section>

<section class="section pricing">
  <div class="wrap">
    <div class="section-head">
      <div class="eyebrow">Tarifs</div>
      <h2>Un tarif adapté à votre école</h2>
      <p>Le prix dépend de la taille de votre établissement. Contactez-nous pour un devis personnalisé et une démo gratuite.</p>
    </div>
    <div class="box">
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
    <div class="section-head">
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
    <div class="cta-final">
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
    </div>
    <div class="foot-contact">
      <strong style="color:var(--white);">Contact</strong><br>
      📞 <a href="tel:+25377825892">77 33 40 40</a><br>
      ✉️ <a href="mailto:abdillahiomar96@gmail.com">contact@dougsi.tech</a><br>
      🌐 www.dugsi.tech
    </div>
  </div>
  <div class="foot-bottom">© {{ date('Y') }} Dugsi — Tous droits réservés.</div>
</footer>

</body>
</html>