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

    /* ---------- PREUVE SOCIALE : statistiques réelles ---------- */
    .proof { background: var(--white); padding: 44px 0; border-bottom: 1px solid var(--border); }
    .proof-grid { display: flex; flex-wrap: wrap; justify-content: center; gap: 48px; }
    .proof-item { text-align: center; }
    .proof-item .pnum {
      font-family: 'Fraunces', serif; font-weight: 800; font-size: 34px; color: var(--navy); line-height: 1.1;
    }
    .proof-item .pnum span { color: var(--primary); }
    .proof-item .plbl { font-size: 13px; color: var(--slate-text); font-weight: 600; margin-top: 4px; }
    .cycle-strip { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-top: 28px; }
    .cycle-chip {
      background: var(--slate); border: 1px solid var(--border); border-radius: 999px;
      padding: 7px 18px; font-size: 13px; font-weight: 600; color: var(--navy);
    }

    /* ---------- BANDE VALEUR (5 cartes courtes) ---------- */
    .value-strip { background: var(--navy); padding: 48px 0; }
    .value-grid { display: grid; grid-template-columns: repeat(5,1fr); gap: 1px; background: rgba(255,255,255,.08); border-radius: 18px; overflow: hidden; }
    .value-item { background: var(--navy); padding: 24px 20px; }
    .value-item h4 { color: white; font-size: 14.5px; font-weight: 700; margin-bottom: 6px; }
    .value-item p { color: rgba(255,255,255,.55); font-size: 12.5px; }

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

    /* ---------- "VOUS VOUS RECONNAISSEZ ?" ---------- */
    .segment-card {
      background: var(--white); border: 1px solid var(--border); border-radius: 20px;
      padding: 28px 26px; position: relative;
    }
    .segment-tag {
      position: absolute; top: -12px; right: 20px; background: var(--primary); color: white;
      font-size: 11px; font-weight: 700; padding: 4px 12px; border-radius: 999px;
    }
    .segment-quote { font-size: 15px; font-style: italic; color: var(--navy); margin-bottom: 14px; line-height: 1.5; }
    .segment-quote::before { content: '"'; color: var(--primary); font-weight: 900; font-size: 22px; }
    .segment-answer { font-size: 14px; color: var(--slate-text); }

    /* ---------- TIMELINE "UNE JOURNÉE AVEC DOUGSI" ---------- */
    .timeline { display: grid; grid-template-columns: repeat(4,1fr); gap: 24px; position: relative; }
    .timeline::before {
      content: ''; position: absolute; top: 22px; left: 10%; right: 10%; height: 2px;
      background: var(--border); z-index: 0;
    }
    .timeline-item { position: relative; z-index: 1; text-align: center; }
    .timeline-time {
      font-family: 'JetBrains Mono', monospace; font-weight: 700; font-size: 13px; color: white;
      background: var(--navy); width: 46px; height: 46px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center; margin: 0 auto 14px;
    }
    .timeline-item h4 { font-size: 15px; font-weight: 700; margin-bottom: 6px; }
    .timeline-item p { font-size: 13.5px; color: var(--slate-text); }

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

    /* ---------- APPLICATION MOBILE ---------- */
    .mobile-section { background: var(--navy); color: white; overflow: hidden; }
    .mobile-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 56px; align-items: center; }
    .mobile-badge {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(232,168,56,0.15); border: 1px solid rgba(232,168,56,0.35);
      color: var(--amber); padding: 6px 16px; border-radius: 999px;
      font-size: 12.5px; font-weight: 700; margin-bottom: 18px;
    }
    .mobile-text h3 { font-family: 'Fraunces', serif; font-size: 28px; margin-bottom: 14px; }
    .mobile-text p { color: rgba(255,255,255,.75); font-size: 15.5px; margin-bottom: 24px; max-width: 460px; }
    .mobile-feats { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .mobile-feat { display: flex; align-items: center; gap: 10px; font-size: 13.5px; color: rgba(255,255,255,.85); font-weight: 500; }
    .mobile-feat .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--primary); flex-shrink: 0; }
    .mobile-mock { display: flex; justify-content: center; }
    .phone-frame {
      width: 240px; background: white; border-radius: 36px;
      border: 7px solid var(--navy-light); position: relative;
      box-shadow: 0 30px 70px rgba(0,0,0,0.5); overflow: hidden;
    }
    .phone-notch {
      width: 70px; height: 16px; background: var(--navy-light); border-radius: 0 0 10px 10px;
      position: absolute; top: 0; left: 50%; transform: translateX(-50%); z-index: 2;
    }
    .phone-screen-head {
      background: var(--navy); color: white; padding: 24px 16px 14px; text-align: left;
    }
    .phone-screen-head .ph-title { font-family: 'Fraunces', serif; font-weight: 700; font-size: 15px; }
    .phone-screen-head .ph-sub { font-size: 10.5px; color: rgba(255,255,255,.55); margin-top: 2px; }
    .phone-list { padding: 10px 10px 4px; background: var(--slate); min-height: 230px; }
    .phone-row {
      display: flex; align-items: center; gap: 8px; background: white; border: 1px solid var(--border);
      border-radius: 10px; padding: 8px 10px; margin-bottom: 7px;
    }
    .phone-row .pr-av {
      width: 26px; height: 26px; border-radius: 50%; background: rgba(42,63,126,.1); color: var(--navy);
      font-family: 'JetBrains Mono', monospace; font-size: 9px; font-weight: 700;
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .phone-row .pr-info { flex: 1; min-width: 0; }
    .phone-row .pr-name { font-size: 11.5px; font-weight: 700; color: var(--navy); }
    .phone-row .pr-sub { font-size: 9.5px; color: #94A3B8; }
    .phone-row .pr-badge {
      font-family: 'JetBrains Mono', monospace; font-size: 8px; font-weight: 700;
      padding: 2px 7px; border-radius: 4px; background: rgba(16,185,129,.12); color: #047857;
    }
    .phone-tabbar {
      display: flex; justify-content: space-around; align-items: center;
      background: white; border-top: 1px solid var(--border); padding: 10px 0 14px;
    }
    .phone-tab { text-align: center; font-size: 9px; font-weight: 600; color: #94A3B8; }
    .phone-tab.active { color: var(--primary-dark); }
    .phone-tab .pt-ic { font-size: 16px; margin-bottom: 2px; }

    /* ---------- COMPARATIF ---------- */
    .compare-table { width: 100%; border-collapse: separate; border-spacing: 0; background: white; border-radius: 18px; overflow: hidden; border: 1px solid var(--border); }
    .compare-table th, .compare-table td { padding: 16px 20px; text-align: center; font-size: 14px; border-bottom: 1px solid var(--border); }
    .compare-table th { font-family: 'Fraunces', serif; font-size: 16px; background: var(--slate); }
    .compare-table th.hl { background: var(--navy); color: white; }
    .compare-table td:first-child, .compare-table th:first-child { text-align: left; font-weight: 600; color: var(--navy); }
    .compare-table tr:last-child td { border-bottom: none; }
    .compare-yes { color: var(--primary-dark); font-weight: 900; }
    .compare-no { color: #CBD5E1; font-weight: 900; }

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

    /* ---------- ONGLETS (fonctionnalités + démo interactive) ---------- */
    .tab-btns { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; margin-bottom: 44px; }
    .tab-btn {
      padding: 10px 20px; border-radius: 10px; border: 1.5px solid var(--border);
      background: white; font-size: 13.5px; font-weight: 600; color: var(--slate-text);
      cursor: pointer; transition: all .15s; font-family: 'Inter', sans-serif;
    }
    .tab-btn:hover { border-color: var(--primary); color: var(--primary-dark); }
    .tab-btn.active { background: var(--navy); border-color: var(--navy); color: white; }
    .tab-panel { display: none; }
    .tab-panel.active { display: block; }
    .tab-panel-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: center; max-width: 1000px; margin: 0 auto; }
    .tab-panel-text h3 { font-family: 'Fraunces', serif; font-size: 22px; margin-bottom: 10px; }
    .tab-panel-text p { color: var(--slate-text); font-size: 15px; margin-bottom: 18px; }
    .mini-panel {
      background: white; border: 1px solid var(--border); border-radius: 16px;
      box-shadow: 0 20px 50px rgba(15,23,42,0.08); overflow: hidden;
    }
    .mini-panel-head {
      background: var(--navy); color: white; padding: 12px 18px;
      font-family: 'JetBrains Mono', monospace; font-size: 11px; font-weight: 700; letter-spacing: .04em;
    }
    .mini-panel-body { padding: 14px 18px; }

    /* ---------- DÉMO INTERACTIVE : espaces ---------- */
    .role-tabs { display: flex; gap: 10px; justify-content: center; margin-bottom: 36px; }
    .role-tab-btn {
      display: flex; align-items: center; gap: 8px; padding: 12px 22px; border-radius: 12px;
      border: 1.5px solid var(--border); background: white; font-size: 14px; font-weight: 700;
      color: var(--slate-text); cursor: pointer; transition: all .15s; font-family: 'Inter', sans-serif;
    }
    .role-tab-btn:hover { border-color: var(--primary); }
    .role-tab-btn.active { background: var(--primary); border-color: var(--primary); color: white; }
    .role-panel { display: none; max-width: 640px; margin: 0 auto; }
    .role-panel.active { display: block; }
    .role-fiction-note {
      text-align: center; font-size: 12px; color: var(--slate-text); opacity: .7; margin-top: 18px;
    }

    /* ---------- SÉCURITÉ ---------- */
    .security-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 20px; margin-bottom: 40px; }
    .security-item {
      background: var(--white); border: 1px solid var(--border); border-radius: 16px; padding: 24px 20px;
    }
    .security-item .si { font-size: 26px; margin-bottom: 10px; }
    .security-item h4 { font-size: 14.5px; font-weight: 700; margin-bottom: 6px; }
    .security-item p { font-size: 13px; color: var(--slate-text); }
    .included-block {
      background: var(--slate); border: 1px solid var(--border); border-radius: 20px;
      padding: 28px 32px; display: grid; grid-template-columns: repeat(4,1fr); gap: 20px;
    }
    .included-block .ib-title { grid-column: 1 / -1; font-weight: 700; font-size: 15px; margin-bottom: 4px; }
    .included-item { display: flex; align-items: flex-start; gap: 8px; font-size: 13.5px; color: var(--navy); }
    .included-item::before { content: '✓'; color: var(--primary); font-weight: 900; }

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
      .grid-3, .roles-grid, .steps, .benefits-grid, .security-grid { grid-template-columns: 1fr 1fr; }
      .value-grid { grid-template-columns: repeat(3,1fr); }
      .timeline { grid-template-columns: 1fr 1fr; }
      .timeline::before { display: none; }
      .nav-links { display: none; }
      .demo-grid, .mobile-grid { grid-template-columns: 1fr; }
      .mobile-mock { order: -1; }
      .compare-table { font-size: 12px; }
      .included-block { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 640px) {
      .hero h1 { font-size: 32px; }
      .section-header h2 { font-size: 28px; }
      .grid-3, .roles-grid, .steps, .benefits-grid, .security-grid, .value-grid, .timeline, .included-block { grid-template-columns: 1fr; }
      .cta-final { padding: 40px 20px; }
      .cta-final h2 { font-size: 28px; }
      .hero-stats { gap: 20px; flex-wrap: wrap; }
      .mock-app-body { flex-direction: column; }
      .mock-sidebar { width: 100%; display: flex; overflow-x: auto; padding: 10px; }
      .mock-brand { display: none; }
      .compare-table { display: block; overflow-x: auto; white-space: nowrap; }
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
      <a href="#mobile">App mobile</a>
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

<!-- ====== PREUVE SOCIALE : statistiques réelles ====== -->
<section class="proof">
  <div class="container">
    <div class="proof-grid">
      <div class="proof-item"><div class="pnum"><span>+6</span></div><div class="plbl">écoles utilisent Dougsi</div></div>
      <div class="proof-item"><div class="pnum"><span>+1000</span></div><div class="plbl">élèves gérés sur la plateforme</div></div>
      <div class="proof-item"><div class="pnum"><span>5</span></div><div class="plbl">cycles scolaires couverts</div></div>
    </div>
    <div class="cycle-strip">
      <span class="cycle-chip">Crèche</span>
      <span class="cycle-chip">Maternelle</span>
      <span class="cycle-chip">Primaire</span>
      <span class="cycle-chip">Collège</span>
      <span class="cycle-chip">Lycée</span>
    </div>
  </div>
</section>

<!-- ====== BANDE VALEUR ====== -->
<section class="value-strip">
  <div class="container">
    <div class="value-grid">
      <div class="value-item"><h4>🎁 Démo gratuite</h4><p>Sans engagement, pour voir Dougsi avec vos propres données.</p></div>
      <div class="value-item"><h4>🇩🇯 Pensé pour Djibouti</h4><p>Franc djiboutien, cycles scolaires et bulletins locaux.</p></div>
      <div class="value-item"><h4>⚡ Mise en route rapide</h4><p>Configuration et import de vos élèves accompagnés.</p></div>
      <div class="value-item"><h4>🤝 Support humain</h4><p>Une équipe joignable en français, pas un robot.</p></div>
      <div class="value-item"><h4>🔐 Accès maîtrisé</h4><p>Chacun voit uniquement ce qui le concerne.</p></div>
    </div>
  </div>
</section>

<!-- ====== VOUS VOUS RECONNAISSEZ ? ====== -->
<section class="section">
  <div class="container">
    <div class="section-header reveal">
      <div class="eyebrow">Pour qui ?</div>
      <h2>Vous vous reconnaissez ?</h2>
    </div>
    <div class="grid-3">
      <div class="segment-card reveal">
        <p class="segment-quote">On note encore tout sur papier et dans des fichiers Excel séparés.</p>
        <p class="segment-answer">Dougsi centralise élèves, notes, absences et paiements au même endroit, accessible à toute l'équipe.</p>
      </div>
      <div class="segment-card reveal d1">
        <span class="segment-tag">Le plus fréquent</span>
        <p class="segment-quote">Mon école grandit, les fichiers Excel ne suivent plus.</p>
        <p class="segment-answer">Plus votre effectif augmente, plus Dougsi fait gagner du temps : calculs automatiques, relances d'impayés, bulletins générés en un clic.</p>
      </div>
      <div class="segment-card reveal d2">
        <p class="segment-quote">Les parents nous appellent sans arrêt pour connaître les notes ou signaler une absence.</p>
        <p class="segment-answer">Avec l'espace parent, chacun consulte notes, absences et annonces directement, sans passer par le secrétariat.</p>
      </div>
    </div>
  </div>
</section>

<!-- ====== TIMELINE : UNE JOURNÉE AVEC DOUGSI ====== -->
<section class="section" style="background:var(--slate);">
  <div class="container">
    <div class="section-header reveal">
      <div class="eyebrow">Au quotidien</div>
      <h2>Une journée avec Dougsi</h2>
    </div>
    <div class="timeline">
      <div class="timeline-item reveal"><div class="timeline-time">07h30</div><h4>Appel du matin</h4><p>L'enseignant marque les présences, les parents sont notifiés en cas d'absence.</p></div>
      <div class="timeline-item reveal d1"><div class="timeline-time">10h00</div><h4>Encaissement</h4><p>La comptabilité enregistre un paiement, le reçu part immédiatement.</p></div>
      <div class="timeline-item reveal d2"><div class="timeline-time">14h00</div><h4>Saisie des notes</h4><p>Les enseignants entrent les notes, Dougsi calcule moyennes et classement.</p></div>
      <div class="timeline-item reveal d3"><div class="timeline-time">17h00</div><h4>Suivi de la direction</h4><p>Le directeur consulte le tableau de bord : effectifs, encaissements, taux de présence.</p></div>
    </div>
  </div>
</section>

<!-- ====== FONCTIONNALITÉS (onglets) ====== -->
<section class="section" id="features">
  <div class="container">
    <div class="section-header reveal">
      <div class="eyebrow">Tout-en-un</div>
      <h2>Une seule plateforme pour toute votre école</h2>
      <p>Fini les cahiers, les fichiers Excel éparpillés et les calculs manuels. Dougsi centralise votre gestion quotidienne.</p>
    </div>

    <div class="tab-btns" data-tabgroup="feat">
      <button type="button" class="tab-btn active" data-tab="feat-grades">Notes & bulletins</button>
      <button type="button" class="tab-btn" data-tab="feat-absences">Absences</button>
      <button type="button" class="tab-btn" data-tab="feat-payments">Paiements</button>
      <button type="button" class="tab-btn" data-tab="feat-comm">Communication</button>
      <button type="button" class="tab-btn" data-tab="feat-timetable">Emploi du temps</button>
      <button type="button" class="tab-btn" data-tab="feat-dashboard">Pilotage</button>
    </div>

    <div class="tab-panel active" id="feat-grades" data-tabgroup="feat">
      <div class="tab-panel-grid">
        <div class="tab-panel-text">
          <h3>Des bulletins sans calculs manuels</h3>
          <p>Les enseignants saisissent les notes à la main ou les importent en masse depuis Excel. Dougsi calcule automatiquement moyennes, classement et mentions.</p>
          <ul class="demo-list">
            <li>Moyennes pondérées par coefficient</li>
            <li>Import de notes par fichier Excel</li>
            <li>Bulletins générés en PDF</li>
          </ul>
        </div>
        <div class="mini-panel">
          <div class="mini-panel-head">BULLETIN — TRIMESTRE 1</div>
          <div class="mini-panel-body">
            <div class="bm-row"><span class="bm-subject">Mathématiques</span><span class="bm-avg">16,5/20</span></div>
            <div class="bm-row"><span class="bm-subject">Français</span><span class="bm-avg">14,2/20</span></div>
            <div class="bm-row"><span class="bm-subject">Sciences</span><span class="bm-avg">17,0/20</span></div>
          </div>
        </div>
      </div>
    </div>

    <div class="tab-panel" id="feat-absences" data-tabgroup="feat">
      <div class="tab-panel-grid">
        <div class="tab-panel-text">
          <h3>Un appel en quelques secondes</h3>
          <p>Marquez les présences par classe, ou importez-les en masse via Excel pour toute une journée. Les tuteurs sont notifiés automatiquement en cas d'absence ou de retard.</p>
          <ul class="demo-list">
            <li>Saisie quotidienne par classe</li>
            <li>Import Excel en masse</li>
            <li>Notification automatique des tuteurs</li>
          </ul>
        </div>
        <div class="mini-panel">
          <div class="mini-panel-head">APPEL — CM2-A</div>
          <div class="mini-panel-body">
            <div class="mock-table" style="border:none;">
              <div class="mock-row header"><span>Élève</span><span>Statut</span><span></span></div>
              <div class="mock-row"><span>Ayan D.</span><span class="mock-badge good">Présent</span><span></span></div>
              <div class="mock-row"><span>Hamza O.</span><span class="mock-badge warn">Retard</span><span></span></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="tab-panel" id="feat-payments" data-tabgroup="feat">
      <div class="tab-panel-grid">
        <div class="tab-panel-text">
          <h3>Encaissements et impayés sous contrôle</h3>
          <p>Plans de paiement par tranches, remises, journal de caisse et relances d'impayés — tout est suivi en temps réel, avec un reçu généré à chaque paiement.</p>
          <ul class="demo-list">
            <li>Plans de paiement par tranches</li>
            <li>Journal de caisse quotidien</li>
            <li>Suivi des impayés en temps réel</li>
          </ul>
        </div>
        <div class="mini-panel">
          <div class="mini-panel-head">JOURNAL DE CAISSE</div>
          <div class="mini-panel-body">
            <div class="mock-kpis" style="margin-bottom:0;">
              <div class="mock-kpi good"><div class="k-lbl">Encaissé</div><div class="k-val">2,4M</div></div>
              <div class="mock-kpi"><div class="k-lbl">Impayés</div><div class="k-val">180K</div></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="tab-panel" id="feat-comm" data-tabgroup="feat">
      <div class="tab-panel-grid">
        <div class="tab-panel-text">
          <h3>Les parents informés, sans effort</h3>
          <p>Annonces de l'école, devoirs à venir, notifications d'absence — tout part automatiquement par email, depuis l'adresse de votre établissement.</p>
          <ul class="demo-list">
            <li>Annonces publiées en un clic</li>
            <li>Emails envoyés depuis votre école</li>
            <li>Espace parent dédié</li>
          </ul>
        </div>
        <div class="mini-panel">
          <div class="mini-panel-head">ANNONCES</div>
          <div class="mini-panel-body">
            <div class="bm-row"><span class="bm-subject">📢 Réunion parents-profs</span><span style="font-size:10px;opacity:.5;">Auj.</span></div>
            <div class="bm-row"><span class="bm-subject">📢 Sortie pédagogique CM2</span><span style="font-size:10px;opacity:.5;">Hier</span></div>
          </div>
        </div>
      </div>
    </div>

    <div class="tab-panel" id="feat-timetable" data-tabgroup="feat">
      <div class="tab-panel-grid">
        <div class="tab-panel-text">
          <h3>Un emploi du temps clair pour tous</h3>
          <p>Construisez l'emploi du temps par classe. Chaque enseignant et chaque parent voit uniquement ce qui le concerne.</p>
          <ul class="demo-list">
            <li>Grille par classe et par enseignant</li>
            <li>Visible par les parents</li>
            <li>Mis à jour en temps réel</li>
          </ul>
        </div>
        <div class="mini-panel">
          <div class="mini-panel-head">EMPLOI DU TEMPS — CM2-A</div>
          <div class="mini-panel-body">
            <div class="bm-row"><span class="bm-subject">08h00 – 09h00</span><span style="font-size:11px;">Mathématiques</span></div>
            <div class="bm-row"><span class="bm-subject">09h00 – 10h00</span><span style="font-size:11px;">Français</span></div>
          </div>
        </div>
      </div>
    </div>

    <div class="tab-panel" id="feat-dashboard" data-tabgroup="feat">
      <div class="tab-panel-grid">
        <div class="tab-panel-text">
          <h3>Un tableau de bord pour piloter votre école</h3>
          <p>Effectifs, encaissements, taux de présence, factures en retard — la direction a une vue d'ensemble, mise à jour en continu.</p>
          <ul class="demo-list">
            <li>Indicateurs clés en un coup d'œil</li>
            <li>Suivi financier consolidé</li>
            <li>Alertes sur les retards de paiement</li>
          </ul>
        </div>
        <div class="mini-panel">
          <div class="mini-panel-head">TABLEAU DE BORD</div>
          <div class="mini-panel-body">
            <div class="mock-kpis" style="margin-bottom:0;">
              <div class="mock-kpi"><div class="k-lbl">Élèves</div><div class="k-val">340</div></div>
              <div class="mock-kpi good"><div class="k-lbl">Recouvrement</div><div class="k-val">94%</div></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ====== DÉMO INTERACTIVE : les 3 espaces ====== -->
<section class="section demo-section">
  <div class="container">
    <div class="section-header reveal">
      <div class="eyebrow">Démo interactive</div>
      <h2>Un espace pensé pour chaque utilisateur</h2>
      <p>Cliquez pour voir ce que chaque profil consulte dans Dougsi.</p>
    </div>

    <div class="role-tabs" data-tabgroup="role">
      <button type="button" class="role-tab-btn active" data-tab="role-parent">👨‍👩‍👧 Espace parent</button>
      <button type="button" class="role-tab-btn" data-tab="role-teacher">👩‍🏫 Espace enseignant</button>
      <button type="button" class="role-tab-btn" data-tab="role-admin">👔 Espace administrateur</button>
    </div>

    <div class="role-panel active" id="role-parent" data-tabgroup="role">
      <div class="mini-panel">
        <div class="mini-panel-head">AYAN DIRIEH — CM2-A</div>
        <div class="mini-panel-body">
          <div class="bm-row"><span class="bm-subject">Moyenne générale</span><span class="bm-avg">15,4/20</span></div>
          <div class="bm-row"><span class="bm-subject">Absences ce trimestre</span><span style="font-size:12px;">1 (justifiée)</span></div>
          <div class="bm-row"><span class="bm-subject">Prochaine échéance</span><span style="font-size:12px;">15 000 FDJ — 05/11</span></div>
          <div class="bm-row"><span class="bm-subject">Dernière annonce</span><span style="font-size:12px;">Réunion parents-profs</span></div>
        </div>
      </div>
    </div>

    <div class="role-panel" id="role-teacher" data-tabgroup="role">
      <div class="mini-panel">
        <div class="mini-panel-head">ESPACE ENSEIGNANT — CM2-A</div>
        <div class="mini-panel-body">
          <div class="mock-table" style="border:none;">
            <div class="mock-row header"><span>Élève</span><span>Présence</span><span>Note</span></div>
            <div class="mock-row"><span>Ayan D.</span><span class="mock-badge good">Présent</span><span>16,5</span></div>
            <div class="mock-row"><span>Hamza O.</span><span class="mock-badge warn">Retard</span><span>12,8</span></div>
            <div class="mock-row"><span>Sagal M.</span><span class="mock-badge good">Présent</span><span>17,1</span></div>
          </div>
        </div>
      </div>
    </div>

    <div class="role-panel" id="role-admin" data-tabgroup="role">
      <div class="mini-panel">
        <div class="mini-panel-head">TABLEAU DE BORD — DIRECTION</div>
        <div class="mini-panel-body">
          <div class="mock-kpis" style="margin-bottom:0;">
            <div class="mock-kpi"><div class="k-lbl">Élèves</div><div class="k-val">340</div></div>
            <div class="mock-kpi good"><div class="k-lbl">Encaissé</div><div class="k-val">2,4M FDJ</div></div>
            <div class="mock-kpi"><div class="k-lbl">Recouvrement</div><div class="k-val">94%</div></div>
          </div>
        </div>
      </div>
    </div>

    <p class="role-fiction-note">Données fictives, à titre d'illustration.</p>
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

<!-- ====== APPLICATION MOBILE ====== -->
<section class="section mobile-section" id="mobile">
  <div class="container">
    <div class="mobile-grid">
      <div class="mobile-text reveal">
        <div class="mobile-badge">🚧 Bientôt disponible</div>
        <h3>Dougsi, bientôt dans votre poche</h3>
        <p>Une application mobile iOS et Android est en cours de développement pour les parents et le personnel — accès aux notes, absences et paiements, et notifications directement sur le téléphone, sans passer par le navigateur.</p>
        <div class="mobile-feats">
          <div class="mobile-feat"><span class="dot"></span>Notifications d'absence</div>
          <div class="mobile-feat"><span class="dot"></span>Notes & bulletins</div>
          <div class="mobile-feat"><span class="dot"></span>Échéances de paiement</div>
          <div class="mobile-feat"><span class="dot"></span>Annonces de l'école</div>
        </div>
      </div>
      <div class="mobile-mock reveal d1">
        <div class="phone-frame">
          <div class="phone-notch"></div>
          <div class="phone-screen-head">
            <div class="ph-title">Mes élèves</div>
            <div class="ph-sub">CM2-A · 28 élèves</div>
          </div>
          <div class="phone-list">
            <div class="phone-row">
              <div class="pr-av">AD</div>
              <div class="pr-info"><div class="pr-name">Ayan Dirieh</div><div class="pr-sub">CM2-A · 15,4/20</div></div>
              <span class="pr-badge">Présent</span>
            </div>
            <div class="phone-row">
              <div class="pr-av">HO</div>
              <div class="pr-info"><div class="pr-name">Hamza Omar</div><div class="pr-sub">CM2-A · 12,8/20</div></div>
              <span class="pr-badge">Présent</span>
            </div>
            <div class="phone-row">
              <div class="pr-av">SM</div>
              <div class="pr-info"><div class="pr-name">Sagal Mohamed</div><div class="pr-sub">CM2-A · 17,1/20</div></div>
              <span class="pr-badge">Présent</span>
            </div>
            <div class="phone-row">
              <div class="pr-av">IA</div>
              <div class="pr-info"><div class="pr-name">Idriss Ali</div><div class="pr-sub">CM2-A · 14,0/20</div></div>
              <span class="pr-badge">Présent</span>
            </div>
          </div>
          <div class="phone-tabbar">
            <div class="phone-tab active"><div class="pt-ic">🎓</div>Élèves</div>
            <div class="phone-tab"><div class="pt-ic">📅</div>Absences</div>
            <div class="phone-tab"><div class="pt-ic">🔔</div>Annonces</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ====== COMPARATIF ====== -->
<section class="section">
  <div class="container">
    <div class="section-header reveal">
      <div class="eyebrow">Comparaison</div>
      <h2>Dougsi, face à vos méthodes actuelles</h2>
    </div>
    <div style="overflow-x:auto;">
      <table class="compare-table reveal">
        <thead>
          <tr>
            <th></th>
            <th class="hl">Dougsi</th>
            <th>Cahiers & Excel</th>
          </tr>
        </thead>
        <tbody>
          <tr><td>Calcul automatique des moyennes</td><td class="compare-yes">✓</td><td class="compare-no">✗</td></tr>
          <tr><td>Notification automatique des parents</td><td class="compare-yes">✓</td><td class="compare-no">✗</td></tr>
          <tr><td>Suivi des impayés en temps réel</td><td class="compare-yes">✓</td><td class="compare-no">✗</td></tr>
          <tr><td>Accès selon le rôle de chacun</td><td class="compare-yes">✓</td><td class="compare-no">✗</td></tr>
          <tr><td>Import/export Excel quand besoin</td><td class="compare-yes">✓</td><td class="compare-yes">✓</td></tr>
          <tr><td>Accessible depuis n'importe où</td><td class="compare-yes">✓</td><td class="compare-no">✗</td></tr>
        </tbody>
      </table>
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

<!-- ====== SÉCURITÉ ====== -->
<section class="section" style="background:var(--slate);">
  <div class="container">
    <div class="section-header reveal">
      <div class="eyebrow">Confiance</div>
      <h2>Vos données, protégées</h2>
    </div>
    <div class="security-grid">
      <div class="security-item reveal"><div class="si">🤐</div><h4>Confidentialité</h4><p>Les données de votre école sont isolées, invisibles aux autres établissements.</p></div>
      <div class="security-item reveal d1"><div class="si">🔒</div><h4>Sécurité</h4><p>Mots de passe chiffrés, accès par rôle, chaque connexion tracée.</p></div>
      <div class="security-item reveal d2"><div class="si">🛡️</div><h4>Zéro risque de perte</h4><p>Vos données sont sauvegardées pour ne jamais être perdues.</p></div>
      <div class="security-item reveal d3"><div class="si">⏱️</div><h4>Garantie de disponibilité</h4><p>Accessible 24h/24, depuis n'importe quel appareil connecté.</p></div>
    </div>
    <div class="included-block reveal">
      <div class="ib-title">Inclus dans chaque abonnement</div>
      <div class="included-item">Accès gratuit pour enseignants et personnel</div>
      <div class="included-item">Mise en place et formation</div>
      <div class="included-item">Mises à jour continues</div>
      <div class="included-item">Support réactif en français</div>
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
      <details><summary>Une application mobile est-elle disponible ?</summary><div class="answer">Elle est en cours de développement pour iOS et Android. En attendant, Dougsi fonctionne déjà très bien depuis le navigateur de votre téléphone.</div></details>
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

  // Onglets (fonctionnalités + démo interactive)
  document.querySelectorAll('.tab-btn, .role-tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const group = btn.dataset.tabgroup;
      const target = btn.dataset.tab;

      document.querySelectorAll(`[data-tabgroup="${group}"]`).forEach(el => {
        if (el.classList.contains('tab-btn') || el.classList.contains('role-tab-btn')) {
          el.classList.toggle('active', el === btn);
        } else {
          el.classList.toggle('active', el.id === target);
        }
      });
    });
  });

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
