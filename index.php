<?php
// Logo SVG (includes/img/logo.svg), inliné côté serveur pour pouvoir le recolorer en CSS (currentColor)
// selon le thème actif — un simple <img src="..."> ne le permettrait pas.
$logoPath = __DIR__ . '/includes/img/logo.svg';
$LOGO_SVG = is_file($logoPath) ? file_get_contents($logoPath) : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>HopHop — recettes de bière</title>
<style>
/* ==========================================================================
   HoPHoP — styles
   5 thèmes de couleur (choisis dans Compte → Apparence), appliqués via
   <html data-theme="vert|ambre|ble|myrtille|sombre">. « vert » (par défaut)
   respecte en plus la préférence sombre/claire du système d'exploitation.
   ========================================================================== */
:root{
  /* Vert nature — thème par défaut */
  --bg:#f0f5ec; --surface:#ffffff; --head:#2d4a2d; --accent:#5a8a5a; --soft:#a8c89a;
  --text:#1c2b1c; --muted:#4b5f4b; --line:#d5e2cd; --btn:#2d4a2d; --btnfg:#ffffff;
  --danger:#9c3324; --dangerbg:#fbeee9; --okbg:#e4efdc;
  --serif:"Iowan Old Style","Palatino Linotype",Palatino,Georgia,serif;
  --sans:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;
}
@media (prefers-color-scheme:dark){
  :root:not([data-theme]), :root[data-theme="vert"]{
    --bg:#131a13; --surface:#1c261c; --head:#a8c89a; --accent:#7fae7f; --soft:#4b6b4b;
    --text:#e7efe1; --muted:#a9bca4; --line:#2d3d2d; --btn:#a8c89a; --btnfg:#132013;
    --danger:#f0a090; --dangerbg:#3a1f1a; --okbg:#243424;
  }
}
/* Sombre — toujours sombre, quelle que soit la préférence système (mêmes valeurs que le repli nuit du thème vert) */
:root[data-theme="sombre"]{
  --bg:#131a13; --surface:#1c261c; --head:#a8c89a; --accent:#7fae7f; --soft:#4b6b4b;
  --text:#e7efe1; --muted:#a9bca4; --line:#2d3d2d; --btn:#a8c89a; --btnfg:#132013;
  --danger:#f0a090; --dangerbg:#3a1f1a; --okbg:#243424;
}
/* Ambre — tons cuivrés/maltés, toujours clair */
:root[data-theme="ambre"]{
  --bg:#fbf3ea; --surface:#ffffff; --head:#6b3a12; --accent:#c97b2e; --soft:#e8c39e;
  --text:#2b1a0d; --muted:#7a5a3a; --line:#ecd6ba; --btn:#6b3a12; --btnfg:#ffffff;
  --danger:#9c3324; --dangerbg:#fbeee9; --okbg:#f3e4cf;
}
/* Blé doré — tons blonds/dorés, toujours clair */
:root[data-theme="ble"]{
  --bg:#fffbea; --surface:#ffffff; --head:#7a6a1f; --accent:#d4af37; --soft:#ecd98f;
  --text:#332c0f; --muted:#8a7a3a; --line:#efe3ad; --btn:#7a6a1f; --btnfg:#ffffff;
  --danger:#9c3324; --dangerbg:#fbeee9; --okbg:#f6eec9;
}
/* Myrtille — tons indigo/prune, toujours clair */
:root[data-theme="myrtille"]{
  --bg:#f3eef8; --surface:#ffffff; --head:#352a52; --accent:#7a5ea8; --soft:#c9b8e0;
  --text:#211a33; --muted:#6a5e82; --line:#ddd0ec; --btn:#352a52; --btnfg:#ffffff;
  --danger:#9c3324; --dangerbg:#fbeee9; --okbg:#e6def2;
}
*,*::before,*::after{box-sizing:border-box}
html{max-width:100%;overflow-x:hidden}
body{margin:0;background:var(--bg);color:var(--text);font:16px/1.5 var(--sans);-webkit-text-size-adjust:100%;max-width:100%;overflow-x:hidden}
#app{max-width:100%;min-width:0;overflow-x:hidden}
h1,h2,h3{font-family:var(--serif);color:var(--head);margin:0;font-weight:600;letter-spacing:.005em;overflow-wrap:anywhere}
h1{font-size:1.6rem} h2{font-size:1.2rem;margin-bottom:.6rem} h3{font-size:1rem;margin:.9rem 0 .3rem}
button{font:inherit;cursor:pointer}
.wrap{max-width:1120px;margin:0 auto;padding:1rem;min-width:0;width:100%;box-sizing:border-box}

.top{position:sticky;top:0;z-index:20;background:var(--surface);border-bottom:1px solid var(--line);max-width:100%}
.top .in{max-width:1120px;margin:0 auto;padding:.6rem 1rem 0;display:flex;flex-wrap:wrap;align-items:center;gap:.45rem .7rem}
.top-user{display:flex;flex-wrap:wrap;align-items:center;justify-content:flex-end;gap:.4rem .55rem;margin-left:auto;min-width:0}
.top-forum{flex:none;white-space:normal;text-align:center;text-decoration:none;box-sizing:border-box}
.brand{font-family:var(--serif);font-size:1.35rem;color:var(--head);display:flex;align-items:center;gap:.5rem;font-weight:700;flex:none}
.brand-logo{width:1.7rem;height:1.7rem;display:inline-flex;flex:none;color:var(--head)}
.brand-logo svg{width:100%;height:100%;display:block}
.grow{flex:1}
.who{font-size:.9rem;color:var(--muted);display:flex;align-items:center;gap:.4rem;min-width:0;max-width:100%}
.uname{display:inline-block;overflow-wrap:anywhere}
.avatar{width:1.8rem;height:1.8rem;border-radius:50%;background:var(--soft);display:flex;align-items:center;justify-content:center;font-size:1.05rem;flex:none;overflow:hidden;line-height:1}
.avatar img{width:100%;height:100%;object-fit:cover;display:block}
.avatar.lg{width:4.5rem;height:4.5rem;font-size:2.2rem}
.avatar-picks{display:flex;flex-wrap:wrap;gap:.4rem;margin:.5rem 0}
.avatar-picks button{width:2.4rem;height:2.4rem;border-radius:50%;border:1px solid var(--line);background:var(--surface);font-size:1.2rem;display:flex;align-items:center;justify-content:center;padding:0}
.avatar-picks button[aria-pressed="true"]{border-color:var(--accent);box-shadow:0 0 0 2px var(--soft)}
.savestate{font-size:.78rem;color:var(--muted);min-width:0;text-align:right}
nav.tabs{max-width:1120px;margin:0 auto;display:flex;gap:.25rem;overflow-x:auto;padding:.3rem .6rem 0;scrollbar-width:none}
nav.tabs button{background:none;border:0;border-bottom:3px solid transparent;color:var(--muted);padding:.55rem .8rem;white-space:nowrap;font-weight:500}
nav.tabs button[aria-current="page"]{color:var(--head);border-bottom-color:var(--accent);font-weight:700}

.btn{background:var(--btn);color:var(--btnfg);border:1px solid var(--btn);border-radius:9px;padding:.55rem 1rem;font-weight:600}
.btn:hover{filter:brightness(1.12)}
.btn.ghost{background:transparent;color:var(--head);border-color:var(--accent)}
.btn.danger{background:transparent;color:var(--danger);border-color:var(--danger)}
.btn.sm{padding:.35rem .7rem;font-size:.9rem}
.btn:disabled{opacity:.6;cursor:default}
.ico{background:transparent;border:1px solid var(--line);color:var(--muted);border-radius:8px;width:2.2rem;height:2.2rem;flex:none}
.ico:hover{color:var(--danger);border-color:var(--danger)}
button:focus-visible,input:focus-visible,select:focus-visible,textarea:focus-visible{outline:3px solid var(--accent);outline-offset:1px}
details.brew-extra{margin-top:.85rem;border:1px solid var(--line);border-radius:10px;padding:.55rem .75rem;background:var(--bg);container-type:inline-size;container-name:brew-extra}
details.brew-extra>summary{cursor:pointer;font-weight:600;color:var(--head);font-family:var(--serif);list-style-position:outside}
details.brew-extra>summary::-webkit-details-marker{color:var(--accent)}
details.brew-extra[open]>summary{margin-bottom:.55rem}
details.brew-extra h3{margin:.75rem 0 .35rem;font-size:.95rem}
details.brew-extra .fgrid{margin-top:.2rem}
details.brew-extra .scroll{overflow-x:visible}
details.brew-extra .scroll .brew-xn-tbl,details.brew-extra .brew-xn-tbl{width:100%;max-width:100%;table-layout:fixed;font-size:.88rem;margin:.25rem 0 .4rem}
details.brew-extra .brew-xn-tbl th,details.brew-extra .brew-xn-tbl td{vertical-align:middle;overflow-wrap:break-word}
details.brew-extra .brew-xn-tbl .xn-planned{width:6.5rem;color:var(--muted);white-space:nowrap}
details.brew-extra .brew-xn-tbl .xn-actual{width:7.5rem}
details.brew-extra .brew-xn-tbl .xn-actions{width:2.6rem;text-align:center;padding-left:.2rem;padding-right:.2rem}
details.brew-extra .brew-xn-tbl .xn-actual input,details.brew-extra .brew-xn-tbl td.xn-actual input{width:100%;max-width:none;padding:.35rem .45rem;font-size:.88rem;box-sizing:border-box}
details.brew-extra .brew-xn-tbl input{padding:.35rem .45rem;font-size:.88rem;max-width:none}
details.brew-extra .brew-xn-tbl td.xn-planned input[data-xn-ep]{width:3.6rem}
details.brew-extra .brew-xn-tbl td.xn-planned input[data-xn-eu]{width:2.4rem}
details.brew-extra .brew-xn-rec{font-size:.72rem;color:var(--muted);font-weight:400;line-height:1.25}
/* Écran étroit : chaque relevé devient une fiche (nom, puis prévu / réel), au lieu d’un tableau dont la 1re colonne s’écrase lettre par lettre. */
@container brew-extra (max-width:26rem){
  details.brew-extra{padding:.5rem .55rem}
  details.brew-extra .scroll .brew-xn-tbl,details.brew-extra .brew-xn-tbl{display:block;table-layout:auto;font-size:.92rem;margin:.15rem 0 .35rem}
  details.brew-extra .brew-xn-tbl thead{display:none}
  details.brew-extra .brew-xn-tbl tbody{display:flex;flex-direction:column;gap:.45rem}
  details.brew-extra .brew-xn-tbl tr{display:grid;grid-template-columns:minmax(0,1fr);gap:.28rem .4rem;background:var(--surface);border:1px solid var(--line);border-radius:10px;padding:.55rem .6rem}
  details.brew-extra .brew-xn-tbl tr:has(> .xn-actions button){grid-template-columns:minmax(0,1fr) auto}
  details.brew-extra .brew-xn-tbl td{display:block;width:auto;max-width:none;min-width:0;padding:0;border:0;white-space:normal;overflow-wrap:break-word}
  details.brew-extra .brew-xn-tbl td:first-child{grid-column:1;grid-row:1;font-weight:600;color:var(--text)}
  details.brew-extra .brew-xn-tbl tr:has(> .xn-actions button)>td.xn-actions{grid-column:2;grid-row:1;align-self:center;width:auto}
  details.brew-extra .brew-xn-tbl td.xn-actions:empty{display:none}
  details.brew-extra .brew-xn-tbl td.xn-planned,details.brew-extra .brew-xn-tbl td.xn-actual{grid-column:1 / -1;display:flex;align-items:center;flex-wrap:wrap;gap:.35rem .45rem;width:auto;max-width:none;min-width:0;font-weight:500;color:var(--text)}
  details.brew-extra .brew-xn-tbl td.xn-planned::before,details.brew-extra .brew-xn-tbl td.xn-actual::before{content:attr(data-label);flex:0 0 5.6rem;font-size:.72rem;font-weight:600;letter-spacing:.03em;text-transform:uppercase;color:var(--muted)}
  details.brew-extra .brew-xn-tbl td.xn-actual input{flex:1 1 6rem;width:auto;min-width:0}
  details.brew-extra .brew-xn-tbl td.xn-planned input[data-xn-ep]{flex:1 1 4.5rem;width:auto;max-width:8rem;min-width:0}
  details.brew-extra .brew-xn-tbl td.xn-planned input[data-xn-eu]{flex:0 0 3.2rem;width:3.2rem}
}
.brew-extra-chip{font-size:.75rem;color:var(--muted);margin-top:.35rem}
.brew-extra-hint{font-size:.9rem;color:var(--muted);margin:.5rem 0 .75rem;padding:.55rem .8rem;border:1px dashed var(--line);border-radius:10px;background:var(--surface)}
.brew-extra-hint button{font:inherit;font-size:inherit;color:var(--head);background:none;border:none;padding:0;text-decoration:underline;cursor:pointer}

input,select,textarea{width:100%;padding:.55rem .65rem;border:1px solid var(--line);border-radius:8px;background:var(--surface);color:var(--text);font:inherit;min-width:0}
textarea{min-height:5rem;resize:vertical}
.fld{display:flex;flex-direction:column;gap:.2rem;min-width:0}
.fld>span{font-size:.78rem;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.03em}
output{padding:.55rem 0;font-weight:600;color:var(--head)}
.fgrid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.8rem}
.fld.span2{grid-column:1 / -1}
.bar{display:flex;flex-wrap:wrap;align-items:center;gap:.6rem;margin-bottom:1rem;min-width:0}
.search{max-width:min(260px,100%)}

.card{background:var(--surface);border:1px solid var(--line);border-radius:14px;padding:1rem;margin-bottom:1rem;min-width:0;max-width:100%;overflow-x:hidden}
.cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:1rem}
.rcard{cursor:pointer;display:flex;flex-direction:column;gap:.5rem;text-align:left;width:100%;color:inherit;margin:0}
.rcard:hover{border-color:var(--accent)}
.rcard .meta{display:flex;flex-wrap:wrap;gap:.35rem}
.chip{display:inline-block;background:var(--okbg);color:var(--head);border-radius:99px;padding:.1rem .6rem;font-size:.8rem;font-weight:600}
.chip.bad{background:var(--dangerbg);color:var(--danger)}
.sub{color:var(--muted);font-size:.9rem}
.foot-usage{text-align:center;color:var(--muted);font-size:.75rem;margin:1.6rem 0 0;opacity:.72}
.empty{text-align:center;color:var(--muted);padding:2.5rem 1rem;border:2px dashed var(--line);border-radius:14px}
.sw{width:22px;height:22px;border-radius:50%;border:2px solid var(--surface);box-shadow:0 0 0 1px var(--line);display:inline-block;vertical-align:middle}

.editor{display:grid;grid-template-columns:minmax(0,1fr) 320px;grid-template-areas:"identity stats" "rest stats";gap:1rem;align-items:start}
.recipe-identity{grid-area:identity;margin-bottom:0;min-width:0}
.editor-rest{grid-area:rest;min-width:0}
.stats{grid-area:stats;position:sticky;top:112px;min-width:0}
.style-line{display:flex;gap:.4rem;align-items:center;flex-wrap:wrap}
.style-line select{flex:1 1 12rem;min-width:0;width:auto}
.sheet-head{display:flex;justify-content:space-between;align-items:flex-start;gap:.6rem;min-width:0}
.sheet-head > div{min-width:0}
.account-id{display:flex;gap:.8rem;align-items:center;min-width:0}
.account-id > div{min-width:0}
@media (max-width:860px){
  .editor{grid-template-columns:minmax(0,1fr);grid-template-areas:"identity" "stats" "rest"}
  .stats{position:static}
}
.namefield{font-family:var(--serif);font-size:1.4rem;font-weight:600}
.row{display:grid;grid-template-columns:repeat(auto-fit,minmax(115px,1fr));gap:.6rem;align-items:end;padding:.7rem 0;border-top:1px solid var(--line)}
.row .rowhead{grid-column:1/-1;display:flex;gap:.5rem;min-width:0}
.row .rowhead select,.row .rowhead input{flex:1;min-width:0;width:auto}
.row .hint{grid-column:1/-1;min-height:0}
.row .hint:empty{display:none}
.secthead{display:flex;flex-wrap:wrap;align-items:center;gap:.6rem;margin-bottom:.2rem;min-width:0}
.beer{display:flex;gap:1rem;align-items:center;margin-bottom:.6rem}
.glass{width:54px;height:76px;border:2px solid var(--head);border-radius:6px 6px 14px 14px;overflow:hidden;position:relative;background:var(--surface);flex:none}
.glass .foam{height:14px;background:#fffdf5;border-bottom:1px solid var(--line)}
.glass .liq{position:absolute;left:0;right:0;top:14px;bottom:0}
.kpis{display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;margin-bottom:.6rem}
.kpi{background:var(--bg);border-radius:10px;padding:.45rem .3rem;text-align:center}
.kpi b{display:block;font-family:var(--serif);font-size:1.15rem;color:var(--head)}
.kpi span{font-size:.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;font-weight:600}
.kpi-sub{font-size:.68rem;color:var(--muted);margin-top:.1rem}
dl.kv{margin:0;display:grid;grid-template-columns:1fr auto;gap:.2rem .8rem;font-size:.92rem}
dl.kv dt{color:var(--muted)} dl.kv dd{margin:0;font-weight:600;text-align:right}
.warn{background:var(--dangerbg);color:var(--danger);border-radius:8px;padding:.4rem .6rem;font-size:.85rem;margin-top:.5rem}

.tbl{width:100%;border-collapse:collapse;font-size:.95rem}
.tbl th{text-align:left;font-size:.75rem;text-transform:uppercase;color:var(--muted);letter-spacing:.04em;padding:.4rem .5rem;border-bottom:2px solid var(--line)}
.tbl td{padding:.5rem;border-bottom:1px solid var(--line);vertical-align:middle}
.tbl input{padding:.35rem .5rem;max-width:110px}
.scroll{display:block;width:100%;max-width:100%;min-width:0;overflow-x:auto;-webkit-overflow-scrolling:touch}
.scroll .tbl{width:max-content;max-width:none}
.seg{display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:1rem}
.seg button{border:1px solid var(--line);background:var(--surface);color:var(--text);border-radius:99px;padding:.35rem .9rem}
.seg button[aria-pressed="true"]{background:var(--btn);color:var(--btnfg);border-color:var(--btn)}

.sheet{min-width:0;max-width:100%}
.sheet h1{font-size:1.9rem}
.sheet ol{padding-left:1.3rem} .sheet li{margin:.4rem 0}
.editor,.editor-rest,.stats,.recipe-identity{min-width:0;max-width:100%}
.bar > .btn,.bar > .seg{max-width:100%}

dialog{border:1px solid var(--line);border-radius:14px;background:var(--surface);color:var(--text);padding:1.2rem;width:min(560px,94vw);max-height:90vh;overflow:auto}
dialog::backdrop{background:rgba(20,30,20,.5)}
.dlg-actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:.6rem;margin-top:1rem}
.br-style-row{display:flex;align-items:flex-start;gap:.55rem;width:100%;margin:0;border:1px solid var(--line);border-radius:6px;padding:.45rem .6rem;background:var(--surface);color:var(--text);font:inherit;cursor:pointer;box-sizing:border-box}
.br-style-row:hover{border-color:var(--accent);background:var(--bg)}
.br-style-row:has(input:checked){border-color:var(--accent);box-shadow:0 0 0 2px var(--soft)}
.br-style-row input{width:auto;margin:.15rem 0 0;flex:none;accent-color:var(--accent)}
.br-style-row span{min-width:0;flex:1;text-align:left}
#toast{position:fixed;left:50%;transform:translateX(-50%);bottom:1.2rem;background:var(--head);color:var(--bg);padding:.6rem 1rem;border-radius:10px;z-index:99;display:none;max-width:90vw;font-size:.92rem}
.authscreen{position:relative;min-height:100vh;overflow:hidden}
.auth-bg{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:var(--soft);opacity:.22;pointer-events:none;z-index:0}
.auth-bg svg{width:62vmin;height:62vmin;max-width:640px;max-height:640px}
.auth{max-width:400px;margin:8vh auto 0;position:relative;z-index:1}
.auth .seg{justify-content:center}
.err{color:var(--danger);font-size:.9rem;min-height:1.3em}

@media print{
  /* Tassé au maximum pour tenir sur une seule feuille : marges de page réduites, polices et espacements
     resserrés. Pour une recette très chargée, combinez avec le mode « Sans étapes » de la fiche recette. */
  @page{ margin:10mm; }
  .top,.bar,.noprint,dialog,#toast{display:none!important}
  html,body{background:#fff;color:#000;font-size:11.5px}
  .wrap{padding:0;max-width:100%}
  .card{border:1px solid #bbb;break-inside:avoid;background:#fff;padding:.5rem;margin-bottom:.4rem;border-radius:4px}
  h1,h2,h3{color:#000}
  h1{font-size:1.25rem}
  h2{font-size:.98rem;margin-bottom:.25rem}
  h3{font-size:.85rem;margin:.4rem 0 .2rem}
  .kpis{gap:.3rem}
  .kpi{padding:.2rem .1rem}
  .kpi b{font-size:.92rem}
  .kpi span{font-size:.62rem}
  dl.kv{font-size:.8rem;gap:.1rem .6rem}
  .tbl{font-size:.8rem}
  .tbl th,.tbl td{padding:.25rem .35rem}
  .sheet .sub{font-size:.74rem}
  .sheet ol{padding-left:1.1rem}
  .sheet li{margin:.2rem 0}
  .chip{padding:.02rem .45rem;font-size:.7rem}
}

/* ==========================================================================
   Mode d'affichage compact (Compte → Apparence) : mêmes informations, moins
   d'espace entre les éléments — utile sur petit écran ou pour tout voir sans
   défiler. Spécificité volontairement plus élevée (préfixe [data-density])
   pour surcharger proprement les règles ci-dessus sans les dupliquer.
   ========================================================================== */
:root[data-density="compact"] body{font-size:14.5px}
:root[data-density="compact"] .wrap{padding:.6rem}
:root[data-density="compact"] h1{font-size:1.3rem}
:root[data-density="compact"] h2{font-size:1.05rem;margin-bottom:.4rem}
:root[data-density="compact"] h3{font-size:.92rem;margin:.6rem 0 .25rem}
:root[data-density="compact"] .card{padding:.65rem;margin-bottom:.6rem;border-radius:10px}
:root[data-density="compact"] .recipe-identity{margin-bottom:0}
:root[data-density="compact"] .bar{gap:.4rem;margin-bottom:.6rem}
:root[data-density="compact"] .fgrid{gap:.5rem}
:root[data-density="compact"] .btn{padding:.4rem .75rem}
:root[data-density="compact"] .btn.sm{padding:.25rem .55rem;font-size:.82rem}
:root[data-density="compact"] input,:root[data-density="compact"] select,:root[data-density="compact"] textarea{padding:.4rem .5rem}
:root[data-density="compact"] .row{padding:.45rem 0;gap:.4rem}
:root[data-density="compact"] .top .in{padding:.4rem .6rem 0}
:root[data-density="compact"] nav.tabs button{padding:.4rem .6rem}
:root[data-density="compact"] .kpis{gap:.35rem}
:root[data-density="compact"] .kpi{padding:.3rem .2rem}
:root[data-density="compact"] .kpi b{font-size:1rem}
:root[data-density="compact"] .chip{padding:.05rem .5rem;font-size:.75rem}
:root[data-density="compact"] .cards{gap:.6rem}
:root[data-density="compact"] .cards .rcard{gap:.3rem}
:root[data-density="compact"] dialog{padding:.8rem}
:root[data-density="compact"] .seg{gap:.3rem;margin-bottom:.6rem}
:root[data-density="compact"] .sub{font-size:.85rem}

@media (max-width:720px){
  .wrap{padding:.75rem}
  .top .in{
    padding:.5rem .7rem 0;
    display:grid;
    grid-template-columns:minmax(0,1fr) auto;
    grid-template-areas:"brand user" "forum forum";
    align-items:center;
    gap:.45rem .6rem;
  }
  .brand{grid-area:brand;min-width:0}
  .top-user{grid-area:user;margin-left:0;justify-self:end}
  .top-forum{grid-area:forum;width:100%}
  .search{max-width:none;flex:1 1 12rem}
  .kpis{grid-template-columns:repeat(2,minmax(0,1fr))}
  .cards{grid-template-columns:minmax(0,1fr)}
  .seg{max-width:100%}
  .seg button{white-space:normal;text-align:center}
  nav.tabs{flex-wrap:wrap;overflow-x:visible;padding:.3rem .4rem 0}
  nav.tabs button{padding:.4rem .55rem;font-size:.92rem}
}
</style>
</head>
<body>
<div id="app"><div class="wrap"><p class="sub">Chargement…</p></div></div>
<dialog id="dlg"></dialog>
<div id="toast" role="status"></div>

<script>
'use strict';
const LOGO_SVG = <?php echo json_encode($LOGO_SVG, JSON_UNESCAPED_SLASHES); ?>;   // logo includes/img/logo.svg, inliné côté serveur (recolorable en CSS)
/* ==========================================================================
   HOPHOP — application de calcul et de suivi de recettes de bière
   Unités européennes : litres, kilogrammes, grammes, °C, EBC, densité (SG).
   Backend : api.php (fichiers JSON côté serveur, un fichier par compte).
   Sections : 1 Utilitaires · 2 API & session · 3 Données par défaut (repli)
              4 Calculs · 5 Vues · 6 Actions · 7 Exports · 8 Démarrage
   ========================================================================== */

/* ---------- 1. UTILITAIRES ---------- */
const $ = (s, r = document) => r.querySelector(s);
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const uid = () => Math.random().toString(36).slice(2, 9) + Date.now().toString(36).slice(-3);
const N = v => { const x = parseFloat(String(v).replace(',', '.')); return isFinite(x) ? x : 0; };  // accepte la virgule
const f = (n, d = 1) => isFinite(n) ? Number(n).toLocaleString('fr-FR', {minimumFractionDigits: d, maximumFractionDigits: d}) : '–';
const rnd = (x, d) => Math.round(x * 10 ** d) / 10 ** d;
const today = () => new Date().toISOString().slice(0, 10);
const fdate = iso => iso ? new Date(iso + 'T12:00:00').toLocaleDateString('fr-FR') : '–';
const clone = o => JSON.parse(JSON.stringify(o));
const slug = s => (s || 'recette').normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-zA-Z0-9]+/g, '-').replace(/^-|-$/g, '').toLowerCase() || 'recette';
const opts = (list, sel) => list.map(([v, l]) => `<option value="${esc(v)}"${String(v) === String(sel) ? ' selected' : ''}>${esc(l)}</option>`).join('');
let toastT;
function toast(msg) { const t = $('#toast'); t.textContent = msg; t.style.display = 'block'; clearTimeout(toastT); toastT = setTimeout(() => t.style.display = 'none', 3200); }

/* Couleur de la bière à partir de l'EBC (interpolation sur une table SRM usuelle) */
const SRM_TABLE = [[0,'#FFF4B0'],[2,'#FFE699'],[4,'#FFBF42'],[6,'#FBB123'],[8,'#F8A600'],[10,'#F39C00'],[13,'#EA8F00'],[17,'#E58500'],[20,'#DE7C00'],[24,'#CF6900'],[29,'#B54C00'],[35,'#8D2F00'],[40,'#5D1C00'],[60,'#1F0800']];
/* Densité relative (SG) ↔ Brix : formule cubique usuelle (SG → degrés Plato), Brix et Plato étant
   quasi identiques dans la plage de densités du brassage — on affiche le résultat en « °Bx ».
   Remarque : comme toute conversion simple SG↔Brix, elle ne corrige pas la présence d'alcool ; pour la
   densité finale (après fermentation), le °Bx affiché est donc une valeur indicative, pas une mesure
   corrigée au réfractomètre. */
function sgToBrix(sg) { return -616.868 + 1111.14 * sg - 630.272 * sg * sg + 135.997 * sg * sg * sg; }
const sgText = sg => f(sg, 3);
const brixText = sg => f(sgToBrix(sg), 1) + ' °Bx';
// Rendu en ligne « principale (secondaire) » selon la préférence du compte — utilisable partout où du HTML est accepté.
function gUnit(sg) {
  const sgT = sgText(sg), bxT = brixText(sg);
  return D.prefs.unitPrimary === 'brix' ? `${bxT} <span class="sub">(${sgT})</span>` : `${sgT} <span class="sub">(${bxT})</span>`;
}
// Paire {primary, secondary} pour les pavés KPI (grande valeur + petite valeur dessous).
function gKpi(sg) {
  const sgT = sgText(sg), bxT = brixText(sg);
  return D.prefs.unitPrimary === 'brix' ? {primary: bxT, secondary: sgT} : {primary: sgT, secondary: bxT};
}
// Texte simple « principale (secondaire) », sans balisage — pour les chips et autres contextes sans mise en forme imbriquée.
function gText(sg) {
  const sgT = sgText(sg), bxT = brixText(sg);
  return D.prefs.unitPrimary === 'brix' ? `${bxT} (${sgT})` : `${sgT} (${bxT})`;
}
// Valeur dans la seule unité principale (ex. pour une mention « visée X » déjà entre parenthèses).
const gPrimaryText = sg => D.prefs.unitPrimary === 'brix' ? brixText(sg) : sgText(sg);
/* Brix NON corrigé (ce qu'affiche un réfractomètre en fin de fermentation, avant correction alcool), à partir
   de la DI et de la DF corrigée (vraie) — deux formules au choix (réglage Compte → Unités de densité) :
   - « linear » : formule linéaire de Sean Terrill (2011), la plus répandue — FG(SG) = 1 − 0,00085683×DI_°Bx + 0,0034941×DF_°Bx_brut.
     Fiable pour des densités courantes ; au-delà d'environ 1,080 de DI l'écart augmente.
   - « cubic » : variante cubique de Terrill, plus précise sur les bières fortes — résolue numériquement
     (par dichotomie) car non inversible algébriquement : FG(SG) = 1 − 0,0044993×OB + 0,011774×FB
     + 0,00027581×OB² − 0,0012717×FB² − 0,0000072800×OB³ + 0,000063293×FB³. */
function rawBrixAtFG_linear(ogSG, fgSG) {
  const ogBrix = sgToBrix(ogSG);
  return (fgSG - 1 + 0.00085683 * ogBrix) / 0.0034941;
}
function cubicFGForward(ob, fb) {
  return 1 - 0.0044993 * ob + 0.011774 * fb + 0.00027581 * ob * ob - 0.0012717 * fb * fb - 0.0000072800 * ob * ob * ob + 0.000063293 * fb * fb * fb;
}
function rawBrixAtFG_cubic(ogSG, fgSG) {
  const ob = sgToBrix(ogSG);
  let lo = 0, hi = Math.max(ob, 30);   // la formule est strictement croissante en FB sur cette plage
  for (let i = 0; i < 60; i++) {
    const mid = (lo + hi) / 2;
    if (cubicFGForward(ob, mid) < fgSG) lo = mid; else hi = mid;
  }
  return (lo + hi) / 2;
}
function rawBrixAtFG(ogSG, fgSG) {
  return D.prefs.refractFormula === 'cubic' ? rawBrixAtFG_cubic(ogSG, fgSG) : rawBrixAtFG_linear(ogSG, fgSG);
}
function ebcColor(ebc) {
  const s = Math.max(0, ebc / 1.97), T = SRM_TABLE;
  const hex = h => [1, 3, 5].map(i => parseInt(h.slice(i, i + 2), 16));
  for (let i = 1; i < T.length; i++) if (s <= T[i][0] || i === T.length - 1) {
    const [a, b] = [T[i - 1], T[i]], t = Math.min(1, (s - a[0]) / (b[0] - a[0]));
    const A = hex(a[1]), B = hex(b[1]);
    return 'rgb(' + A.map((v, k) => Math.round(v + (B[k] - v) * t)).join(',') + ')';
  }
}

/* ---------- 2. API & SESSION ---------- */
async function api(action, payload = {}) {
  let res, j;
  const ctrl = new AbortController();
  const t = setTimeout(() => ctrl.abort(), 60000);
  try {
    res = await fetch('api.php', {method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({action, ...payload}), signal: ctrl.signal});
  } catch (e) {
    throw new Error(e.name === 'AbortError' ? 'Délai dépassé — le serveur ne répond pas (session forum bloquée ?). Actualisez la page.' : 'Serveur injoignable — vérifiez votre connexion.');
  } finally { clearTimeout(t); }
  try { j = await res.json(); } catch {
    throw new Error('Réponse invalide du serveur (HTTP ' + res.status + '). Vérifiez les logs PHP / phpBB.');
  }
  if (!j.ok) throw new Error(j.error || 'Erreur inconnue.');
  return j;
}
let USER = null, D = null;                    // utilisateur courant et ses données
const AUTH = {mode: 'standalone', features: {register: true, local_login: true, delete_account: true}, login_url: '', logout_url: '', brewrecipes_share_url: '', brewrecipes_style_guides_url: '', brewrecipes_styles_url: '', avatar_url: '', mysql_ready: true, blocked: '', brewer_count: null, visitor_count: null};
const ui = {view: 'recipes', rid: null, q: '', tab: 'ferm', auth: 'login', showArchivedIng: false, sheetSteps: true, sheetCost: true, ingSort: {key: 'name', dir: 'asc'}};
const R = () => D && D.recipes.find(r => r.id === ui.rid);

let saveT, saveBusy = false, saveQueued = false;
function setSaveState(s) { const el = $('#savestate'); if (el) el.textContent = s; }
async function saveNow() {
  if (!USER) return false;
  if (saveBusy) { saveQueued = true; return false; }
  saveBusy = true; setSaveState('Enregistrement…');
  try {
    await api('save', {data: D});
    setSaveState('Enregistré');
    return true;
  } catch (e) {
    setSaveState('Non enregistré');
    toast('Échec de l’enregistrement : ' + e.message);
    return false;
  } finally {
    saveBusy = false;
    if (saveQueued) { saveQueued = false; saveNow(); }
  }
}
const persist = () => { setSaveState('Modifié…'); clearTimeout(saveT); saveT = setTimeout(saveNow, 800); };

async function authSubmit() {
  const name = $('#a-user').value.trim(), pw = $('#a-pw').value, err = $('#a-err'), btn = $('#a-go');
  err.textContent = '';
  if (!name || !pw) { err.textContent = 'Renseignez un identifiant et un mot de passe.'; return; }
  if (ui.auth === 'register' && pw !== $('#a-pw2').value) { err.textContent = 'Les mots de passe ne correspondent pas.'; return; }
  btn.disabled = true;
  try {
    await api(ui.auth === 'register' ? 'register' : 'login', {username: name, password: pw});
    await enterApp(name);
  } catch (e) { err.textContent = e.message; btn.disabled = false; }
}
function normalizeData(d) {
  d.ing = d.ing || {};
  ['ferm', 'hop', 'yeast', 'misc', 'style'].forEach(k => { if (!Array.isArray(d.ing[k])) d.ing[k] = []; });
  d.profiles = Array.isArray(d.profiles) ? d.profiles : [];
  d.profiles.forEach(p => {
    if (p.ratio === undefined || p.ratio === null) p.ratio = 3;
    if (p.infusionTemp === undefined || p.infusionTemp === null) p.infusionTemp = 96;
    p.mashMethod = p.mashMethod === 'biab' ? 'biab' : 'classic';
    if (typeof p.biabSqueeze !== 'boolean') p.biabSqueeze = false;
  });
  d.recipes = Array.isArray(d.recipes) ? d.recipes : [];
  d.recipes.forEach(r => {   // rapport eau/grain, rendement, température d'infusion et méthode de brassage : modifiables par recette, initialisés depuis le profil si absents
    const prof = d.profiles.find(p => p.id === r.profileId) || d.profiles[0] || DEFAULT_P;
    if (r.ratio === undefined || r.ratio === null) r.ratio = prof.ratio ?? 3;
    if (r.eff === undefined || r.eff === null) r.eff = prof.eff ?? 72;
    if (r.infusionTemp === undefined || r.infusionTemp === null) r.infusionTemp = prof.infusionTemp ?? 96;
    if (r.mashMethod === undefined || r.mashMethod === null) r.mashMethod = prof.mashMethod ?? 'classic';
    if (typeof r.biabSqueeze !== 'boolean') r.biabSqueeze = prof.biabSqueeze ?? false;
  });
  d.brews = Array.isArray(d.brews) ? d.brews : [];
  d.stock = (d.stock && typeof d.stock === 'object') ? d.stock : {};
  d.prefs = (d.prefs && typeof d.prefs === 'object') ? d.prefs : {};
  if (!THEMES.some(([k]) => k === d.prefs.theme)) d.prefs.theme = 'vert';
  if (d.prefs.density !== 'compact') d.prefs.density = 'classic';
  if (d.prefs.unitPrimary !== 'brix') d.prefs.unitPrimary = 'sg';
  if (d.prefs.refractFormula !== 'cubic') d.prefs.refractFormula = 'linear';
  if (d.prefs.avatarType !== 'image') d.prefs.avatarType = 'emoji';
  if (!d.prefs.avatarEmoji) d.prefs.avatarEmoji = '🍺';
  if (typeof d.prefs.avatarImage !== 'string') d.prefs.avatarImage = '';
  if (d.prefs.brewExtraNotes !== true) d.prefs.brewExtraNotes = false;
  d.brews.forEach(normalizeBrewExtraNotes);
  return d;
}
// Recadre l'image importée en carré (centre), la réduit à 160×160 et la compresse en JPEG — garde la
// sauvegarde légère quel que soit le fichier d'origine (une photo de plusieurs Mo devient quelques Ko).
function resizeAvatarImage(file) {
  return new Promise((resolve, reject) => {
    const img = new Image(), url = URL.createObjectURL(file);
    img.onload = () => {
      URL.revokeObjectURL(url);
      const SIZE = 160, side = Math.min(img.naturalWidth, img.naturalHeight);
      const sx = (img.naturalWidth - side) / 2, sy = (img.naturalHeight - side) / 2;
      const cv = document.createElement('canvas'); cv.width = SIZE; cv.height = SIZE;
      const ctx = cv.getContext('2d');
      ctx.drawImage(img, sx, sy, side, side, 0, 0, SIZE, SIZE);
      resolve(cv.toDataURL('image/jpeg', 0.85));
    };
    img.onerror = () => { URL.revokeObjectURL(url); reject(new Error('illisible')); };
    img.src = url;
  });
}
const AVATAR_PICKS = ['🍺', '🍻', '🌾', '🍯', '🔥', '🧉', '🍋', '🫐'];
function avatarHTML(cls) {
  const p = D?.prefs || {};
  // Mode forum : avatar phpBB par défaut ; photo HopHop locale si l’utilisateur en a importé une.
  if (p.avatarType === 'image' && p.avatarImage) {
    return `<span class="avatar ${cls || ''}"><img src="${p.avatarImage}" alt=""></span>`;
  }
  if (AUTH.mode === 'phpbb' && AUTH.avatar_url) {
    return `<span class="avatar ${cls || ''}"><img src="${esc(AUTH.avatar_url)}" alt=""></span>`;
  }
  return `<span class="avatar ${cls || ''}">${esc(p.avatarEmoji || '🍺')}</span>`;
}
const THEMES = [['vert', 'Vert nature'], ['ambre', 'Ambre'], ['ble', 'Blé doré'], ['myrtille', 'Myrtille'], ['sombre', 'Sombre']];
function applyPrefs() {
  const html = document.documentElement;
  html.setAttribute('data-theme', D?.prefs?.theme || 'vert');
  html.setAttribute('data-density', D?.prefs?.density || 'classic');
}
async function enterApp(name) {
  const r = await api('load');
  USER = name;
  D = normalizeData(r.data);
  applyPrefs();
  ui.view = 'recipes';
  render();
  await maybeConsumeBrewRecipesImport();
}
/** BeerXML en attente après POST import_preview.php (BrewRecipes → HopHop). */
async function maybeConsumeBrewRecipesImport() {
  const params = new URLSearchParams(location.search);
  if (params.get('brewrecipes_import') !== '1') return;
  try {
    const pending = await api('consume_brewrecipes_import');
    const added = importBeerXML(pending.beerxml);
    if (added.length === 1) {
      if (pending.brewrecipes_id) added[0].brewrecipesId = pending.brewrecipes_id;
      ui.rid = added[0].id;
      ui.view = 'editor';
      renderMain();
      window.scrollTo(0, 0);
    } else {
      renderMain(true);
    }
    const saved = await saveNow();
    toast(saved
      ? (added.length > 1 ? `${added.length} recettes importées depuis BrewRecipes` : 'Recette ouverte depuis BrewRecipes')
      : 'Recette à l’écran mais non enregistrée sur le serveur.');
  } catch (e) {
    toast(e.message || 'Import BrewRecipes impossible.');
  }
  params.delete('brewrecipes_import');
  params.delete('brewrecipes_id');
  const qs = params.toString();
  history.replaceState(null, '', qs ? (location.pathname + '?' + qs) : location.pathname);
}
async function logout() {
  if (AUTH.mode === 'phpbb' && AUTH.logout_url) { window.location.href = AUTH.logout_url; return; }
  try { await api('logout'); } catch {}
  USER = D = null; ui.auth = 'login';
  document.documentElement.removeAttribute('data-theme'); document.documentElement.removeAttribute('data-density');
  render();
}
function applySessionMeta(s) {
  AUTH.mode = s.auth_mode || 'standalone';
  AUTH.features = s.features || AUTH.features;
  AUTH.login_url = s.login_url || '';
  AUTH.logout_url = s.logout_url || '';
  AUTH.brewrecipes_share_url = s.brewrecipes_share_url || '';
  AUTH.brewrecipes_style_guides_url = s.brewrecipes_style_guides_url || '';
  AUTH.brewrecipes_styles_url = s.brewrecipes_styles_url || '';
  AUTH.avatar_url = s.avatar_url || '';
  AUTH.mysql_ready = s.mysql_ready !== false;
  AUTH.blocked = s.blocked || '';
  const usageOk = AUTH.mode === 'phpbb';
  AUTH.brewer_count = (usageOk && typeof s.brewer_count === 'number' && s.brewer_count >= 0) ? s.brewer_count : null;
  AUTH.visitor_count = (usageOk && typeof s.visitor_count === 'number' && s.visitor_count >= 0) ? s.visitor_count : null;
}
/** URLs BrewRecipes fournies par la session (vides hors intégration phpBB). */
function brewrecipesShareUrl() { return AUTH.brewrecipes_share_url || ''; }
function brewrecipesStyleGuidesUrl() { return AUTH.brewrecipes_style_guides_url || ''; }
function brewrecipesStylesUrl() { return AUTH.brewrecipes_styles_url || ''; }

/* ---------- 3. LIBELLÉS & GABARITS ---------- */
const FTYPE = {grain: 'Grain (empâté)', sugar: 'Sucre', extract: 'Extrait'};
const USE_H = {boil: 'Ébullition', fwh: 'Premier moût', whirl: 'Whirlpool', hopstand: 'Hop stand', dry: 'Dry hop'};
const USE_M = {mash: 'Empâtage', boil: 'Ébullition', ferm: 'Fermentation', bottle: 'Embouteillage'};
const STATUS = {planned: 'Prévu', brewed: 'Brassé', ferm: 'En fermentation', cond: 'En garde / conditionné', done: 'Terminé'};
const LIBNAME = {ferm: 'Fermentescibles', hop: 'Houblons', yeast: 'Levures', misc: 'Divers', style: 'Styles'};
const STOCK_TYPES = ['ferm', 'hop', 'yeast', 'misc'];   // bibliothèques suivies en stock (les styles ne se stockent pas)
const RLIB = {ferm: 'ferm', hops: 'hop', yeast: 'yeast', misc: 'misc'};   // section de recette → bibliothèque
const lib = (t, id) => D.ing[t].find(x => x.id === id);
const unitOf = (t, x) => t === 'ferm' ? 'kg' : t === 'hop' ? 'g' : t === 'yeast' ? 'sachet(s)' : (x.unit || 'g');
const DEFAULT_P = {name: 'Défaut', batch: 20, boil: 60, evap: 3, trub: 1.5, mashLoss: 1, absorb: 1, shrink: 4, eff: 72, ratio: 3, infusionTemp: 96, mashMethod: 'classic', biabSqueeze: false};
// Méthode de brassage : classique (empâtage + rinçage) ou BIAB (volume unique dans la cuve d’ébullition, sans rinçage).
const BREW_METHOD = [['classic', 'Classique (empâtage + rinçage)'], ['biab', 'Brew in a Bag — BIAB (volume unique, sans rinçage)']];

function newRecipe() {
  const p = D.profiles[0] || DEFAULT_P;
  return {id: uid(), name: 'Nouvelle recette', style: '', styleId: '', profileId: D.profiles[0]?.id || '', batch: p.batch, ratio: p.ratio, eff: p.eff, infusionTemp: p.infusionTemp, mashMethod: p.mashMethod, biabSqueeze: p.biabSqueeze, grainTemp: 20, co2: 2.4,
    ferm: [], hops: [], yeast: [], misc: [],
    mash: [{name: 'Saccharification', temp: 67, time: 60}, {name: 'Mash out', temp: 78, time: 10}],
    ferment: [{name: 'Fermentation tumultueuse', temp: 19, days: 14}, {name: 'Cold crash', temp: 4, days: 7}],
    notes: '', created: Date.now(), updated: Date.now()};
}

/* ---------- 4. CALCULS ----------
   Potentiel : 1 kg d'extrait pur = 384,6 points·L (1 L à SG 1,3846).
   OG  : (potentiel grains × rendement + potentiel sucres/extraits) / volume en fermenteur
   FG  : atténuation apparente moyenne des levures      ABV : (OG − FG) × 131,25
   IBU : formule de Tinseth      EBC : formule de Morey (SRM × 1,97)
   Eau : volume pré-ébullition = (volume + pertes) / (1 − contraction) + évaporation           */
function calc(r) {
  const P = D.profiles.find(p => p.id === r.profileId) || D.profiles[0] || DEFAULT_P;
  const V = Math.max(N(r.batch), 0.1), effPct = r.eff != null ? N(r.eff) : P.eff, eff = effPct / 100;
  let potGrain = 0, potOther = 0, kgMash = 0, kgTot = 0, mcu = 0;
  r.ferm.forEach(row => {
    const g = lib('ferm', row.ref); if (!g) return;
    const kg = N(row.kg), pot = kg * 384.6 * g.extract / 100;
    if (g.type === 'grain') { potGrain += pot; kgMash += kg; } else potOther += pot;
    kgTot += kg; mcu += (g.ebc / 1.97) * kg * 2.2046;
  });
  const fp = r.ferm.map(row => kgTot ? N(row.kg) / kgTot * 100 : 0);
  const potEff = potGrain * eff + potOther;
  const ogPts = potEff / V, og = 1 + ogPts / 1000;
  const yeasts = r.yeast.map(y => lib('yeast', y.ref)).filter(Boolean);
  const att = yeasts.length ? yeasts.reduce((s, y) => s + y.att, 0) / yeasts.length : 75;
  const fgPts = ogPts * (1 - att / 100), fg = 1 + fgPts / 1000;
  const abv = (og - fg) * 131.25;
  const srm = mcu > 0 ? 1.4922 * Math.pow(mcu / (V / 3.785), 0.6859) : 0, ebc = srm * 1.97;

  const mashMethod = (r.mashMethod != null ? r.mashMethod : P.mashMethod) === 'biab' ? 'biab' : 'classic';
  const biabSqueeze = !!(r.biabSqueeze != null ? r.biabSqueeze : P.biabSqueeze);
  // BIAB (sac filtrant pressé) : on récupère davantage de moût du grain, pertes d'absorption réduites de moitié (approximation usuelle).
  const effAbsorb = P.absorb * (mashMethod === 'biab' && biabSqueeze ? 0.5 : 1);
  const preboil = (V + P.trub) / (1 - P.shrink / 100) + P.evap * P.boil / 60;
  const pbSG = 1 + potEff / preboil / 1000;
  const totalWater = preboil + effAbsorb * kgMash + P.mashLoss;
  const ratio = Math.max(N(r.ratio != null ? r.ratio : P.ratio), 0.5);
  // Classique : eau d'empâtage plafonnée au rapport eau/grain, le reste en rinçage. BIAB : volume unique dès l'empâtage.
  const mashWater = mashMethod === 'biab' ? totalWater : Math.min(totalWater, ratio * kgMash);
  const sparge = mashMethod === 'biab' ? 0 : Math.max(0, totalWater - mashWater);
  const mashTemp = r.mash[0] ? N(r.mash[0].temp) : 67;
  const strikeRatio = mashMethod === 'biab' && kgMash ? mashWater / kgMash : ratio;
  const strike = kgMash ? (0.41 / strikeRatio) * (mashTemp - N(r.grainTemp)) + mashTemp : 0;

  const gu = (potEff / ((V + preboil) / 2)) / 1000, big = 1.65 * Math.pow(0.000125, gu);
  const hi = r.hops.map(row => {
    const h = lib('hop', row.ref); if (!h || row.use === 'dry') return 0;
    let t = N(row.time), k = 1, tempFactor = 1;
    if (row.use === 'fwh') { t = P.boil; k = 1.1; }
    else if (row.use === 'whirl') { k = 0.5; }
    else if (row.use === 'hopstand') {
      // Hop stand : température en dessous de l'ébullition → isomérisation plus lente. Approximation usuelle
      // (énergie d'activation ≈ 11858, cf. Malowicki/Huang et al.) : ratio du taux d'isomérisation à la
      // température du stand par rapport à 100 °C, appliqué au facteur temporel habituel de Tinseth.
      const standTemp = row.temp != null ? N(row.temp) : 80;
      tempFactor = Math.min(1, Math.exp(-11858 * (1 / (standTemp + 273.15) - 1 / 373.15)));
    }
    const util = big * (1 - Math.exp(-0.04 * t)) / 4.15 * tempFactor;
    return util * (h.alpha / 100 * N(row.g) * 1000 / V) * (h.form === 'Pellet' ? 1.1 : 1) * k;
  });
  const ibu = hi.reduce((a, b) => a + b, 0);

  const tmax = r.ferment.length ? Math.max(...r.ferment.map(s => N(s.temp))) : 20, tF = tmax * 9 / 5 + 32;
  const residual = Math.max(0, 3.0378 - 0.050062 * tF + 0.00026555 * tF * tF);
  const sugarL = Math.max(0, N(r.co2) - residual) * 4, sugarTot = sugarL * V;

  // Coût estimé : prix renseigné dans la bibliothèque × quantité utilisée (même unité que la ligne de recette).
  const costFerm = r.ferm.reduce((s, row) => { const g = lib('ferm', row.ref); return s + (g ? N(row.kg) * (g.price || 0) : 0); }, 0);
  const costHop = r.hops.reduce((s, row) => { const h = lib('hop', row.ref); return s + (h ? N(row.g) * (h.price || 0) : 0); }, 0);
  const costYeast = r.yeast.reduce((s, row) => { const y = lib('yeast', row.ref); return s + (y ? N(row.qty) * (y.price || 0) : 0); }, 0);
  const costMisc = r.misc.reduce((s, row) => { const m = lib('misc', row.ref); return s + (m ? N(row.qty) * (m.price || 0) : 0); }, 0);
  const costTotal = costFerm + costHop + costYeast + costMisc;
  const costPerL = V > 0 ? costTotal / V : 0;

  // Volume de maische à chaque palier : le 1er palier reçoit l'eau d'empâtage (mashWater) ; les paliers suivants
  // sont traités comme des infusions (ajout d'eau chaude pour monter en température) selon la formule usuelle
  // Winfusion = (Tcible − Tprécédent) × (0,41 × kg de grain + eau déjà présente) / (Teau infusion − Tcible).
  const INFUSION_TEMP = r.infusionTemp != null ? N(r.infusionTemp) : P.infusionTemp;   // °C — eau d'infusion ajoutée entre paliers ; réglable au profil et par recette
  const DECOCTION_TEMP = 100;  // °C — la portion décoctée est portée à ébullition (fixe)
  const GRAIN_DISP = 0.7;      // L déplacés par kg de grain (empattage) — approximation usuelle
  let cumWater = mashWater;
  const mashSteps = r.mash.map((s, i) => {
    const temp = N(s.temp), method = i === 0 ? 'infusion' : (s.method || 'infusion');
    let infusion = 0, decoctionVol = 0;
    if (i === 0) { cumWater = mashWater; }
    else {
      const prevTemp = N(r.mash[i - 1].temp);
      if (method === 'infusion') {
        const denom = INFUSION_TEMP - temp;
        infusion = denom > 0 ? Math.max(0, (temp - prevTemp) * (0.41 * kgMash + cumWater) / denom) : 0;
        cumWater += infusion;   // eau chaude ajoutée : le volume total augmente
      } else if (method === 'decoction') {
        const prevVolume = cumWater + kgMash * GRAIN_DISP, denom = DECOCTION_TEMP - prevTemp;
        decoctionVol = denom > 0 ? Math.max(0, (temp - prevTemp) * prevVolume / denom) : 0;
        // la portion prélevée est bouillie puis remise : le volume total ne change pas
      }
      // method === 'direct' : la cuve est chauffée directement, aucun volume ajouté ou prélevé
    }
    return {name: s.name, temp, method, infusion: rnd(infusion, 1), decoctionVol: rnd(decoctionVol, 1), water: rnd(cumWater, 1), volume: rnd(cumWater + kgMash * GRAIN_DISP, 1)};
  });

  return {P, V, og, fg, abv, ibu, ebc, srm, att, hasYeast: yeasts.length > 0, bugu: ogPts > 0 ? ibu / ogPts : 0,
    preboil, pbSG, totalWater, mashWater, sparge, strike, mashTemp, kgMash, kgTot, fp, hi, potGrain, potOther,
    evapL: P.evap * P.boil / 60, sugarL, sugarTot, residual, effPct, ratio, costFerm, costHop, costYeast, costMisc, costTotal, costPerL, mashSteps, infusionTemp: INFUSION_TEMP,
    mashMethod, biabSqueeze};
}

/* ---------- 5. VUES ---------- */
const countLabel = (n, one, many) => (n === 1 ? '1 ' + one : f(n, 0) + ' ' + many);
const pageFoot = () => {
  const brewers = AUTH.mode === 'phpbb' ? AUTH.brewer_count : null;
  const visitors = AUTH.mode === 'phpbb' ? AUTH.visitor_count : null;
  const usage = (typeof brewers === 'number' && typeof visitors === 'number')
    ? `<p class="foot-usage noprint">${countLabel(brewers, 'brasseur', 'brasseurs')} sur HopHop avec au moins une recette, et ${countLabel(visitors, 'visiteur', 'visiteurs')}</p>`
    : '';
  const copyStyle = usage ? 'text-align:center;margin-top:.2rem' : 'text-align:center';
  return `${usage}<p class="sub noprint" style="${copyStyle}"><a href="help.html" style="color:inherit">Aide</a> — HopHop © claude.ai &amp; Penn-Maen + Jean-Luc (intégration phpBB+BrewRecipes) — <a href="https://creativecommons.org/licenses/by-nc-sa/4.0/deed.fr" target="_blank" rel="noopener" style="color:inherit">CC BY-NC-SA 4.0</a></p>`;
};
const fld = (label, html, cls = '') => `<label class="fld ${cls}"><span>${label}</span>${html}</label>`;
const bi = (path, val, n) => `<input data-b="${path}" ${n ? 'data-n inputmode="decimal"' : ''} value="${esc(val)}">`;
const bs = (path, list, val) => `<select data-b="${path}">${opts(list, val)}</select>`;
const libOpts = (t, sel) => {
  // La liste proposée pour un NOUVEAU choix n'affiche que les ingrédients actifs ; un ingrédient déjà utilisé
  // dans cette recette reste sélectionné même s'il a depuis été retiré (archivé) de la bibliothèque.
  const active = [...D.ing[t]].filter(x => !x.archived).sort((a, b) => a.name.localeCompare(b.name, 'fr'));
  let h = opts(active.map(x => [x.id, x.name]), sel);
  if (sel) {
    const cur = lib(t, sel);
    if (cur && cur.archived) h = `<option value="${esc(sel)}" selected>${esc(cur.name)} (archivé)</option>` + h;
    else if (!cur) h = `<option value="${esc(sel)}" selected>(ingrédient supprimé)</option>` + h;
  }
  return h;
};

function authHTML() {
  if (AUTH.mode === 'phpbb' && AUTH.blocked === 'robot') {
    return `<div class="authscreen"><div class="auth-bg" aria-hidden="true">${LOGO_SVG}</div><div class="wrap"><div class="auth card">
    <h1 style="text-align:center;display:flex;align-items:center;justify-content:center;gap:.5rem"><span class="brand-logo" style="width:2.2rem;height:2.2rem">${LOGO_SVG}</span>HopHop</h1>
    <p class="err" role="alert">Les robots du forum ne peuvent pas utiliser HopHop.</p>
  </div></div></div>`;
  }
  if (AUTH.mode === 'phpbb') {
    const loginBtn = AUTH.login_url
      ? `<a class="btn" href="${esc(AUTH.login_url)}" style="width:100%;text-align:center;text-decoration:none;display:inline-block;box-sizing:border-box">Se connecter au forum</a>`
      : '<p class="err">Connexion forum indisponible.</p>';
    const dbWarn = AUTH.mysql_ready === false
      ? '<p class="err" role="alert">Stockage MySQL : créez la table <code>hophop_store</code> (voir docs/sql/hophop_store.sql).</p>' : '';
    return `<div class="authscreen"><div class="auth-bg" aria-hidden="true">${LOGO_SVG}</div><div class="wrap"><div class="auth card">
    <h1 style="text-align:center;display:flex;align-items:center;justify-content:center;gap:.5rem"><span class="brand-logo" style="width:2.2rem;height:2.2rem">${LOGO_SVG}</span>HopHop</h1>
    <p class="sub" style="text-align:center">Atelier recettes BrassageAmateur — utilisez votre compte forum.</p>
    ${dbWarn}${loginBtn}
    <p class="sub" style="margin-bottom:0">Une fois connecté au forum, rouvrez cette page ou actualisez.</p>
  </div></div></div>`;
  }
  const reg = ui.auth === 'register' && AUTH.features.register;
  return `<div class="authscreen"><div class="auth-bg" aria-hidden="true">${LOGO_SVG}</div><div class="wrap"><div class="auth card">
    <h1 style="text-align:center;display:flex;align-items:center;justify-content:center;gap:.5rem"><span class="brand-logo" style="width:2.2rem;height:2.2rem">${LOGO_SVG}</span>HopHop</h1>
    <p class="sub" style="text-align:center">Créez et calculez vos recettes de bière.</p>
    ${AUTH.features.register ? `<div class="seg"><button data-act="authMode" data-m="login" aria-pressed="${!reg}">Connexion</button><button data-act="authMode" data-m="register" aria-pressed="${reg}">Créer un compte</button></div>` : ''}
    <div class="fgrid" style="grid-template-columns:1fr">
      ${fld('Identifiant', '<input id="a-user" autocomplete="username" autocapitalize="off">')}
      ${fld('Mot de passe', '<input id="a-pw" type="password" autocomplete="' + (reg ? 'new-password' : 'current-password') + '">')}
      ${reg ? fld('Confirmer le mot de passe', '<input id="a-pw2" type="password" autocomplete="new-password">') : ''}
    </div>
    <div class="err" id="a-err" role="alert"></div>
    <button class="btn" id="a-go" style="width:100%" data-act="authGo">${reg ? 'Créer mon compte' : 'Se connecter'}</button>
    <p class="sub" style="margin-bottom:0">Compte et recettes enregistrés sur ce serveur.</p>
  </div></div></div>`;
}

function shellHTML() {
  const cur = ['editor', 'sheet'].includes(ui.view) ? 'recipes' : ui.view;
  const T = [['recipes', 'Recettes'], ['brews', 'Brassins'], ['ings', 'Ingrédients'], ['profiles', 'Matériel'], ['account', 'Compte']];
  const forumBack = AUTH.mode === 'phpbb'
    ? '<a class="btn ghost sm top-forum" href="/forum/">Retour sur le forum</a>'
    : '<button class="btn ghost sm top-forum" data-act="logout">Déconnexion</button>';
  return `<header class="top"><div class="in"><div class="brand"><span class="brand-logo">${LOGO_SVG}</span>HopHop</div>
    <div class="top-user"><span class="savestate" id="savestate"></span><span class="who">${avatarHTML()}<span class="uname">${esc(USER)}</span></span></div>${forumBack}</div>
    <nav class="tabs" aria-label="Navigation principale">${T.map(([v, l]) => `<button data-act="go" data-v="${v}" ${cur === v ? 'aria-current="page"' : ''}>${l}</button>`).join('')}</nav></header>
    <main class="wrap" id="main"></main>`;
}

function recipeCard(r) {
  const c = calc(r);
  return `<button class="card rcard" data-act="open" data-id="${r.id}">
    <div style="display:flex;justify-content:space-between;gap:.5rem;align-items:center"><h2 style="margin:0">${esc(r.name)}</h2><span class="sw" style="background:${ebcColor(c.ebc)}"></span></div>
    <div class="sub">${esc(recipeStyleName(r)) || 'Style non précisé'} · ${f(N(r.batch), 1)} L</div>
    <div class="meta"><span class="chip">DI ${gText(c.og)}</span><span class="chip">${f(c.abv, 1)} % vol.</span><span class="chip">${f(c.ibu, 0)} IBU</span><span class="chip">${f(c.ebc, 0)} EBC</span></div>
  </button>`;
}
function recipeListHTML() {
  const q = ui.q.toLowerCase();
  const l = D.recipes.filter(r => (r.name + ' ' + recipeStyleName(r)).toLowerCase().includes(q)).sort((a, b) => b.updated - a.updated);
  return l.length ? `<div class="cards">${l.map(recipeCard).join('')}</div>`
    : `<div class="empty">${D.recipes.length ? 'Aucune recette ne correspond à la recherche.' : 'Aucune recette pour l’instant.<br>Créez votre première recette pour commencer.'}</div>`;
}
function viewRecipes() {
  return `<div class="bar"><h1>Recettes</h1><div class="grow"></div>
    <input class="search" placeholder="Rechercher…" data-q value="${esc(ui.q)}" aria-label="Rechercher une recette">
    <button class="btn ghost" data-act="importXML">Importer BeerXML</button>
    <input type="file" id="importBeerXMLFile" accept=".xml,text/xml,application/xml" style="display:none">
    <button class="btn ghost" data-act="importRecipeJSON">Importer BeerJSON</button>
    <input type="file" id="importBeerJSONFile" accept=".json,application/json" style="display:none">
    <button class="btn" data-act="newRecipe">+ Nouvelle recette</button></div><div id="rlist">${recipeListHTML()}</div>${pageFoot()}`;
}

/* --- Éditeur de recette --- */
function rowFerm(x, i) {
  return `<div class="row"><div class="rowhead"><select data-b="ferm.${i}.ref" aria-label="Fermentescible">${libOpts('ferm', x.ref)}</select><button class="ico" data-act="rm" data-t="ferm" data-i="${i}" aria-label="Retirer">✕</button></div>
    ${fld('Quantité (kg)', bi(`ferm.${i}.kg`, x.kg, 1))}<div class="fld"><span>Part</span><output data-cell="fp:${i}">–</output></div><div class="hint" data-stk="ferm:${i}"></div></div>`;
}
function rowHop(x, i) {
  const dry = x.use === 'dry', hopstand = x.use === 'hopstand';
  const timeLabel = dry ? 'Durée (jours)' : hopstand ? 'Durée (min)' : 'Temps (min)';
  const h0 = lib('hop', x.ref), hformTxt = h0 ? f(h0.alpha, 1) + ' % · ' + h0.form : '–';
  return `<div class="row"><div class="rowhead"><select data-b="hops.${i}.ref" aria-label="Houblon">${libOpts('hop', x.ref)}</select><button class="ico" data-act="rm" data-t="hops" data-i="${i}" aria-label="Retirer">✕</button></div>
    ${fld('Quantité (g)', bi(`hops.${i}.g`, x.g, 1))}${fld('Usage', `<select data-b="hops.${i}.use" data-rr>${opts(Object.entries(USE_H), x.use)}</select>`)}
    ${x.use === 'fwh' ? '' : fld(timeLabel, bi(`hops.${i}.time`, x.time, 1))}
    ${hopstand ? fld('Température (°C)', bi(`hops.${i}.temp`, x.temp != null ? x.temp : 80, 1)) : ''}
    ${dry ? fld('Température (°C) — optionnel', bi(`hops.${i}.temp`, x.temp != null ? x.temp : '', 1)) : ''}
    <div class="fld"><span>αA / Forme</span><output data-cell="hform:${i}">${esc(hformTxt)}</output></div>
    <div class="fld"><span>Amertume</span><output data-cell="hi:${i}">–</output></div><div class="hint" data-stk="hops:${i}"></div></div>`;
}
function rowYeast(x, i) {
  return `<div class="row"><div class="rowhead"><select data-b="yeast.${i}.ref" aria-label="Levure">${libOpts('yeast', x.ref)}</select><button class="ico" data-act="rm" data-t="yeast" data-i="${i}" aria-label="Retirer">✕</button></div>
    ${fld('Sachets / unités', bi(`yeast.${i}.qty`, x.qty, 1))}<div class="hint" data-stk="yeast:${i}"></div></div>`;
}
function rowMisc(x, i) {
  const m = lib('misc', x.ref);
  return `<div class="row"><div class="rowhead"><select data-b="misc.${i}.ref" aria-label="Ingrédient divers">${libOpts('misc', x.ref)}</select><button class="ico" data-act="rm" data-t="misc" data-i="${i}" aria-label="Retirer">✕</button></div>
    ${fld('Quantité' + (m ? ' (' + esc(m.unit) + ')' : ''), bi(`misc.${i}.qty`, x.qty, 1))}${fld('Moment', bs(`misc.${i}.use`, Object.entries(USE_M), x.use))}
    ${fld('Temps (min)', bi(`misc.${i}.time`, x.time, 1))}<div class="hint" data-stk="misc:${i}"></div></div>`;
}
const rowStep = (x, i, key, lab, unit) => `<div class="row"><div class="rowhead"><input data-b="${key}.${i}.name" value="${esc(x.name)}" aria-label="Nom de l’étape"><button class="ico" data-act="rm" data-t="${key}" data-i="${i}" aria-label="Retirer">✕</button></div>
    ${fld('Température (°C)', bi(`${key}.${i}.temp`, x.temp, 1))}${fld(lab, bi(`${key}.${i}.${unit}`, x[unit], 1))}</div>`;
const MASH_METHOD = [['infusion', 'Infusion (eau chaude)'], ['direct', 'Chauffage direct'], ['decoction', 'Décoction']];
function rowMash(x, i) {
  return `<div class="row"><div class="rowhead"><input data-b="mash.${i}.name" value="${esc(x.name)}" aria-label="Nom du palier"><button class="ico" data-act="rm" data-t="mash" data-i="${i}" aria-label="Retirer">✕</button></div>
    ${fld('Température (°C)', bi(`mash.${i}.temp`, x.temp, 1))}${fld('Durée (min)', bi(`mash.${i}.time`, x.time, 1))}
    ${i > 0 ? fld('Méthode', `<select data-b="mash.${i}.method">${opts(MASH_METHOD, x.method || 'infusion')}</select>`) : '<div class="fld"><span>Méthode</span><output>Empâtage (eau d’infusion)</output></div>'}</div>`;
}

function sectionHTML(title, key, rows, addLabel) {
  return `<section class="card"><div class="secthead"><h2>${title}</h2><div class="grow"></div><button class="btn ghost sm" data-act="add" data-t="${key}">+ ${addLabel}</button></div>
    ${rows.length ? rows.join('') : '<div class="sub">Aucun élément.</div>'}</section>`;
}

function viewEditor() {
  const r = R(); if (!r) { ui.view = 'recipes'; return viewRecipes(); }
  return `<div class="bar"><button class="btn ghost sm" data-act="go" data-v="recipes">← Recettes</button><div class="grow"></div>
    <button class="btn ghost sm" data-act="sheet">Fiche recette</button><button class="btn sm" data-act="brewFrom">Enregistrer un brassage</button></div>
  <div class="editor">
    <section class="card recipe-identity"><input class="namefield" data-b="name" value="${esc(r.name)}" aria-label="Nom de la recette" style="margin-bottom:.8rem">
      <div class="fgrid">
        ${fld('Style', `<div class="style-line"><select data-b="styleId">${styleSelectOpts(r.styleId)}</select>${brewrecipesStyleGuidesUrl() ? '<button type="button" class="btn ghost sm" data-act="pickBrewRecipesStyle">Depuis BrewRecipes…</button>' : ''}</div>`, 'span2')}
        ${fld('Profil matériel', `<select data-b="profileId">${libProfiles(r.profileId)}</select>`)}
        ${fld('Volume final (L)', bi('batch', r.batch, 1))}
        ${fld('Rapport eau/grain (L/kg)', bi('ratio', r.ratio, 1))}
        ${fld('Rendement (%)', bi('eff', r.eff, 1))}
        ${fld('Température eau d’infusion (°C)', bi('infusionTemp', r.infusionTemp, 1))}
        ${fld('Température des grains (°C)', bi('grainTemp', r.grainTemp, 1))}
        ${fld('CO₂ visé (vol.)', bi('co2', r.co2, 1))}
        ${fld('Méthode de brassage', `<select data-b="mashMethod">${opts(BREW_METHOD, r.mashMethod)}</select>`)}
        ${r.mashMethod === 'biab' ? `<label class="fld" style="flex-direction:row;align-items:center;gap:.5rem;padding-top:1.5rem">
          <input type="checkbox" data-b="biabSqueeze" style="width:auto" ${r.biabSqueeze ? 'checked' : ''}>
          <span style="text-transform:none;font-size:.95rem;color:var(--text)">Pressurage du sac (pertes réduites)</span></label>` : ''}
      </div></section>
    <div class="editor-rest">
    ${sectionHTML('Fermentescibles', 'ferm', r.ferm.map(rowFerm), 'Ajouter')}
    ${sectionHTML('Houblons', 'hops', r.hops.map(rowHop), 'Ajouter')}
    ${sectionHTML('Levure', 'yeast', r.yeast.map(rowYeast), 'Ajouter')}
    ${sectionHTML('Divers', 'misc', r.misc.map(rowMisc), 'Ajouter')}
    ${sectionHTML('Empâtage', 'mash', r.mash.map((x, i) => rowMash(x, i)), 'Palier')}
    ${sectionHTML('Fermentation', 'ferment', r.ferment.map((x, i) => rowStep(x, i, 'ferment', 'Durée (jours)', 'days')), 'Étape')}
    <section class="card"><h2>Notes</h2><textarea data-b="notes" aria-label="Notes">${esc(r.notes)}</textarea></section>
    <section class="card"><h2>Redimensionner</h2>
      <p class="sub" style="margin-top:0">Toutes les quantités (grains, houblons, divers) sont multipliées pour conserver densité, amertume et couleur. Vérifiez la quantité de levure.</p>
      <div class="fgrid" style="align-items:end">${fld('Nouveau volume final (L)', `<input id="newvol" inputmode="decimal" value="${esc(r.batch)}">`)}<div class="sub" id="kfac"></div></div>
      <div class="dlg-actions" style="justify-content:flex-start"><button class="btn ghost sm" data-act="resizeApply">Appliquer à cette recette</button><button class="btn ghost sm" data-act="resizeCopy">Créer une copie à ce volume</button></div></section>
    <section class="card"><h2>Exporter et gérer</h2><div class="dlg-actions" style="justify-content:flex-start;flex-wrap:wrap;margin:0">
      ${brewrecipesShareUrl() ? '<button class="btn sm" data-act="shareBrewRecipes">Partager sur BrewRecipes</button>' : ''}
      <button class="btn ghost sm" data-act="exp" data-k="xml">Exporter BeerXML</button><button class="btn ghost sm" data-act="exp" data-k="json">Exporter BeerJSON</button>
      <button class="btn ghost sm" data-act="dupRecipe">Dupliquer</button><button class="btn danger sm" data-act="delRecipe">Supprimer</button></div></section>
    </div>
  <aside class="stats" id="stats"></aside></div>
  ${pageFoot()}`;
}
const libProfiles = sel => D.profiles.length ? opts([...D.profiles].sort((a, b) => a.name.localeCompare(b.name, 'fr')).map(p => [p.id, p.name]), sel) : '<option value="">(aucun profil — valeurs par défaut)</option>';

const inRange = (v, mn, mx) => (mn == null || mx == null) ? true : v >= mn - 1e-9 && v <= mx + 1e-9;
function statsHTML(c, r) {
  const col = ebcColor(c.ebc);
  const st = r ? recipeStyleRef(r) : null;
  return `<div class="card"><div class="beer"><div class="glass" aria-hidden="true"><div class="foam"></div><div class="liq" style="background:${col}"></div></div>
    <div><h2 style="margin:0">Résultats</h2><div class="sub">Volume final ${f(c.V, 1)} L</div></div></div>
    <div class="kpis"><div class="kpi"><b>${gKpi(c.og).primary}</b><span>DI</span><div class="kpi-sub">${gKpi(c.og).secondary}</div></div><div class="kpi"><b>${gKpi(c.fg).primary}</b><span>DF</span><div class="kpi-sub">${gKpi(c.fg).secondary}</div></div><div class="kpi"><b>${f(c.abv, 1)} %</b><span>Alcool</span></div>
      <div class="kpi"><b>${f(c.ibu, 0)}</b><span>IBU</span></div><div class="kpi"><b>${f(c.ebc, 0)}</b><span>EBC</span></div><div class="kpi"><b>${f(c.bugu, 2)}</b><span>IBU/DI</span></div></div>
    <div class="sub" style="margin:-.2rem 0 .5rem">Au réfractomètre non corrigé, en fin de fermentation : ≈ ${f(rawBrixAtFG(c.og, c.fg), 1)} °Bx (pour une DF corrigée de ${gUnit(c.fg)}).</div>
    ${st ? `<h3>Style : ${esc(st.name)}${st.category ? ' (' + esc(st.category) + ')' : ''}${st.guide ? ` — ${esc(st.guide)}` : ''}</h3>
      <div class="meta" style="margin-bottom:.6rem">
        <span class="chip${inRange(c.og, st.ogMin, st.ogMax) ? '' : ' bad'}">DI ${f(st.ogMin, 3)}–${f(st.ogMax, 3)}</span>
        <span class="chip${inRange(c.fg, st.fgMin, st.fgMax) ? '' : ' bad'}">DF ${f(st.fgMin, 3)}–${f(st.fgMax, 3)}</span>
        <span class="chip${inRange(c.ibu, st.ibuMin, st.ibuMax) ? '' : ' bad'}">IBU ${f(st.ibuMin, 0)}–${f(st.ibuMax, 0)}</span>
        <span class="chip${inRange(c.ebc, st.colorMin, st.colorMax) ? '' : ' bad'}">EBC ${f(st.colorMin, 0)}–${f(st.colorMax, 0)}</span>
        <span class="chip${inRange(c.abv, st.abvMin, st.abvMax) ? '' : ' bad'}">Alcool ${f(st.abvMin, 1)}–${f(st.abvMax, 1)} %</span>
      </div>` : ''}
    <h3>Eau</h3><dl class="kv"><dt>Empâtage</dt><dd>${f(c.mashWater, 1)} L à ${f(c.strike, 0)} °C</dd><dt>Rinçage</dt><dd>${c.mashMethod === 'biab' ? 'Aucun (BIAB)' : f(c.sparge, 1) + ' L'}</dd><dt>Eau totale</dt><dd>${f(c.totalWater, 1)} L</dd>
      <dt>Pré-ébullition</dt><dd>${f(c.preboil, 1)} L · ${gUnit(c.pbSG)}</dd><dt>Évaporation</dt><dd>${f(c.evapL, 1)} L</dd></dl>
    ${c.mashMethod === 'biab' ? `<div class="sub" style="margin-top:-.2rem;margin-bottom:.5rem">Brew in a Bag : tout le volume d’eau est présent dès l’empâtage (sac filtrant dans la cuve d’ébullition), sans rinçage séparé.${c.biabSqueeze ? ' Pressurage du sac pris en compte (pertes d’absorption réduites).' : ''}</div>` : ''}
    ${c.mashSteps.length ? `<h3>Volume de maische par palier</h3><dl class="kv">${c.mashSteps.map((m, i) => `<dt>${esc(m.name)} (${f(m.temp, 0)} °C)</dt><dd>${f(m.volume, 1)} L${i > 0 && m.method === 'infusion' && m.infusion > 0 ? ` (+${f(m.infusion, 1)} L)` : ''}${i > 0 && m.method === 'decoction' && m.decoctionVol > 0 ? ` (préléver ${f(m.decoctionVol, 1)} L)` : ''}</dd>`).join('')}</dl>
      <div class="sub" style="margin-top:.2rem">Volume de maische = eau présente + grain (≈ 0,7 L/kg). « + X L » : infusion d’eau chaude à ${f(c.infusionTemp, 0)} °C. « Prélever X L » : portion à décocter.</div>` : ''}
    <h3>Grains et rendement</h3><dl class="kv"><dt>Total fermentescibles</dt><dd>${f(c.kgTot, 2)} kg</dd><dt>Rapport eau/grain</dt><dd>${f(c.ratio, 1)} L/kg</dd><dt>Rendement</dt><dd>${f(c.effPct, 0)} %</dd><dt>Atténuation</dt><dd>${f(c.att, 0)} %</dd></dl>
    <h3>Conditionnement</h3><dl class="kv"><dt>Saccharose</dt><dd>${f(c.sugarL, 1)} g/L</dd><dt>Total</dt><dd>${f(c.sugarTot, 0)} g</dd></dl>
    <h3>Coût estimé</h3><dl class="kv"><dt>Fermentescibles</dt><dd>${f(c.costFerm, 2)} €</dd><dt>Houblons</dt><dd>${f(c.costHop, 2)} €</dd><dt>Levure</dt><dd>${f(c.costYeast, 2)} €</dd><dt>Divers</dt><dd>${f(c.costMisc, 2)} €</dd>
      <dt>Total</dt><dd>${f(c.costTotal, 2)} €</dd><dt>Par litre</dt><dd>${f(c.costPerL, 2)} €/L</dd></dl>
    ${c.costTotal === 0 && (r.ferm.length || r.hops.length) ? '<div class="sub" style="margin-top:.3rem">Renseignez le prix des ingrédients dans leur bibliothèque pour estimer le coût.</div>' : ''}
    ${c.hasYeast ? '' : '<div class="warn">Aucune levure : atténuation de 75 % supposée.</div>'}
    ${c.kgTot === 0 ? '<div class="warn">Ajoutez des fermentescibles pour obtenir les résultats.</div>' : ''}</div>`;
}

function updateStats() {
  const r = R(); if (!r || !$('#stats')) return;
  const c = calc(r); $('#stats').innerHTML = statsHTML(c, r);
  document.querySelectorAll('[data-cell]').forEach(el => {
    const [k, i] = el.dataset.cell.split(':');
    if (k === 'fp') el.textContent = f(c.fp[i], 1) + ' %';
    else if (k === 'hi') el.textContent = f(c.hi[i], 1) + ' IBU';
    else if (k === 'hform') { const h = lib('hop', r.hops[i] ? r.hops[i].ref : null); el.textContent = h ? f(h.alpha, 1) + ' % · ' + h.form : '–'; }
  });
  document.querySelectorAll('[data-stk]').forEach(el => { const [t, i] = el.dataset.stk.split(':'); el.innerHTML = stockChip(r, t, +i); });
}
function stockChip(r, t, i) {
  const row = r[t][i]; if (!row) return '';
  const lt = RLIB[t], it = lib(lt, row.ref); if (!it) return '';
  const have = D.stock[it.id]; if (have === undefined) return '';
  const need = N(t === 'ferm' ? row.kg : t === 'hops' ? row.g : row.qty), u = unitOf(lt, it);
  return need > have + 1e-9 ? `<span class="chip bad">Stock insuffisant : ${f(have, 2)} ${u}</span>` : `<span class="chip">En stock : ${f(have, 2)} ${u}</span>`;
}

// Ordre d'affichage des houblons sur la fiche recette : étape du process (premier moût → ébullition →
// whirlpool/hop stand → dry hop), puis durée décroissante au sein d'une même étape. Renvoie des index dans
// le tableau d'origine (pour rester aligné avec c.hi, calculé dans l'ordre de saisie).
function hopSheetOrder(hops) {
  const stage = {fwh: 0, boil: 1, whirl: 2, hopstand: 2, dry: 3};
  return hops.map((_, i) => i).sort((ia, ib) => {
    const a = hops[ia], b = hops[ib], sa = stage[a.use] ?? 1, sb = stage[b.use] ?? 1;
    return sa !== sb ? sa - sb : N(b.time) - N(a.time);
  });
}

/* --- Fiche recette (lecture / impression) --- */
function brewSteps(r, c) {
  const P = c.P, S = [], sg = x => gUnit(x);
  const name = (t, id) => esc(lib(t, id)?.name || '?');
  if (c.kgMash > 0) S.push(c.mashMethod === 'biab'
    ? `<b>Empâtage (BIAB).</b> Chauffer la totalité de l’eau (${f(c.mashWater, 1)} L) à ${f(c.strike, 0)} °C directement dans la cuve d’ébullition, placer le sac filtrant, puis ajouter ${f(c.kgMash, 2)} kg de grains (grains à ${f(N(r.grainTemp), 0)} °C).`
    : `<b>Empâtage.</b> Chauffer ${f(c.mashWater, 1)} L d’eau à ${f(c.strike, 0)} °C (grains à ${f(N(r.grainTemp), 0)} °C, rapport ${f(c.ratio, 1)} L/kg) puis ajouter ${f(c.kgMash, 2)} kg de grains.`);
  c.mashSteps.forEach((m, i) => {
    let extra = '';
    if (i > 0 && m.method === 'infusion' && m.infusion > 0) extra = ` Ajouter ≈ ${f(m.infusion, 1)} L d’eau chaude pour cette montée en température.`;
    else if (i > 0 && m.method === 'decoction' && m.decoctionVol > 0) extra = ` Prélever ≈ ${f(m.decoctionVol, 1)} L de maische épaisse, porter à ébullition, puis remettre dans la cuve pour cette montée.`;
    else if (i > 0 && m.method === 'direct') extra = ` Chauffer directement la cuve jusqu’à cette température.`;
    S.push(`Palier « ${esc(m.name)} » : ${f(m.temp, 0)} °C pendant ${f(N(r.mash[i].time), 0)} min (volume de maische ≈ ${f(m.volume, 1)} L).${extra}`);
  });
  const mashMisc = r.misc.filter(m => m.use === 'mash');
  mashMisc.forEach(m => S.push(`À l’empâtage : ${f(N(m.qty), 1)} ${esc(lib('misc', m.ref)?.unit || '')} de ${name('misc', m.ref)}.`));
  if (c.mashMethod === 'biab') S.push(`<b>Fin d’empâtage.</b> Retirer le sac filtrant et le laisser s’égoutter${c.biabSqueeze ? ', en le pressant légèrement pour récupérer un maximum de moût' : ' sans le presser'} : ≈ ${f(c.preboil, 1)} L de moût (densité pré-ébullition ≈ ${sg(c.pbSG)}).`);
  else if (c.sparge > 0) S.push(`<b>Rinçage.</b> Rincer les drêches avec ${f(c.sparge, 1)} L d’eau à 78 °C pour obtenir ${f(c.preboil, 1)} L de moût (densité pré-ébullition ≈ ${sg(c.pbSG)}).`);
  r.hops.filter(h => h.use === 'fwh').forEach(h => S.push(`Premier moût : ${f(N(h.g), 0)} g de ${name('hop', h.ref)} dans la cuve d’ébullition.`));
  S.push(`<b>Ébullition</b> de ${f(P.boil, 0)} min (évaporation ≈ ${f(c.evapL, 1)} L).`);
  const adds = [];
  r.hops.filter(h => h.use === 'boil').forEach(h => adds.push([N(h.time), `${f(N(h.g), 0)} g de ${name('hop', h.ref)}`]));
  r.misc.filter(m => m.use === 'boil').forEach(m => adds.push([N(m.time), `${f(N(m.qty), 1)} ${esc(lib('misc', m.ref)?.unit || '')} de ${name('misc', m.ref)}`]));
  adds.sort((a, b) => b[0] - a[0]).forEach(([t, txt]) => S.push(`À ${f(t, 0)} min de la fin : ${txt}.`));
  r.hops.filter(h => h.use === 'whirl').forEach(h => S.push(`Hors flamme, whirlpool ${f(N(h.time), 0)} min : ${f(N(h.g), 0)} g de ${name('hop', h.ref)}.`));
  r.hops.filter(h => h.use === 'hopstand').forEach(h => S.push(`Hop stand : refroidir à ${f(N(h.temp) || 80, 0)} °C, ajouter ${f(N(h.g), 0)} g de ${name('hop', h.ref)}, laisser reposer ${f(N(h.time), 0)} min.`));
  S.push(`<b>Refroidissement</b> à température de levurage et transfert de ${f(c.V, 1)} L en fermenteur (densité initiale visée ${sg(c.og)}).`);
  r.yeast.forEach(y => S.push(`Ensemencer avec ${f(N(y.qty), 1)} × ${name('yeast', y.ref)}.`));
  r.ferment.forEach(s => S.push(`${esc(s.name)} : ${f(N(s.temp), 0)} °C pendant ${f(N(s.days), 0)} jours.`));
  r.hops.filter(h => h.use === 'dry').forEach(h => S.push(`Dry hop : ${f(N(h.g), 0)} g de ${name('hop', h.ref)} pendant ${f(N(h.time), 0)} jours${h.temp != null && h.temp !== '' ? ` à ≈ ${f(N(h.temp), 0)} °C` : ''}.`));
  r.misc.filter(m => m.use === 'ferm').forEach(m => S.push(`En fermentation : ${f(N(m.qty), 1)} ${esc(lib('misc', m.ref)?.unit || '')} de ${name('misc', m.ref)}.`));
  S.push(`<b>Conditionnement.</b> Densité finale visée ${sg(c.fg)}. Pour ${f(N(r.co2), 1)} volumes de CO₂ : ${f(c.sugarL, 1)} g/L de saccharose, soit ≈ ${f(c.sugarTot, 0)} g pour ${f(c.V, 1)} L.`);
  return S;
}
function viewSheet() {
  const r = R(); if (!r) return viewRecipes();
  const c = calc(r), P = c.P;
  const tr = (cells) => `<tr>${cells.map(x => `<td>${x}</td>`).join('')}</tr>`;
  const th = h => `<thead><tr>${h.map(x => `<th>${x}</th>`).join('')}</tr></thead>`;
  return `<div class="bar"><button class="btn ghost sm" data-act="open" data-id="${r.id}">← Modifier</button><div class="grow"></div>
    <div class="seg noprint" style="margin:0"><button data-act="sheetMode" data-v="1" aria-pressed="${ui.sheetSteps}">Avec étapes</button><button data-act="sheetMode" data-v="0" aria-pressed="${!ui.sheetSteps}">Sans étapes</button></div>
    <label class="sub noprint" style="display:flex;align-items:center;gap:.35rem;margin:0 .2rem"><input type="checkbox" data-act="toggleSheetCost" style="width:auto" ${ui.sheetCost ? 'checked' : ''}> Coût estimé</label>
    <button class="btn sm" data-act="print">Imprimer</button></div>
  <div class="sheet"><div class="card"><div class="sheet-head"><div><h1>${esc(r.name)}</h1><div class="sub">${esc(recipeStyleName(r)) || 'Style non précisé'} · ${f(c.V, 1)} L · matériel : ${esc(P.name)}</div></div><span class="sw" style="width:44px;height:44px;flex:none;background:${ebcColor(c.ebc)}"></span></div>
    <div class="kpis" style="grid-template-columns:repeat(auto-fit,minmax(90px,1fr));margin-top:1rem">
      <div class="kpi"><b>${gKpi(c.og).primary}</b><span>Densité init.</span><div class="kpi-sub">${gKpi(c.og).secondary}</div></div><div class="kpi"><b>${gKpi(c.fg).primary}</b><span>Densité fin.</span><div class="kpi-sub">${gKpi(c.fg).secondary}</div></div><div class="kpi"><b>${f(c.abv, 1)} %</b><span>Alcool</span></div>
      <div class="kpi"><b>${f(c.ibu, 0)}</b><span>IBU</span></div><div class="kpi"><b>${f(c.ebc, 0)}</b><span>EBC</span></div><div class="kpi"><b>${f(c.bugu, 2)}</b><span>IBU/DI</span></div></div>
    <div class="sub" style="margin-top:.4rem">Au réfractomètre non corrigé, en fin de fermentation : ≈ ${f(rawBrixAtFG(c.og, c.fg), 1)} °Bx.</div></div>
  <div class="card"><h2>Ingrédients</h2>
    ${r.ferm.length ? `<h3>Fermentescibles</h3><div class="scroll"><table class="tbl">${th(['Ingrédient', 'Quantité', 'Part', 'EBC'])}${r.ferm.map((x, i) => { const g = lib('ferm', x.ref); return tr([esc(g?.name || '?'), f(N(x.kg), 3) + ' kg', f(c.fp[i], 1) + ' %', g ? f(g.ebc, 0) : '–']); }).join('')}</table></div>` : ''}
    ${r.hops.length ? `<h3>Houblons</h3><div class="scroll"><table class="tbl">${th(['Houblon', 'Quantité', 'Forme', 'Usage', 'Temps', 'αA %', 'IBU'])}${hopSheetOrder(r.hops).map(i => { const x = r.hops[i], h = lib('hop', x.ref); return tr([esc(h?.name || '?'), f(N(x.g), 0) + ' g', h ? esc(h.form) : '–', USE_H[x.use], x.use === 'fwh' ? '–' : f(N(x.time), 0) + (x.use === 'dry' ? ' j' : ' min'), h ? f(h.alpha, 1) : '–', f(c.hi[i], 1)]); }).join('')}</table></div>` : ''}
    ${r.yeast.length ? `<h3>Levure</h3><ul>${r.yeast.map(y => `<li>${f(N(y.qty), 1)} × ${esc(lib('yeast', y.ref)?.name || '?')}</li>`).join('')}</ul>` : ''}
    ${r.misc.length ? `<h3>Divers</h3><ul>${r.misc.map(m => `<li>${f(N(m.qty), 1)} ${esc(lib('misc', m.ref)?.unit || '')} ${esc(lib('misc', m.ref)?.name || '?')} — ${USE_M[m.use]}${m.use === 'boil' || m.use === 'mash' ? ' (' + f(N(m.time), 0) + ' min)' : ''}</li>`).join('')}</ul>` : ''}
    ${!r.ferm.length && !r.hops.length ? '<div class="sub">Aucun ingrédient.</div>' : ''}</div>
  <div class="card"><h2>Eau et rendement</h2>${c.mashMethod === 'biab' ? `<div class="sub" style="margin-top:0">Brew in a Bag (BIAB) : volume unique, sans rinçage${c.biabSqueeze ? ', sac pressé' : ''}.</div>` : ''}<dl class="kv"><dt>Eau d’empâtage</dt><dd>${f(c.mashWater, 1)} L à ${f(c.strike, 0)} °C</dd><dt>Eau de rinçage</dt><dd>${c.mashMethod === 'biab' ? 'Aucune (BIAB)' : f(c.sparge, 1) + ' L'}</dd><dt>Eau totale</dt><dd>${f(c.totalWater, 1)} L</dd>
    <dt>Volume pré-ébullition</dt><dd>${f(c.preboil, 1)} L (${gUnit(c.pbSG)})</dd><dt>Rapport eau/grain</dt><dd>${f(c.ratio, 1)} L/kg</dd><dt>Rendement brasserie</dt><dd>${f(c.effPct, 0)} %</dd><dt>Atténuation apparente</dt><dd>${f(c.att, 0)} %</dd></dl>
    ${!ui.sheetSteps && c.mashSteps.length ? `<h3 style="margin-top:.7rem">Paliers d’empâtage</h3><div class="scroll"><table class="tbl">${th(['Palier', 'Température', 'Durée', 'Volume de maische'])}${c.mashSteps.map((m, i) => tr([esc(m.name), f(m.temp, 0) + ' °C', f(N(r.mash[i].time), 0) + ' min',
      f(m.volume, 1) + ' L' + (i > 0 && m.method === 'infusion' && m.infusion > 0 ? ` (+${f(m.infusion, 1)} L)` : '') + (i > 0 && m.method === 'decoction' && m.decoctionVol > 0 ? ` (prélever ${f(m.decoctionVol, 1)} L)` : '')])).join('')}</table></div>` : ''}</div>
  ${ui.sheetCost && c.costTotal > 0 ? `<div class="card"><h2>Coût estimé</h2><dl class="kv"><dt>Fermentescibles</dt><dd>${f(c.costFerm, 2)} €</dd><dt>Houblons</dt><dd>${f(c.costHop, 2)} €</dd><dt>Levure</dt><dd>${f(c.costYeast, 2)} €</dd><dt>Divers</dt><dd>${f(c.costMisc, 2)} €</dd>
    <dt>Total</dt><dd>${f(c.costTotal, 2)} €</dd><dt>Par litre</dt><dd>${f(c.costPerL, 2)} €/L</dd></dl></div>` : ''}
  ${ui.sheetSteps ? `<div class="card"><h2>Étapes de brassage</h2><ol>${brewSteps(r, c).map(s => `<li>${s}</li>`).join('')}</ol></div>` : ''}
  ${r.notes ? `<div class="card"><h2>Notes</h2><div style="white-space:pre-wrap">${esc(r.notes)}</div></div>` : ''}</div>${pageFoot()}`;
}

/* --- Brassins (historique) --- */
function emptyBrewExtraNotes() {
  return {
    qtyActual: {},
    qtyExtra: [],
    temp: {},
    ph: {mashBefore: '', mashDuring: '', mashEnd: '', spargeWater: '', preBoil: '', postBoil: ''},
    sg: {preSparge: '', preBoil: '', postBoil: ''},
    vol: {},
    free: '',
  };
}
function normalizeBrewExtraNotes(b) {
  if (!b || typeof b !== 'object') return;
  const base = emptyBrewExtraNotes();
  const src = (b.extraNotes && typeof b.extraNotes === 'object') ? b.extraNotes : {};
  const mapVals = (obj) => {
    const out = {};
    if (obj && typeof obj === 'object' && !Array.isArray(obj)) {
      Object.keys(obj).forEach(k => { if (obj[k] != null && obj[k] !== '') out[k] = obj[k]; });
    }
    return out;
  };
  const ph = {...base.ph};
  const phSrc = (src.ph && typeof src.ph === 'object') ? src.ph : {};
  Object.keys(ph).forEach(k => { if (phSrc[k] != null && phSrc[k] !== '') ph[k] = phSrc[k]; });
  const sg = {...base.sg};
  const sgSrc = (src.sg && typeof src.sg === 'object') ? src.sg : {};
  Object.keys(sg).forEach(k => { if (sgSrc[k] != null && sgSrc[k] !== '') sg[k] = sgSrc[k]; });
  let free = src.free != null ? String(src.free) : '';
  // Ancien format (textes libres par catégorie) → basculé dans les notes libres
  if (src.qty && typeof src.qty === 'object' && !Array.isArray(src.qty) && ('ferm' in src.qty || 'hop' in src.qty)) {
    const bits = [['Malts', src.qty.ferm], ['Houblons', src.qty.hop], ['Levure', src.qty.yeast], ['Autre', src.qty.misc]]
      .filter(([, v]) => String(v || '').trim()).map(([l, v]) => l + ' : ' + String(v).trim());
    if (bits.length) free = (free ? free + '\n\n' : '') + bits.join('\n');
  }
  const temp = mapVals(src.temp);
  // Ancienne clé unique « mash » → premier palier si présent
  if (temp.mash != null && temp.mash !== '' && temp['mash.0'] == null) temp['mash.0'] = temp.mash;
  const vol = mapVals(src.vol);
  b.extraNotes = {
    qtyActual: mapVals(src.qtyActual),
    qtyExtra: Array.isArray(src.qtyExtra) ? src.qtyExtra.map(x => ({
      name: x?.name != null ? String(x.name) : '',
      unit: x?.unit != null ? String(x.unit) : '',
      planned: x?.planned != null && x.planned !== '' ? x.planned : '',
      actual: x?.actual != null && x.actual !== '' ? x.actual : '',
    })) : [],
    temp, ph, sg, vol, free,
  };
}
function brewExtraHasVal(v) { return v != null && String(v).trim() !== ''; }
function brewExtraNotesFilled(b) {
  if (!b?.extraNotes) return false;
  const e = b.extraNotes;
  if (Object.values(e.qtyActual || {}).some(brewExtraHasVal)) return true;
  if ((e.qtyExtra || []).some(x => brewExtraHasVal(x.name) || brewExtraHasVal(x.actual) || brewExtraHasVal(x.planned))) return true;
  if ([e.temp, e.ph, e.sg, e.vol].some(g => g && Object.values(g).some(brewExtraHasVal))) return true;
  return brewExtraHasVal(e.free);
}
function brewSnapCalc(b) {
  const r = b?.snap && typeof b.snap === 'object' ? b.snap : null;
  if (!r) return {r: null, c: null};
  try { return {r, c: calc(r)}; } catch { return {r, c: null}; }
}
function brewExtraQtyPlan(b) {
  const {r} = brewSnapCalc(b);
  if (!r) return [];
  const rows = [];
  (r.ferm || []).forEach((x, i) => {
    const g = lib('ferm', x.ref);
    rows.push({k: 'ferm.' + i, label: g?.name || 'Fermentescible', planned: N(x.kg), unit: 'kg', plannedTxt: f(N(x.kg), 3) + ' kg'});
  });
  (r.hops || []).forEach((x, i) => {
    const h = lib('hop', x.ref);
    const use = USE_H[x.use] ? ' · ' + USE_H[x.use] : '';
    rows.push({k: 'hops.' + i, label: (h?.name || 'Houblon') + use, planned: N(x.g), unit: 'g', plannedTxt: f(N(x.g), 1) + ' g'});
  });
  (r.yeast || []).forEach((x, i) => {
    const y = lib('yeast', x.ref);
    rows.push({k: 'yeast.' + i, label: y?.name || 'Levure', planned: N(x.qty), unit: '×', plannedTxt: f(N(x.qty), 1) + ' ×'});
  });
  (r.misc || []).forEach((x, i) => {
    const m = lib('misc', x.ref);
    const unit = m?.unit || '';
    rows.push({k: 'misc.' + i, label: m?.name || 'Divers', planned: N(x.qty), unit, plannedTxt: f(N(x.qty), 2) + (unit ? ' ' + unit : '')});
  });
  return rows;
}
function brewExtraTempPlan(b) {
  const {r, c} = brewSnapCalc(b);
  const rows = [];
  if (c && c.kgMash > 0) rows.push({k: 'strike', label: 'Eau d’empâtage (strike)', planned: rnd(c.strike, 0), plannedTxt: f(c.strike, 0) + ' °C'});
  (r?.mash || []).forEach((s, i) => rows.push({k: 'mash.' + i, label: s.name || ('Palier ' + (i + 1)), planned: N(s.temp), plannedTxt: f(N(s.temp), 0) + ' °C'}));
  if (c && c.sparge > 0) rows.push({k: 'sparge', label: 'Rinçage', planned: 78, plannedTxt: '78 °C'});
  rows.push({k: 'boil', label: 'Ébullition', planned: 100, plannedTxt: '100 °C'});
  rows.push({k: 'postBoil', label: 'Après ébullition', planned: '', plannedTxt: '—'});
  const ft = r?.ferment?.[0] != null && r.ferment[0].temp !== '' && r.ferment[0].temp != null ? N(r.ferment[0].temp) : '';
  rows.push({k: 'chilled', label: 'Après refroidissement', planned: ft, plannedTxt: ft === '' ? '—' : f(ft, 0) + ' °C'});
  return rows;
}
function brewExtraVolPlan(b) {
  const {c} = brewSnapCalc(b);
  if (!c) {
    return [
      {k: 'preSparge', label: 'Avant rinçage', plannedTxt: '—'},
      {k: 'spargeWater', label: 'Eau de rinçage', plannedTxt: '—'},
      {k: 'preBoil', label: 'Avant ébullition', plannedTxt: '—'},
      {k: 'postBoil', label: 'Après ébullition', plannedTxt: '—'},
      {k: 'fermenter', label: 'Dans le fermenteur', plannedTxt: '—'},
      {k: 'packaging', label: 'Final au conditionnement', plannedTxt: '—'},
    ];
  }
  const mashVol = c.mashSteps.length ? c.mashSteps[c.mashSteps.length - 1].volume : c.mashWater;
  const postBoil = (c.V + c.P.trub) / (1 - c.P.shrink / 100);
  return [
    {k: 'preSparge', label: 'Avant rinçage', planned: rnd(mashVol, 1), plannedTxt: f(mashVol, 1) + ' L'},
    {k: 'spargeWater', label: 'Eau de rinçage', planned: rnd(c.sparge, 1), plannedTxt: f(c.sparge, 1) + ' L'},
    {k: 'preBoil', label: 'Avant ébullition', planned: rnd(c.preboil, 1), plannedTxt: f(c.preboil, 1) + ' L'},
    {k: 'postBoil', label: 'Après ébullition', planned: rnd(postBoil, 1), plannedTxt: f(postBoil, 1) + ' L'},
    {k: 'fermenter', label: 'Dans le fermenteur', planned: rnd(c.V, 1), plannedTxt: f(c.V, 1) + ' L'},
    {k: 'packaging', label: 'Final au conditionnement', planned: rnd(c.V, 1), plannedTxt: f(c.V, 1) + ' L'},
  ];
}
function brewExtraSgPlan(b) {
  const {c} = brewSnapCalc(b);
  return [
    {k: 'preSparge', label: 'Avant rinçage', plannedTxt: '—'},
    {k: 'preBoil', label: 'Avant ébullition', plannedTxt: c ? gPrimaryText(c.pbSG) : '—'},
    {k: 'postBoil', label: 'Après ébullition', plannedTxt: c ? gPrimaryText(c.og) : '—'},
  ];
}
const BREW_PH_ROWS = [
  {k: 'mashBefore', label: 'Maische avant brassage', rec: '—'},
  {k: 'mashDuring', label: 'Maische pendant (~15 min)', rec: '5,2–5,6 (idéal 5,2–5,4 pâles · 5,4–5,6 foncées)'},
  {k: 'mashEnd', label: 'Maische fin de brassage', rec: '5,2–5,6'},
  {k: 'spargeWater', label: 'Eau de rinçage', rec: '≤ 6,0 (souvent 5,5–6,0 acidifiée)'},
  {k: 'preBoil', label: 'Avant ébullition', rec: '≈ 5,2–5,6'},
  {k: 'postBoil', label: 'Après ébullition', rec: '≈ 5,0–5,4'},
];
function brewXnNum(attrs, val) {
  return `<input ${attrs} data-t="n" inputmode="decimal" value="${esc(val ?? '')}">`;
}
function brewExtraQtyExtraRow(x = {}) {
  return `<tr data-xn-extra>
    <td><input data-xn-en placeholder="Ingrédient" aria-label="Ingrédient ajouté" value="${esc(x.name || '')}"></td>
    <td class="xn-planned" data-label="Q prévu"><input data-xn-ep data-t="n" inputmode="decimal" aria-label="Q prévu" value="${esc(x.planned ?? '')}"> <input data-xn-eu placeholder="u" aria-label="Unité" value="${esc(x.unit || '')}"></td>
    <td class="xn-actual" data-label="Q réel"><input data-xn-ea data-t="n" inputmode="decimal" aria-label="Q réel" value="${esc(x.actual ?? '')}"></td>
    <td class="xn-actions"><button type="button" class="ico" data-act="xnRmQty" aria-label="Retirer">✕</button></td>
  </tr>`;
}
/** Sous-formulaire dépliable « Autres notes » (si activé dans Compte). */
function brewExtraNotesHTML(b) {
  normalizeBrewExtraNotes(b);
  const e = b.extraNotes, open = brewExtraNotesFilled(b) ? ' open' : '';
  const qtyRows = brewExtraQtyPlan(b);
  const qtyExtras = [...(e.qtyExtra || []), {}];
  const qtyBody = qtyRows.map(row => `<tr>
      <td>${esc(row.label)}</td>
      <td class="xn-planned" data-label="Q prévu">${esc(row.plannedTxt)}</td>
      <td class="xn-actual" data-label="Q réel">${brewXnNum(`data-xn-qty="${esc(row.k)}" aria-label="Q réel ${esc(row.label)}"`, e.qtyActual[row.k])}</td>
      <td class="xn-actions"></td>
    </tr>`).join('') + qtyExtras.map(brewExtraQtyExtraRow).join('');
  const tempBody = brewExtraTempPlan(b).map(row => `<tr>
      <td>${esc(row.label)}</td>
      <td class="xn-planned" data-label="T° prévu">${esc(row.plannedTxt)}</td>
      <td class="xn-actual" data-label="T° réel">${brewXnNum(`data-xn-temp="${esc(row.k)}" aria-label="T° réel ${esc(row.label)}"`, e.temp[row.k])}</td>
    </tr>`).join('');
  const volBody = brewExtraVolPlan(b).map(row => `<tr>
      <td>${esc(row.label)}</td>
      <td class="xn-planned" data-label="Vol. prévu">${esc(row.plannedTxt)}</td>
      <td class="xn-actual" data-label="Vol. réel">${brewXnNum(`data-xn-vol="${esc(row.k)}" aria-label="Vol. réel ${esc(row.label)}"`, e.vol[row.k])}</td>
    </tr>`).join('');
  const sgBody = brewExtraSgPlan(b).map(row => `<tr>
      <td>${esc(row.label)}</td>
      <td class="xn-planned" data-label="Prévu">${esc(row.plannedTxt)}</td>
      <td class="xn-actual" data-label="Réel">${brewXnNum(`data-f="xn.sg.${esc(row.k)}" aria-label="Densité réelle ${esc(row.label)}"`, e.sg[row.k])}</td>
    </tr>`).join('');
  const phBody = BREW_PH_ROWS.map(row => `<tr>
      <td>${esc(row.label)}<div class="brew-xn-rec">${esc(row.rec)}</div></td>
      <td class="xn-actual" data-label="pH réel">${brewXnNum(`data-f="xn.ph.${esc(row.k)}" aria-label="pH ${esc(row.label)}"`, e.ph[row.k])}</td>
    </tr>`).join('');
  return `<details class="brew-extra"${open}>
    <summary>Autres notes</summary>
    <p class="sub" style="margin:.2rem 0 .5rem">Relevés optionnels du jour de brassage. Les densités principales (DI / DF mesurées) restent ci-dessus. Les valeurs « prévu » viennent de la recette enregistrée.</p>
    <h3>Quantités</h3>
    <div class="scroll"><table class="tbl brew-xn-tbl"><thead><tr><th>Ingrédient</th><th class="xn-planned">Q prévu</th><th class="xn-actual">Q réel</th><th class="xn-actions"></th></tr></thead>
      <tbody id="xn-qty-body">${qtyBody}</tbody></table></div>
    <div class="dlg-actions" style="justify-content:flex-start;margin:.2rem 0 .5rem"><button type="button" class="btn ghost sm" data-act="xnAddQty">+ Ingrédient</button></div>
    <h3>Températures (°C)</h3>
    <div class="scroll"><table class="tbl brew-xn-tbl"><thead><tr><th>Étape</th><th class="xn-planned">T° prévu</th><th class="xn-actual">T° réel</th></tr></thead><tbody>${tempBody}</tbody></table></div>
    <h3>pH</h3>
    <p class="sub" style="margin:.1rem 0 .35rem">Mesures à température ambiante (~20 °C). Recommandations indicatives (brassage amateur).</p>
    <div class="scroll"><table class="tbl brew-xn-tbl"><thead><tr><th>Mesure</th><th class="xn-actual">pH réel</th></tr></thead><tbody>${phBody}</tbody></table></div>
    <h3>Densités</h3>
    <div class="scroll"><table class="tbl brew-xn-tbl"><thead><tr><th>Mesure</th><th class="xn-planned">Prévu</th><th class="xn-actual">Réel</th></tr></thead><tbody>${sgBody}</tbody></table></div>
    <h3>Volumes (L)</h3>
    <div class="scroll"><table class="tbl brew-xn-tbl"><thead><tr><th>Mesure</th><th class="xn-planned">Vol. prévu</th><th class="xn-actual">Vol. réel</th></tr></thead><tbody>${volBody}</tbody></table></div>
    <h3>Autres notes de brassage</h3>
    <div class="fgrid"><label class="fld full"><span>Notes libres</span><textarea id="xn-free" data-f="xn.free">${esc(e.free)}</textarea></label></div>
  </details>`;
}
function readBrewExtraFromDlg() {
  const e = emptyBrewExtraNotes();
  document.querySelectorAll('#dlg [data-xn-qty]').forEach(el => {
    e.qtyActual[el.dataset.xnQty] = el.value === '' ? '' : N(el.value);
  });
  document.querySelectorAll('#dlg tr[data-xn-extra]').forEach(tr => {
    const name = tr.querySelector('[data-xn-en]')?.value || '';
    const unit = tr.querySelector('[data-xn-eu]')?.value || '';
    const ep = tr.querySelector('[data-xn-ep]');
    const ea = tr.querySelector('[data-xn-ea]');
    const planned = ep && ep.value !== '' ? N(ep.value) : '';
    const actual = ea && ea.value !== '' ? N(ea.value) : '';
    if (!name.trim() && planned === '' && actual === '' && !unit.trim()) return;
    e.qtyExtra.push({name, unit, planned, actual});
  });
  document.querySelectorAll('#dlg [data-xn-temp]').forEach(el => {
    e.temp[el.dataset.xnTemp] = el.value === '' ? '' : N(el.value);
  });
  document.querySelectorAll('#dlg [data-xn-vol]').forEach(el => {
    e.vol[el.dataset.xnVol] = el.value === '' ? '' : N(el.value);
  });
  document.querySelectorAll('#dlg [data-f^="xn.ph."], #dlg [data-f^="xn.sg."], #dlg [data-f="xn.free"]').forEach(el => {
    const path = el.dataset.f.slice(3).split('.');
    let t = e;
    for (let i = 0; i < path.length - 1; i++) t = t[path[i]];
    const last = path[path.length - 1];
    t[last] = el.dataset.t === 'n' ? (el.value === '' ? '' : N(el.value)) : el.value;
  });
  return e;
}
function brewCalc(b) {
  const og = b.ogM, fg = b.fgM, has = og > 1 && fg > 0.99;
  const abv = has ? (og - fg) * 131.25 : null, att = has && og > 1 ? (og - fg) / (og - 1) * 100 : null;
  let eff = null;
  if (og > 1 && b.vol > 0 && b.stats.potGrain > 0) eff = (((og - 1) * 1000 * b.vol) - b.stats.potOther) / b.stats.potGrain * 100;
  return {abv, att, eff};
}
function viewBrews() {
  const l = [...D.brews].sort((a, b) => (b.date || '').localeCompare(a.date || ''));
  return `<div class="bar"><h1>Historique de brassage</h1><div class="grow"></div><button class="btn" data-act="newBrew">+ Nouveau brassage</button></div>
  ${l.length ? `<div class="cards">${l.map(b => { const k = brewCalc(b); return `<div class="card" style="margin:0"><div style="display:flex;justify-content:space-between;gap:.5rem"><h2 style="margin:0">${esc(b.recipeName)}</h2><span class="chip">${STATUS[b.status]}</span></div>
    <div class="sub">${fdate(b.date)} · ${f(b.vol, 1)} L</div>
    <dl class="kv" style="margin-top:.6rem"><dt>Densité initiale</dt><dd>${b.ogM > 1 ? gUnit(b.ogM) : '–'} <span class="sub">(visée ${gPrimaryText(b.stats.og)})</span></dd><dt>Densité finale</dt><dd>${b.fgM > 0.99 ? gUnit(b.fgM) : '–'} <span class="sub">(visée ${gPrimaryText(b.stats.fg)})</span></dd>
      <dt>Réfractomètre non corrigé</dt><dd>≈ ${f(rawBrixAtFG(b.ogM > 1 ? b.ogM : b.stats.og, b.fgM > 0.99 ? b.fgM : b.stats.fg), 1)} °Bx</dd>
      <dt>Alcool réel</dt><dd>${k.abv === null ? '–' : f(k.abv, 1) + ' %'}</dd><dt>Atténuation</dt><dd>${k.att === null ? '–' : f(k.att, 0) + ' %'}</dd><dt>Rendement réel</dt><dd>${k.eff === null ? '–' : f(k.eff, 0) + ' %'}</dd></dl>
    ${b.notes ? `<p class="sub" style="white-space:pre-wrap">${esc(b.notes)}</p>` : ''}
    ${D.prefs.brewExtraNotes && brewExtraNotesFilled(b) ? '<p class="brew-extra-chip">Autres notes renseignées</p>' : ''}
    <div class="dlg-actions" style="justify-content:flex-start"><button class="btn ghost sm" data-act="editBrew" data-id="${b.id}">Modifier</button><button class="btn danger sm" data-act="delBrew" data-id="${b.id}">Supprimer</button></div></div>`; }).join('')}</div>`
    : '<div class="empty">Aucun brassage enregistré.<br>Depuis une recette, utilisez « Enregistrer un brassage ».</div>'}${pageFoot()}`;
}

/* --- Bibliothèque d'ingrédients --- */
const ING_FIELDS = {
  ferm: [{k: 'name', l: 'Nom'}, {k: 'type', l: 'Type', t: 'select', o: Object.entries(FTYPE)}, {k: 'ebc', l: 'Couleur (EBC)', t: 'number'}, {k: 'extract', l: 'Extrait sec (%)', t: 'number'}, {k: 'price', l: 'Prix (€/kg)', t: 'number'}, {k: 'note', l: 'Note'}],
  hop: [{k: 'name', l: 'Nom'}, {k: 'alpha', l: 'Acides alpha (%)', t: 'number'}, {k: 'form', l: 'Forme', t: 'select', o: [['Pellet', 'Pellet'], ['Cône', 'Cône entier']]}, {k: 'price', l: 'Prix (€/g)', t: 'number'}, {k: 'note', l: 'Note'}],
  yeast: [{k: 'name', l: 'Nom'}, {k: 'type', l: 'Type', t: 'select', o: [['ale', 'Ale'], ['lager', 'Lager'], ['autre', 'Autre']]}, {k: 'form', l: 'Forme', t: 'select', o: [['dry', 'Sèche'], ['liquid', 'Liquide']]}, {k: 'att', l: 'Atténuation (%)', t: 'number'}, {k: 'tmin', l: 'T° min (°C)', t: 'number'}, {k: 'tmax', l: 'T° max (°C)', t: 'number'}, {k: 'price', l: 'Prix (€/sachet ou unité)', t: 'number'}, {k: 'note', l: 'Note'}],
  misc: [{k: 'name', l: 'Nom'}, {k: 'unit', l: 'Unité', t: 'select', o: [['g', 'g'], ['mL', 'mL'], ['pce', 'pièce']]}, {k: 'price', l: 'Prix (€/unité)', t: 'number'}, {k: 'note', l: 'Note'}],
  style: [{k: 'name', l: 'Nom'}, {k: 'guide', l: 'Guide / millésime (ex. BJCP 2021)'}, {k: 'category', l: 'Catégorie (ex. 21A)'}, {k: 'type', l: 'Type', t: 'select', o: [['ale', 'Ale'], ['lager', 'Lager'], ['mixed', 'Mixte'], ['cider', 'Cidre'], ['mead', 'Hydromel'], ['other', 'Autre']]},
    {k: 'ogMin', l: 'DI min', t: 'number'}, {k: 'ogMax', l: 'DI max', t: 'number'}, {k: 'fgMin', l: 'DF min', t: 'number'}, {k: 'fgMax', l: 'DF max', t: 'number'},
    {k: 'ibuMin', l: 'IBU min', t: 'number'}, {k: 'ibuMax', l: 'IBU max', t: 'number'}, {k: 'colorMin', l: 'Couleur min (EBC)', t: 'number'}, {k: 'colorMax', l: 'Couleur max (EBC)', t: 'number'},
    {k: 'abvMin', l: 'Alcool min (%)', t: 'number'}, {k: 'abvMax', l: 'Alcool max (%)', t: 'number'}, {k: 'note', l: 'Description', t: 'textarea'}]
};
const ING_NEW = {ferm: {name: '', type: 'grain', ebc: 5, extract: 80, price: 0}, hop: {name: '', alpha: 5, form: 'Pellet', price: 0}, yeast: {name: '', type: 'ale', form: 'dry', att: 75, tmin: 15, tmax: 22, price: 0}, misc: {name: '', unit: 'g', price: 0},
  style: {name: '', guide: '', category: '', type: 'ale', ogMin: 1.040, ogMax: 1.050, fgMin: 1.008, fgMax: 1.012, ibuMin: 20, ibuMax: 35, colorMin: 8, colorMax: 20, abvMin: 4, abvMax: 6}};
const ING_COLS = {
  ferm: [['Nom', x => esc(x.name)], ['Type', x => FTYPE[x.type]], ['EBC', x => f(x.ebc, 0)], ['Extrait', x => f(x.extract, 0) + ' %'], ['Prix', x => x.price ? f(x.price, 2) + ' €/kg' : '–']],
  hop: [['Nom', x => esc(x.name)], ['α', x => f(x.alpha, 1) + ' %'], ['Forme', x => esc(x.form)], ['Prix', x => x.price ? f(x.price, 3) + ' €/g' : '–']],
  yeast: [['Nom', x => esc(x.name)], ['Type', x => esc(x.type)], ['Atténuation', x => f(x.att, 0) + ' %'], ['Plage', x => f(x.tmin, 0) + '–' + f(x.tmax, 0) + ' °C'], ['Prix', x => x.price ? f(x.price, 2) + ' €' : '–']],
  misc: [['Nom', x => esc(x.name)], ['Unité', x => esc(x.unit)], ['Prix', x => x.price ? f(x.price, 3) + ' €/' + x.unit : '–']],
  style: [['Nom', x => esc(x.name)], ['Guide', x => esc(x.guide) || '–'], ['Catégorie', x => esc(x.category) || '–'], ['DI', x => f(x.ogMin, 3) + '–' + f(x.ogMax, 3)], ['IBU', x => f(x.ibuMin, 0) + '–' + f(x.ibuMax, 0)], ['EBC', x => f(x.colorMin, 0) + '–' + f(x.colorMax, 0)]]
};
// Tri du tableau Ingrédients : par nom (alphabétique) ou par quantité en stock — colonnes cliquables, un
// ingrédient non suivi (stock vide) est traité comme la plus petite quantité.
function ingSortArrow(k) { return ui.ingSort.key === k ? (ui.ingSort.dir === 'desc' ? ' ▼' : ' ▲') : ''; }
function sortIng(list, stockable) {
  const key = (ui.ingSort.key === 'stock' && stockable) ? 'stock' : 'name', dir = ui.ingSort.dir === 'desc' ? -1 : 1;
  return [...list].sort((a, b) => {
    if (key === 'stock') {
      const av = D.stock[a.id], bv = D.stock[b.id], an = av === undefined ? -1 : av, bn = bv === undefined ? -1 : bv;
      return (an - bn) * dir || a.name.localeCompare(b.name, 'fr');
    }
    return a.name.localeCompare(b.name, 'fr') * dir;
  });
}
function viewIngs() {
  const t = ui.tab, C = ING_COLS[t], showArch = ui.showArchivedIng;
  const stockable = !showArch && STOCK_TYPES.includes(t);   // les styles, et les archives, ne se stockent pas
  const l = D.ing[t].filter(x => !!x.archived === showArch);
  const archCount = D.ing[t].filter(x => x.archived).length;
  return `<div class="bar"><h1>Ingrédients</h1><div class="grow"></div>
    <button class="btn ghost" data-act="importLib">Importer bibliothèque</button>
    <input type="file" id="importLibFile" accept=".xml,.json,text/xml,application/xml,application/json" style="display:none">
    <button class="btn" data-act="addIng">+ Ajouter</button></div>
  <div class="seg">${Object.entries(LIBNAME).map(([k, v]) => `<button data-act="ingTab" data-t="${k}" aria-pressed="${t === k}">${v}</button>`).join('')}</div>
  <div class="card"><p class="sub" style="margin-top:0">${t === 'style' ? 'Vos styles de référence (ex. BJCP) : servent de repère lors de la conception d’une recette (indiquez le même nom dans le champ « Style » de la recette).' : 'Votre bibliothèque personnelle : modifiez les valeurs (couleur, extrait, acides alpha, atténuation…) selon vos lots et fournisseurs.'}${stockable ? ' Renseignez le stock à droite ; laissez vide pour ne pas suivre un ingrédient — les recettes signalent les stocks insuffisants.' : ''}
    ${l.length ? '' : (showArch ? ' Aucun ingrédient archivé.' : ' Bibliothèque vide — utilisez « Importer bibliothèque » pour charger un fichier BeerXML ou BeerJSON (ingrédients ou styles, ex. BJCP 2015/2021/2025).')}
    ${archCount ? ` <button class="btn ghost sm" data-act="toggleArchived" style="margin-left:.4rem">${showArch ? 'Voir les ingrédients actifs' : `Voir les ${archCount} archivé(s)`}</button>` : ''}</p>
  ${showArch ? '<p class="sub" style="margin-top:-.4rem">Retirés de la bibliothèque active, mais toujours utilisables dans les recettes qui les référencent déjà. Restaurez-les pour les proposer à nouveau.</p>' : ''}
  ${l.length ? `<div class="scroll"><table class="tbl"><thead><tr><th data-act="ingSort" data-k="name" style="cursor:pointer">Nom${ingSortArrow('name')}</th>${C.slice(1).map(c => `<th>${c[0]}</th>`).join('')}${stockable ? `<th data-act="ingSort" data-k="stock" style="cursor:pointer">Stock${ingSortArrow('stock')}</th>` : ''}<th></th></tr></thead>${sortIng(l, stockable).map(x => `<tr>${C.map(c => `<td>${c[1](x)}</td>`).join('')}${stockable ? `<td><input inputmode="decimal" data-stock="${x.id}" value="${D.stock[x.id] === undefined ? '' : esc(D.stock[x.id])}" placeholder="—" aria-label="Stock ${esc(x.name)}" style="max-width:6.5rem"></td>` : ''}<td style="white-space:nowrap;text-align:right">${showArch
    ? `<button class="btn ghost sm" data-act="restoreIng" data-id="${x.id}">Restaurer</button>`
    : `<button class="btn ghost sm" data-act="editIng" data-id="${x.id}">Modifier</button> <button class="btn ghost sm" data-act="dupIng" data-id="${x.id}">Copier</button> <button class="ico" data-act="delIng" data-id="${x.id}" aria-label="Retirer">✕</button>`}</td></tr>`).join('')}</table></div>` : ''}</div>${pageFoot()}`;
}

/* --- Profils matériel --- */
const PROF_FIELDS = [{k: 'name', l: 'Nom du profil'}, {k: 'batch', l: 'Volume final par défaut (L)', t: 'number'}, {k: 'boil', l: 'Durée d’ébullition (min)', t: 'number'}, {k: 'evap', l: 'Évaporation (L/h)', t: 'number'},
  {k: 'trub', l: 'Pertes cuve d’ébullition / trub (L)', t: 'number'}, {k: 'mashLoss', l: 'Pertes cuve d’empâtage (L)', t: 'number'}, {k: 'absorb', l: 'Absorption des drêches (L/kg)', t: 'number'},
  {k: 'ratio', l: 'Rapport eau/grain (L/kg)', t: 'number'}, {k: 'infusionTemp', l: 'Température eau d’infusion (°C)', t: 'number'}, {k: 'shrink', l: 'Contraction au refroidissement (%)', t: 'number'}, {k: 'eff', l: 'Rendement brasserie (%)', t: 'number'},
  {k: 'mashMethod', l: 'Méthode de brassage', t: 'select', o: BREW_METHOD}, {k: 'biabSqueeze', l: 'Pressurage du sac (BIAB, pertes réduites)', t: 'check'}];
function viewProfiles() {
  return `<div class="bar"><h1>Matériel</h1><div class="grow"></div><button class="btn" data-act="addProfile">+ Nouveau profil</button></div>
  <div class="cards">${D.profiles.map(p => `<div class="card" style="margin:0"><h2>${esc(p.name)}</h2>
    <dl class="kv"><dt>Volume final</dt><dd>${f(p.batch, 1)} L</dd><dt>Ébullition</dt><dd>${f(p.boil, 0)} min</dd><dt>Évaporation</dt><dd>${f(p.evap, 1)} L/h</dd><dt>Pertes trub</dt><dd>${f(p.trub, 1)} L</dd><dt>Pertes empâtage</dt><dd>${f(p.mashLoss, 1)} L</dd>
    <dt>Absorption drêches</dt><dd>${f(p.absorb, 2)} L/kg</dd><dt>Rapport eau/grain</dt><dd>${f(p.ratio, 1)} L/kg</dd><dt>Eau d’infusion</dt><dd>${f(p.infusionTemp, 0)} °C</dd><dt>Contraction</dt><dd>${f(p.shrink, 1)} %</dd><dt>Rendement</dt><dd>${f(p.eff, 0)} %</dd>
    <dt>Méthode</dt><dd>${esc((BREW_METHOD.find(([k]) => k === p.mashMethod) || BREW_METHOD[0])[1])}</dd>${p.mashMethod === 'biab' ? `<dt>Pressurage du sac</dt><dd>${p.biabSqueeze ? 'Oui' : 'Non'}</dd>` : ''}</dl>
    <div class="dlg-actions" style="justify-content:flex-start"><button class="btn ghost sm" data-act="editProfile" data-id="${p.id}">Modifier</button><button class="btn ghost sm" data-act="dupProfile" data-id="${p.id}">Dupliquer</button><button class="btn danger sm" data-act="delProfile" data-id="${p.id}">Supprimer</button></div></div>`).join('') || '<div class="empty">Aucun profil.</div>'}</div>${pageFoot()}`;
}

/* --- Compte --- */
function viewAccount() {
  return `<div class="bar"><h1>Compte</h1></div>
  <div class="card"><div class="account-id">${avatarHTML('lg')}<div><h2 style="margin:0">${esc(USER)}</h2><p class="sub" style="margin:.1rem 0 0">${D.recipes.length} recette(s) · ${D.brews.length} brassage(s) · ${D.profiles.length} profil(s) matériel</p></div></div>
    <div class="dlg-actions" style="justify-content:flex-start;flex-wrap:wrap;margin:.8rem 0 0">
      <a class="btn ghost sm" href="help.html" style="text-decoration:none">Aide</a>
      ${AUTH.mode === 'phpbb'
      ? '<a class="btn ghost sm" href="/forum/" style="text-decoration:none">Retour sur le forum</a>'
      : '<button class="btn ghost sm" data-act="logout">Se déconnecter</button>'}</div></div>
  <div class="card"><h2>Avatar</h2>
    ${AUTH.mode === 'phpbb'
      ? `<p class="sub" style="margin-top:0">Avatar du compte forum${AUTH.avatar_url ? '' : ' (aucun avatar défini sur le forum)'}. Vous pouvez le changer dans le profil phpBB, ou importer une photo locale HopHop ci-dessous.</p>
        <div class="dlg-actions" style="justify-content:flex-start;flex-wrap:wrap;margin:0">
          <button class="btn ghost sm" data-act="avatarUpload">Importer une photo HopHop</button>
          <input type="file" id="avatarFile" accept="image/*" style="display:none">
          ${D.prefs.avatarType === 'image' ? '<button class="btn ghost sm" data-act="avatarReset">Revenir à l’avatar forum</button>' : ''}
        </div>`
      : `<p class="sub" style="margin-top:0">Choisissez un emoji ou importez une photo (recadrée et réduite automatiquement).</p>
    <div class="avatar-picks">${AVATAR_PICKS.map(e => `<button data-act="setAvatarEmoji" data-v="${e}" aria-pressed="${D.prefs.avatarType === 'emoji' && D.prefs.avatarEmoji === e}">${e}</button>`).join('')}</div>
    <div class="dlg-actions" style="justify-content:flex-start;flex-wrap:wrap;margin:0">
      <button class="btn ghost sm" data-act="avatarUpload">Importer une photo</button>
      <input type="file" id="avatarFile" accept="image/*" style="display:none">
      ${D.prefs.avatarType === 'image' ? '<button class="btn ghost sm" data-act="avatarReset">Revenir à l’emoji</button>' : ''}
    </div>`}</div>
  <div class="card"><h2>Apparence</h2>
    <h3>Thème</h3><div class="seg">${THEMES.map(([k, l]) => `<button data-act="setTheme" data-v="${k}" aria-pressed="${D.prefs.theme === k}">${l}</button>`).join('')}</div>
    <h3>Mode d’affichage</h3><div class="seg">
      <button data-act="setDensity" data-v="classic" aria-pressed="${D.prefs.density === 'classic'}">Classique</button>
      <button data-act="setDensity" data-v="compact" aria-pressed="${D.prefs.density === 'compact'}">Compact</button></div>
    <p class="sub" style="margin-bottom:0">Le mode compact réduit les marges et la taille des éléments pour voir plus d’informations à l’écran, pratique sur petit écran.</p></div>
  <div class="card"><h2>Unités de densité</h2>
    <p class="sub" style="margin-top:0">Les densités (DI, DF, densité pré-ébullition…) s’affichent toujours dans les deux unités ; choisissez laquelle est mise en avant.</p>
    <div class="seg">
      <button data-act="setUnit" data-v="sg" aria-pressed="${D.prefs.unitPrimary === 'sg'}">Densité relative (SG) en principal</button>
      <button data-act="setUnit" data-v="brix" aria-pressed="${D.prefs.unitPrimary === 'brix'}">Brix (°Bx) en principal</button></div>
    <h3>Formule de correction réfractomètre</h3>
    <div class="seg">
      <button data-act="setRefract" data-v="linear" aria-pressed="${D.prefs.refractFormula !== 'cubic'}">Sean Terrill (linéaire)</button>
      <button data-act="setRefract" data-v="cubic" aria-pressed="${D.prefs.refractFormula === 'cubic'}">Cubique</button></div>
    <p class="sub" style="margin-bottom:0">La formule linéaire convient à la plupart des densités ; la cubique est recommandée au-delà d’environ 1,080 de densité initiale (bières fortes).</p></div>
  <div class="card"><h2>Brassins</h2>
    <p class="sub" style="margin-top:0">Sous-formulaire « Autres notes » à l’édition d’un brassage : quantités réelles, températures, pH, densités et volumes du jour de brassage.</p>
    <div class="seg">
      <button data-act="setBrewExtra" data-v="1" aria-pressed="${D.prefs.brewExtraNotes === true}">Activé</button>
      <button data-act="setBrewExtra" data-v="0" aria-pressed="${D.prefs.brewExtraNotes !== true}">Désactivé</button>
    </div>
    <p class="sub" style="margin-bottom:0">Désactivé par défaut. Les notes déjà saisies restent enregistrées si vous désactivez l’option.</p></div>
  <div class="card"><h2>Sauvegarde</h2><p class="sub" style="margin-top:0">Vos données vivent sur ce serveur. Exportez tout de même une sauvegarde JSON de temps en temps, et importez-la pour restaurer un état antérieur.</p>
    <div class="dlg-actions" style="justify-content:flex-start;flex-wrap:wrap;margin:0"><button class="btn ghost sm" data-act="backup">Exporter la sauvegarde</button><button class="btn ghost sm" data-act="restore">Importer une sauvegarde</button><input type="file" id="restoreFile" accept=".json,application/json" style="display:none"></div></div>
  ${AUTH.features.delete_account ? '<div class="card"><h2>Zone sensible</h2><button class="btn danger sm" data-act="delAccount">Supprimer ce compte et ses données</button></div>' : ''}
  ${pageFoot()}`;
}

/* --- Rendu général --- */
function render() {
  const app = $('#app');
  if (!USER || !D) { app.innerHTML = authHTML(); return; }
  app.innerHTML = shellHTML(); renderMain();
}
function renderMain(keep) {
  const y = keep ? window.scrollY : 0;
  const V = {recipes: viewRecipes, editor: viewEditor, sheet: viewSheet, brews: viewBrews, ings: viewIngs, profiles: viewProfiles, account: viewAccount};
  $('#main').innerHTML = V[ui.view]();
  const cur = ['editor', 'sheet'].includes(ui.view) ? 'recipes' : ui.view;
  document.querySelectorAll('nav.tabs button').forEach(b => cur === b.dataset.v ? b.setAttribute('aria-current', 'page') : b.removeAttribute('aria-current'));
  if (ui.view === 'editor') updateStats();
  window.scrollTo(0, y);
}

/* --- Boîtes de dialogue génériques --- */
function showDlg(html) { const d = $('#dlg'); d.innerHTML = html; try { d.showModal(); } catch { d.setAttribute('open', ''); } }
function closeDlg() { const d = $('#dlg'); try { d.close(); } catch { d.removeAttribute('open'); } }
let dlgSave = null;
function openForm(title, fields, vals, onSave, extra = '') {
  const inner = fields.map(fd => {
    const v = vals[fd.k] ?? '', id = 'f-' + fd.k;
    if (fd.t === 'select') return fld(fd.l, `<select id="${id}" data-f="${fd.k}">${opts(fd.o, v)}</select>`);
    if (fd.t === 'textarea') return fld(fd.l, `<textarea id="${id}" data-f="${fd.k}">${esc(v)}</textarea>`, 'full');
    if (fd.t === 'check') return `<label class="fld full" style="flex-direction:row;align-items:center;gap:.5rem"><input type="checkbox" style="width:auto" id="${id}" data-f="${fd.k}" ${v ? 'checked' : ''}><span style="text-transform:none;font-size:.95rem;color:var(--text)">${fd.l}</span></label>`;
    if (fd.t === 'date') return fld(fd.l, `<input type="date" id="${id}" data-f="${fd.k}" value="${esc(v)}">`);
    return fld(fd.l, `<input id="${id}" data-f="${fd.k}" ${fd.t === 'number' ? 'data-t="n" inputmode="decimal"' : ''} value="${esc(v)}">`);
  }).join('');
  dlgSave = () => {
    const out = {};
    document.querySelectorAll('#dlg [data-f]').forEach(el => out[el.dataset.f] = el.type === 'checkbox' ? el.checked : el.dataset.t === 'n' ? N(el.value) : el.value);
    if (onSave(out) !== false) closeDlg();
  };
  showDlg(`<h2>${esc(title)}</h2><div class="fgrid">${inner}</div>${extra}<div class="dlg-actions"><button class="btn ghost" data-act="dlgClose">Annuler</button><button class="btn" data-act="dlgSave">Enregistrer</button></div>`);
}
function ask(msg, yes, onYes) {
  dlgSave = () => { closeDlg(); onYes(); };
  showDlg(`<h2>Confirmation</h2><p>${msg}</p><div class="dlg-actions"><button class="btn ghost" data-act="dlgClose">Annuler</button><button class="btn danger" data-act="dlgSave">${yes}</button></div>`);
}

/* ---------- 6. ACTIONS ---------- */
const setPath = (o, path, v) => { const p = path.split('.'); const last = p.pop(); let t = o; p.forEach(k => t = t[k]); t[last] = v; };
const scaleRecipe = (r, nv) => {
  const k = nv / N(r.batch);
  r.ferm.forEach(x => x.kg = rnd(N(x.kg) * k, 3)); r.hops.forEach(x => x.g = rnd(N(x.g) * k, 1)); r.misc.forEach(x => x.qty = rnd(N(x.qty) * k, 2));
  r.batch = nv; r.updated = Date.now();
};
function createBrew(r, date) {
  const c = calc(r);
  const b = {id: uid(), recipeId: r.id, recipeName: r.name, date, status: 'brewed', ogM: 0, fgM: 0, vol: N(r.batch), notes: '', tasting: '',
    extraNotes: emptyBrewExtraNotes(),
    stats: {og: c.og, fg: c.fg, abv: c.abv, ibu: c.ibu, ebc: c.ebc, potGrain: c.potGrain, potOther: c.potOther}, snap: clone(r)};
  D.brews.push(b); persist();
  return b;
}
// Ingrédients suivis en stock utilisés par la recette, regroupés par ingrédient (une recette peut ajouter
// le même houblon à plusieurs moments) — sert de quantité proposée, ajustable avant la sortie de stock.
function brewStockRows(r) {
  const agg = {};
  const add = (t, ref, qty, unit) => {
    if (D.stock[ref] === undefined) return;
    const it = lib(t, ref); if (!it) return;
    if (!agg[ref]) agg[ref] = {id: ref, name: it.name, unit, qty: 0};
    agg[ref].qty += qty;
  };
  r.ferm.forEach(x => add('ferm', x.ref, N(x.kg), 'kg'));
  r.hops.forEach(x => add('hop', x.ref, N(x.g), 'g'));
  r.yeast.forEach(x => add('yeast', x.ref, N(x.qty), 'sachet(s)'));
  r.misc.forEach(x => { const it = lib('misc', x.ref); add('misc', x.ref, N(x.qty), it ? it.unit : ''); });
  return Object.values(agg);
}
function openStockDeductDialog(r) {
  const rows = brewStockRows(r);
  if (!rows.length) { toast('Brassage enregistré — aucun ingrédient de cette recette n’est suivi en stock.'); return; }
  const fields = rows.map(row => ({k: 'q_' + row.id, l: `${row.name} (${row.unit})`, t: 'number'}));
  const vals = {}; rows.forEach(row => vals['q_' + row.id] = rnd(row.qty, 3));
  openForm('Quantités réellement utilisées', fields, vals, v => {
    rows.forEach(row => { const used = N(v['q_' + row.id]); D.stock[row.id] = Math.max(0, rnd(D.stock[row.id] - used, 3)); });
    persist(); toast('Stock mis à jour');
  }, '<p class="sub">Préremplies avec les quantités de la recette — ajustez si vous avez utilisé plus ou moins, puis validez la sortie de stock.</p>');
}
const brewFields = [{k: 'date', l: 'Date de brassage', t: 'date'}, {k: 'deduct', l: 'Décompter les ingrédients du stock (uniquement ceux suivis)', t: 'check'}];
const brewEditFields = [
  {k: 'date', l: 'Date', t: 'date'},
  {k: 'status', l: 'Statut', t: 'select', o: Object.entries(STATUS)},
  {k: 'ogM', l: 'Densité initiale mesurée', t: 'number'},
  {k: 'fgM', l: 'Densité finale mesurée', t: 'number'},
  {k: 'vol', l: 'Volume obtenu (L)', t: 'number'},
  {k: 'notes', l: 'Notes de brassage', t: 'textarea'},
  {k: 'tasting', l: 'Dégustation', t: 'textarea'},
];
const brewEditHelp = '<p class="sub">Saisissez les densités au format 1.052 (ou 1,052). Le rendement réel est calculé à partir de la densité initiale et du volume obtenu.</p>';
/** Sous le formulaire d’édition : Autres notes (si activé) ou rappel Compte. */
function brewFormExtraNotesHTML(b) {
  if (D.prefs.brewExtraNotes === true) {
    normalizeBrewExtraNotes(b);
    return brewExtraNotesHTML(b);
  }
  return '<p class="brew-extra-hint">Autres notes activables dans l’onglet <button type="button" data-act="go" data-v="account">Compte</button>.</p>';
}
const profDefaults = () => ({name: 'Nouveau profil', batch: 20, boil: 60, evap: 3, trub: 1.5, mashLoss: 1, absorb: 1, ratio: 3, infusionTemp: 96, shrink: 4, eff: 72, mashMethod: 'classic', biabSqueeze: false});

const ACT = {
  authMode: el => { ui.auth = el.dataset.m; render(); }, authGo: () => authSubmit(), logout: () => logout(),
  setTheme: el => { D.prefs.theme = el.dataset.v; applyPrefs(); persist(); renderMain(true); },
  setDensity: el => { D.prefs.density = el.dataset.v; applyPrefs(); persist(); renderMain(true); },
  setUnit: el => { D.prefs.unitPrimary = el.dataset.v; persist(); renderMain(true); },
  setRefract: el => { D.prefs.refractFormula = el.dataset.v; persist(); renderMain(true); },
  setBrewExtra: el => { D.prefs.brewExtraNotes = el.dataset.v === '1'; persist(); renderMain(true); },
  xnAddQty: () => { const tb = $('#xn-qty-body'); if (tb) tb.insertAdjacentHTML('beforeend', brewExtraQtyExtraRow()); },
  xnRmQty: el => { const tr = el.closest('tr'); if (tr) tr.remove(); },
  setAvatarEmoji: el => { D.prefs.avatarType = 'emoji'; D.prefs.avatarEmoji = el.dataset.v; persist(); renderMain(true); },
  avatarUpload: () => $('#avatarFile').click(),
  avatarReset: () => { D.prefs.avatarType = 'emoji'; D.prefs.avatarImage = ''; persist(); renderMain(true); },
  go: el => { closeDlg(); ui.view = el.dataset.v; renderMain(); window.scrollTo(0, 0); },
  dlgClose: () => closeDlg(), dlgSave: () => dlgSave && dlgSave(),
  newRecipe: () => { const r = newRecipe(); D.recipes.push(r); ui.rid = r.id; ui.view = 'editor'; persist(); renderMain(); window.scrollTo(0, 0); },
  open: el => { ui.rid = el.dataset.id; ui.view = 'editor'; renderMain(); window.scrollTo(0, 0); },
  sheet: () => { ui.view = 'sheet'; renderMain(); window.scrollTo(0, 0); },
  sheetMode: el => { ui.sheetSteps = el.dataset.v === '1'; renderMain(true); },
  toggleSheetCost: el => { ui.sheetCost = el.checked; renderMain(true); },
  print: () => { try { window.print(); } catch { toast('Utilisez la fonction Imprimer de votre navigateur.'); } },
  dupRecipe: () => { const r = clone(R()); r.id = uid(); r.name += ' (copie)'; r.created = r.updated = Date.now(); D.recipes.push(r); ui.rid = r.id; persist(); renderMain(); window.scrollTo(0, 0); toast('Recette dupliquée'); },
  delRecipe: () => ask(`Supprimer la recette « ${esc(R().name)} » ? Les brassages déjà enregistrés sont conservés.`, 'Supprimer', () => { D.recipes = D.recipes.filter(x => x.id !== ui.rid); ui.view = 'recipes'; persist(); renderMain(); }),
  add: el => {
    const r = R(), t = el.dataset.t, lt = RLIB[t], first = lt && D.ing[lt][0];
    if (lt && !first) { toast('Ajoutez d’abord des ingrédients dans la bibliothèque.'); return; }
    const row = {ferm: () => ({ref: first.id, kg: 1}), hops: () => ({ref: first.id, g: 20, use: 'boil', time: 60}), yeast: () => ({ref: first.id, qty: 1}),
      misc: () => ({ref: first.id, qty: 1, use: 'boil', time: 15}), mash: () => ({name: 'Palier', temp: 65, time: 30, method: 'infusion'}), ferment: () => ({name: 'Étape', temp: 18, days: 7})}[t]();
    r[t].push(row); r.updated = Date.now(); persist(); renderMain(true);
  },
  rm: el => { const r = R(); r[el.dataset.t].splice(+el.dataset.i, 1); r.updated = Date.now(); persist(); renderMain(true); },
  resizeApply: () => {
    const nv = N($('#newvol').value), r = R(); if (nv <= 0 || nv === N(r.batch)) { toast('Indiquez un volume différent.'); return; }
    ask(`Multiplier toutes les quantités par ${f(nv / N(r.batch), 3)} (${f(N(r.batch), 1)} L → ${f(nv, 1)} L) ?`, 'Appliquer', () => { scaleRecipe(r, nv); persist(); renderMain(true); toast('Recette redimensionnée'); });
  },
  resizeCopy: () => {
    const nv = N($('#newvol').value), r = R(); if (nv <= 0) { toast('Indiquez un volume valide.'); return; }
    const c = clone(r); c.id = uid(); c.name += ` (${f(nv, 0)} L)`; c.created = Date.now(); scaleRecipe(c, nv);
    D.recipes.push(c); ui.rid = c.id; persist(); renderMain(); window.scrollTo(0, 0); toast('Copie redimensionnée créée');
  },
  exp: el => showExport(el.dataset.k),
  shareBrewRecipes: () => shareToBrewRecipes(),
  pickBrewRecipesStyle: () => openBrewRecipesStylePicker(),
  brewFrom: () => openForm('Enregistrer un brassage', brewFields, {date: today(), deduct: false}, v => {
    const r = R(); createBrew(r, v.date || today());
    if (v.deduct) { openStockDeductDialog(r); return false; }
    toast('Brassage enregistré');
  }),
  newBrew: () => {
    if (!D.recipes.length) { toast('Créez d’abord une recette.'); return; }
    openForm('Nouveau brassage', [{k: 'rid', l: 'Recette', t: 'select', o: D.recipes.map(r => [r.id, r.name])}, ...brewFields], {rid: D.recipes[0].id, date: today(), deduct: false},
      v => {
        const rec = D.recipes.find(r => r.id === v.rid); createBrew(rec, v.date || today()); renderMain(true);
        if (v.deduct) { openStockDeductDialog(rec); return false; }
        toast('Brassage enregistré');
      });
  },
  editBrew: el => {
    const b = D.brews.find(x => x.id === el.dataset.id);
    normalizeBrewExtraNotes(b);
    openForm(b.recipeName, brewEditFields, b,
      v => {
        b.date = v.date; b.status = v.status; b.ogM = v.ogM; b.fgM = v.fgM; b.vol = v.vol; b.notes = v.notes; b.tasting = v.tasting;
        if (D.prefs.brewExtraNotes === true) b.extraNotes = readBrewExtraFromDlg();
        persist(); renderMain(true);
      },
      brewEditHelp + brewFormExtraNotesHTML(b));
  },
  delBrew: el => ask('Supprimer ce brassage de l’historique ?', 'Supprimer', () => { D.brews = D.brews.filter(x => x.id !== el.dataset.id); persist(); renderMain(true); }),
  ingTab: el => { ui.tab = el.dataset.t; renderMain(true); },
  importLib: () => $('#importLibFile').click(),
  ingSort: el => { const k = el.dataset.k; if (ui.ingSort.key === k) ui.ingSort.dir = ui.ingSort.dir === 'desc' ? 'asc' : 'desc'; else ui.ingSort = {key: k, dir: 'asc'}; renderMain(true); },
  addIng: () => { const t = ui.tab; openForm('Nouvel ingrédient — ' + LIBNAME[t], ING_FIELDS[t], ING_NEW[t], v => { if (!v.name.trim()) { toast('Le nom est obligatoire.'); return false; } D.ing[t].push({id: uid(), note: '', ...v}); persist(); renderMain(true); }); },
  editIng: el => { const t = ui.tab, x = lib(t, el.dataset.id); openForm(x.name, ING_FIELDS[t], x, v => { if (!v.name.trim()) { toast('Le nom est obligatoire.'); return false; } Object.assign(x, v); persist(); renderMain(true); updateStats(); }); },
  dupIng: el => { const t = ui.tab, x = clone(lib(t, el.dataset.id)); x.id = uid(); x.name += ' (copie)'; x.archived = false; D.ing[t].push(x); persist(); renderMain(true); },
  delIng: el => { const t = ui.tab, x = lib(t, el.dataset.id);
    ask(`Retirer « ${esc(x.name)} » de la bibliothèque active ? Les recettes qui l’utilisent déjà continueront de fonctionner normalement — retrouvez-le via « Voir les archivé(s) » pour le restaurer.`, 'Retirer', () => { x.archived = true; persist(); renderMain(true); }); },
  restoreIng: el => { const t = ui.tab, x = lib(t, el.dataset.id); x.archived = false; persist(); renderMain(true); toast('Ingrédient restauré'); },
  toggleArchived: () => { ui.showArchivedIng = !ui.showArchivedIng; renderMain(true); },
  addProfile: () => openForm('Nouveau profil matériel', PROF_FIELDS, profDefaults(), v => { D.profiles.push({id: uid(), ...v}); persist(); renderMain(true); }),
  editProfile: el => { const p = D.profiles.find(x => x.id === el.dataset.id); openForm(p.name, PROF_FIELDS, p, v => { Object.assign(p, v); persist(); renderMain(true); }); },
  dupProfile: el => { const p = clone(D.profiles.find(x => x.id === el.dataset.id)); p.id = uid(); p.name += ' (copie)'; D.profiles.push(p); persist(); renderMain(true); },
  delProfile: el => ask('Supprimer ce profil matériel ? Les recettes concernées utiliseront le premier profil restant.', 'Supprimer', () => { D.profiles = D.profiles.filter(x => x.id !== el.dataset.id); persist(); renderMain(true); }),
  backup: () => saveFile(`HopHop-${slug(USER)}-${today()}.json`, JSON.stringify({app: 'HopHop', user: USER, data: D}, null, 2), 'application/json'),
  restore: () => $('#restoreFile').click(),
  importXML: () => $('#importBeerXMLFile').click(),
  importRecipeJSON: () => $('#importBeerJSONFile').click(),
  delAccount: () => ask('Supprimer définitivement ce compte et toutes ses données du serveur ?', 'Supprimer', async () => { try { await api('delete_account'); } catch (e) { toast(e.message); return; } USER = D = null; ui.auth = 'login'; render(); }),
};

/* Délégation d'événements : un seul écouteur par type */
document.addEventListener('click', e => { const el = e.target.closest('[data-act]'); if (el && ACT[el.dataset.act]) ACT[el.dataset.act](el, e); });
document.addEventListener('input', e => {
  const t = e.target;
  if (t.dataset.b) {                                   // champ de la recette en cours d'édition
    const r = R(); if (!r) return;
    setPath(r, t.dataset.b, t.type === 'checkbox' ? t.checked : (t.hasAttribute('data-n') ? N(t.value) : t.value));
    if (t.dataset.b === 'styleId') { const st = styleById(r.styleId); r.style = st ? st.name : ''; }
    r.updated = Date.now(); persist(); updateStats();
    if (t.dataset.b === 'batch') { const nv = $('#newvol'); if (nv) nv.value = t.value; }
    if (t.dataset.b === 'mashMethod') { renderMain(true); return; }
    if (t.dataset.b === 'profileId') {                   // reprend les valeurs du nouveau profil (volume, ratio, rendement, eau d'infusion, méthode…)
      const p = D.profiles.find(x => x.id === r.profileId) || DEFAULT_P;
      r.batch = p.batch; r.ratio = p.ratio; r.eff = p.eff; r.infusionTemp = p.infusionTemp; r.mashMethod = p.mashMethod; r.biabSqueeze = p.biabSqueeze;
      toast('Valeurs reprises du profil « ' + p.name + ' »'); renderMain(true); return;
    }
  } else if (t.id === 'newvol') { const r = R(), nv = N(t.value); $('#kfac').textContent = nv > 0 && N(r.batch) > 0 ? '× ' + f(nv / N(r.batch), 3) : ''; }
  else if (t.dataset.q !== undefined) { ui.q = t.value; $('#rlist').innerHTML = recipeListHTML(); }
  else if (t.dataset.stock) {                          // stock : vide = non suivi
    if (t.value.trim() === '') delete D.stock[t.dataset.stock]; else D.stock[t.dataset.stock] = Math.max(0, N(t.value));
    persist();
  }
});
document.addEventListener('change', e => {
  const t = e.target;
  if (t.dataset.b && t.tagName === 'SELECT' && 'rr' in t.dataset) renderMain(true);   // le champ "temps" dépend de l'usage
  if (t.id === 'restoreFile' && t.files[0]) {
    const fr = new FileReader();
    fr.onload = () => { try {
      const j = JSON.parse(fr.result); if (!j.data || !j.data.ing) throw 0;
      ask('Remplacer toutes les données de ce compte par la sauvegarde importée ?', 'Importer', () => { D = normalizeData(j.data); persist(); saveNow(); renderMain(); toast('Sauvegarde importée'); });
    } catch { toast('Fichier de sauvegarde invalide.'); } };
    fr.readAsText(t.files[0]); t.value = '';
  }
  if (t.id === 'importLibFile' && t.files[0]) {
    const file = t.files[0];
    const fr = new FileReader();
    fr.onload = () => {
      const text = fr.result;
      openForm('Millésime du guide importé', [{k: 'guide', l: 'Étiquette du guide (ex. « BJCP 2021 ») — sert uniquement pour les styles'}], {guide: guessGuide(file.name)}, v => {
        let counts;
        try { counts = importLibraryFile(text, v.guide.trim()); }
        catch (e) { toast(e.message); return false; }
        const LBL = {ferm: 'fermentescible(s)', hop: 'houblon(s)', yeast: 'levure(s)', misc: 'divers', style: 'style(s)'};
        const parts = Object.entries(counts).filter(([, n]) => n).map(([k, n]) => `${n} ${LBL[k]}`);
        persist(); toast('Importé : ' + parts.join(', ')); renderMain(true);
      }, '<p class="sub">Deux styles de même nom mais de guides différents restent distincts ; réimporter le même guide met à jour ses styles.</p>');
    };
    fr.readAsText(file); t.value = '';
  }
  if (t.id === 'importBeerXMLFile' && t.files[0]) {
    const fr = new FileReader();
    fr.onload = async () => {
      let added;
      try { added = importBeerXML(fr.result); }
      catch (e) { toast(e.message); return; }
      if (added.length === 1) { ui.rid = added[0].id; ui.view = 'editor'; renderMain(); window.scrollTo(0, 0); }
      else renderMain(true);
      const saved = await saveNow();
      toast(saved
        ? (added.length > 1 ? `${added.length} recettes importées` : 'Recette importée')
        : 'Recette ajoutée à l’écran mais non enregistrée sur le serveur.');
    };
    fr.readAsText(t.files[0]); t.value = '';
  }
  if (t.id === 'importBeerJSONFile' && t.files[0]) {
    const fr = new FileReader();
    fr.onload = () => {
      let added;
      try { added = importBeerJSONRecipes(fr.result); }
      catch (e) { toast(e.message); return; }
      persist(); toast(added.length > 1 ? `${added.length} recettes importées` : 'Recette importée');
      if (added.length === 1) { ui.rid = added[0].id; ui.view = 'editor'; renderMain(); window.scrollTo(0, 0); }
      else renderMain(true);
    };
    fr.readAsText(t.files[0]); t.value = '';
  }
  if (t.id === 'avatarFile' && t.files[0]) {
    resizeAvatarImage(t.files[0]).then(dataUrl => {
      D.prefs.avatarType = 'image'; D.prefs.avatarImage = dataUrl; persist(); renderMain(true);
    }).catch(() => toast('Image illisible.'));
    t.value = '';
  }
});
document.addEventListener('keydown', e => { if (e.key === 'Enter' && !USER && e.target.tagName === 'INPUT') authSubmit(); });
window.addEventListener('beforeunload', e => { if (saveT || saveBusy) { saveNow(); } });

/* ---------- 7a. IMPORT BeerXML 1.0 ----------
   Retrouve chaque ingrédient par son nom dans la bibliothèque de l'utilisateur (insensible à la casse) ;
   le crée sinon. Un profil matériel dédié est créé (ou réutilisé s'il porte déjà le même nom) à partir
   du bloc EQUIPMENT quand il existe, pour préserver rendement/pertes/évaporation de la recette source. */
function xtext(el, tag) { if (!el) return ''; const n = el.querySelector(tag); return n ? (n.textContent || '').trim() : ''; }
function xnum(el, tag, def = 0) { const v = xtext(el, tag); if (v === '') return def; const n = parseFloat(v.replace(',', '.')); return isFinite(n) ? n : def; }
function findIng(t, name) { const n = name.trim().toLowerCase(); return D.ing[t].find(x => x.name.trim().toLowerCase() === n); }
function ensureIng(t, name, extra) { let x = findIng(t, name); if (!x) { x = {id: uid(), name: name.trim(), note: '', ...extra}; D.ing[t].push(x); } return x; }
/* Import de bibliothèque (bouton dédié, distinct de l'import de recette) : met à jour l'entrée existante si le nom
   correspond déjà (utile pour recharger une édition plus récente d'un guide BJCP), sinon la crée. */
function upsertIng(t, name, fields) {
  const nm = (name || '').trim(); if (!nm) return null;
  let x = findIng(t, nm);
  if (x) Object.assign(x, fields); else { x = {id: uid(), name: nm, note: '', ...fields}; D.ing[t].push(x); }
  return x;
}
/* Les styles sont identifiés par nom + guide/millésime (ex. « American IPA » BJCP 2015 et BJCP 2021 restent deux
   entrées distinctes) : un ré-import du même guide met à jour ses styles, un import d'un autre guide n'écrase rien. */
const styleComposite = s => s.guide ? `${s.name} — ${s.guide}` : s.name;
function findStyleEntry(name, guide) {
  const n = (name || '').trim().toLowerCase(), g = (guide || '').trim().toLowerCase();
  return D.ing.style.find(x => x.name.trim().toLowerCase() === n && (x.guide || '').trim().toLowerCase() === g);
}
function upsertStyle(name, guide, fields) {
  const nm = (name || '').trim(); if (!nm) return null;
  let x = findStyleEntry(nm, guide);
  if (x) Object.assign(x, fields); else { x = {id: uid(), name: nm, guide: (guide || '').trim(), note: '', ...fields}; D.ing.style.push(x); }
  return x;
}
function findStyleForRecipe(styleText) {
  if (!styleText) return null;
  const t = styleText.trim().toLowerCase();
  return D.ing.style.find(s => styleComposite(s).toLowerCase() === t) || D.ing.style.find(s => s.name.trim().toLowerCase() === t) || null;
}
const styleById = id => id ? D.ing.style.find(s => s.id === id) : null;
// Sélection explicite du style de référence à la conception ; reste vide si aucun n'est choisi.
const styleSelectOpts = sel => opts([['', '— Aucun —'], ...[...D.ing.style].sort((a, b) => a.name.localeCompare(b.name, 'fr') || (a.guide || '').localeCompare(b.guide || '', 'fr')).map(s => [s.id, styleComposite(s)])], sel || '');
/* Le style de référence choisi (styleId) prime ; à défaut, on retombe sur une correspondance par texte
   (compatibilité avec les recettes plus anciennes ou importées en BeerXML, où seul le texte libre existe). */
function recipeStyleRef(r) { return styleById(r.styleId) || (r.style ? findStyleForRecipe(r.style) : null); }
// Nom de style à afficher : le style de référence choisi fait foi ; le texte libre ne sert plus que de
// repli pour les recettes plus anciennes ou importées en BeerXML sans style de référence associé.
function recipeStyleName(r) { return styleById(r.styleId)?.name || r.style || ''; }

/* ---------- 7b. IMPORT de bibliothèques (ingrédients et styles) — BeerXML 1.0 ou BeerJSON ----------
   Fichiers autonomes (levures, malts, houblons, divers, styles BJCP 2015/2021/2025…), pas des recettes.
   BeerXML : cherche toute balise FERMENTABLE / HOP / YEAST / MISC / STYLE dans le document, à n'importe quel niveau.
   JSON (BeerJSON ou variantes usuelles) : tolère plusieurs noms de clé pour chaque tableau. */
function jval(v) {
  if (v == null) return null;
  if (typeof v === 'number') return v;
  if (typeof v === 'string') { const n = parseFloat(v.replace(',', '.')); return isFinite(n) ? n : null; }
  if (typeof v === 'object' && 'value' in v) return jval(v.value);
  return null;
}
function junit(v) { return (v && typeof v === 'object' && v.unit) ? String(v.unit).toLowerCase() : ''; }
function jrange(v) {
  if (v == null) return [null, null];
  if (typeof v === 'object' && ('minimum' in v || 'maximum' in v)) return [jval(v.minimum), jval(v.maximum)];
  const s = jval(v); return [s, s];
}
function jcolorEBC(raw, unit) {
  if (raw == null) return null;
  if (unit.includes('ebc')) return rnd(raw, 1);
  if (unit.includes('srm')) return rnd(raw * 1.97, 1);
  if (unit.includes('lovibond') || unit === 'l') return rnd(((raw * 1.3546) - 0.76) * 1.97, 1);
  return rnd(raw, 1);   // pas d'unité fournie : on suppose déjà des EBC
}
function jcolorRange(v) {
  const u = junit(v) || junit(v && v.minimum) || junit(v && v.maximum);
  const [mn, mx] = jrange(v); return [jcolorEBC(mn, u), jcolorEBC(mx, u)];
}

function importLibraryXML(doc, guide) {
  const counts = {ferm: 0, hop: 0, yeast: 0, misc: 0, style: 0};
  doc.querySelectorAll('FERMENTABLE').forEach(el => {
    const nm = xtext(el, 'NAME'); if (!nm) return;
    const type = {Grain: 'grain', Adjunct: 'grain', Sugar: 'sugar', Extract: 'extract', 'Dry Extract': 'extract'}[xtext(el, 'TYPE')] || 'grain';
    upsertIng('ferm', nm, {type, ebc: rnd(xnum(el, 'COLOR', 5) * 1.97, 1), extract: rnd(xnum(el, 'YIELD', 78), 0)}); counts.ferm++;
  });
  doc.querySelectorAll('HOP').forEach(el => {
    const nm = xtext(el, 'NAME'); if (!nm) return;
    upsertIng('hop', nm, {alpha: rnd(xnum(el, 'ALPHA', 5), 1), form: xtext(el, 'FORM') === 'Leaf' ? 'Cône entier' : 'Pellet'}); counts.hop++;
  });
  doc.querySelectorAll('YEAST').forEach(el => {
    const nm = xtext(el, 'NAME'); if (!nm) return;
    upsertIng('yeast', nm, {type: xtext(el, 'TYPE') === 'Lager' ? 'lager' : 'ale', form: xtext(el, 'FORM') === 'Liquid' ? 'liquid' : 'dry',
      att: rnd(xnum(el, 'ATTENUATION', 75), 0), tmin: rnd(xnum(el, 'MIN_TEMPERATURE', 15), 0), tmax: rnd(xnum(el, 'MAX_TEMPERATURE', 22), 0)}); counts.yeast++;
  });
  doc.querySelectorAll('MISC').forEach(el => {
    const nm = xtext(el, 'NAME'); if (!nm) return;
    upsertIng('misc', nm, {unit: xtext(el, 'AMOUNT_IS_WEIGHT').toLowerCase() === 'true' ? 'g' : 'mL'}); counts.misc++;
  });
  doc.querySelectorAll('STYLE').forEach(el => {
    if (upsertStyleFromBeerXmlEl(el, guide)) counts.style++;
  });
  return counts;
}

/** Crée / met à jour un style HopHop depuis une balise BeerXML STYLE (guide = STYLE_GUIDE ou repli). */
function upsertStyleFromBeerXmlEl(el, guideFallback) {
  if (!el) return null;
  const nm = xtext(el, 'NAME');
  if (!nm) return null;
  const guide = xtext(el, 'STYLE_GUIDE') || (guideFallback || '');
  const category = (xtext(el, 'CATEGORY_NUMBER') + xtext(el, 'STYLE_LETTER')) || xtext(el, 'CATEGORY');
  const type = {Lager: 'lager', Ale: 'ale', Mead: 'mead', Wheat: 'ale', Mixed: 'mixed', Cider: 'cider'}[xtext(el, 'TYPE')] || 'other';
  return upsertStyle(nm, guide, {
    category, type,
    ogMin: rnd(xnum(el, 'OG_MIN', 1.040), 3), ogMax: rnd(xnum(el, 'OG_MAX', 1.050), 3),
    fgMin: rnd(xnum(el, 'FG_MIN', 1.008), 3), fgMax: rnd(xnum(el, 'FG_MAX', 1.012), 3),
    ibuMin: rnd(xnum(el, 'IBU_MIN', 0), 0), ibuMax: rnd(xnum(el, 'IBU_MAX', 0), 0),
    colorMin: rnd(xnum(el, 'COLOR_MIN', 0) * 1.97, 1), colorMax: rnd(xnum(el, 'COLOR_MAX', 0) * 1.97, 1),
    abvMin: rnd(xnum(el, 'ABV_MIN', 0), 1), abvMax: rnd(xnum(el, 'ABV_MAX', 0), 1),
    note: xtext(el, 'NOTES'),
  });
}

function importLibraryJSON(data, guide) {
  const root = data.beerjson || data;
  const counts = {ferm: 0, hop: 0, yeast: 0, misc: 0, style: 0};
  (root.fermentables || []).forEach(it => {
    if (!it || !it.name) return;
    const ty = String(it.type || '').toLowerCase();
    const type = ty.includes('sugar') ? 'sugar' : ty.includes('extract') ? 'extract' : 'grain';
    const [ebc] = jcolorRange(it.color);
    const extractPct = jval(it.yield && (it.yield.fine_grind || it.yield)) ?? (type === 'sugar' ? 100 : type === 'extract' ? 96 : 78);
    upsertIng('ferm', it.name, {type, ebc: ebc ?? (type === 'grain' ? 8 : 0), extract: rnd(extractPct, 0)}); counts.ferm++;
  });
  (root.hop_varieties || root.hops || []).forEach(it => {
    if (!it || !it.name) return;
    const alpha = jval(it.alpha_acid) ?? jval(it.alpha) ?? 5;
    const form = /leaf/i.test(it.form || '') ? 'Cône entier' : 'Pellet';
    upsertIng('hop', it.name, {alpha: rnd(alpha, 1), form}); counts.hop++;
  });
  (root.cultures || root.yeasts || []).forEach(it => {
    if (!it || !it.name) return;
    const type = /lager/i.test(it.type || '') ? 'lager' : 'ale', form = /liquid/i.test(it.form || '') ? 'liquid' : 'dry';
    const [aMin, aMax] = jrange(it.attenuation_range || it.attenuation);
    const att = (aMin != null && aMax != null) ? (aMin + aMax) / 2 : (aMin ?? aMax ?? 75);
    const [tMin, tMax] = jrange(it.temperature_range);
    upsertIng('yeast', it.name, {type, form, att: rnd(att, 0), tmin: rnd(tMin ?? 15, 0), tmax: rnd(tMax ?? 22, 0)}); counts.yeast++;
  });
  (root.miscellaneous_ingredients || root.miscs || root.misc || []).forEach(it => {
    if (!it || !it.name) return;
    upsertIng('misc', it.name, {unit: /ml|liq/i.test(it.unit || '') ? 'mL' : 'g'}); counts.misc++;
  });
  (root.styles || []).forEach(it => {
    if (!it || !it.name) return;
    const [ogMin, ogMax] = jrange(it.original_gravity), [fgMin, fgMax] = jrange(it.final_gravity);
    const [ibuMin, ibuMax] = jrange(it.international_bitterness_units || it.ibu);
    const [colorMin, colorMax] = jcolorRange(it.color);
    const [abvMin, abvMax] = jrange(it.alcohol_by_volume || it.abv);
    const category = [it.category_number, it.style_letter].filter(Boolean).join('') || it.category || '';
    const ty = String(it.type || '').toLowerCase();
    const type = ty.includes('lager') ? 'lager' : ty.includes('mixed') ? 'mixed' : ty.includes('cider') ? 'cider' : ty.includes('mead') ? 'mead' : ty.includes('ale') ? 'ale' : 'other';
    upsertStyle(it.name, guide, {category, type,
      ogMin: rnd(ogMin ?? 1.040, 3), ogMax: rnd(ogMax ?? 1.050, 3), fgMin: rnd(fgMin ?? 1.008, 3), fgMax: rnd(fgMax ?? 1.012, 3),
      ibuMin: rnd(ibuMin ?? 0, 0), ibuMax: rnd(ibuMax ?? 0, 0), colorMin: colorMin ?? 0, colorMax: colorMax ?? 0,
      abvMin: rnd(abvMin ?? 0, 1), abvMax: rnd(abvMax ?? 0, 1), note: it.notes || it.description || ''}); counts.style++;
  });
  return counts;
}

function importLibraryFile(text, guide) {
  const t = text.trim();
  let counts;
  if (t.startsWith('{') || t.startsWith('[')) {
    let data; try { data = JSON.parse(t); } catch { throw new Error('Fichier JSON invalide.'); }
    if (Array.isArray(data)) throw new Error('Format JSON non reconnu (tableau à la racine).');
    counts = importLibraryJSON(data, guide);
  } else {
    let doc; try { doc = new DOMParser().parseFromString(t, 'application/xml'); } catch { throw new Error('Fichier illisible.'); }
    if (doc.querySelector('parsererror')) throw new Error('Fichier BeerXML invalide ou mal formé.');
    counts = importLibraryXML(doc, guide);
  }
  if (!Object.values(counts).some(n => n)) throw new Error('Aucun ingrédient ni style reconnu dans ce fichier.');
  return counts;
}
/* Devine un millésime de guide à partir du nom de fichier (ex. "bjcp-2021-styleguide.xml" → « BJCP 2021 »),
   simple point de départ que l'utilisateur confirme ou corrige avant l'import. */
function guessGuide(filename) {
  const y = (filename || '').match(/\b(19|20)\d{2}\b/);
  const bjcp = /bjcp/i.test(filename || '');
  if (bjcp && y) return `BJCP ${y[0]}`;
  if (y) return y[0];
  if (bjcp) return 'BJCP';
  return '';
}

function ensureProfileFromEquipment(eqEl, recEl, recipeName) {
  const name = xtext(eqEl, 'NAME') || `Profil importé — ${recipeName}`;
  const existing = D.profiles.find(p => p.name === name);
  if (existing) return existing.id;
  const boilSize = eqEl ? xnum(eqEl, 'BOIL_SIZE', null) : null;
  const evapRate = eqEl ? xnum(eqEl, 'EVAP_RATE', null) : null;   // % par heure
  const boil = xnum(eqEl, 'BOIL_TIME', xnum(recEl, 'BOIL_TIME', 60));
  const evap = (evapRate !== null && boilSize) ? rnd(evapRate / 100 * boilSize, 2) : 3;
  const trub = xnum(eqEl, 'TRUB_CHILLER_LOSS', 1.5);
  const mashLoss = xnum(eqEl, 'LAUTER_DEADSPACE', xnum(eqEl, 'MASH_TUN_DEADSPACE', 1));
  const eff = xnum(recEl, 'EFFICIENCY', 72);
  const batch = xnum(recEl, 'BATCH_SIZE', xnum(eqEl, 'BATCH_SIZE', 20));
  const p = {id: uid(), name, batch: rnd(batch, 1), boil: rnd(boil, 0), evap: rnd(evap, 2), trub: rnd(trub, 2), mashLoss: rnd(mashLoss, 2), absorb: 1, ratio: 3, infusionTemp: 96, shrink: 4, eff: rnd(eff, 0)};
  D.profiles.push(p); return p.id;
}

function importBeerXML(text) {
  let doc;
  try { doc = new DOMParser().parseFromString(text, 'application/xml'); } catch { throw new Error('Fichier illisible.'); }
  if (doc.querySelector('parsererror')) throw new Error('Le fichier BeerXML est invalide ou mal formé.');
  const recipeEls = [...doc.querySelectorAll('RECIPE')];
  if (!recipeEls.length) throw new Error('Aucune recette (balise RECIPE) trouvée dans ce fichier.');

  return recipeEls.map(rec => {
    const name = xtext(rec, 'NAME') || 'Recette importée';
    const styleEl = rec.querySelector(':scope > STYLE') || rec.querySelector('STYLE');
    const style = styleEl ? xtext(styleEl, 'NAME') : '';
    const equipEl = rec.querySelector('EQUIPMENT');
    const r = newRecipe();
    r.id = uid(); r.name = name; r.style = style;
    const st = upsertStyleFromBeerXmlEl(styleEl, '');
    if (st) r.styleId = st.id;
    r.profileId = ensureProfileFromEquipment(equipEl, rec, name);
    const rprof = D.profiles.find(x => x.id === r.profileId) || DEFAULT_P;
    r.ratio = rprof.ratio; r.eff = rprof.eff; r.infusionTemp = rprof.infusionTemp;
    r.batch = rnd(xnum(rec, 'BATCH_SIZE', 20), 2);
    r.co2 = xnum(rec, 'CARBONATION', 2.4) || 2.4;
    r.grainTemp = rnd(xnum(rec.querySelector('MASH'), 'GRAIN_TEMP', 20), 0);
    r.notes = xtext(rec, 'NOTES');

    r.ferm = [...rec.querySelectorAll('FERMENTABLES > FERMENTABLE')].map(el => {
      const nm = xtext(el, 'NAME') || 'Fermentescible';
      const type = {Grain: 'grain', Adjunct: 'grain', Sugar: 'sugar', Extract: 'extract', 'Dry Extract': 'extract'}[xtext(el, 'TYPE')] || 'grain';
      const ing = ensureIng('ferm', nm, {type, ebc: rnd(xnum(el, 'COLOR', 5) * 1.97, 1), extract: rnd(xnum(el, 'YIELD', 78), 0)});
      return {ref: ing.id, kg: rnd(xnum(el, 'AMOUNT', 0), 3)};
    });

    r.hops = [...rec.querySelectorAll('HOPS > HOP')].map(el => {
      const nm = xtext(el, 'NAME') || 'Houblon';
      const form = xtext(el, 'FORM') === 'Leaf' ? 'Cône entier' : 'Pellet';
      const ing = ensureIng('hop', nm, {alpha: rnd(xnum(el, 'ALPHA', 5), 1), form});
      ing.alpha = rnd(xnum(el, 'ALPHA', ing.alpha), 1); ing.form = form;
      const use = {Boil: 'boil', Mash: 'boil', 'First Wort': 'fwh', 'Dry Hop': 'dry', Aroma: 'whirl'}[xtext(el, 'USE')] || 'boil';
      let time = xnum(el, 'TIME', use === 'dry' ? 10080 : 60);
      time = use === 'dry' ? rnd(time / 1440, 1) : (use === 'fwh' ? 0 : rnd(time, 0));
      return {ref: ing.id, g: rnd(xnum(el, 'AMOUNT', 0) * 1000, 1), use, time};
    });

    r.yeast = [...rec.querySelectorAll('YEASTS > YEAST')].map(el => {
      const nm = xtext(el, 'NAME') || 'Levure';
      const type = xtext(el, 'TYPE') === 'Lager' ? 'lager' : 'ale', form = xtext(el, 'FORM') === 'Liquid' ? 'liquid' : 'dry';
      const att = rnd(xnum(el, 'ATTENUATION', 75), 0), tmin = rnd(xnum(el, 'MIN_TEMPERATURE', 15), 0), tmax = rnd(xnum(el, 'MAX_TEMPERATURE', 22), 0);
      const ing = ensureIng('yeast', nm, {type, form, att, tmin, tmax});
      ing.att = att; ing.tmin = tmin; ing.tmax = tmax;
      const amt = xnum(el, 'AMOUNT', null);
      return {ref: ing.id, qty: amt ? Math.max(1, Math.round(amt / 0.0115)) : 1};
    });

    r.misc = [...rec.querySelectorAll('MISCS > MISC')].map(el => {
      const nm = xtext(el, 'NAME') || 'Divers';
      const unit = xtext(el, 'AMOUNT_IS_WEIGHT').toLowerCase() === 'true' ? 'g' : 'mL';
      const ing = ensureIng('misc', nm, {unit});
      const use = {Mash: 'mash', Boil: 'boil', Primary: 'ferm', Secondary: 'ferm', Bottling: 'bottle'}[xtext(el, 'USE')] || 'boil';
      return {ref: ing.id, qty: rnd(xnum(el, 'AMOUNT', 0) * 1000, 2), use, time: rnd(xnum(el, 'TIME', 0), 0)};
    });

    const steps = [...rec.querySelectorAll('MASH MASH_STEPS > MASH_STEP')];
    if (steps.length) r.mash = steps.map(el => ({name: xtext(el, 'NAME') || 'Palier', temp: rnd(xnum(el, 'STEP_TEMP', 65), 0), time: rnd(xnum(el, 'STEP_TIME', 30), 0),
      method: {Infusion: 'infusion', Temperature: 'direct', Decoction: 'decoction'}[xtext(el, 'TYPE')] || 'infusion'}));

    const fsteps = [];
    const addStage = (ageTag, tempTag, label) => { const age = xnum(rec, ageTag, null); if (age !== null) fsteps.push({name: label, temp: rnd(xnum(rec, tempTag, 18), 0), days: rnd(age, 0)}); };
    addStage('PRIMARY_AGE', 'PRIMARY_TEMP', 'Fermentation primaire');
    addStage('SECONDARY_AGE', 'SECONDARY_TEMP', 'Fermentation secondaire');
    addStage('TERTIARY_AGE', 'TERTIARY_TEMP', 'Garde / tertiaire');
    if (fsteps.length) r.ferment = fsteps;

    r.created = r.updated = Date.now();
    D.recipes.push(r);
    return r;
  });
}

/* ---------- 7c. IMPORT BeerJSON (recettes) ----------
   Même principe que l'import BeerXML ci-dessus (ingrédients résolus/créés par nom, profil matériel dédié),
   mais pour le schéma BeerJSON : ingredients.{fermentable,hop,culture,miscellaneous}_additions, mash.mash_steps,
   fermentation.fermentation_steps. Réutilise les petits convertisseurs jval/jrange/jcolorRange de l'import
   de bibliothèque ci-dessus (section 7b), tolérants aux variantes de schéma usuelles. */
function ensureProfileFromBeerJSON(rec, recipeName) {
  const name = (rec.equipment && rec.equipment.name) || `Profil importé — ${recipeName}`;
  const existing = D.profiles.find(p => p.name === name);
  if (existing) return existing.id;
  const eff = jval(rec.efficiency && rec.efficiency.brewhouse) ?? 72;
  const boil = jval(rec.boil && rec.boil.boil_time) ?? 60;
  const batch = jval(rec.batch_size) ?? 20;
  const p = {id: uid(), name, batch: rnd(batch, 1), boil: rnd(boil, 0), evap: 3, trub: 1.5, mashLoss: 1, absorb: 1, ratio: 3, infusionTemp: 96, shrink: 4, eff: rnd(eff, 0)};
  D.profiles.push(p); return p.id;
}
function importBeerJSONRecipes(text) {
  let data; try { data = JSON.parse(text); } catch { throw new Error('Fichier JSON invalide.'); }
  const root = data.beerjson || data;
  const recipesArr = root.recipes || [];
  if (!recipesArr.length) throw new Error('Aucune recette trouvée dans ce fichier BeerJSON.');

  return recipesArr.map(rec => {
    const name = rec.name || 'Recette importée';
    const r = newRecipe();
    r.id = uid(); r.name = name;
    r.style = (rec.style && rec.style.name) || '';
    r.profileId = ensureProfileFromBeerJSON(rec, name);
    const prof = D.profiles.find(p => p.id === r.profileId) || DEFAULT_P;
    r.ratio = prof.ratio; r.eff = prof.eff; r.infusionTemp = prof.infusionTemp;
    r.batch = rnd(jval(rec.batch_size) ?? prof.batch, 2);
    r.co2 = jval(rec.carbonation) || 2.4;
    r.grainTemp = rnd(jval(rec.mash && rec.mash.grain_temperature) ?? 20, 0);
    r.notes = rec.notes || '';

    const ing = rec.ingredients || {};
    r.ferm = (ing.fermentable_additions || []).map(it => {
      const ty = String(it.type || '').toLowerCase();
      const type = ty.includes('sugar') ? 'sugar' : ty.includes('extract') ? 'extract' : 'grain';
      const [ebc] = jcolorRange(it.color);
      const extractPct = jval(it.yield && (it.yield.fine_grind || it.yield)) ?? (type === 'sugar' ? 100 : type === 'extract' ? 96 : 78);
      const g = ensureIng('ferm', it.name || 'Fermentescible', {type, ebc: ebc ?? (type === 'grain' ? 8 : 0), extract: rnd(extractPct, 0)});
      return {ref: g.id, kg: rnd(jval(it.amount) ?? 0, 3)};
    });
    r.hops = (ing.hop_additions || []).map(it => {
      const alpha = jval(it.alpha_acid) ?? 5, form = /leaf/i.test(it.form || '') ? 'Cône entier' : 'Pellet';
      const h = ensureIng('hop', it.name || 'Houblon', {alpha: rnd(alpha, 1), form});
      h.alpha = rnd(alpha, 1); h.form = form;
      const timing = it.timing || {}, useRaw = timing.use || 'add_to_boil';
      const durVal = jval(timing.duration), durUnit = junit(timing.duration);
      let use = 'boil', time = durVal ?? 60, temp = null;
      if (useRaw === 'add_to_whirlpool') { const t = jval(timing.temperature); if (t != null) { use = 'hopstand'; temp = t; } else use = 'whirl'; }
      else if (useRaw === 'add_to_fermentation' || useRaw === 'add_to_package') { use = 'dry'; time = durUnit.includes('min') ? rnd((durVal || 0) / 1440, 1) : (durVal ?? 7); }
      const row = {ref: h.id, g: rnd((jval(it.amount) ?? 0) * 1000, 1), use, time: rnd(time ?? 0, use === 'dry' ? 1 : 0)};
      if (temp != null) row.temp = rnd(temp, 1);
      return row;
    });
    r.yeast = (ing.culture_additions || []).map(it => {
      const type = /lager/i.test(it.type || '') ? 'lager' : 'ale', form = /liquid/i.test(it.form || '') ? 'liquid' : 'dry';
      const [aMin, aMax] = jrange(it.attenuation_range || it.attenuation);
      const att = (aMin != null && aMax != null) ? (aMin + aMax) / 2 : (aMin ?? aMax ?? 75);
      const [tMin, tMax] = jrange(it.temperature_range);
      const y = ensureIng('yeast', it.name || 'Levure', {type, form, att: rnd(att, 0), tmin: rnd(tMin ?? 15, 0), tmax: rnd(tMax ?? 22, 0)});
      y.att = rnd(att, 0); y.tmin = rnd(tMin ?? 15, 0); y.tmax = rnd(tMax ?? 22, 0);
      const unit = ((it.amount && it.amount.unit) || '').toLowerCase(), amtVal = jval(it.amount);
      const qty = (unit === 'pkg' || unit === 'each' || unit === '') ? Math.max(1, Math.round(amtVal ?? 1)) : Math.max(1, Math.round((amtVal ?? 0.0115) / 0.0115));
      return {ref: y.id, qty};
    });
    r.misc = (ing.miscellaneous_ingredients || []).map(it => {
      const unitRaw = ((it.amount && it.amount.unit) || '').toLowerCase();
      const unit = unitRaw === 'l' ? 'mL' : 'g';
      const m = ensureIng('misc', it.name || 'Divers', {unit});
      const useRaw = (it.timing && it.timing.use) || 'add_to_boil';
      const use = {add_to_mash: 'mash', add_to_boil: 'boil', add_to_fermentation: 'ferm', add_to_package: 'bottle'}[useRaw] || 'boil';
      let qty = jval(it.amount) ?? 0;
      if (unitRaw === 'kg' || unitRaw === 'l') qty *= 1000;
      return {ref: m.id, qty: rnd(qty, 2), use, time: rnd(jval(it.timing && it.timing.duration) ?? 0, 0)};
    });

    const steps = (rec.mash && rec.mash.mash_steps) || [];
    if (steps.length) r.mash = steps.map(s => ({name: s.name || 'Palier', temp: rnd(jval(s.step_temperature) ?? 65, 0), time: rnd(jval(s.step_time) ?? 30, 0),
      method: {infusion: 'infusion', temperature: 'direct', decoction: 'decoction'}[String(s.type || '').toLowerCase()] || 'infusion'}));

    const fsteps = (rec.fermentation && rec.fermentation.fermentation_steps) || [];
    if (fsteps.length) r.ferment = fsteps.map(s => ({name: s.name || 'Étape', temp: rnd(jval(s.start_temperature) ?? 18, 0), days: rnd(jval(s.step_time) ?? 7, 0)}));

    r.created = r.updated = Date.now();
    D.recipes.push(r);
    return r;
  });
}

/* ---------- 7. EXPORTS : BeerXML 1.0 et BeerJSON 1.0 ---------- */
function saveFile(name, text, mime) {
  const url = URL.createObjectURL(new Blob([text], {type: mime}));
  const a = document.createElement('a'); a.href = url; a.download = name; document.body.appendChild(a); a.click(); a.remove();
  setTimeout(() => URL.revokeObjectURL(url), 1500);
}
function copyText(t) {
  if (navigator.clipboard && navigator.clipboard.writeText) return navigator.clipboard.writeText(t).then(() => toast('Copié')).catch(() => fallbackCopy());
  fallbackCopy();
  function fallbackCopy() { const ta = $('#exptxt'); ta.select(); try { document.execCommand('copy'); toast('Copié'); } catch { toast('Sélectionnez le texte et copiez-le manuellement.'); } }
}
async function shareToBrewRecipes() {
  const url = brewrecipesShareUrl();
  if (!url) { toast('Partage BrewRecipes indisponible.'); return; }
  const r = R();
  if (!r) { toast('Ouvrez une recette.'); return; }
  const xml = toBeerXML(r);
  toast('Envoi vers BrewRecipes…');
  try {
    const res = await fetch(url, {
      method: 'POST',
      credentials: 'include',
      headers: {'Content-Type': 'application/xml; charset=UTF-8'},
      body: xml,
      redirect: 'follow',
    });
    if (res.redirected || res.ok) {
      window.location.href = res.url;
      return;
    }
    const msg = (await res.text()).trim();
    toast(msg.slice(0, 160) || `Erreur BrewRecipes (${res.status})`);
  } catch {
    toast('Impossible de contacter BrewRecipes.');
  }
}

/** Sélecteur de style BrewRecipes (guide → liste → Enregistrer). */
async function openBrewRecipesStylePicker() {
  const guidesUrl = brewrecipesStyleGuidesUrl(), stylesUrl = brewrecipesStylesUrl();
  if (!guidesUrl || !stylesUrl) { toast('Catalogue BrewRecipes indisponible.'); return; }
  const r = R();
  if (!r) { toast('Ouvrez une recette.'); return; }
  let stylesCache = [], selectedIdx = null;
  dlgSave = () => {
    if (selectedIdx == null || !stylesCache[selectedIdx]) { toast('Choisissez un style dans la liste.'); return; }
    applyBrewRecipesStyle(stylesCache[selectedIdx]);
  };
  showDlg(`<h2>Style BrewRecipes</h2>
    <p class="sub" style="margin-top:0">Choisissez un guide, puis un style : les données sont copiées dans votre bibliothèque HopHop et associées à cette recette.</p>
    <label class="sub" for="br-guide">Guide</label>
    <select id="br-guide" style="width:100%;margin:.3rem 0 .6rem" disabled><option>Chargement…</option></select>
    <label class="sub" for="br-style-q">Rechercher</label>
    <input id="br-style-q" type="search" placeholder="Nom, code (ex. IPA, 21A)…" autocomplete="off" disabled style="width:100%;margin:.3rem 0 .6rem">
    <div id="br-styles" class="sub">Sélectionnez un guide.</div>
    <div class="dlg-actions"><button class="btn ghost" data-act="dlgClose">Annuler</button><button class="btn" id="br-style-save" data-act="dlgSave" disabled>Enregistrer</button></div>`);
  const guideEl = $('#br-guide'), listEl = $('#br-styles'), qEl = $('#br-style-q'), saveEl = $('#br-style-save');
  const syncSave = () => { saveEl.disabled = selectedIdx == null || !stylesCache[selectedIdx]; };
  const renderStyles = () => {
    if (!stylesCache.length) { syncSave(); return; }
    const needle = (qEl.value || '').trim().toLowerCase();
    const matches = stylesCache
      .map((s, i) => ({s, i}))
      .filter(({s}) => !needle || String(s.label || s.name || '').toLowerCase().includes(needle)
        || String(s.name || '').toLowerCase().includes(needle)
        || String(s.category || '').toLowerCase().includes(needle));
    if (selectedIdx != null && !matches.some(m => m.i === selectedIdx)) selectedIdx = null;
    listEl.innerHTML = matches.length
      ? `<div style="max-height:16rem;overflow:auto;display:flex;flex-direction:column;gap:.35rem" role="radiogroup" aria-label="Styles">`
        + matches.map(({s, i}) => `<label class="br-style-row"><input type="radio" name="br-style" value="${i}"${selectedIdx === i ? ' checked' : ''}><span>${esc(s.label || s.name)}</span></label>`).join('')
        + `</div>`
      : '<p class="sub" style="margin:0">Aucun style ne correspond.</p>';
    listEl.querySelectorAll('input[name="br-style"]').forEach(inp => {
      inp.onchange = () => { selectedIdx = +inp.value; syncSave(); };
    });
    syncSave();
  };
  qEl.oninput = () => renderStyles();
  try {
    const res = await fetch(guidesUrl, {credentials: 'include'});
    if (!res.ok) throw new Error('HTTP ' + res.status);
    const data = await res.json();
    const guides = data.guides || [];
    if (!guides.length) {
      guideEl.innerHTML = '<option value="">Aucun guide</option>';
      listEl.textContent = 'Aucun guide de styles dans BrewRecipes.';
      return;
    }
    guideEl.innerHTML = '<option value="">— Choisir un guide —</option>'
      + guides.map(g => `<option value="${esc(g)}">${esc(g)}</option>`).join('');
    guideEl.disabled = false;
    const load = async (guide) => {
      stylesCache = [];
      selectedIdx = null;
      qEl.value = '';
      qEl.disabled = true;
      syncSave();
      await loadBrewRecipesStylesList(guide, listEl, stylesUrl, (styles) => {
        stylesCache = styles || [];
        qEl.disabled = !stylesCache.length;
        renderStyles();
        if (stylesCache.length) qEl.focus();
      });
    };
    const bjcp = guides.find(g => /^BJCP/i.test(g));
    if (bjcp) { guideEl.value = bjcp; await load(bjcp); }
    guideEl.onchange = () => load(guideEl.value);
  } catch {
    guideEl.innerHTML = '<option value="">Erreur</option>';
    listEl.textContent = 'Impossible de charger les guides BrewRecipes.';
  }
}

async function loadBrewRecipesStylesList(guide, listEl, stylesUrl, onLoaded) {
  if (!guide) {
    listEl.innerHTML = '<p class="sub">Sélectionnez un guide.</p>';
    onLoaded([]);
    return;
  }
  listEl.innerHTML = '<p class="sub">Chargement des styles…</p>';
  try {
    const sep = stylesUrl.includes('?') ? '&' : '?';
    const res = await fetch(stylesUrl + sep + 'guide=' + encodeURIComponent(guide), {credentials: 'include'});
    if (!res.ok) throw new Error('HTTP ' + res.status);
    const data = await res.json();
    const styles = data.styles || [];
    if (!styles.length) {
      listEl.innerHTML = '<p class="sub">Aucun style pour ce guide.</p>';
      onLoaded([]);
      return;
    }
    onLoaded(styles);
  } catch {
    listEl.innerHTML = '<p class="sub">Erreur de chargement des styles.</p>';
    onLoaded([]);
  }
}

function applyBrewRecipesStyle(style) {
  const r = R();
  if (!r || !style || !style.name) { toast('Style invalide.'); return; }
  const st = upsertStyle(style.name, style.guide || '', {
    category: style.category || '',
    type: style.type || 'other',
    ogMin: N(style.ogMin), ogMax: N(style.ogMax),
    fgMin: N(style.fgMin), fgMax: N(style.fgMax),
    ibuMin: N(style.ibuMin), ibuMax: N(style.ibuMax),
    colorMin: N(style.colorMin), colorMax: N(style.colorMax),
    abvMin: N(style.abvMin), abvMax: N(style.abvMax),
    note: style.note || '',
  });
  if (!st) { toast('Impossible d’enregistrer le style.'); return; }
  r.styleId = st.id;
  r.style = st.name;
  r.updated = Date.now();
  closeDlg();
  persist();
  renderMain(true);
  toast('Style « ' + styleComposite(st) + ' » associé à la recette.');
}
/** « 21A » → numéro + lettre. `category` stocke ce code, ou un nom de catégorie s’il n’y a pas de code. */
function splitStyleCode(category) {
  const c = String(category || '').trim();
  const m = c.match(/^(\d{1,3})\s*([A-Za-z])$/);
  if (m) return {number: m[1], letter: m[2].toUpperCase(), label: ''};
  if (/^\d{1,3}$/.test(c)) return {number: c, letter: '', label: ''};
  return {number: '', letter: '', label: c};
}
const STYLE_XML_TYPE = {ale: 'Ale', lager: 'Lager', mixed: 'Mixed', cider: 'Cider', mead: 'Mead', other: 'Ale'};
/** Bloc BeerXML <STYLE> : nom, code (CATEGORY_NUMBER + STYLE_LETTER) et guide, pour que BrewRecipes retrouve le style. */
function beerXmlStyle(r, X) {
  const st = recipeStyleRef(r);
  const name = (st && st.name) || String(r.style || '').trim();
  if (!name) return '';
  const code = splitStyleCode(st ? st.category : '');
  const srm = v => rnd(N(v) / 1.97, 1);
  let inner = X('NAME', name) + X('VERSION', 1) + X('CATEGORY', code.label);
  if (code.number) inner += X('CATEGORY_NUMBER', code.number);
  if (code.letter) inner += X('STYLE_LETTER', code.letter);
  if (st && st.guide) inner += X('STYLE_GUIDE', st.guide);
  inner += X('TYPE', STYLE_XML_TYPE[st && st.type] || 'Ale');
  if (st) {
    inner += X('OG_MIN', st.ogMin) + X('OG_MAX', st.ogMax) + X('FG_MIN', st.fgMin) + X('FG_MAX', st.fgMax)
      + X('IBU_MIN', st.ibuMin) + X('IBU_MAX', st.ibuMax)
      + X('COLOR_MIN', srm(st.colorMin)) + X('COLOR_MAX', srm(st.colorMax))
      + X('ABV_MIN', st.abvMin) + X('ABV_MAX', st.abvMax);
    if (st.note) inner += X('NOTES', st.note);
  }
  return `<STYLE>${inner}</STYLE>`;
}
function beerJsonStyle(r) {
  const st = recipeStyleRef(r);
  const name = (st && st.name) || String(r.style || '').trim();
  if (!name) return null;
  const code = splitStyleCode(st ? st.category : '');
  const range = (a, b, unit) => ({minimum: {value: rnd(N(a), 4), unit}, maximum: {value: rnd(N(b), 4), unit}});
  const style = {name, type: (st && (st.type === 'mead' || st.type === 'cider')) ? st.type : 'beer', category: code.label || name};
  if (st && st.guide) style.style_guide = st.guide;
  if (code.number) style.category_number = Number(code.number);
  if (code.letter) style.style_letter = code.letter;
  if (st) {
    style.original_gravity = range(st.ogMin, st.ogMax, 'sg');
    style.final_gravity = range(st.fgMin, st.fgMax, 'sg');
    style.international_bitterness_units = range(st.ibuMin, st.ibuMax, 'IBUs');
    style.color = range(st.colorMin, st.colorMax, 'EBC');
    style.alcohol_by_volume = range(st.abvMin, st.abvMax, '%');
    if (st.note) style.notes = st.note;
  }
  return style;
}
function showExport(kind) {
  const r = R(), xml = kind === 'xml', txt = xml ? toBeerXML(r) : toBeerJSON(r), name = slug(r.name) + (xml ? '.xml' : '.json');
  dlgSave = null;
  showDlg(`<h2>Export ${xml ? 'BeerXML 1.0' : 'BeerJSON 1.0'}</h2><textarea id="exptxt" readonly style="min-height:14rem;font-family:ui-monospace,Menlo,monospace;font-size:.8rem">${esc(txt)}</textarea>
    <div class="dlg-actions"><button class="btn ghost" data-act="dlgClose">Fermer</button><button class="btn ghost" id="cpy">Copier</button><button class="btn" id="sv">Enregistrer ${esc(name)}</button></div>`);
  $('#cpy').onclick = () => copyText(txt);
  $('#sv').onclick = () => saveFile(name, txt, xml ? 'application/xml' : 'application/json');
}
function toBeerXML(r) {
  const c = calc(r), P = c.P, X = (t, v) => `<${t}>${esc(v)}</${t}>`, kg = x => rnd(x / 1000, 5);
  const sec = (tag, items) => `<${tag}>${items.join('')}</${tag}>`;
  const ferm = r.ferm.map(x => { const g = lib('ferm', x.ref); return g && `<FERMENTABLE>${X('NAME', g.name)}${X('VERSION', 1)}${X('TYPE', {grain: 'Grain', sugar: 'Sugar', extract: 'Dry Extract'}[g.type])}${X('AMOUNT', rnd(N(x.kg), 4))}${X('YIELD', g.extract)}${X('COLOR', rnd(g.ebc / 1.97, 1))}${X('ADD_AFTER_BOIL', 'FALSE')}</FERMENTABLE>`; }).filter(Boolean);
  const hops = r.hops.map(x => { const h = lib('hop', x.ref); if (!h) return null;
    const use = {boil: 'Boil', fwh: 'First Wort', whirl: 'Aroma', hopstand: 'Aroma', dry: 'Dry Hop'}[x.use], time = x.use === 'fwh' ? P.boil : x.use === 'dry' ? N(x.time) * 1440 : N(x.time);
    return `<HOP>${X('NAME', h.name)}${X('VERSION', 1)}${X('ALPHA', h.alpha)}${X('AMOUNT', kg(N(x.g)))}${X('USE', use)}${X('TIME', time)}${X('FORM', h.form === 'Pellet' ? 'Pellet' : 'Leaf')}</HOP>`; }).filter(Boolean);
  const miscs = r.misc.map(x => { const m = lib('misc', x.ref); if (!m) return null; const w = m.unit !== 'mL', q = N(x.qty);
    return `<MISC>${X('NAME', m.name)}${X('VERSION', 1)}${X('TYPE', 'Other')}${X('USE', {mash: 'Mash', boil: 'Boil', ferm: 'Primary', bottle: 'Bottling'}[x.use])}${X('TIME', N(x.time))}${X('AMOUNT', m.unit === 'g' ? kg(q) : m.unit === 'mL' ? rnd(q / 1000, 5) : q)}${X('AMOUNT_IS_WEIGHT', w ? 'TRUE' : 'FALSE')}</MISC>`; }).filter(Boolean);
  const yeasts = r.yeast.map(x => { const y = lib('yeast', x.ref); return y && `<YEAST>${X('NAME', y.name)}${X('VERSION', 1)}${X('TYPE', y.type === 'lager' ? 'Lager' : 'Ale')}${X('FORM', y.form === 'liquid' ? 'Liquid' : 'Dry')}${X('AMOUNT', rnd(N(x.qty) * 0.0115, 4))}${X('AMOUNT_IS_WEIGHT', 'TRUE')}${X('ATTENUATION', y.att)}${X('MIN_TEMPERATURE', y.tmin)}${X('MAX_TEMPERATURE', y.tmax)}</YEAST>`; }).filter(Boolean);
  const steps = r.mash.map((s, i) => `<MASH_STEP>${X('NAME', s.name)}${X('VERSION', 1)}${X('TYPE', i === 0 ? 'Infusion' : {infusion: 'Infusion', direct: 'Temperature', decoction: 'Decoction'}[s.method || 'infusion'])}${X('STEP_TEMP', N(s.temp))}${X('STEP_TIME', N(s.time))}</MASH_STEP>`);
  const xmlDecl = '<' + '?xml version="1.0" encoding="UTF-8"?>';
  return `${xmlDecl}\n<RECIPES>\n<RECIPE>${X('VERSION', 1)}${X('NAME', r.name)}${X('TYPE', r.ferm.some(x => lib('ferm', x.ref)?.type === 'grain') ? 'All Grain' : 'Extract')}${X('BREWER', USER)}
${beerXmlStyle(r, X)}
${X('BATCH_SIZE', rnd(c.V, 2))}${X('BOIL_SIZE', rnd(c.preboil, 2))}${X('BOIL_TIME', P.boil)}${X('EFFICIENCY', rnd(c.effPct, 0))}
${sec('HOPS', hops)}${sec('FERMENTABLES', ferm)}${sec('MISCS', miscs)}${sec('YEASTS', yeasts)}
<EQUIPMENT>${X('NAME', P.name)}${X('VERSION', 1)}${X('BOIL_SIZE', rnd(c.preboil, 2))}${X('BATCH_SIZE', rnd(c.V, 2))}${X('TRUB_CHILLER_LOSS', P.trub)}${X('EVAP_RATE', rnd(P.evap / c.preboil * 100, 2))}${X('BOIL_TIME', P.boil)}</EQUIPMENT>
<MASH>${X('NAME', 'Empâtage')}${X('VERSION', 1)}${X('GRAIN_TEMP', N(r.grainTemp))}<MASH_STEPS>${steps.join('')}</MASH_STEPS></MASH>
${X('NOTES', r.notes || '')}${X('OG', rnd(c.og, 4))}${X('FG', rnd(c.fg, 4))}${X('IBU', rnd(c.ibu, 1))}${X('EST_ABV', rnd(c.abv, 2))}${X('CARBONATION', N(r.co2))}${X('FERMENTATION_STAGES', r.ferment.length)}
${r.ferment[0] ? X('PRIMARY_AGE', N(r.ferment[0].days)) + X('PRIMARY_TEMP', N(r.ferment[0].temp)) : ''}
</RECIPE>\n</RECIPES>\n`;
}
function toBeerJSON(r) {
  const c = calc(r), P = c.P, q = (v, unit) => ({value: rnd(+v, 4), unit});
  const use = {mash: 'add_to_mash', boil: 'add_to_boil', ferm: 'add_to_fermentation', bottle: 'add_to_package'};
  const style = beerJsonStyle(r);
  const rec = {
    name: r.name, type: r.ferm.some(x => lib('ferm', x.ref)?.type === 'grain') ? 'all grain' : 'extract', author: USER,
    ...(style ? {style} : {}),
    batch_size: q(c.V, 'l'), efficiency: {brewhouse: q(c.effPct, '%')},
    boil: {name: 'Ébullition', pre_boil_size: q(c.preboil, 'l'), boil_time: q(P.boil, 'min')},
    ingredients: {
      fermentable_additions: r.ferm.map(x => { const g = lib('ferm', x.ref); return g && {name: g.name, type: {grain: 'grain', sugar: 'sugar', extract: 'dry extract'}[g.type], color: q(g.ebc, 'EBC'), amount: q(N(x.kg), 'kg'), yield: {fine_grind: q(g.extract, '%')}}; }).filter(Boolean),
      hop_additions: r.hops.map(x => { const h = lib('hop', x.ref); if (!h) return null;
        const dry = x.use === 'dry', dur = x.use === 'fwh' ? P.boil : N(x.time);
        const useMap = {boil: 'add_to_boil', fwh: 'add_to_boil', whirl: 'add_to_whirlpool', hopstand: 'add_to_whirlpool', dry: 'add_to_fermentation'};
        const timing = {use: useMap[x.use] || 'add_to_boil', duration: q(dur, dry ? 'day' : 'min')};
        if (x.use === 'hopstand' || (dry && x.temp != null && x.temp !== '')) timing.temperature = q(N(x.temp), 'C');
        return {name: h.name, alpha_acid: q(h.alpha, '%'), amount: q(N(x.g), 'g'), form: h.form === 'Pellet' ? 'pellet' : 'leaf', timing}; }).filter(Boolean),
      culture_additions: r.yeast.map(x => { const y = lib('yeast', x.ref); return y && {name: y.name, type: y.type === 'lager' ? 'lager' : 'ale', form: y.form === 'liquid' ? 'liquid' : 'dry', amount: q(N(x.qty), 'pkg'), attenuation: q(y.att, '%'),
        temperature_range: {minimum: q(y.tmin, 'C'), maximum: q(y.tmax, 'C')}}; }).filter(Boolean),
      miscellaneous_additions: r.misc.map(x => { const m = lib('misc', x.ref); return m && {name: m.name, type: 'other', timing: {use: use[x.use], duration: q(N(x.time), 'min')},
        amount: q(N(x.qty), m.unit === 'mL' ? 'ml' : m.unit === 'g' ? 'g' : 'each')}; }).filter(Boolean)
    },
    mash: {name: 'Empâtage', grain_temperature: q(N(r.grainTemp), 'C'), mash_steps: r.mash.map((s, i) => ({name: s.name, type: i === 0 ? 'infusion' : {infusion: 'infusion', direct: 'temperature', decoction: 'decoction'}[s.method || 'infusion'], step_temperature: q(N(s.temp), 'C'), step_time: q(N(s.time), 'min')}))},
    fermentation: {name: 'Fermentation', fermentation_steps: r.ferment.map(s => ({name: s.name, start_temperature: q(N(s.temp), 'C'), step_time: q(N(s.days), 'day')}))},
    original_gravity: q(c.og, 'sg'), final_gravity: q(c.fg, 'sg'), alcohol_by_volume: q(c.abv, '%'), ibu_estimate: {value: rnd(c.ibu, 1), unit: 'IBUs'}, color_estimate: q(c.ebc, 'EBC'),
    carbonation: N(r.co2), notes: r.notes || ''
  };
  return JSON.stringify({beerjson: {version: 1, recipes: [rec]}}, null, 2);
}

/* ---------- 8. DÉMARRAGE ---------- */
(async function init() {
  try {
    const s = await api('session');
    applySessionMeta(s);
    if (s.username) {
      try { await enterApp(s.username); }
      catch (e) {
        USER = D = null;
        toast(e.message || 'Impossible de charger vos données.');
        render();
      }
    } else render();
  } catch (e) {
    USER = D = null;
    toast(e.message || 'Impossible de contacter le serveur.');
    render();
  }
})();
</script>
</body>
</html>
