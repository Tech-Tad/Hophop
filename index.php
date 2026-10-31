<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/i18n.php';

// Logo SVG (includes/img/logo.svg), inliné côté serveur pour pouvoir le recolorer en CSS (currentColor)
// selon le thème actif — un simple <img src="..."> ne le permettrait pas.
$logoPath = __DIR__ . '/includes/img/logo.svg';
$LOGO_SVG = is_file($logoPath) ? file_get_contents($logoPath) : '';
// Favicon : même logo, couleur fixe (un onglet de navigateur ne peut pas hériter des variables CSS du thème).
$FAVICON_SVG = $LOGO_SVG !== '' ? str_replace('fill="currentColor"', 'fill="#2d4a2d"', $LOGO_SVG) : '';

// ${t('lang_title')} : préférence enregistrée du compte (si connecté) sinon détection du navigateur, repli sur le français.
$AVAILABLE_LANGS = hh_available_langs();
$SAVED_LANG = null;
$ctx0 = function_exists('hophop_user_context') ? hophop_user_context() : null;
if ($ctx0) {
    $d0 = hophop_user_store_load($ctx0);
    if (is_array($d0) && isset($d0['prefs']['lang'])) {
        $SAVED_LANG = (string) $d0['prefs']['lang'];
    }
} elseif (($CUR_USER0 = current_user())) {
    $d0 = read_json(user_file($CUR_USER0), null);
    if (is_array($d0) && isset($d0['prefs']['lang'])) {
        $SAVED_LANG = (string) $d0['prefs']['lang'];
    }
}
$CUR_LANG = hh_resolve_lang($SAVED_LANG, $AVAILABLE_LANGS);
$LANG = hh_lang_strings($CUR_LANG);
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($CUR_LANG); ?>" id="htmlroot">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title id="pagetitle"><?php echo htmlspecialchars($LANG['app_title'] ?? 'HopHop'); ?></title>
<?php if ($FAVICON_SVG): ?>
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml;base64,<?php echo base64_encode($FAVICON_SVG); ?>">
<?php endif; ?>
<style>
/* ==========================================================================
   BRASSIN — styles
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
body{margin:0;background:var(--bg);color:var(--text);font:16px/1.5 var(--sans);-webkit-text-size-adjust:100%}
h1,h2,h3{font-family:var(--serif);color:var(--head);margin:0;font-weight:600;letter-spacing:.005em}
h1{font-size:1.6rem} h2{font-size:1.2rem;margin-bottom:.6rem} h3{font-size:1rem;margin:.9rem 0 .3rem}
button{font:inherit;cursor:pointer}
.wrap{max-width:1120px;margin:0 auto;padding:1rem}
.sitefooter{padding:1.2rem 1rem 2rem}

.top{position:sticky;top:0;z-index:20;background:var(--surface);border-bottom:1px solid var(--line)}
.top .in{max-width:1120px;margin:0 auto;padding:.6rem 1rem 0;display:flex;align-items:center;gap:.8rem}
.brand{font-family:var(--serif);font-size:1.35rem;color:var(--head);display:flex;align-items:center;gap:.5rem;font-weight:700}
.brand-logo{width:1.7rem;height:1.7rem;display:inline-flex;flex:none;color:var(--head)}
.brand-logo svg{width:100%;height:100%;display:block}
.grow{flex:1}
.top-forum{flex:none;white-space:normal;text-align:center;text-decoration:none;box-sizing:border-box}
.who{font-size:.9rem;color:var(--muted);display:flex;align-items:center;gap:.4rem}
.avatar{width:1.8rem;height:1.8rem;border-radius:50%;background:var(--soft);display:flex;align-items:center;justify-content:center;font-size:1.05rem;flex:none;overflow:hidden;line-height:1}
.avatar img{width:100%;height:100%;object-fit:cover;display:block}
.avatar.lg{width:4.5rem;height:4.5rem;font-size:2.2rem}
.avatar-picks{display:flex;flex-wrap:wrap;gap:.4rem;margin:.5rem 0}
.avatar-picks button{width:2.4rem;height:2.4rem;border-radius:50%;border:1px solid var(--line);background:var(--surface);font-size:1.2rem;display:flex;align-items:center;justify-content:center;padding:0}
.avatar-picks button[aria-pressed="true"]{border-color:var(--accent);box-shadow:0 0 0 2px var(--soft)}
.savestate{font-size:.78rem;color:var(--muted);min-width:5.5em;text-align:right}
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

input,select,textarea{width:100%;padding:.55rem .65rem;border:1px solid var(--line);border-radius:8px;background:var(--surface);color:var(--text);font:inherit;min-width:0}
textarea{min-height:5rem;resize:vertical}
.fld{display:flex;flex-direction:column;gap:.2rem;min-width:0}
.fld>span{font-size:.78rem;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.03em}
output{padding:.55rem 0;font-weight:600;color:var(--head)}
.fgrid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.8rem}
.fld.span2{grid-column:span 2}
.fld.full{grid-column:1/-1}
.bar{display:flex;flex-wrap:wrap;align-items:center;gap:.6rem;margin-bottom:1rem}
.search{max-width:260px}

.card{background:var(--surface);border:1px solid var(--line);border-radius:14px;padding:1rem;margin-bottom:1rem}
.cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:1rem}
.cards>.card{min-width:0}
.cards>.card .dlg-actions{flex-wrap:wrap;justify-content:flex-start;gap:.4rem}
.rcard{cursor:pointer;display:flex;flex-direction:column;gap:.5rem;text-align:left;width:100%;color:inherit;margin:0}
.rcard:hover{border-color:var(--accent)}
.rcard .meta{display:flex;flex-wrap:wrap;gap:.35rem}
.chip{display:inline-block;background:var(--okbg);color:var(--head);border-radius:99px;padding:.1rem .6rem;font-size:.8rem;font-weight:600}
.chip.bad{background:var(--dangerbg);color:var(--danger)}
.sub{color:var(--muted);font-size:.9rem}
.empty{text-align:center;color:var(--muted);padding:2.5rem 1rem;border:2px dashed var(--line);border-radius:14px}
.sw{width:22px;height:22px;border-radius:50%;border:2px solid var(--surface);box-shadow:0 0 0 1px var(--line);display:inline-block;vertical-align:middle}

.editor{display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:1rem;align-items:start}
.stats{position:sticky;top:112px}
@media (max-width:860px){.editor{grid-template-columns:1fr}.stats{position:static;order:-1}}
.namefield{font-family:var(--serif);font-size:1.4rem;font-weight:600}
.row{display:grid;grid-template-columns:repeat(auto-fit,minmax(115px,1fr));gap:.6rem;align-items:end;padding:.7rem 0;border-top:1px solid var(--line)}
.row .rowhead{grid-column:1/-1;display:flex;gap:.5rem}
.row .hint{grid-column:1/-1;min-height:0}
.row .hint:empty{display:none}
.secthead{display:flex;align-items:center;gap:.6rem;margin-bottom:.2rem}
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
.foot-usage{text-align:center;color:var(--muted);font-size:.75rem;margin:1.6rem 0 0;opacity:.72}
.style-line{display:flex;gap:.4rem;align-items:center;flex-wrap:wrap}
.style-line select{flex:1 1 12rem;min-width:0;width:auto}
.br-style-row{display:flex;align-items:center;gap:.45rem;cursor:pointer;color:var(--text);font-size:.95rem;margin:0}
.br-style-row input[type=radio]{width:auto;min-width:0;flex:none;margin:0;padding:0;accent-color:var(--accent)}
.account-id{display:flex;gap:.8rem;align-items:center;min-width:0}
.account-id > div{min-width:0}

.tbl{width:100%;border-collapse:collapse;font-size:.95rem}
.tbl th{text-align:left;font-size:.75rem;text-transform:uppercase;color:var(--muted);letter-spacing:.04em;padding:.4rem .5rem;border-bottom:2px solid var(--line)}
.tbl td{padding:.5rem;border-bottom:1px solid var(--line);vertical-align:middle}
.tbl input{padding:.35rem .5rem;max-width:110px}
.scroll{overflow-x:auto}
.seg{display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:1rem}
.seg button{border:1px solid var(--line);background:var(--surface);color:var(--text);border-radius:99px;padding:.35rem .9rem}
.seg button[aria-pressed="true"]{background:var(--btn);color:var(--btnfg);border-color:var(--btn)}

.sheet h1{font-size:1.9rem}
.sheet ol{padding-left:1.3rem} .sheet li{margin:.4rem 0}

dialog{border:1px solid var(--line);border-radius:14px;background:var(--surface);color:var(--text);padding:1.2rem;width:min(560px,94vw);max-height:90vh;overflow:auto}
dialog::backdrop{background:rgba(20,30,20,.5)}
.dlg-actions{display:flex;justify-content:flex-end;gap:.6rem;margin-top:1rem}
/* Sous-formulaire « Autres notes » (brassins, activable dans Compte) */
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
details.stat-sec{margin:.9rem 0 .3rem}
details.stat-sec>summary{cursor:pointer;font-family:var(--serif);color:var(--head);font-weight:600;font-size:1rem;letter-spacing:.005em;list-style-position:outside}
details.stat-sec>summary::-webkit-details-marker{color:var(--accent)}
details.stat-sec[open]>summary{margin-bottom:.3rem}
details.stat-sec+details.stat-sec{margin-top:.6rem}
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
  .cards{display:block}
  .card{border:1px solid #bbb;break-inside:avoid;background:#fff;padding:.5rem;margin-bottom:.4rem;border-radius:4px;width:100%}
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
  .print-hide{display:none!important}
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
</style>
</head>
<body>
<div id="app"><div class="wrap"><p class="sub"><?php echo htmlspecialchars($LANG['loading'] ?? t('loading_app')); ?></p></div></div>
<dialog id="dlg"></dialog>
<div id="toast" role="status"></div>

<script>
'use strict';
const LOGO_SVG = <?php echo json_encode($LOGO_SVG, JSON_UNESCAPED_SLASHES); ?>;   // logo includes/img/logo.svg, inliné côté serveur (recolorable en CSS)
/* ==========================================================================
   BRASSIN — application de calcul et de suivi de recettes de bière
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
/* ---------- 0. I18N ---------- */
const I18N = <?php echo json_encode($LANG, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
const CUR_LANG = <?php echo json_encode($CUR_LANG); ?>;
const AVAILABLE_LANGS = <?php echo json_encode($AVAILABLE_LANGS); ?>;
const LANG_NATIVE_NAMES = {fr: 'Français', en: 'English', es: 'Español', pt: 'Português', it: 'Italiano', de: 'Deutsch'};
const LOCALE_MAP = {fr: 'fr-FR', en: 'en-US', es: 'es-ES', pt: 'pt-PT', it: 'it-IT', de: 'de-DE'};
function t(key, vars) {
  let str = (I18N && I18N[key]) || key;
  if (vars) Object.keys(vars).forEach(k => { str = str.split('{' + k + '}').join(vars[k]); });
  return str;
}
const f = (n, d = 1) => isFinite(n) ? Number(n).toLocaleString(LOCALE_MAP[CUR_LANG] || 'fr-FR', {minimumFractionDigits: d, maximumFractionDigits: d}) : '–';
const rnd = (x, d) => Math.round(x * 10 ** d) / 10 ** d;
const today = () => new Date().toISOString().slice(0, 10);
const fdate = iso => iso ? new Date(iso + 'T12:00:00').toLocaleDateString(LOCALE_MAP[CUR_LANG] || 'fr-FR') : '–';
const clone = o => JSON.parse(JSON.stringify(o));
const slug = s => (s || t('recette_fallback')).normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-zA-Z0-9]+/g, '-').replace(/^-|-$/g, '').toLowerCase() || t('recette_fallback');
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
  try {
    res = await fetch('api.php', {method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({action, lang: CUR_LANG, ...payload})});
  } catch { throw new Error(t('err_offline')); }
  try { j = await res.json(); } catch { throw new Error(t('err_badresponse')); }
  if (!j.ok) throw new Error(j.error || t('err_unknown'));
  return j;
}
let USER = null, D = null;
const AUTH = {mode: 'standalone', features: {register: true, local_login: true, delete_account: true}, login_url: '', logout_url: '', brewrecipes_share_url: '', brewrecipes_style_guides_url: '', brewrecipes_styles_url: '', avatar_url: '', mysql_ready: true, blocked: '', brewer_count: null, visitor_count: null};                    // utilisateur courant et ses données
const ui = {view: 'recipes', rid: null, q: '', tab: 'ferm', auth: 'login', showArchivedIng: false, sheetSteps: true, sheetCost: true, ingSort: {key: 'name', dir: 'asc'}};
const R = () => D && D.recipes.find(r => r.id === ui.rid);

let saveT, saveBusy = false, saveQueued = false;
function setSaveState(s) { const el = $('#savestate'); if (el) el.textContent = s; }
async function saveNow() {
  if (!USER) return;
  if (saveBusy) { saveQueued = true; return; }
  saveBusy = true; setSaveState(t('save_saving'));
  try { await api('save', {data: D}); setSaveState(t('save_saved')); }
  catch (e) { setSaveState(t('save_notsaved')); toast(t('err_save_failed') + e.message); }
  finally { saveBusy = false; if (saveQueued) { saveQueued = false; saveNow(); } }
}
const persist = () => { setSaveState(t('save_modified')); clearTimeout(saveT); saveT = setTimeout(saveNow, 800); };

async function authSubmit() {
  const name = $('#a-user').value.trim(), pw = $('#a-pw').value, err = $('#a-err'), btn = $('#a-go');
  err.textContent = '';
  if (!name || !pw) { err.textContent = t('err_fill_login'); return; }
  if (ui.auth === 'register' && pw !== $('#a-pw2').value) { err.textContent = t('err_pw_mismatch'); return; }
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
    if (p.mashTunMax === undefined || p.mashTunMax === null) p.mashTunMax = 0;   // 0 = pas de limite renseignée
  });
  d.recipes = Array.isArray(d.recipes) ? d.recipes : [];
  d.recipes.forEach(r => {   // rapport eau/grain, rendement, température d'infusion et méthode de brassage : modifiables par recette, initialisés depuis le profil si absents
    const prof = d.profiles.find(p => p.id === r.profileId) || d.profiles[0] || DEFAULT_P();
    if (r.ratio === undefined || r.ratio === null) r.ratio = prof.ratio ?? 3;
    if (r.eff === undefined || r.eff === null) r.eff = prof.eff ?? 72;
    if (r.infusionTemp === undefined || r.infusionTemp === null) r.infusionTemp = prof.infusionTemp ?? 96;
    if (r.mashMethod === undefined || r.mashMethod === null) r.mashMethod = prof.mashMethod ?? 'classic';
    if (typeof r.biabSqueeze !== 'boolean') r.biabSqueeze = prof.biabSqueeze ?? false;
  });
  d.brews = Array.isArray(d.brews) ? d.brews : [];
  d.brews.forEach(normalizeBrewExtraNotes);
  d.stock = (d.stock && typeof d.stock === 'object') ? d.stock : {};
  d.prefs = (d.prefs && typeof d.prefs === 'object') ? d.prefs : {};
  if (!THEMES().some(([k]) => k === d.prefs.theme)) d.prefs.theme = 'vert';
  if (d.prefs.density !== 'compact') d.prefs.density = 'classic';
  if (d.prefs.unitPrimary !== 'brix') d.prefs.unitPrimary = 'sg';
  if (d.prefs.refractFormula !== 'cubic') d.prefs.refractFormula = 'linear';
  if (d.prefs.avatarType !== 'image') d.prefs.avatarType = 'emoji';
  if (!d.prefs.avatarEmoji) d.prefs.avatarEmoji = '🍺';
  if (typeof d.prefs.avatarImage !== 'string') d.prefs.avatarImage = '';
  if (d.prefs.brewExtraNotes !== true) d.prefs.brewExtraNotes = false;
  if (!AVAILABLE_LANGS.includes(d.prefs.lang)) d.prefs.lang = AVAILABLE_LANGS.includes(CUR_LANG) ? CUR_LANG : 'fr';
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
  if (p.avatarType === 'image' && p.avatarImage) {
    return `<span class="avatar ${cls || ''}"><img src="${p.avatarImage}" alt=""></span>`;
  }
  if (AUTH.mode === 'phpbb' && AUTH.avatar_url) {
    return `<span class="avatar ${cls || ''}"><img src="${esc(AUTH.avatar_url)}" alt=""></span>`;
  }
  return `<span class="avatar ${cls || ''}">${esc(p.avatarEmoji || '🍺')}</span>`;
}
const THEMES = () => [['vert', t('theme_vert')], ['ambre', t('theme_ambre')], ['ble', t('theme_ble')], ['myrtille', t('theme_myrtille')], ['sombre', t('theme_sombre')]];
function applyPrefs() {
  const html = document.documentElement;
  html.setAttribute('data-theme', D?.prefs?.theme || 'vert');
  html.setAttribute('data-density', D?.prefs?.density || 'classic');
}
async function enterApp(name) {
  USER = name;
  const r = await api('load');
  D = normalizeData(r.data);
  if (D.prefs.lang !== CUR_LANG && AVAILABLE_LANGS.includes(D.prefs.lang)) { location.reload(); return; }
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
      ? (added.length > 1 ? t('br_import_many', {n: added.length}) : t('br_import_one'))
      : t('br_import_unsaved'));
  } catch (e) {
    toast(e.message || t('br_import_fail'));
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
function brewrecipesShareUrl() { return AUTH.brewrecipes_share_url || ''; }
function brewrecipesStyleGuidesUrl() { return AUTH.brewrecipes_style_guides_url || ''; }
function brewrecipesStylesUrl() { return AUTH.brewrecipes_styles_url || ''; }
const countLabel = (n, one, many) => (n === 1 ? '1 ' + one : f(n, 0) + ' ' + many);
function pageFootExtra() {
  const brewers = AUTH.mode === 'phpbb' ? AUTH.brewer_count : null;
  const visitors = AUTH.mode === 'phpbb' ? AUTH.visitor_count : null;
  if (typeof brewers !== 'number' || typeof visitors !== 'number') return '';
  return `<p class="foot-usage noprint">${countLabel(brewers, t('foot_brewer_one'), t('foot_brewer_many'))} ${t('foot_on_hophop')} ${countLabel(visitors, t('foot_visitor_one'), t('foot_visitor_many'))}</p>`;
}

/* ---------- 3. LIBELLÉS & GABARITS ---------- */
// Version affichée de l'application (écran de connexion + pied de page), à incrémenter à chaque publication.
const APP_VERSION = '1.3';
// Mention de licence : affichée sur l'écran de connexion et en pied de page sur toutes les vues de l'application.
const LICENSE_HTML = `HopHop v${APP_VERSION} © claude.ai, Penn-Maen, Jean-Luc — <a href="https://creativecommons.org/licenses/by-nc-sa/4.0/deed.fr" target="_blank" rel="noopener" style="color:inherit">CC BY-NC-SA 4.0</a>`;
const FTYPE = () => ({grain: t('ftype_grain'), sugar: t('ftype_sugar'), extract: t('ftype_extract')});
const USE_H = () => ({boil: t('useh_boil'), fwh: t('useh_fwh'), whirl: t('useh_whirl'), hopstand: t('useh_hopstand'), dry: t('useh_dry')});
const USE_M = () => ({mash: t('usem_mash'), boil: t('usem_boil'), ferm: t('usem_ferm'), bottle: t('usem_bottle')});
const STATUS = () => ({planned: t('status_planned'), brewed: t('status_brewed'), ferm: t('status_ferm'), cond: t('status_cond'), done: t('status_done')});
const LIBNAME = () => ({ferm: t('libname_ferm'), hop: t('libname_hop'), yeast: t('libname_yeast'), misc: t('libname_misc'), style: t('libname_style')});
const STOCK_TYPES = ['ferm', 'hop', 'yeast', 'misc'];   // bibliothèques suivies en stock (les styles ne se stockent pas)
const RLIB = {ferm: 'ferm', hops: 'hop', yeast: 'yeast', misc: 'misc'};   // section de recette → bibliothèque
const lib = (t, id) => D.ing[t].find(x => x.id === id);
const unitOf = (t, x) => t === 'ferm' ? 'kg' : t === 'hop' ? 'g' : t === 'yeast' ? 'sachet(s)' : (x.unit || 'g');
const DEFAULT_P = () => ({name: t('default_profile_name'), batch: 20, boil: 60, evap: 3, trub: 1.5, mashLoss: 1, absorb: 1, shrink: 4, eff: 72, ratio: 3, infusionTemp: 96, mashMethod: 'classic', biabSqueeze: false, mashTunMax: 0});
// Méthode de brassage : classique (empâtage dans une cuve dédiée + rinçage des drêches) ou BIAB (Brew in a Bag :
// tout le volume d'eau est présent dès l'empâtage, grains dans un sac filtrant, pas de rinçage séparé).
const BREW_METHOD = () => [['classic', t('brewm_classic')], ['biab', t('brewm_biab')]];

function newRecipe() {
  const p = D.profiles[0] || DEFAULT_P();
  return {id: uid(), name: t('new_recipe_name'), style: '', styleId: '', profileId: D.profiles[0]?.id || '', batch: p.batch, ratio: p.ratio, eff: p.eff, infusionTemp: p.infusionTemp, mashMethod: p.mashMethod, biabSqueeze: p.biabSqueeze, grainTemp: 20, co2: 2.4,
    ferm: [], hops: [], yeast: [], misc: [],
    mash: [{name: t('new_mash_step1'), temp: 67, time: 60}, {name: t('new_mash_step2'), temp: 78, time: 10}],
    ferment: [{name: t('new_ferment_step1'), temp: 19, days: 14}, {name: t('new_ferment_step2'), temp: 4, days: 14}],
    notes: '', created: Date.now(), updated: Date.now()};
}

/* ---------- 4. CALCULS ----------
   Potentiel : 1 kg d'extrait pur = 384,6 points·L (1 L à SG 1,3846).
   OG  : (potentiel grains × rendement + potentiel sucres/extraits) / volume en fermenteur
   FG  : atténuation apparente moyenne des levures      ABV : (OG − FG) × 131,25
   IBU : formule de Tinseth      EBC : formule de Morey (SRM × 1,97)
   Eau : volume pré-ébullition = (volume + pertes) / (1 − contraction) + évaporation           */
function calc(r) {
  const P = D.profiles.find(p => p.id === r.profileId) || D.profiles[0] || DEFAULT_P();
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
  // Classique : eau d'empâtage plafonnée au rapport eau/grain, le reste en rinçage. BIAB : volume unique dès
  // l'empâtage (sac filtrant dans la cuve d'ébullition), aucun rinçage séparé.
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
  // Volume maximal atteint dans la cuve d'empâtage, tous paliers confondus (une décoction/infusion peut
  // faire grimper le volume au-delà du 1er palier) ; comparé au volume max de la cuve (profil) si renseigné.
  const maxMashVol = mashSteps.length ? Math.max(...mashSteps.map(m => m.volume)) : rnd(mashWater + kgMash * GRAIN_DISP, 1);

  return {P, V, og, fg, abv, ibu, ebc, srm, att, hasYeast: yeasts.length > 0, bugu: ogPts > 0 ? ibu / ogPts : 0,
    preboil, pbSG, totalWater, mashWater, sparge, strike, mashTemp, kgMash, kgTot, fp, hi, potGrain, potOther,
    evapL: P.evap * P.boil / 60, sugarL, sugarTot, residual, effPct, ratio, costFerm, costHop, costYeast, costMisc, costTotal, costPerL, mashSteps, maxMashVol, infusionTemp: INFUSION_TEMP,
    mashMethod, biabSqueeze};
}

/* ---------- 5. VUES ---------- */
const fld = (label, html, cls = '') => `<label class="fld ${cls}"><span>${label}</span>${html}</label>`;
const bi = (path, val, n) => `<input data-b="${path}" ${n ? 'data-n inputmode="decimal"' : ''} value="${esc(val)}">`;
const bs = (path, list, val) => `<select data-b="${path}">${opts(list, val)}</select>`;
const libOpts = (typ, sel) => {
  // La liste proposée pour un NOUVEAU choix n'affiche que les ingrédients actifs ; un ingrédient déjà utilisé
  // dans cette recette reste sélectionné même s'il a depuis été retiré (archivé) de la bibliothèque.
  const active = [...D.ing[typ]].filter(x => !x.archived).sort((a, b) => a.name.localeCompare(b.name, 'fr'));
  let h = opts(active.map(x => [x.id, x.name]), sel);
  if (sel) {
    const cur = lib(typ, sel);
    if (cur && cur.archived) h = `<option value="${esc(sel)}" selected>${esc(cur.name)}${t('opt_archived')}</option>` + h;
    else if (!cur) h = `<option value="${esc(sel)}" selected>${t('opt_ing_removed')}</option>` + h;
  }
  return h;
};

function authHTML() {
  if (AUTH.mode === 'phpbb' && AUTH.blocked === 'robot') {
    return `<div class="authscreen"><div class="auth-bg" aria-hidden="true">${LOGO_SVG}</div><div class="wrap"><div class="auth card">
    <h1 style="text-align:center;display:flex;align-items:center;justify-content:center;gap:.5rem"><span class="brand-logo" style="width:2.2rem;height:2.2rem">${LOGO_SVG}</span>HopHop</h1>
    <p class="err" role="alert">${t('auth_robot_blocked')}</p>
  </div></div></div>`;
  }
  if (AUTH.mode === 'phpbb') {
    const loginBtn = AUTH.login_url
      ? `<a class="btn" href="${esc(AUTH.login_url)}" style="width:100%;text-align:center;text-decoration:none;display:inline-block;box-sizing:border-box">${t('auth_forum_login')}</a>`
      : `<p class="err">${t('auth_forum_login_unavail')}</p>`;
    const dbWarn = AUTH.mysql_ready === false
      ? `<p class="err" role="alert">${t('auth_mysql_warn')}</p>` : '';
    return `<div class="authscreen"><div class="auth-bg" aria-hidden="true">${LOGO_SVG}</div><div class="wrap"><div class="auth card">
    <h1 style="text-align:center;display:flex;align-items:center;justify-content:center;gap:.5rem"><span class="brand-logo" style="width:2.2rem;height:2.2rem">${LOGO_SVG}</span>HopHop</h1>
    <p class="sub" style="text-align:center">${t('auth_forum_tagline')}</p>
    ${dbWarn}${loginBtn}
    <p class="sub" style="margin-bottom:0">${t('auth_forum_refresh')}</p>
  </div><p class="sub" style="text-align:center;margin-top:1rem">${LICENSE_HTML}</p></div></div>`;
  }
  const reg = ui.auth === 'register' && AUTH.features.register;
  return `<div class="authscreen"><div class="auth-bg" aria-hidden="true">${LOGO_SVG}</div><div class="wrap"><div class="auth card">
    <h1 style="text-align:center;display:flex;align-items:center;justify-content:center;gap:.5rem"><span class="brand-logo" style="width:2.2rem;height:2.2rem">${LOGO_SVG}</span>HopHop</h1>
    <p class="sub" style="text-align:center">${t('auth_tagline')}</p>
    ${AUTH.features.register ? `<div class="seg"><button data-act="authMode" data-m="login" aria-pressed="${!reg}">${t('auth_login_tab')}</button><button data-act="authMode" data-m="register" aria-pressed="${reg}">${t('auth_register_tab')}</button></div>` : ''}
    <div class="fgrid" style="grid-template-columns:1fr">
      ${fld(t('auth_username'), '<input id="a-user" autocomplete="username" autocapitalize="off">')}
      ${fld(t('auth_password'), '<input id="a-pw" type="password" autocomplete="' + (reg ? 'new-password' : 'current-password') + '">')}
      ${reg ? fld(t('auth_password2'), '<input id="a-pw2" type="password" autocomplete="new-password">') : ''}
    </div>
    <div class="err" id="a-err" role="alert"></div>
    <button class="btn" id="a-go" style="width:100%" data-act="authGo">${reg ? t('auth_create_btn') : t('auth_login_btn')}</button>
    <p class="sub" style="margin-bottom:0">${t('auth_server_note')}</p>
  </div><p class="sub" style="text-align:center;margin-top:1rem">${LICENSE_HTML}</p></div></div>`;
}

function shellHTML() {
  const cur = ['editor', 'sheet'].includes(ui.view) ? 'recipes' : ui.view;
  const T = [['recipes', t('nav_recipes')], ['brews', t('nav_brews')], ['ings', t('ings_title')], ['profiles', t('profiles_title')], ['account', t('account_title')]];
  const forumBack = AUTH.mode === 'phpbb'
    ? `<a class="btn ghost sm top-forum" href="/forum/" style="text-decoration:none">${t('nav_forum_back')}</a>`
    : `<button class="btn ghost sm top-forum" data-act="logout">${t('nav_logout')}</button>`;
  return `<header class="top"><div class="in"><div class="brand"><span class="brand-logo">${LOGO_SVG}</span>HopHop</div><div class="grow"></div>
    <div class="top-user"><span class="savestate" id="savestate"></span><span class="who">${avatarHTML()}<span class="uname">${esc(USER)}</span></span></div>${forumBack}</div>
    <nav class="tabs" aria-label="Navigation principale">${T.map(([v, l]) => `<button data-act="go" data-v="${v}" ${cur === v ? 'aria-current="page"' : ''}>${l}</button>`).join('')}</nav></header>
    <main class="wrap" id="main"></main>
    <footer class="sitefooter noprint">${pageFootExtra()}<p class="sub" style="text-align:center;margin-top:.2rem"><a href="help.html" style="color:inherit">${t('nav_help')}</a> — ${LICENSE_HTML}</p></footer>`;
}

function recipeCard(r) {
  const c = calc(r);
  return `<button class="card rcard" data-act="open" data-id="${r.id}">
    <div style="display:flex;justify-content:space-between;gap:.5rem;align-items:center"><h2 style="margin:0">${esc(r.name)}</h2><span class="sw" style="background:${ebcColor(c.ebc)}"></span></div>
    <div class="sub">${esc(recipeStyleName(r)) || t('style_unspecified')} · ${f(N(r.batch), 1)} L</div>
    <div class="meta"><span class="chip">DI ${gText(c.og)}</span><span class="chip">${f(c.abv, 1)} % vol.</span><span class="chip">${f(c.ibu, 0)} IBU</span><span class="chip">${f(c.ebc, 0)} EBC</span></div>
  </button>`;
}
function recipeListHTML() {
  const q = ui.q.toLowerCase();
  const l = D.recipes.filter(r => (r.name + ' ' + recipeStyleName(r)).toLowerCase().includes(q)).sort((a, b) => b.updated - a.updated);
  return l.length ? `<div class="cards">${l.map(recipeCard).join('')}</div>`
    : `<div class="empty">${D.recipes.length ? t('recipes_empty_search') : t('recipes_empty_true')}</div>`;
}
function viewRecipes() {
  return `<div class="bar"><h1>Recettes</h1><div class="grow"></div>
    <input class="search" placeholder="${t('recipes_search')}" data-q value="${esc(ui.q)}" aria-label="${t('recipes_search')}">
    <button class="btn ghost" data-act="importXML">${t('recipes_import_xml')}</button>
    <input type="file" id="importBeerXMLFile" accept=".xml,text/xml,application/xml" style="display:none">
    <button class="btn ghost" data-act="importRecipeJSON">${t('recipes_import_json')}</button>
    <input type="file" id="importBeerJSONFile" accept=".json,application/json" style="display:none">
    <button class="btn" data-act="newRecipe">${t('recipes_new')}</button></div><div id="rlist">${recipeListHTML()}</div>`;
}

/* --- Éditeur de recette --- */
function rowFerm(x, i) {
  return `<div class="row"><div class="rowhead"><select data-b="ferm.${i}.ref" aria-label="${t('imp_ferm_name')}">${libOpts('ferm', x.ref)}</select><button class="ico" data-act="rm" data-t="ferm" data-i="${i}" aria-label="${t('ing_remove_aria')}">✕</button></div>
    ${fld(t('fld_qty_kg'), bi(`ferm.${i}.kg`, x.kg, 1))}<div class="fld"><span>${t('fld_part')}</span><output data-cell="fp:${i}">–</output></div><div class="hint" data-stk="ferm:${i}"></div></div>`;
}
function rowHop(x, i) {
  const dry = x.use === 'dry', hopstand = x.use === 'hopstand';
  const timeLabel = dry ? t('fld_duration_days') : hopstand ? t('fld_duration_min') : t('fld_time_min');
  const h0 = lib('hop', x.ref), hformTxt = h0 ? f(h0.alpha, 1) + ' % · ' + h0.form : '–';
  return `<div class="row"><div class="rowhead"><select data-b="hops.${i}.ref" aria-label="${t('col_hop')}">${libOpts('hop', x.ref)}</select><button class="ico" data-act="rm" data-t="hops" data-i="${i}" aria-label="${t('ing_remove_aria')}">✕</button></div>
    ${fld(t('fld_qty_g'), bi(`hops.${i}.g`, x.g, 1))}${fld(t('fld_use'), `<select data-b="hops.${i}.use" data-rr>${opts(Object.entries(USE_H()), x.use)}</select>`)}
    ${x.use === 'fwh' ? '' : fld(timeLabel, bi(`hops.${i}.time`, x.time, 1))}
    ${hopstand ? fld(t('fld_temp_c'), bi(`hops.${i}.temp`, x.temp != null ? x.temp : 80, 1)) : ''}
    ${dry ? fld(t('fld_temp_c_optional'), bi(`hops.${i}.temp`, x.temp != null ? x.temp : '', 1)) : ''}
    <div class="fld"><span>${t('fld_alpha_form')}</span><output data-cell="hform:${i}">${esc(hformTxt)}</output></div>
    <div class="fld"><span>${t('fld_bitterness')}</span><output data-cell="hi:${i}">–</output></div><div class="hint" data-stk="hops:${i}"></div></div>`;
}
function rowYeast(x, i) {
  return `<div class="row"><div class="rowhead"><select data-b="yeast.${i}.ref" aria-label="${t('sheet_yeast')}">${libOpts('yeast', x.ref)}</select><button class="ico" data-act="rm" data-t="yeast" data-i="${i}" aria-label="${t('ing_remove_aria')}">✕</button></div>
    ${fld(t('fld_packets'), bi(`yeast.${i}.qty`, x.qty, 1))}<div class="hint" data-stk="yeast:${i}"></div></div>`;
}
function rowMisc(x, i) {
  const m = lib('misc', x.ref);
  return `<div class="row"><div class="rowhead"><select data-b="misc.${i}.ref" aria-label="${t('misc_select_aria')}">${libOpts('misc', x.ref)}</select><button class="ico" data-act="rm" data-t="misc" data-i="${i}" aria-label="${t('ing_remove_aria')}">✕</button></div>
    ${fld(t('fld_qty_unit') + (m ? ' (' + esc(m.unit) + ')' : ''), bi(`misc.${i}.qty`, x.qty, 1))}${fld(t('fld_moment'), bs(`misc.${i}.use`, Object.entries(USE_M()), x.use))}
    ${fld(t('fld_time_min'), bi(`misc.${i}.time`, x.time, 1))}<div class="hint" data-stk="misc:${i}"></div></div>`;
}
const rowStep = (x, i, key, lab, unit) => `<div class="row"><div class="rowhead"><input data-b="${key}.${i}.name" value="${esc(x.name)}" aria-label="${t('fld_step_name')}"><button class="ico" data-act="rm" data-t="${key}" data-i="${i}" aria-label="${t('ing_remove_aria')}">✕</button></div>
    ${fld(t('fld_temp_c'), bi(`${key}.${i}.temp`, x.temp, 1))}${fld(lab, bi(`${key}.${i}.${unit}`, x[unit], 1))}</div>`;
const MASH_METHOD = () => [['infusion', t('mashm_infusion')], ['direct', t('mashm_direct')], ['decoction', t('mashm_decoction')]];
function rowMash(x, i) {
  return `<div class="row"><div class="rowhead"><input data-b="mash.${i}.name" value="${esc(x.name)}" aria-label="${t('fld_rest_name')}"><button class="ico" data-act="rm" data-t="mash" data-i="${i}" aria-label="${t('ing_remove_aria')}">✕</button></div>
    ${fld(t('fld_temp_c'), bi(`mash.${i}.temp`, x.temp, 1))}${fld(t('fld_duration_min'), bi(`mash.${i}.time`, x.time, 1))}
    ${i > 0 ? fld(t('p_card_method'), `<select data-b="mash.${i}.method">${opts(MASH_METHOD(), x.method || 'infusion')}</select>`) : `<div class="fld"><span>${t('p_card_method')}</span><output>${t('fld_infusion_water')}</output></div>`}</div>`;
}

function sectionHTML(title, key, rows, addLabel) {
  return `<section class="card"><div class="secthead"><h2>${title}</h2><div class="grow"></div><button class="btn ghost sm" data-act="add" data-t="${key}">+ ${addLabel}</button></div>
    ${rows.length ? rows.join('') : `<div class="sub">${t('no_elements')}</div>`}</section>`;
}

function viewEditor() {
  const r = R(); if (!r) { ui.view = 'recipes'; return viewRecipes(); }
  return `<div class="bar"><button class="btn ghost sm" data-act="go" data-v="recipes">${t('editor_back')}</button><div class="grow"></div>
    <button class="btn ghost sm" data-act="sheet">${t('editor_sheet')}</button><button class="btn sm" data-act="brewFrom">${t('editor_brewfrom')}</button></div>
  <div class="editor"><div>
    <section class="card"><input class="namefield" data-b="name" value="${esc(r.name)}" aria-label="${t('editor_name_aria')}" style="margin-bottom:.8rem">
      <div class="fgrid">
        ${fld(t('fld_style'), `<div class="style-line"><select data-b="styleId">${styleSelectOpts(r.styleId)}</select>${brewrecipesStyleGuidesUrl() ? `<button type="button" class="btn ghost sm" data-act="pickBrewRecipesStyle">${t('br_pick_style')}</button>` : ''}</div>`, 'span2')}
        ${fld(t('fld_profile'), `<select data-b="profileId">${libProfiles(r.profileId)}</select>`)}
        ${fld(t('fld_batch'), bi('batch', r.batch, 1))}
        ${fld(t('fld_ratio'), bi('ratio', r.ratio, 1))}
        ${fld(t('fld_eff'), bi('eff', r.eff, 1))}
        ${fld(t('fld_infusiontemp'), bi('infusionTemp', r.infusionTemp, 1))}
        ${fld(t('fld_graintemp'), bi('grainTemp', r.grainTemp, 1))}
        ${fld(t('fld_co2'), bi('co2', r.co2, 1))}
        ${fld(t('fld_mashmethod'), `<select data-b="mashMethod">${opts(BREW_METHOD(), r.mashMethod)}</select>`)}
        ${r.mashMethod === 'biab' ? `<label class="fld" style="flex-direction:row;align-items:center;gap:.5rem;padding-top:1.5rem">
          <input type="checkbox" data-b="biabSqueeze" style="width:auto" ${r.biabSqueeze ? 'checked' : ''}>
          <span style="text-transform:none;font-size:.95rem;color:var(--text)">${t('biab_squeeze')}</span></label>` : ''}
      </div></section>
    ${sectionHTML(t('sec_ferm'), 'ferm', r.ferm.map(rowFerm), t('add_label'))}
    ${sectionHTML(t('sec_hops'), 'hops', r.hops.map(rowHop), t('add_label'))}
    ${sectionHTML(t('sheet_yeast'), 'yeast', r.yeast.map(rowYeast), t('add_label'))}
    ${sectionHTML(t('sec_misc'), 'misc', r.misc.map(rowMisc), t('add_label'))}
    ${sectionHTML(t('sec_mash'), 'mash', r.mash.map((x, i) => rowMash(x, i)), t('palier_label'))}
    ${sectionHTML(t('sec_ferment'), 'ferment', r.ferment.map((x, i) => rowStep(x, i, 'ferment', t('fld_duration_days'), 'days')), t('step_label'))}
    <section class="card"><h2>Notes</h2><textarea data-b="notes" aria-label="${t('notes_title')}">${esc(r.notes)}</textarea></section>
    <section class="card"><h2>${t('resize_title')}</h2>
      <p class="sub" style="margin-top:0">${t('resize_note')}</p>
      <div class="fgrid" style="align-items:end">${fld(t('resize_newvol'), `<input id="newvol" inputmode="decimal" value="${esc(r.batch)}">`)}<div class="sub" id="kfac"></div></div>
      <div class="dlg-actions" style="justify-content:flex-start"><button class="btn ghost sm" data-act="resizeApply">${t('resize_apply')}</button><button class="btn ghost sm" data-act="resizeCopy">${t('resize_copy')}</button></div></section>
    <section class="card"><h2>${t('export_title')}</h2><div class="dlg-actions" style="justify-content:flex-start;flex-wrap:wrap;margin:0">
      ${brewrecipesShareUrl() ? `<button class="btn sm" data-act="shareBrewRecipes">${t('br_share')}</button>` : ''}
      <button class="btn ghost sm" data-act="exp" data-k="xml">${t('export_xml')}</button><button class="btn ghost sm" data-act="exp" data-k="json">${t('export_json')}</button>
      <button class="btn ghost sm" data-act="dupRecipe">Dupliquer</button><button class="btn danger sm" data-act="delRecipe">Supprimer</button></div></section>
  </div><aside class="stats" id="stats"></aside></div>`;
}
const libProfiles = sel => D.profiles.length ? opts([...D.profiles].sort((a, b) => a.name.localeCompare(b.name, 'fr')).map(p => [p.id, p.name]), sel) : `<option value="">${t('no_profile_default')}</option>`;

const inRange = (v, mn, mx) => (mn == null || mx == null) ? true : v >= mn - 1e-9 && v <= mx + 1e-9;
function statsHTML(c, r) {
  const col = ebcColor(c.ebc);
  const st = r ? recipeStyleRef(r) : null;
  return `<div class="card"><div class="beer"><div class="glass" aria-hidden="true"><div class="foam"></div><div class="liq" style="background:${col}"></div></div>
    <div><h2 style="margin:0">${t('stats_results')}</h2><div class="sub">${t('stats_finalvol')} ${f(c.V, 1)} L</div></div></div>
    <div class="kpis"><div class="kpi"><b>${gKpi(c.og).primary}</b><span>${t('kpi_og')}</span><div class="kpi-sub">${gKpi(c.og).secondary}</div></div><div class="kpi"><b>${gKpi(c.fg).primary}</b><span>${t('kpi_fg')}</span><div class="kpi-sub">${gKpi(c.fg).secondary}</div></div><div class="kpi"><b>${f(c.abv, 1)} %</b><span>${t('kpi_alcohol')}</span></div>
      <div class="kpi"><b>${f(c.ibu, 0)}</b><span>IBU</span></div><div class="kpi"><b>${f(c.ebc, 0)}</b><span>EBC</span></div><div class="kpi"><b>${f(c.bugu, 2)}</b><span>${t('kpi_ibu_ratio')}</span></div></div>
    <div class="sub" style="margin:-.2rem 0 .5rem">${t('stats_refracto', {brix: f(rawBrixAtFG(c.og, c.fg), 1), fg: gUnit(c.fg)})}</div>
    ${st ? `<h3>${t('stats_style', {name: esc(st.name)})}${st.category ? ' (' + esc(st.category) + ')' : ''}${st.guide ? ` — ${esc(st.guide)}` : ''}</h3>
      <div class="meta" style="margin-bottom:.6rem">
        <span class="chip${inRange(c.og, st.ogMin, st.ogMax) ? '' : ' bad'}">${t('kpi_og')} ${f(st.ogMin, 3)}–${f(st.ogMax, 3)}</span>
        <span class="chip${inRange(c.fg, st.fgMin, st.fgMax) ? '' : ' bad'}">${t('kpi_fg')} ${f(st.fgMin, 3)}–${f(st.fgMax, 3)}</span>
        <span class="chip${inRange(c.ibu, st.ibuMin, st.ibuMax) ? '' : ' bad'}">IBU ${f(st.ibuMin, 0)}–${f(st.ibuMax, 0)}</span>
        <span class="chip${inRange(c.ebc, st.colorMin, st.colorMax) ? '' : ' bad'}">EBC ${f(st.colorMin, 0)}–${f(st.colorMax, 0)}</span>
        <span class="chip${inRange(c.abv, st.abvMin, st.abvMax) ? '' : ' bad'}">${t('kpi_alcohol')} ${f(st.abvMin, 1)}–${f(st.abvMax, 1)} %</span>
      </div>` : ''}
    ${c.hasYeast ? '' : `<div class="warn">${t('stats_no_yeast')}</div>`}
    ${c.P.mashTunMax > 0 && c.maxMashVol > c.P.mashTunMax ? `<div class="warn">${t('stats_mashtun_overflow', {vol: f(c.maxMashVol, 1), max: f(c.P.mashTunMax, 1)})}</div>` : ''}
    <details class="stat-sec" open><summary>${t('stats_water')}</summary><dl class="kv"><dt>${t('stats_mash')}</dt><dd>${t('vol_at_temp', {vol: f(c.mashWater, 1), temp: f(c.strike, 0)})}</dd><dt>${t('stats_sparge')}</dt><dd>${c.mashMethod === 'biab' ? t('stats_none_biab') : f(c.sparge, 1) + ' L'}</dd><dt>${t('stats_totalwater')}</dt><dd>${f(c.totalWater, 1)} L</dd>
      <dt>${t('stats_preboil')}</dt><dd>${f(c.preboil, 1)} L · ${gUnit(c.pbSG)}</dd><dt>${t('stats_evap')}</dt><dd>${f(c.evapL, 1)} L</dd></dl>
    ${c.mashMethod === 'biab' ? `<div class="sub" style="margin-top:-.2rem;margin-bottom:.5rem">${t('stats_biab_note')}${c.biabSqueeze ? t('stats_biab_squeeze_note') : ''}</div>` : ''}</details>
    ${c.mashSteps.length ? `<details class="stat-sec" open><summary>${t('stats_mash_vol')}</summary><dl class="kv">${c.mashSteps.map((m, i) => `<dt>${esc(m.name)} (${f(m.temp, 0)} °C)</dt><dd>${f(m.volume, 1)} L${i > 0 && m.method === 'infusion' && m.infusion > 0 ? ` (+${f(m.infusion, 1)} L)` : ''}${i > 0 && m.method === 'decoction' && m.decoctionVol > 0 ? t('stats_decoct_inline', {vol: f(m.decoctionVol, 1)}) : ''}</dd>`).join('')}</dl>
      <div class="sub" style="margin-top:.2rem">${t('stats_mash_vol_note', {temp: f(c.infusionTemp, 0)})}</div></details>` : ''}
    <details class="stat-sec" open><summary>${t('stats_grains')}</summary><dl class="kv"><dt>${t('stats_total_ferm')}</dt><dd>${f(c.kgTot, 2)} kg</dd><dt>${t('stats_ratio')}</dt><dd>${f(c.ratio, 1)} L/kg</dd><dt>${t('stats_eff')}</dt><dd>${f(c.effPct, 0)} %</dd><dt>${t('stats_attenuation')}</dt><dd>${f(c.att, 0)} %</dd></dl></details>
    <details class="stat-sec" open><summary>${t('stats_conditioning')}</summary><dl class="kv"><dt>${t('stats_sucrose')}</dt><dd>${f(c.sugarL, 1)} g/L</dd><dt>${t('stats_total')}</dt><dd>${f(c.sugarTot, 0)} g</dd></dl></details>
    <details class="stat-sec" open><summary>${t('stats_cost')}</summary><dl class="kv"><dt>${t('sec_ferm')}</dt><dd>${f(c.costFerm, 2)} €</dd><dt>${t('sec_hops')}</dt><dd>${f(c.costHop, 2)} €</dd><dt>${t('sheet_yeast')}</dt><dd>${f(c.costYeast, 2)} €</dd><dt>${t('sec_misc')}</dt><dd>${f(c.costMisc, 2)} €</dd>
      <dt>${t('stats_total')}</dt><dd>${f(c.costTotal, 2)} €</dd><dt>${t('stats_perliter')}</dt><dd>${f(c.costPerL, 2)} €/L</dd></dl>
    ${c.costTotal === 0 && (r.ferm.length || r.hops.length) ? `<div class="sub" style="margin-top:.3rem">${t('stats_cost_note')}</div>` : ''}</details>
    ${c.kgTot === 0 ? `<div class="warn">${t('stats_no_ferm')}</div>` : ''}</div>`;
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
  const ingName = (typ, id) => esc(lib(typ, id)?.name || '?');
  if (c.kgMash > 0) S.push(c.mashMethod === 'biab'
    ? t('bs_mash_biab', {water: f(c.mashWater, 1), temp: f(c.strike, 0), kg: f(c.kgMash, 2), graintemp: f(N(r.grainTemp), 0)})
    : t('bs_mash_classic', {water: f(c.mashWater, 1), temp: f(c.strike, 0), graintemp: f(N(r.grainTemp), 0), ratio: f(c.ratio, 1), kg: f(c.kgMash, 2)}));
  c.mashSteps.forEach((m, i) => {
    let extra = '';
    if (i > 0 && m.method === 'infusion' && m.infusion > 0) extra = t('bs_infusion_extra', {vol: f(m.infusion, 1)});
    else if (i > 0 && m.method === 'decoction' && m.decoctionVol > 0) extra = t('bs_decoction_extra', {vol: f(m.decoctionVol, 1)});
    else if (i > 0 && m.method === 'direct') extra = t('bs_direct_extra');
    S.push(t('bs_rest', {name: esc(m.name), temp: f(m.temp, 0), time: f(N(r.mash[i].time), 0), vol: f(m.volume, 1), extra}));
  });
  const mashMisc = r.misc.filter(m => m.use === 'mash');
  mashMisc.forEach(m => S.push(t('bs_mash_misc', {qty: f(N(m.qty), 1), unit: esc(lib('misc', m.ref)?.unit || ''), name: ingName('misc', m.ref)})));
  if (c.mashMethod === 'biab') S.push(c.biabSqueeze ? t('bs_biab_end_squeeze', {vol: f(c.preboil, 1), sg: sg(c.pbSG)}) : t('bs_biab_end_nosqueeze', {vol: f(c.preboil, 1), sg: sg(c.pbSG)}));
  else if (c.sparge > 0) S.push(t('bs_sparge', {water: f(c.sparge, 1), vol: f(c.preboil, 1), sg: sg(c.pbSG)}));
  r.hops.filter(h => h.use === 'fwh').forEach(h => S.push(t('bs_fwh', {g: f(N(h.g), 0), name: ingName('hop', h.ref)})));
  S.push(t('bs_boil', {min: f(P.boil, 0), evap: f(c.evapL, 1)}));
  const adds = [];
  r.hops.filter(h => h.use === 'boil').forEach(h => adds.push([N(h.time), t('bs_add_hop', {g: f(N(h.g), 0), name: ingName('hop', h.ref)})]));
  r.misc.filter(m => m.use === 'boil').forEach(m => adds.push([N(m.time), t('bs_add_misc', {qty: f(N(m.qty), 1), unit: esc(lib('misc', m.ref)?.unit || ''), name: ingName('misc', m.ref)})]));
  adds.sort((a, b) => b[0] - a[0]).forEach(([min, txt]) => S.push(t('bs_add_at', {min: f(min, 0), txt})));
  r.hops.filter(h => h.use === 'whirl').forEach(h => S.push(t('bs_whirlpool', {min: f(N(h.time), 0), g: f(N(h.g), 0), name: ingName('hop', h.ref)})));
  r.hops.filter(h => h.use === 'hopstand').forEach(h => S.push(t('bs_hopstand', {temp: f(N(h.temp) || 80, 0), g: f(N(h.g), 0), name: ingName('hop', h.ref), min: f(N(h.time), 0)})));
  S.push(t('bs_cooldown', {vol: f(c.V, 1), og: sg(c.og)}));
  r.yeast.forEach(y => S.push(t('bs_pitch', {qty: f(N(y.qty), 1), name: ingName('yeast', y.ref)})));
  r.ferment.forEach(s => S.push(t('bs_ferment_step', {name: esc(s.name), temp: f(N(s.temp), 0), days: f(N(s.days), 0)})));
  r.hops.filter(h => h.use === 'dry').forEach(h => S.push(t('bs_dryhop', {g: f(N(h.g), 0), name: ingName('hop', h.ref), days: f(N(h.time), 0), temp: h.temp != null && h.temp !== '' ? t('bs_dryhop_temp', {temp: f(N(h.temp), 0)}) : ''})));
  r.misc.filter(m => m.use === 'ferm').forEach(m => S.push(t('bs_ferm_misc', {qty: f(N(m.qty), 1), unit: esc(lib('misc', m.ref)?.unit || ''), name: ingName('misc', m.ref)})));
  S.push(t('bs_condition', {fg: sg(c.fg), co2: f(N(r.co2), 1), g_l: f(c.sugarL, 1), g_tot: f(c.sugarTot, 0), vol: f(c.V, 1)}));
  return S;
}
function viewSheet() {
  const r = R(); if (!r) return viewRecipes();
  const c = calc(r), P = c.P;
  const tr = (cells) => `<tr>${cells.map(x => `<td>${x}</td>`).join('')}</tr>`;
  const th = h => `<thead><tr>${h.map(x => `<th>${x}</th>`).join('')}</tr></thead>`;
  return `<div class="bar"><button class="btn ghost sm" data-act="open" data-id="${r.id}">${t('sheet_back')}</button><div class="grow"></div>
    <div class="seg noprint" style="margin:0"><button data-act="sheetMode" data-v="1" aria-pressed="${ui.sheetSteps}">${t('sheet_withsteps')}</button><button data-act="sheetMode" data-v="0" aria-pressed="${!ui.sheetSteps}">${t('sheet_withoutsteps')}</button></div>
    <label class="sub noprint" style="display:flex;align-items:center;gap:.35rem;margin:0 .2rem"><input type="checkbox" data-act="toggleSheetCost" style="width:auto" ${ui.sheetCost ? 'checked' : ''}> ${t('sheet_cost_toggle')}</label>
    <button class="btn sm" data-act="print">${t('sheet_print')}</button></div>
  <div class="sheet"><div class="card"><div style="display:flex;justify-content:space-between;align-items:center;gap:1rem"><div><h1>${esc(r.name)}</h1><div class="sub">${esc(recipeStyleName(r)) || t('style_unspecified')} · ${f(c.V, 1)} L · ${t('sheet_equip')} : ${esc(P.name)}</div></div><span class="sw" style="width:44px;height:44px;background:${ebcColor(c.ebc)}"></span></div>
    <div class="kpis" style="grid-template-columns:repeat(auto-fit,minmax(90px,1fr));margin-top:1rem">
      <div class="kpi"><b>${gKpi(c.og).primary}</b><span>${t('sheet_og')}</span><div class="kpi-sub">${gKpi(c.og).secondary}</div></div><div class="kpi"><b>${gKpi(c.fg).primary}</b><span>${t('sheet_fg')}</span><div class="kpi-sub">${gKpi(c.fg).secondary}</div></div><div class="kpi"><b>${f(c.abv, 1)} %</b><span>${t('kpi_alcohol')}</span></div>
      <div class="kpi"><b>${f(c.ibu, 0)}</b><span>IBU</span></div><div class="kpi"><b>${f(c.ebc, 0)}</b><span>EBC</span></div><div class="kpi"><b>${f(c.bugu, 2)}</b><span>${t('kpi_ibu_ratio')}</span></div></div>
    <div class="sub" style="margin-top:.4rem">${t('sheet_refracto', {brix: f(rawBrixAtFG(c.og, c.fg), 1)})}</div></div>
  <div class="card"><h2>${t('sheet_ingredients')}</h2>
    ${r.ferm.length ? `<h3>${t('sec_ferm')}</h3><div class="scroll"><table class="tbl">${th([t('col_ingredient'), t('col_amount'), t('col_part'), 'EBC'])}${r.ferm.map((x, i) => { const g = lib('ferm', x.ref); return tr([esc(g?.name || '?'), f(N(x.kg), 3) + ' kg', f(c.fp[i], 1) + ' %', g ? f(g.ebc, 0) : '–']); }).join('')}</table></div>` : ''}
    ${r.hops.length ? `<h3>${t('sec_hops')}</h3><div class="scroll"><table class="tbl">${th([t('col_hop'), t('col_amount'), t('col_form'), t('col_use'), t('col_time'), 'α %', 'IBU'])}${hopSheetOrder(r.hops).map(i => { const x = r.hops[i], h = lib('hop', x.ref); return tr([esc(h?.name || '?'), f(N(x.g), 0) + ' g', h ? esc(h.form) : '–', USE_H()[x.use], x.use === 'fwh' ? '–' : f(N(x.time), 0) + (x.use === 'dry' ? ' j' : ' min'), h ? f(h.alpha, 1) : '–', f(c.hi[i], 1)]); }).join('')}</table></div>` : ''}
    ${r.yeast.length ? `<h3>${t('sheet_yeast')}</h3><ul>${r.yeast.map(y => `<li>${f(N(y.qty), 1)} × ${esc(lib('yeast', y.ref)?.name || '?')}</li>`).join('')}</ul>` : ''}
    ${r.misc.length ? `<h3>${t('sec_misc')}</h3><ul>${r.misc.map(m => `<li>${f(N(m.qty), 1)} ${esc(lib('misc', m.ref)?.unit || '')} ${esc(lib('misc', m.ref)?.name || '?')} — ${USE_M()[m.use]}${m.use === 'boil' || m.use === 'mash' ? ' (' + f(N(m.time), 0) + ' min)' : ''}</li>`).join('')}</ul>` : ''}
    ${!r.ferm.length && !r.hops.length ? `<div class="sub">${t('sheet_no_ing')}</div>` : ''}</div>
  <div class="card"><h2>${t('sheet_water_yield')}</h2>${c.mashMethod === 'biab' ? `<div class="sub" style="margin-top:0">${t('sheet_biab_note', {squeeze: c.biabSqueeze ? t('sheet_biab_squeeze_suffix') : ''})}</div>` : ''}<dl class="kv"><dt>${t('sheet_mash_water')}</dt><dd>${t('vol_at_temp', {vol: f(c.mashWater, 1), temp: f(c.strike, 0)})}</dd><dt>${t('sheet_sparge_water')}</dt><dd>${c.mashMethod === 'biab' ? t('sheet_none_biab') : f(c.sparge, 1) + ' L'}</dd><dt>${t('stats_totalwater')}</dt><dd>${f(c.totalWater, 1)} L</dd>
    <dt>${t('sheet_preboil_vol')}</dt><dd>${f(c.preboil, 1)} L (${gUnit(c.pbSG)})</dd><dt>${t('sheet_ratio')}</dt><dd>${f(c.ratio, 1)} L/kg</dd><dt>${t('sheet_eff')}</dt><dd>${f(c.effPct, 0)} %</dd><dt>${t('sheet_attenuation')}</dt><dd>${f(c.att, 0)} %</dd></dl>
    ${!ui.sheetSteps && c.mashSteps.length ? `<h3 style="margin-top:.7rem">${t('sheet_mash_steps')}</h3><div class="scroll"><table class="tbl">${th([t('palier_label'), t('col_temp'), t('col_duration'), t('col_mashvol')])}${c.mashSteps.map((m, i) => tr([esc(m.name), f(m.temp, 0) + ' °C', f(N(r.mash[i].time), 0) + ' min',
      f(m.volume, 1) + ' L' + (i > 0 && m.method === 'infusion' && m.infusion > 0 ? ` (+${f(m.infusion, 1)} L)` : '') + (i > 0 && m.method === 'decoction' && m.decoctionVol > 0 ? t('stats_decoct_inline', {vol: f(m.decoctionVol, 1)}) : '')])).join('')}</table></div>` : ''}</div>
  ${ui.sheetCost && c.costTotal > 0 ? `<div class="card"><h2>${t('stats_cost')}</h2><dl class="kv"><dt>${t('sec_ferm')}</dt><dd>${f(c.costFerm, 2)} €</dd><dt>${t('sec_hops')}</dt><dd>${f(c.costHop, 2)} €</dd><dt>${t('sheet_yeast')}</dt><dd>${f(c.costYeast, 2)} €</dd><dt>${t('sec_misc')}</dt><dd>${f(c.costMisc, 2)} €</dd>
    <dt>${t('stats_total')}</dt><dd>${f(c.costTotal, 2)} €</dd><dt>${t('stats_perliter')}</dt><dd>${f(c.costPerL, 2)} €/L</dd></dl></div>` : ''}
  ${ui.sheetSteps ? `<div class="card"><h2>${t('sheet_steps_title')}</h2><ol>${brewSteps(r, c).map(s => `<li>${s}</li>`).join('')}</ol></div>` : ''}
  ${r.notes ? `<div class="card"><h2>${t('notes_title')}</h2><div style="white-space:pre-wrap">${esc(r.notes)}</div></div>` : ''}</div>`;
}

/* --- Brassins (historique) --- */
// « Autres notes » : sous-formulaire optionnel (activable dans Compte) relevant, le jour de brassage,
// les quantités réellement utilisées, températures, pH, densités et volumes à chaque étape, en plus des
// notes de brassage/dégustation déjà existantes. Porté depuis le fork communautaire Mirrabeau/Hophop.
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
  const free = src.free != null ? String(src.free) : '';
  const temp = mapVals(src.temp);
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
    rows.push({k: 'ferm.' + i, label: g?.name || t('imp_ferm_name'), planned: N(x.kg), unit: 'kg', plannedTxt: f(N(x.kg), 3) + ' kg'});
  });
  (r.hops || []).forEach((x, i) => {
    const h = lib('hop', x.ref);
    const use = USE_H()[x.use] ? ' · ' + USE_H()[x.use] : '';
    rows.push({k: 'hops.' + i, label: (h?.name || t('imp_hop_name')) + use, planned: N(x.g), unit: 'g', plannedTxt: f(N(x.g), 1) + ' g'});
  });
  (r.yeast || []).forEach((x, i) => {
    const y = lib('yeast', x.ref);
    rows.push({k: 'yeast.' + i, label: y?.name || t('imp_yeast_name'), planned: N(x.qty), unit: '×', plannedTxt: f(N(x.qty), 1) + ' ×'});
  });
  (r.misc || []).forEach((x, i) => {
    const m = lib('misc', x.ref);
    const unit = m?.unit || '';
    rows.push({k: 'misc.' + i, label: m?.name || t('imp_misc_name'), planned: N(x.qty), unit, plannedTxt: f(N(x.qty), 2) + (unit ? ' ' + unit : '')});
  });
  return rows;
}
function brewExtraTempPlan(b) {
  const {r, c} = brewSnapCalc(b);
  const rows = [];
  if (c && c.kgMash > 0) rows.push({k: 'strike', label: t('xn_strike_label'), planned: rnd(c.strike, 0), plannedTxt: f(c.strike, 0) + ' °C'});
  (r?.mash || []).forEach((s, i) => rows.push({k: 'mash.' + i, label: s.name || t('xn_palier_n', {n: i + 1}), planned: N(s.temp), plannedTxt: f(N(s.temp), 0) + ' °C'}));
  if (c && c.sparge > 0) rows.push({k: 'sparge', label: t('xn_sparge_label'), planned: 78, plannedTxt: '78 °C'});
  rows.push({k: 'boil', label: t('xn_boil_label'), planned: 100, plannedTxt: '100 °C'});
  rows.push({k: 'postBoil', label: t('xn_postboil_label'), planned: '', plannedTxt: '—'});
  const ft = r?.ferment?.[0] != null && r.ferment[0].temp !== '' && r.ferment[0].temp != null ? N(r.ferment[0].temp) : '';
  rows.push({k: 'chilled', label: t('xn_chilled_label'), planned: ft, plannedTxt: ft === '' ? '—' : f(ft, 0) + ' °C'});
  return rows;
}
function brewExtraVolPlan(b) {
  const {c} = brewSnapCalc(b);
  if (!c) {
    return [
      {k: 'preSparge', label: t('xn_presparge_label'), plannedTxt: '—'},
      {k: 'spargeWater', label: t('xn_spargewater_label'), plannedTxt: '—'},
      {k: 'preBoil', label: t('xn_preboil_label'), plannedTxt: '—'},
      {k: 'postBoil', label: t('xn_postboil_label'), plannedTxt: '—'},
      {k: 'fermenter', label: t('xn_fermenter_label'), plannedTxt: '—'},
      {k: 'packaging', label: t('xn_packaging_label'), plannedTxt: '—'},
    ];
  }
  const mashVol = c.mashSteps.length ? c.mashSteps[c.mashSteps.length - 1].volume : c.mashWater;
  const postBoil = (c.V + c.P.trub) / (1 - c.P.shrink / 100);
  return [
    {k: 'preSparge', label: t('xn_presparge_label'), planned: rnd(mashVol, 1), plannedTxt: f(mashVol, 1) + ' L'},
    {k: 'spargeWater', label: t('xn_spargewater_label'), planned: rnd(c.sparge, 1), plannedTxt: f(c.sparge, 1) + ' L'},
    {k: 'preBoil', label: t('xn_preboil_label'), planned: rnd(c.preboil, 1), plannedTxt: f(c.preboil, 1) + ' L'},
    {k: 'postBoil', label: t('xn_postboil_label'), planned: rnd(postBoil, 1), plannedTxt: f(postBoil, 1) + ' L'},
    {k: 'fermenter', label: t('xn_fermenter_label'), planned: rnd(c.V, 1), plannedTxt: f(c.V, 1) + ' L'},
    {k: 'packaging', label: t('xn_packaging_label'), planned: rnd(c.V, 1), plannedTxt: f(c.V, 1) + ' L'},
  ];
}
function brewExtraSgPlan(b) {
  const {c} = brewSnapCalc(b);
  return [
    {k: 'preSparge', label: t('xn_presparge_label'), plannedTxt: '—'},
    {k: 'preBoil', label: t('xn_preboil_label'), plannedTxt: c ? gPrimaryText(c.pbSG) : '—'},
    {k: 'postBoil', label: t('xn_postboil_label'), plannedTxt: c ? gPrimaryText(c.og) : '—'},
  ];
}
const BREW_PH_ROWS = () => [
  {k: 'mashBefore', label: t('xn_ph_mashbefore'), rec: t('xn_rec_mashbefore')},
  {k: 'mashDuring', label: t('xn_ph_mashduring'), rec: t('xn_rec_mashduring')},
  {k: 'mashEnd', label: t('xn_ph_mashend'), rec: t('xn_rec_mashend')},
  {k: 'spargeWater', label: t('xn_spargewater_label'), rec: t('xn_rec_spargewater')},
  {k: 'preBoil', label: t('xn_preboil_label'), rec: t('xn_rec_preboil')},
  {k: 'postBoil', label: t('xn_postboil_label'), rec: t('xn_rec_postboil')},
];
function brewXnNum(attrs, val) {
  return `<input ${attrs} data-t="n" inputmode="decimal" value="${esc(val ?? '')}">`;
}
function brewExtraQtyExtraRow(x = {}) {
  return `<tr data-xn-extra>
    <td><input data-xn-en placeholder="${t('xn_ing_placeholder')}" aria-label="${t('xn_ing_added_aria')}" value="${esc(x.name || '')}"></td>
    <td class="xn-planned" data-label="${t('xn_col_qty_planned')}"><input data-xn-ep data-t="n" inputmode="decimal" aria-label="${t('xn_col_qty_planned')}" value="${esc(x.planned ?? '')}"> <input data-xn-eu placeholder="${t('xn_unit_placeholder')}" aria-label="${t('f_unit')}" value="${esc(x.unit || '')}"></td>
    <td class="xn-actual" data-label="${t('xn_col_qty_actual')}"><input data-xn-ea data-t="n" inputmode="decimal" aria-label="${t('xn_col_qty_actual')}" value="${esc(x.actual ?? '')}"></td>
    <td class="xn-actions noprint"><button type="button" class="ico" data-act="xnRmQty" aria-label="${t('ing_remove_aria')}">✕</button></td>
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
      <td class="xn-planned" data-label="${t('xn_col_qty_planned')}">${esc(row.plannedTxt)}</td>
      <td class="xn-actual" data-label="${t('xn_col_qty_actual')}">${brewXnNum(`data-xn-qty="${esc(row.k)}" aria-label="${t('xn_col_qty_actual')} ${esc(row.label)}"`, e.qtyActual[row.k])}</td>
      <td class="xn-actions"></td>
    </tr>`).join('') + qtyExtras.map(brewExtraQtyExtraRow).join('');
  const tempBody = brewExtraTempPlan(b).map(row => `<tr>
      <td>${esc(row.label)}</td>
      <td class="xn-planned" data-label="${t('xn_col_temp_planned')}">${esc(row.plannedTxt)}</td>
      <td class="xn-actual" data-label="${t('xn_col_temp_actual')}">${brewXnNum(`data-xn-temp="${esc(row.k)}" aria-label="${t('xn_col_temp_actual')} ${esc(row.label)}"`, e.temp[row.k])}</td>
    </tr>`).join('');
  const volBody = brewExtraVolPlan(b).map(row => `<tr>
      <td>${esc(row.label)}</td>
      <td class="xn-planned" data-label="${t('xn_col_vol_planned')}">${esc(row.plannedTxt)}</td>
      <td class="xn-actual" data-label="${t('xn_col_vol_actual')}">${brewXnNum(`data-xn-vol="${esc(row.k)}" aria-label="${t('xn_col_vol_actual')} ${esc(row.label)}"`, e.vol[row.k])}</td>
    </tr>`).join('');
  const sgBody = brewExtraSgPlan(b).map(row => `<tr>
      <td>${esc(row.label)}</td>
      <td class="xn-planned" data-label="${t('xn_col_sg_planned')}">${esc(row.plannedTxt)}</td>
      <td class="xn-actual" data-label="${t('xn_col_sg_actual')}">${brewXnNum(`data-f="xn.sg.${esc(row.k)}" aria-label="${t('xn_col_sg_actual')} ${esc(row.label)}"`, e.sg[row.k])}</td>
    </tr>`).join('');
  const phBody = BREW_PH_ROWS().map(row => `<tr>
      <td>${esc(row.label)}<div class="brew-xn-rec">${esc(row.rec)}</div></td>
      <td class="xn-actual" data-label="${t('xn_col_ph_actual')}">${brewXnNum(`data-f="xn.ph.${esc(row.k)}" aria-label="${t('xn_col_ph_actual')} ${esc(row.label)}"`, e.ph[row.k])}</td>
    </tr>`).join('');
  return `<details class="brew-extra"${open}>
    <summary>${t('xn_section_title')}</summary>
    <p class="sub" style="margin:.2rem 0 .5rem">${t('xn_intro')}</p>
    <h3>${t('xn_qty_title')}</h3>
    <div class="scroll"><table class="tbl brew-xn-tbl"><thead><tr><th>${t('col_ingredient')}</th><th class="xn-planned">${t('xn_col_qty_planned')}</th><th class="xn-actual">${t('xn_col_qty_actual')}</th><th class="xn-actions"></th></tr></thead>
      <tbody id="xn-qty-body">${qtyBody}</tbody></table></div>
    <div class="dlg-actions noprint" style="justify-content:flex-start;margin:.2rem 0 .5rem"><button type="button" class="btn ghost sm" data-act="xnAddQty">${t('xn_add_ing')}</button></div>
    <h3>${t('xn_temp_title')}</h3>
    <div class="scroll"><table class="tbl brew-xn-tbl"><thead><tr><th>${t('xn_col_step')}</th><th class="xn-planned">${t('xn_col_temp_planned')}</th><th class="xn-actual">${t('xn_col_temp_actual')}</th></tr></thead><tbody>${tempBody}</tbody></table></div>
    <h3>${t('xn_ph_title')}</h3>
    <p class="sub" style="margin:.1rem 0 .35rem">${t('xn_ph_note')}</p>
    <div class="scroll"><table class="tbl brew-xn-tbl"><thead><tr><th>${t('xn_col_measure')}</th><th class="xn-actual">${t('xn_col_ph_actual')}</th></tr></thead><tbody>${phBody}</tbody></table></div>
    <h3>${t('xn_sg_title')}</h3>
    <div class="scroll"><table class="tbl brew-xn-tbl"><thead><tr><th>${t('xn_col_measure')}</th><th class="xn-planned">${t('xn_col_sg_planned')}</th><th class="xn-actual">${t('xn_col_sg_actual')}</th></tr></thead><tbody>${sgBody}</tbody></table></div>
    <h3>${t('xn_vol_title')}</h3>
    <div class="scroll"><table class="tbl brew-xn-tbl"><thead><tr><th>${t('xn_col_measure')}</th><th class="xn-planned">${t('xn_col_vol_planned')}</th><th class="xn-actual">${t('xn_col_vol_actual')}</th></tr></thead><tbody>${volBody}</tbody></table></div>
    <h3>${t('xn_free_title')}</h3>
    <div class="fgrid"><label class="fld full"><span>${t('xn_free_label')}</span><textarea id="xn-free" data-f="xn.free">${esc(e.free)}</textarea></label></div>
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
    let node = e;
    for (let i = 0; i < path.length - 1; i++) node = node[path[i]];
    const last = path[path.length - 1];
    node[last] = el.dataset.t === 'n' ? (el.value === '' ? '' : N(el.value)) : el.value;
  });
  return e;
}
/** Sous le formulaire d'édition d'un brassin : Autres notes (si activé) ou rappel vers Compte. */
function brewFormExtraNotesHTML(b) {
  if (D.prefs.brewExtraNotes === true) {
    normalizeBrewExtraNotes(b);
    return brewExtraNotesHTML(b);
  }
  return `<p class="brew-extra-hint">${t('xn_hint_disabled', {btn: `<button type="button" data-act="go" data-v="account">${t('nav_account')}</button>`})}</p>`;
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
  return `<div class="bar"><h1>${t('brews_title')}</h1><div class="grow"></div><button class="btn ghost sm noprint" data-act="print">${t('sheet_print')}</button><button class="btn" data-act="newBrew">${t('brews_new')}</button></div>
  ${l.length ? `<div class="cards">${l.map(b => { const k = brewCalc(b); return `<div class="card" data-brewid="${b.id}" style="margin:0"><div style="display:flex;justify-content:space-between;gap:.5rem"><h2 style="margin:0">${esc(b.recipeName)}</h2><span class="chip">${STATUS()[b.status]}</span></div>
    <div class="sub">${fdate(b.date)} · ${f(b.vol, 1)} L</div>
    <dl class="kv" style="margin-top:.6rem"><dt>${t('brews_og')}</dt><dd>${b.ogM > 1 ? gUnit(b.ogM) : '–'} <span class="sub">${t('brews_target', {v: gPrimaryText(b.stats.og)})}</span></dd><dt>${t('brews_fg')}</dt><dd>${b.fgM > 0.99 ? gUnit(b.fgM) : '–'} <span class="sub">${t('brews_target', {v: gPrimaryText(b.stats.fg)})}</span></dd>
      <dt>${t('brews_refracto')}</dt><dd>≈ ${f(rawBrixAtFG(b.ogM > 1 ? b.ogM : b.stats.og, b.fgM > 0.99 ? b.fgM : b.stats.fg), 1)} °Bx</dd>
      <dt>${t('brews_real_alcohol')}</dt><dd>${k.abv === null ? '–' : f(k.abv, 1) + ' %'}</dd><dt>${t('stats_attenuation')}</dt><dd>${k.att === null ? '–' : f(k.att, 0) + ' %'}</dd><dt>${t('brews_real_eff')}</dt><dd>${k.eff === null ? '–' : f(k.eff, 0) + ' %'}</dd></dl>
    ${b.notes ? `<div style="margin-top:.7rem"><h3 style="margin:0 0 .2rem">${t('brew_edit_notes')}</h3><p style="white-space:pre-wrap;margin:0;font-size:1rem">${esc(b.notes)}</p></div>` : ''}
    ${b.tasting ? `<div style="margin-top:.7rem"><h3 style="margin:0 0 .2rem">${t('brew_edit_tasting')}</h3><p style="white-space:pre-wrap;margin:0;font-size:1rem">${esc(b.tasting)}</p></div>` : ''}
    ${D.prefs.brewExtraNotes && brewExtraNotesFilled(b) ? `<p class="brew-extra-chip">${t('xn_chip_filled')}</p>` : ''}
    <div class="dlg-actions noprint brew-card-actions"><button class="btn ghost sm" data-act="editBrew" data-id="${b.id}">${t('ing_edit')}</button><button class="btn ghost sm" data-act="printBrew" data-id="${b.id}">${t('sheet_print')}</button>${D.prefs.brewExtraNotes === true ? `<button class="btn ghost sm" data-act="printBrewExtra" data-id="${b.id}">${t('xn_print_plus')}</button>` : ''}<button class="btn danger sm" data-act="delBrew" data-id="${b.id}">${t('btn_delete')}</button></div></div>`; }).join('')}</div>`
    : `<div class="empty">${t('brews_empty')}</div>`}`;
}

/* --- Bibliothèque d'ingrédients --- */
const ING_FIELDS = {
  ferm: [{k: 'name', l: t('col_name')}, {k: 'type', l: t('col_type'), t: 'select', o: Object.entries(FTYPE())}, {k: 'ebc', l: t('f_color_ebc'), t: 'number'}, {k: 'extract', l: t('f_extract'), t: 'number'}, {k: 'price', l: t('f_price_kg'), t: 'number'}, {k: 'note', l: t('f_note')}],
  hop: [{k: 'name', l: t('col_name')}, {k: 'alpha', l: t('f_alpha'), t: 'number'}, {k: 'form', l: t('col_form'), t: 'select', o: [['Pellet', t('hop_form_pellet')], ['Cône entier', t('hop_form_leaf')]]}, {k: 'price', l: t('f_price_g'), t: 'number'}, {k: 'note', l: t('f_note')}],
  yeast: [{k: 'name', l: t('col_name')}, {k: 'type', l: t('col_type'), t: 'select', o: [['ale', 'Ale'], ['lager', 'Lager'], ['autre', t('f_other')]]}, {k: 'form', l: t('col_form'), t: 'select', o: [['dry', t('f_dry')], ['liquid', t('f_liquid')]]}, {k: 'att', l: t('f_attenuation'), t: 'number'}, {k: 'tmin', l: t('f_tmin'), t: 'number'}, {k: 'tmax', l: t('f_tmax'), t: 'number'}, {k: 'price', l: t('f_price_pkg'), t: 'number'}, {k: 'note', l: t('f_note')}],
  misc: [{k: 'name', l: t('col_name')}, {k: 'unit', l: t('f_unit'), t: 'select', o: [['g', 'g'], ['mL', 'mL'], ['pce', t('f_piece')]]}, {k: 'price', l: t('f_price_unit'), t: 'number'}, {k: 'note', l: t('f_note')}],
  style: [{k: 'name', l: t('col_name')}, {k: 'guide', l: t('f_guide')}, {k: 'category', l: t('f_category')}, {k: 'type', l: t('col_type'), t: 'select', o: [['ale', 'Ale'], ['lager', 'Lager'], ['mixed', t('f_mixed')], ['cider', t('f_cider')], ['mead', t('f_mead')], ['other', t('f_other')]]},
    {k: 'ogMin', l: t('f_ogmin'), t: 'number'}, {k: 'ogMax', l: t('f_ogmax'), t: 'number'}, {k: 'fgMin', l: t('f_fgmin'), t: 'number'}, {k: 'fgMax', l: t('f_fgmax'), t: 'number'},
    {k: 'ibuMin', l: t('f_ibumin'), t: 'number'}, {k: 'ibuMax', l: t('f_ibumax'), t: 'number'}, {k: 'colorMin', l: t('f_colormin'), t: 'number'}, {k: 'colorMax', l: t('f_colormax'), t: 'number'},
    {k: 'abvMin', l: t('f_abvmin'), t: 'number'}, {k: 'abvMax', l: t('f_abvmax'), t: 'number'}, {k: 'note', l: t('f_description'), t: 'textarea'}]
};
const ING_NEW = {ferm: {name: '', type: 'grain', ebc: 5, extract: 80, price: 0}, hop: {name: '', alpha: 5, form: 'Pellet', price: 0}, yeast: {name: '', type: 'ale', form: 'dry', att: 75, tmin: 15, tmax: 22, price: 0}, misc: {name: '', unit: 'g', price: 0},
  style: {name: '', guide: '', category: '', type: 'ale', ogMin: 1.040, ogMax: 1.050, fgMin: 1.008, fgMax: 1.012, ibuMin: 20, ibuMax: 35, colorMin: 8, colorMax: 20, abvMin: 4, abvMax: 6}};
const ING_COLS = {
  ferm: [[t('col_name'), x => esc(x.name)], [t('col_type'), x => FTYPE()[x.type]], ['EBC', x => f(x.ebc, 0)], [t('col_extract_short'), x => f(x.extract, 0) + ' %'], [t('col_price'), x => x.price ? f(x.price, 2) + ' €/kg' : '–']],
  hop: [[t('col_name'), x => esc(x.name)], ['α', x => f(x.alpha, 1) + ' %'], [t('col_form'), x => esc(x.form)], [t('col_price'), x => x.price ? f(x.price, 3) + ' €/g' : '–']],
  yeast: [[t('col_name'), x => esc(x.name)], [t('col_type'), x => esc(x.type)], [t('stats_attenuation'), x => f(x.att, 0) + ' %'], [t('col_range'), x => f(x.tmin, 0) + '–' + f(x.tmax, 0) + ' °C'], [t('col_price'), x => x.price ? f(x.price, 2) + ' €' : '–']],
  misc: [[t('col_name'), x => esc(x.name)], [t('f_unit'), x => esc(x.unit)], [t('col_price'), x => x.price ? f(x.price, 3) + ' €/' + x.unit : '–']],
  style: [[t('col_name'), x => esc(x.name)], [t('col_guide'), x => esc(x.guide) || '–'], [t('col_category'), x => esc(x.category) || '–'], [t('kpi_og'), x => f(x.ogMin, 3) + '–' + f(x.ogMax, 3)], ['IBU', x => f(x.ibuMin, 0) + '–' + f(x.ibuMax, 0)], ['EBC', x => f(x.colorMin, 0) + '–' + f(x.colorMax, 0)]]
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
  const tab = ui.tab, C = ING_COLS[tab], showArch = ui.showArchivedIng;
  const stockable = !showArch && STOCK_TYPES.includes(tab);   // les styles, et les archives, ne se stockent pas
  const l = D.ing[tab].filter(x => !!x.archived === showArch);
  const archCount = D.ing[tab].filter(x => x.archived).length;
  return `<div class="bar"><h1>${t('ings_title')}</h1><div class="grow"></div>
    <button class="btn ghost" data-act="importLib">${t('ings_import_lib')}</button>
    <input type="file" id="importLibFile" accept=".xml,.json,text/xml,application/xml,application/json" style="display:none">
    <button class="btn" data-act="addIng">${t('ings_add')}</button></div>
  <div class="seg">${Object.entries(LIBNAME()).map(([k, v]) => `<button data-act="ingTab" data-t="${k}" aria-pressed="${tab === k}">${v}</button>`).join('')}</div>
  <div class="card"><p class="sub" style="margin-top:0">${tab === 'style' ? t('ings_note_style') : t('ings_note_normal')}${stockable ? t('ings_note_stockable') : ''}
    ${l.length ? '' : (showArch ? t('ings_none_archived') : t('ings_empty_lib'))}
    ${archCount ? ` <button class="btn ghost sm" data-act="toggleArchived" style="margin-left:.4rem">${showArch ? t('ings_view_active') : t('ings_view_archived', {n: archCount})}</button>` : ''}</p>
  ${showArch ? `<p class="sub" style="margin-top:-.4rem">${t('ings_archived_note')}</p>` : ''}
  ${l.length ? `<div class="scroll"><table class="tbl"><thead><tr><th data-act="ingSort" data-k="name" style="cursor:pointer">${t('col_name')}${ingSortArrow('name')}</th>${C.slice(1).map(c => `<th>${c[0]}</th>`).join('')}${stockable ? `<th data-act="ingSort" data-k="stock" style="cursor:pointer">${t('col_stock')}${ingSortArrow('stock')}</th>` : ''}<th></th></tr></thead>${sortIng(l, stockable).map(x => `<tr>${C.map(c => `<td>${c[1](x)}</td>`).join('')}${stockable ? `<td><input inputmode="decimal" data-stock="${x.id}" value="${D.stock[x.id] === undefined ? '' : esc(D.stock[x.id])}" placeholder="—" aria-label="${t('col_stock')} ${esc(x.name)}" style="max-width:6.5rem"></td>` : ''}<td style="white-space:nowrap;text-align:right">${showArch
    ? `<button class="btn ghost sm" data-act="restoreIng" data-id="${x.id}">${t('ing_restore')}</button>`
    : `<button class="btn ghost sm" data-act="editIng" data-id="${x.id}">${t('ing_edit')}</button> <button class="btn ghost sm" data-act="dupIng" data-id="${x.id}">${t('ing_copy')}</button> <button class="ico" data-act="delIng" data-id="${x.id}" aria-label="${t('ing_remove_aria')}">✕</button>`}</td></tr>`).join('')}</table></div>` : ''}</div>`;
}

/* --- Profils matériel --- */
const PROF_FIELDS = [{k: 'name', l: t('p_name')}, {k: 'batch', l: t('p_batch'), t: 'number'}, {k: 'boil', l: t('p_boil'), t: 'number'}, {k: 'evap', l: t('p_evap'), t: 'number'},
  {k: 'trub', l: t('p_trub'), t: 'number'}, {k: 'mashLoss', l: t('p_mashloss'), t: 'number'}, {k: 'absorb', l: t('p_absorb'), t: 'number'},
  {k: 'ratio', l: t('fld_ratio'), t: 'number'}, {k: 'infusionTemp', l: t('fld_infusion_temp'), t: 'number'}, {k: 'shrink', l: t('p_shrink'), t: 'number'}, {k: 'eff', l: t('p_eff'), t: 'number'},
  {k: 'mashMethod', l: t('fld_mashmethod'), t: 'select', o: BREW_METHOD()}, {k: 'biabSqueeze', l: t('p_biabsqueeze'), t: 'check'},
  {k: 'mashTunMax', l: t('p_mashtunmax'), t: 'number'}];
function viewProfiles() {
  return `<div class="bar"><h1>${t('nav_profiles')}</h1><div class="grow"></div><button class="btn" data-act="addProfile">${t('profiles_new')}</button></div>
  <div class="cards">${D.profiles.map(p => `<div class="card" style="margin:0"><h2>${esc(p.name)}</h2>
    <dl class="kv"><dt>${t('p_card_batch')}</dt><dd>${f(p.batch, 1)} L</dd><dt>${t('p_card_boil')}</dt><dd>${f(p.boil, 0)} min</dd><dt>${t('p_card_evap')}</dt><dd>${f(p.evap, 1)} L/h</dd><dt>${t('p_card_trub')}</dt><dd>${f(p.trub, 1)} L</dd><dt>${t('p_card_mashloss')}</dt><dd>${f(p.mashLoss, 1)} L</dd>
    <dt>${t('p_card_absorb')}</dt><dd>${f(p.absorb, 2)} L/kg</dd><dt>${t('p_card_ratio')}</dt><dd>${f(p.ratio, 1)} L/kg</dd><dt>${t('p_card_infusion')}</dt><dd>${f(p.infusionTemp, 0)} °C</dd><dt>${t('p_card_shrink')}</dt><dd>${f(p.shrink, 1)} %</dd><dt>${t('p_card_eff')}</dt><dd>${f(p.eff, 0)} %</dd>
    <dt>${t('p_card_method')}</dt><dd>${esc((BREW_METHOD().find(([k]) => k === p.mashMethod) || BREW_METHOD()[0])[1])}</dd>${p.mashMethod === 'biab' ? `<dt>${t('p_card_squeeze')}</dt><dd>${p.biabSqueeze ? t('yes') : t('no')}</dd>` : ''}${p.mashTunMax > 0 ? `<dt>${t('p_card_mashtunmax')}</dt><dd>${f(p.mashTunMax, 1)} L</dd>` : ''}</dl>
    <div class="dlg-actions" style="justify-content:flex-start"><button class="btn ghost sm" data-act="editProfile" data-id="${p.id}">${t('ing_edit')}</button><button class="btn ghost sm" data-act="dupProfile" data-id="${p.id}">${t('ing_copy')}</button><button class="btn danger sm" data-act="delProfile" data-id="${p.id}">${t('btn_delete')}</button></div></div>`).join('') || `<div class="empty">${t('profiles_empty')}</div>`}</div>`;
}

/* --- Compte --- */
function viewAccount() {
  return `<div class="bar"><h1>${t('account_title')}</h1></div>
  <div class="card"><div class="account-id">${avatarHTML('lg')}<div><h2 style="margin:0">${esc(USER)}</h2><p class="sub" style="margin:.1rem 0 0">${t('account_summary', {recipes: D.recipes.length, brews: D.brews.length, profiles: D.profiles.length})}</p></div></div>
    <div class="dlg-actions" style="justify-content:flex-start;flex-wrap:wrap;margin:.8rem 0 0">
      <a class="btn ghost sm" href="help.html" style="text-decoration:none">${t('nav_help')}</a>
      ${AUTH.mode === 'phpbb'
      ? `<a class="btn ghost sm" href="/forum/" style="text-decoration:none">${t('nav_forum_back')}</a>`
      : `<button class="btn ghost sm" data-act="logout">${t('account_logout')}</button>`}
    </div></div>
  <div class="card"><h2>${t('account_language_title')}</h2>
    <div class="seg">${AVAILABLE_LANGS.map(k => `<button data-act="setLang" data-v="${k}" aria-pressed="${D.prefs.lang === k}">${esc(LANG_NATIVE_NAMES[k] || k)}</button>`).join('')}</div>
    <p class="sub" style="margin-bottom:0">${t('account_language_note')}</p></div>
  <div class="card"><h2>${t('avatar_title')}</h2>
    ${AUTH.mode === 'phpbb'
      ? `<p class="sub" style="margin-top:0">${t('avatar_forum_note')}${AUTH.avatar_url ? '' : t('avatar_forum_none')}</p>
        <div class="dlg-actions" style="justify-content:flex-start;flex-wrap:wrap;margin:0">
          <button class="btn ghost sm" data-act="avatarUpload">${t('avatar_upload_hophop')}</button>
          <input type="file" id="avatarFile" accept="image/*" style="display:none">
          ${D.prefs.avatarType === 'image' ? `<button class="btn ghost sm" data-act="avatarReset">${t('avatar_reset_forum')}</button>` : ''}
        </div>`
      : `<p class="sub" style="margin-top:0">${t('avatar_note')}</p>
    <div class="avatar-picks">${AVATAR_PICKS.map(e => `<button data-act="setAvatarEmoji" data-v="${e}" aria-pressed="${D.prefs.avatarType === 'emoji' && D.prefs.avatarEmoji === e}">${e}</button>`).join('')}</div>
    <div class="dlg-actions" style="justify-content:flex-start;flex-wrap:wrap;margin:0">
      <button class="btn ghost sm" data-act="avatarUpload">${t('avatar_upload')}</button>
      <input type="file" id="avatarFile" accept="image/*" style="display:none">
      ${D.prefs.avatarType === 'image' ? `<button class="btn ghost sm" data-act="avatarReset">${t('avatar_reset')}</button>` : ''}
    </div>`}</div>
  <div class="card"><h2>${t('appearance_title')}</h2>
    <h3>${t('appearance_theme')}</h3><div class="seg">${THEMES().map(([k, l]) => `<button data-act="setTheme" data-v="${k}" aria-pressed="${D.prefs.theme === k}">${l}</button>`).join('')}</div>
    <h3>${t('appearance_density')}</h3><div class="seg">
      <button data-act="setDensity" data-v="classic" aria-pressed="${D.prefs.density === 'classic'}">${t('density_classic')}</button>
      <button data-act="setDensity" data-v="compact" aria-pressed="${D.prefs.density === 'compact'}">${t('density_compact')}</button></div>
    <p class="sub" style="margin-bottom:0">${t('density_note')}</p></div>
  <div class="card"><h2>${t('units_title')}</h2>
    <p class="sub" style="margin-top:0">${t('units_note')}</p>
    <div class="seg">
      <button data-act="setUnit" data-v="sg" aria-pressed="${D.prefs.unitPrimary === 'sg'}">${t('unit_sg')}</button>
      <button data-act="setUnit" data-v="brix" aria-pressed="${D.prefs.unitPrimary === 'brix'}">${t('unit_brix')}</button></div>
    <h3>${t('refract_title')}</h3>
    <div class="seg">
      <button data-act="setRefract" data-v="linear" aria-pressed="${D.prefs.refractFormula !== 'cubic'}">${t('refract_linear')}</button>
      <button data-act="setRefract" data-v="cubic" aria-pressed="${D.prefs.refractFormula === 'cubic'}">${t('refract_cubic')}</button></div>
    <p class="sub" style="margin-bottom:0">${t('refract_note')}</p></div>
  <div class="card"><h2>${t('xn_toggle_title')}</h2>
    <p class="sub" style="margin-top:0">${t('xn_toggle_desc')}</p>
    <div class="seg">
      <button data-act="setBrewExtra" data-v="1" aria-pressed="${D.prefs.brewExtraNotes === true}">${t('xn_enabled')}</button>
      <button data-act="setBrewExtra" data-v="0" aria-pressed="${D.prefs.brewExtraNotes !== true}">${t('xn_disabled')}</button>
    </div>
    <p class="sub" style="margin-bottom:0">${t('xn_toggle_note')}</p></div>
  <div class="card"><h2>${t('backup_title')}</h2><p class="sub" style="margin-top:0">${t('backup_note')}</p>
    <div class="dlg-actions" style="justify-content:flex-start;flex-wrap:wrap;margin:0"><button class="btn ghost sm" data-act="backup">${t('backup_export')}</button><button class="btn ghost sm" data-act="restore">${t('backup_import')}</button><input type="file" id="restoreFile" accept=".json,application/json" style="display:none"></div></div>
  ${AUTH.features.delete_account ? `<div class="card"><h2>${t('danger_zone')}</h2><button class="btn danger sm" data-act="delAccount">${t('delete_account_btn')}</button></div>` : ''}`;
}

/* --- Rendu général --- */
function render() {
  const app = $('#app');
  if (!USER) { app.innerHTML = authHTML(); return; }
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
    if (fd.t === 'textarea') return fld(fd.l, `<textarea id="${id}" data-f="${fd.k}"${fd.big ? ' rows="4"' : ''}>${esc(v)}</textarea>`, 'full');
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
  showDlg(`<h2>${t('dlg_confirm_title')}</h2><p>${msg}</p><div class="dlg-actions"><button class="btn ghost" data-act="dlgClose">Annuler</button><button class="btn danger" data-act="dlgSave">${yes}</button></div>`);
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
  if (!rows.length) { toast(t('brew_recorded_notracked')); return; }
  const fields = rows.map(row => ({k: 'q_' + row.id, l: `${row.name} (${row.unit})`, t: 'number'}));
  const vals = {}; rows.forEach(row => vals['q_' + row.id] = rnd(row.qty, 3));
  openForm(t('brew_used_qty_title'), fields, vals, v => {
    rows.forEach(row => { const used = N(v['q_' + row.id]); D.stock[row.id] = Math.max(0, rnd(D.stock[row.id] - used, 3)); });
    persist(); toast(t('stock_updated'));
  }, `<p class="sub">${t('brew_used_qty_note')}</p>`);
}
const brewFields = [{k: 'date', l: t('brew_date_target'), t: 'date'}, {k: 'deduct', l: t('brew_deduct'), t: 'check'}];
const profDefaults = () => ({name: t('new_profile_name'), batch: 20, boil: 60, evap: 3, trub: 1.5, mashLoss: 1, absorb: 1, ratio: 3, infusionTemp: 96, shrink: 4, eff: 72, mashMethod: 'classic', biabSqueeze: false, mashTunMax: 0});

/** Retire les blocs « Autres notes » injectés pour Print+ (filet si revert incomplet). */
function clearPrintInjections() {
  document.querySelectorAll('#main .print-inject').forEach(el => el.remove());
}

/** Impression navigateur : revert dès que print() rend la main (Annuler ou Imprimer) + afterprint / matchMedia en secours. */
function printWithRevert(revert) {
  let finished = false;
  const finish = () => {
    if (finished) return;
    finished = true;
    revert();
    window.removeEventListener('afterprint', finish);
    mq.removeEventListener('change', onMq);
  };
  const mq = window.matchMedia('print');
  const onMq = () => { if (!mq.matches) finish(); };
  window.addEventListener('afterprint', finish);
  mq.addEventListener('change', onMq);
  try {
    window.print();
  } catch {
    toast(t('toast_print_fallback'));
  } finally {
    // Sur Chrome/Safari/Firefox, print() bloque jusqu’à fermeture du dialogue : finally = Annuler ou OK.
    finish();
  }
  setTimeout(finish, 500);
}

const ACT = {
  authMode: el => { ui.auth = el.dataset.m; render(); }, authGo: () => authSubmit(), logout: () => logout(),
  setLang: el => { const v = el.dataset.v; if (v === D.prefs.lang) return; D.prefs.lang = v; persist(); saveNow().then(() => location.reload()); },
  setTheme: el => { D.prefs.theme = el.dataset.v; applyPrefs(); persist(); renderMain(true); },
  setDensity: el => { D.prefs.density = el.dataset.v; applyPrefs(); persist(); renderMain(true); },
  setUnit: el => { D.prefs.unitPrimary = el.dataset.v; persist(); renderMain(true); },
  setRefract: el => { D.prefs.refractFormula = el.dataset.v; persist(); renderMain(true); },
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
  print: () => { try { window.print(); } catch { toast(t('toast_print_fallback')); } },
  printBrew: el => {
    const id = el.dataset.id;
    const cards = document.querySelectorAll('#main .cards > .card[data-brewid]');
    cards.forEach(c => { if (c.dataset.brewid !== id) c.classList.add('print-hide'); });
    printWithRevert(() => cards.forEach(c => c.classList.remove('print-hide')));
  },
  printBrewExtra: el => {
    const id = el.dataset.id;
    const b = D.brews.find(x => x.id === id);
    if (!b) return;
    const cards = document.querySelectorAll('#main .cards > .card[data-brewid]');
    cards.forEach(c => { if (c.dataset.brewid !== id) c.classList.add('print-hide'); });
    let injected = null;
    const card = document.querySelector(`#main .cards > .card[data-brewid="${id}"]`);
    if (card) {
      normalizeBrewExtraNotes(b);
      const wrap = document.createElement('div');
      wrap.innerHTML = brewExtraNotesHTML(b);
      injected = wrap.firstElementChild;
      injected.setAttribute('open', '');   // déplié pour l'aperçu papier uniquement (retiré après le dialogue)
      injected.classList.add('print-inject');
      const actions = card.querySelector('.dlg-actions');
      card.insertBefore(injected, actions || null);
    }
    printWithRevert(() => {
      cards.forEach(c => c.classList.remove('print-hide'));
      clearPrintInjections();
    });
  },
  dupRecipe: () => { const r = clone(R()); r.id = uid(); r.name += ' (copie)'; r.created = r.updated = Date.now(); D.recipes.push(r); ui.rid = r.id; persist(); renderMain(); window.scrollTo(0, 0); toast(t('toast_recipe_dup')); },
  delRecipe: () => ask(t('recipe_confirm_delete', {name: esc(R().name)}), t('btn_delete'), () => { D.recipes = D.recipes.filter(x => x.id !== ui.rid); ui.view = 'recipes'; persist(); renderMain(); }),
  add: el => {
    const r = R(), rowType = el.dataset.t, lt = RLIB[rowType], first = lt && D.ing[lt][0];
    if (lt && !first) { toast(t('ings_need_first')); return; }
    const row = {ferm: () => ({ref: first.id, kg: 1}), hops: () => ({ref: first.id, g: 20, use: 'boil', time: 60}), yeast: () => ({ref: first.id, qty: 1}),
      misc: () => ({ref: first.id, qty: 1, use: 'boil', time: 15}), mash: () => ({name: t('palier_label'), temp: 65, time: 30, method: 'infusion'}), ferment: () => ({name: t('step_label'), temp: 18, days: 7})}[rowType]();
    r[rowType].push(row); r.updated = Date.now(); persist(); renderMain(true);
  },
  rm: el => { const r = R(); r[el.dataset.t].splice(+el.dataset.i, 1); r.updated = Date.now(); persist(); renderMain(true); },
  resizeApply: () => {
    const nv = N($('#newvol').value), r = R(); if (nv <= 0 || nv === N(r.batch)) { toast(t('toast_need_diff_vol')); return; }
    ask(t('resize_confirm', {factor: f(nv / N(r.batch), 3), from: f(N(r.batch), 1), to: f(nv, 1)}), t('btn_apply'), () => { scaleRecipe(r, nv); persist(); renderMain(true); toast(t('toast_resized')); });
  },
  resizeCopy: () => {
    const nv = N($('#newvol').value), r = R(); if (nv <= 0) { toast(t('toast_need_valid_vol')); return; }
    const c = clone(r); c.id = uid(); c.name += ` (${f(nv, 0)} L)`; c.created = Date.now(); scaleRecipe(c, nv);
    D.recipes.push(c); ui.rid = c.id; persist(); renderMain(); window.scrollTo(0, 0); toast(t('toast_resized_copy'));
  },
  exp: el => showExport(el.dataset.k),
  shareBrewRecipes: () => shareToBrewRecipes(),
  pickBrewRecipesStyle: () => openBrewRecipesStylePicker(),
  brewFrom: () => openForm(t('editor_brewfrom'), brewFields, {date: today(), deduct: false}, v => {
    const r = R(); createBrew(r, v.date || today());
    if (v.deduct) { openStockDeductDialog(r); return false; }
    toast(t('toast_brew_saved'));
  }),
  newBrew: () => {
    if (!D.recipes.length) { toast(t('brew_need_recipe')); return; }
    openForm(t('brew_new_title'), [{k: 'rid', l: t('brew_new_recipe_field'), t: 'select', o: D.recipes.map(r => [r.id, r.name])}, ...brewFields], {rid: D.recipes[0].id, date: today(), deduct: false},
      v => {
        const rec = D.recipes.find(r => r.id === v.rid); createBrew(rec, v.date || today()); renderMain(true);
        if (v.deduct) { openStockDeductDialog(rec); return false; }
        toast(t('toast_brew_saved'));
      });
  },
  editBrew: el => {
    const b = D.brews.find(x => x.id === el.dataset.id);
    normalizeBrewExtraNotes(b);
    openForm(b.recipeName, [{k: 'date', l: t('brew_edit_date'), t: 'date'}, {k: 'status', l: t('brew_edit_status'), t: 'select', o: Object.entries(STATUS())}, {k: 'ogM', l: t('brew_edit_ogm'), t: 'number'}, {k: 'fgM', l: t('brew_edit_fgm'), t: 'number'},
      {k: 'vol', l: t('brew_edit_vol'), t: 'number'}, {k: 'notes', l: t('brew_edit_notes'), t: 'textarea', big: true}, {k: 'tasting', l: t('brew_edit_tasting'), t: 'textarea', big: true}], b,
      v => {
        b.date = v.date; b.status = v.status; b.ogM = v.ogM; b.fgM = v.fgM; b.vol = v.vol; b.notes = v.notes; b.tasting = v.tasting;
        if (D.prefs.brewExtraNotes === true) b.extraNotes = readBrewExtraFromDlg();
        persist(); renderMain(true);
      },
      `<p class="sub">${t('brew_edit_note')}</p>${brewFormExtraNotesHTML(b)}`);
  },
  delBrew: el => ask(t('brew_confirm_delete'), t('btn_delete'), () => { D.brews = D.brews.filter(x => x.id !== el.dataset.id); persist(); renderMain(true); }),
  setBrewExtra: el => { D.prefs.brewExtraNotes = el.dataset.v === '1'; persist(); renderMain(true); },
  xnAddQty: () => { const tb = $('#xn-qty-body'); if (tb) tb.insertAdjacentHTML('beforeend', brewExtraQtyExtraRow()); },
  xnRmQty: el => { const tr = el.closest('tr'); if (tr) tr.remove(); },
  ingTab: el => { ui.tab = el.dataset.t; renderMain(true); },
  importLib: () => $('#importLibFile').click(),
  ingSort: el => { const k = el.dataset.k; if (ui.ingSort.key === k) ui.ingSort.dir = ui.ingSort.dir === 'desc' ? 'asc' : 'desc'; else ui.ingSort = {key: k, dir: 'asc'}; renderMain(true); },
  addIng: () => { const tab = ui.tab; openForm(t('ing_new_title', {name: LIBNAME()[tab]}), ING_FIELDS[tab], ING_NEW[tab], v => { if (!v.name.trim()) { toast(t('ing_name_required')); return false; } D.ing[tab].push({id: uid(), note: '', ...v}); persist(); renderMain(true); }); },
  editIng: el => { const tab = ui.tab, x = lib(tab, el.dataset.id); openForm(x.name, ING_FIELDS[tab], x, v => { if (!v.name.trim()) { toast(t('ing_name_required')); return false; } Object.assign(x, v); persist(); renderMain(true); updateStats(); }); },
  dupIng: el => { const tab = ui.tab, x = clone(lib(tab, el.dataset.id)); x.id = uid(); x.name += t('copy_suffix'); x.archived = false; D.ing[tab].push(x); persist(); renderMain(true); },
  delIng: el => { const tab = ui.tab, x = lib(tab, el.dataset.id);
    ask(t('ing_confirm_remove', {name: esc(x.name)}), t('ing_remove_aria'), () => { x.archived = true; persist(); renderMain(true); }); },
  restoreIng: el => { const tab = ui.tab, x = lib(tab, el.dataset.id); x.archived = false; persist(); renderMain(true); toast(t('ing_restored')); },
  toggleArchived: () => { ui.showArchivedIng = !ui.showArchivedIng; renderMain(true); },
  addProfile: () => openForm(t('new_profile_dlg_title'), PROF_FIELDS, profDefaults(), v => { D.profiles.push({id: uid(), ...v}); persist(); renderMain(true); }),
  editProfile: el => { const p = D.profiles.find(x => x.id === el.dataset.id); openForm(p.name, PROF_FIELDS, p, v => { Object.assign(p, v); persist(); renderMain(true); }); },
  dupProfile: el => { const p = clone(D.profiles.find(x => x.id === el.dataset.id)); p.id = uid(); p.name += ' (copie)'; D.profiles.push(p); persist(); renderMain(true); },
  delProfile: el => ask(t('profile_confirm_delete'), t('btn_delete'), () => { D.profiles = D.profiles.filter(x => x.id !== el.dataset.id); persist(); renderMain(true); }),
  backup: () => saveFile(`hophop-${slug(USER)}-${today()}.json`, JSON.stringify({app: 'hophop', user: USER, data: D}, null, 2), 'application/json'),
  restore: () => $('#restoreFile').click(),
  importXML: () => $('#importBeerXMLFile').click(),
  importRecipeJSON: () => $('#importBeerJSONFile').click(),
  delAccount: () => ask(t('account_confirm_delete'), t('btn_delete'), async () => { try { await api('delete_account'); } catch (e) { toast(e.message); return; } USER = D = null; ui.auth = 'login'; render(); }),
};

/* Délégation d'événements : un seul écouteur par type */
document.addEventListener('click', e => { const el = e.target.closest('[data-act]'); if (el && ACT[el.dataset.act]) ACT[el.dataset.act](el, e); });
document.addEventListener('input', e => {
  const el = e.target;
  if (el.dataset.b) {                                   // champ de la recette en cours d'édition
    const r = R(); if (!r) return;
    setPath(r, el.dataset.b, el.type === 'checkbox' ? el.checked : (el.hasAttribute('data-n') ? N(el.value) : el.value));
    r.updated = Date.now(); persist(); updateStats();
    if (el.dataset.b === 'batch') { const nv = $('#newvol'); if (nv) nv.value = el.value; }
    if (el.dataset.b === 'mashMethod') { renderMain(true); return; }   // affiche/masque l'option de pressurage du sac (BIAB)
    if (el.dataset.b === 'profileId') {                   // reprend les valeurs du nouveau profil (volume, ratio, rendement, eau d'infusion, méthode…)
      const p = D.profiles.find(x => x.id === r.profileId) || DEFAULT_P();
      r.batch = p.batch; r.ratio = p.ratio; r.eff = p.eff; r.infusionTemp = p.infusionTemp; r.mashMethod = p.mashMethod; r.biabSqueeze = p.biabSqueeze;
      toast(t('toast_values_from_profile', {name: p.name})); renderMain(true); return;
    }
  } else if (el.id === 'newvol') { const r = R(), nv = N(el.value); $('#kfac').textContent = nv > 0 && N(r.batch) > 0 ? '× ' + f(nv / N(r.batch), 3) : ''; }
  else if (el.dataset.q !== undefined) { ui.q = el.value; $('#rlist').innerHTML = recipeListHTML(); }
  else if (el.dataset.stock) {                          // stock : vide = non suivi
    if (el.value.trim() === '') delete D.stock[el.dataset.stock]; else D.stock[el.dataset.stock] = Math.max(0, N(el.value));
    persist();
  }
});
document.addEventListener('change', e => {
  const el = e.target;
  if (el.dataset.b && el.tagName === 'SELECT' && 'rr' in el.dataset) renderMain(true);   // le champ "temps" dépend de l'usage
  if (el.id === 'restoreFile' && el.files[0]) {
    const fr = new FileReader();
    fr.onload = () => { try {
      const j = JSON.parse(fr.result); if (!j.data || !j.data.ing) throw 0;
      ask(t('confirm_restore'), t('btn_import'), () => { D = normalizeData(j.data); persist(); saveNow(); renderMain(); toast(t('toast_backup_imported')); });
    } catch { toast(t('toast_backup_invalid')); } };
    fr.readAsText(el.files[0]); el.value = '';
  }
  if (el.id === 'importLibFile' && el.files[0]) {
    const file = el.files[0];
    const fr = new FileReader();
    fr.onload = () => {
      const text = fr.result;
      openForm(t('import_guide_title'), [{k: 'guide', l: t('import_guide_field')}], {guide: guessGuide(file.name)}, v => {
        let counts;
        try { counts = importLibraryFile(text, v.guide.trim()); }
        catch (e) { toast(e.message); return false; }
        const LBL = LIBNAME();
        const parts = Object.entries(counts).filter(([, n]) => n).map(([k, n]) => `${n} ${LBL[k]}`);
        persist(); toast(t('toast_lib_imported', {parts: parts.join(', ')})); renderMain(true);
      }, `<p class="sub">${t('import_guide_note')}</p>`);
    };
    fr.readAsText(file); el.value = '';
  }
  if (el.id === 'importBeerXMLFile' && el.files[0]) {
    const fr = new FileReader();
    fr.onload = () => {
      let added;
      try { added = importBeerXML(fr.result); }
      catch (e) { toast(e.message); return; }
      persist(); toast(added.length > 1 ? t('toast_recipes_imported_many', {n: added.length}) : t('toast_recipe_imported_one'));
      if (added.length === 1) { ui.rid = added[0].id; ui.view = 'editor'; renderMain(); window.scrollTo(0, 0); }
      else renderMain(true);
    };
    fr.readAsText(el.files[0]); el.value = '';
  }
  if (el.id === 'importBeerJSONFile' && el.files[0]) {
    const fr = new FileReader();
    fr.onload = () => {
      let added;
      try { added = importBeerJSONRecipes(fr.result); }
      catch (e) { toast(e.message); return; }
      persist(); toast(added.length > 1 ? t('toast_recipes_imported_many', {n: added.length}) : t('toast_recipe_imported_one'));
      if (added.length === 1) { ui.rid = added[0].id; ui.view = 'editor'; renderMain(); window.scrollTo(0, 0); }
      else renderMain(true);
    };
    fr.readAsText(el.files[0]); el.value = '';
  }
  if (el.id === 'avatarFile' && el.files[0]) {
    resizeAvatarImage(el.files[0]).then(dataUrl => {
      D.prefs.avatarType = 'image'; D.prefs.avatarImage = dataUrl; persist(); renderMain(true);
    }).catch(() => toast(t('toast_invalid_image')));
    el.value = '';
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
    const nm = xtext(el, 'NAME'); if (!nm) return;
    const category = (xtext(el, 'CATEGORY_NUMBER') + xtext(el, 'STYLE_LETTER')) || xtext(el, 'CATEGORY');
    const type = {Lager: 'lager', Ale: 'ale', Mead: 'mead', Wheat: 'ale', Mixed: 'mixed', Cider: 'cider'}[xtext(el, 'TYPE')] || 'other';
    upsertStyle(nm, guide, {category, type,
      ogMin: rnd(xnum(el, 'OG_MIN', 1.040), 3), ogMax: rnd(xnum(el, 'OG_MAX', 1.050), 3),
      fgMin: rnd(xnum(el, 'FG_MIN', 1.008), 3), fgMax: rnd(xnum(el, 'FG_MAX', 1.012), 3),
      ibuMin: rnd(xnum(el, 'IBU_MIN', 0), 0), ibuMax: rnd(xnum(el, 'IBU_MAX', 0), 0),
      colorMin: rnd(xnum(el, 'COLOR_MIN', 0) * 1.97, 1), colorMax: rnd(xnum(el, 'COLOR_MAX', 0) * 1.97, 1),
      abvMin: rnd(xnum(el, 'ABV_MIN', 0), 1), abvMax: rnd(xnum(el, 'ABV_MAX', 0), 1),
      note: xtext(el, 'NOTES')}); counts.style++;
  });
  return counts;
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
  const txt = text.trim();
  let counts;
  if (txt.startsWith('{') || txt.startsWith('[')) {
    let data; try { data = JSON.parse(txt); } catch { throw new Error(t('err_invalid_json')); }
    if (Array.isArray(data)) throw new Error(t('err_json_array_root'));
    counts = importLibraryJSON(data, guide);
  } else {
    let doc; try { doc = new DOMParser().parseFromString(txt, 'application/xml'); } catch { throw new Error(t('err_unreadable_file')); }
    if (doc.querySelector('parsererror')) throw new Error(t('err_invalid_beerxml'));
    counts = importLibraryXML(doc, guide);
  }
  if (!Object.values(counts).some(n => n)) throw new Error(t('err_no_ing_recognized'));
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
  const name = xtext(eqEl, 'NAME') || t('imp_profile_name', {recipe: recipeName});
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
  const mashTunMax = xnum(eqEl, 'TUN_VOLUME', 0);
  const p = {id: uid(), name, batch: rnd(batch, 1), boil: rnd(boil, 0), evap: rnd(evap, 2), trub: rnd(trub, 2), mashLoss: rnd(mashLoss, 2), absorb: 1, ratio: 3, infusionTemp: 96, shrink: 4, eff: rnd(eff, 0), mashTunMax: rnd(mashTunMax, 1)};
  D.profiles.push(p); return p.id;
}

function importBeerXML(text) {
  let doc;
  try { doc = new DOMParser().parseFromString(text, 'application/xml'); } catch { throw new Error('Fichier illisible.'); }
  if (doc.querySelector('parsererror')) throw new Error(t('err_beerxml_invalid2'));
  const recipeEls = [...doc.querySelectorAll('RECIPE')];
  if (!recipeEls.length) throw new Error(t('err_no_recipe_tag'));

  return recipeEls.map(rec => {
    const name = xtext(rec, 'NAME') || t('imp_recipe_name');
    const style = xtext(rec, 'STYLE > NAME');
    const equipEl = rec.querySelector('EQUIPMENT');
    const r = newRecipe();
    r.id = uid(); r.name = name; r.style = style;
    r.profileId = ensureProfileFromEquipment(equipEl, rec, name);
    const rprof = D.profiles.find(x => x.id === r.profileId) || DEFAULT_P();
    r.ratio = rprof.ratio; r.eff = rprof.eff; r.infusionTemp = rprof.infusionTemp;
    r.batch = rnd(xnum(rec, 'BATCH_SIZE', 20), 2);
    r.co2 = xnum(rec, 'CARBONATION', 2.4) || 2.4;
    r.grainTemp = rnd(xnum(rec.querySelector('MASH'), 'GRAIN_TEMP', 20), 0);
    r.notes = xtext(rec, 'NOTES');

    r.ferm = [...rec.querySelectorAll('FERMENTABLES > FERMENTABLE')].map(el => {
      const nm = xtext(el, 'NAME') || t('imp_ferm_name');
      const type = {Grain: 'grain', Adjunct: 'grain', Sugar: 'sugar', Extract: 'extract', 'Dry Extract': 'extract'}[xtext(el, 'TYPE')] || 'grain';
      const ing = ensureIng('ferm', nm, {type, ebc: rnd(xnum(el, 'COLOR', 5) * 1.97, 1), extract: rnd(xnum(el, 'YIELD', 78), 0)});
      return {ref: ing.id, kg: rnd(xnum(el, 'AMOUNT', 0), 3)};
    });

    r.hops = [...rec.querySelectorAll('HOPS > HOP')].map(el => {
      const nm = xtext(el, 'NAME') || t('imp_hop_name');
      const form = xtext(el, 'FORM') === 'Leaf' ? 'Cône entier' : 'Pellet';
      const ing = ensureIng('hop', nm, {alpha: rnd(xnum(el, 'ALPHA', 5), 1), form});
      ing.alpha = rnd(xnum(el, 'ALPHA', ing.alpha), 1); ing.form = form;
      const use = {Boil: 'boil', Mash: 'boil', 'First Wort': 'fwh', 'Dry Hop': 'dry', Aroma: 'whirl'}[xtext(el, 'USE')] || 'boil';
      let time = xnum(el, 'TIME', use === 'dry' ? 10080 : 60);
      time = use === 'dry' ? rnd(time / 1440, 1) : (use === 'fwh' ? 0 : rnd(time, 0));
      return {ref: ing.id, g: rnd(xnum(el, 'AMOUNT', 0) * 1000, 1), use, time};
    });

    r.yeast = [...rec.querySelectorAll('YEASTS > YEAST')].map(el => {
      const nm = xtext(el, 'NAME') || t('imp_yeast_name');
      const type = xtext(el, 'TYPE') === 'Lager' ? 'lager' : 'ale', form = xtext(el, 'FORM') === 'Liquid' ? 'liquid' : 'dry';
      const att = rnd(xnum(el, 'ATTENUATION', 75), 0), tmin = rnd(xnum(el, 'MIN_TEMPERATURE', 15), 0), tmax = rnd(xnum(el, 'MAX_TEMPERATURE', 22), 0);
      const ing = ensureIng('yeast', nm, {type, form, att, tmin, tmax});
      ing.att = att; ing.tmin = tmin; ing.tmax = tmax;
      const amt = xnum(el, 'AMOUNT', null);
      return {ref: ing.id, qty: amt ? Math.max(1, Math.round(amt / 0.0115)) : 1};
    });

    r.misc = [...rec.querySelectorAll('MISCS > MISC')].map(el => {
      const nm = xtext(el, 'NAME') || t('imp_misc_name');
      const unit = xtext(el, 'AMOUNT_IS_WEIGHT').toLowerCase() === 'true' ? 'g' : 'mL';
      const ing = ensureIng('misc', nm, {unit});
      const use = {Mash: 'mash', Boil: 'boil', Primary: 'ferm', Secondary: 'ferm', Bottling: 'bottle'}[xtext(el, 'USE')] || 'boil';
      return {ref: ing.id, qty: rnd(xnum(el, 'AMOUNT', 0) * 1000, 2), use, time: rnd(xnum(el, 'TIME', 0), 0)};
    });

    const steps = [...rec.querySelectorAll('MASH MASH_STEPS > MASH_STEP')];
    if (steps.length) r.mash = steps.map(el => ({name: xtext(el, 'NAME') || t('imp_rest_name'), temp: rnd(xnum(el, 'STEP_TEMP', 65), 0), time: rnd(xnum(el, 'STEP_TIME', 30), 0),
      method: {Infusion: 'infusion', Temperature: 'direct', Decoction: 'decoction'}[xtext(el, 'TYPE')] || 'infusion'}));

    const fsteps = [];
    const addStage = (ageTag, tempTag, label) => { const age = xnum(rec, ageTag, null); if (age !== null) fsteps.push({name: label, temp: rnd(xnum(rec, tempTag, 18), 0), days: rnd(age, 0)}); };
    addStage('PRIMARY_AGE', 'PRIMARY_TEMP', t('imp_ferment_primary'));
    addStage('SECONDARY_AGE', 'SECONDARY_TEMP', t('imp_ferment_secondary'));
    addStage('TERTIARY_AGE', 'TERTIARY_TEMP', t('imp_ferment_tertiary'));
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
  const name = (rec.equipment && rec.equipment.name) || t('imp_profile_name', {recipe: recipeName});
  const existing = D.profiles.find(p => p.name === name);
  if (existing) return existing.id;
  const eff = jval(rec.efficiency && rec.efficiency.brewhouse) ?? 72;
  const boil = jval(rec.boil && rec.boil.boil_time) ?? 60;
  const batch = jval(rec.batch_size) ?? 20;
  const mashTunMax = jval(rec.equipment && rec.equipment.mash_tun_volume) ?? 0;
  const p = {id: uid(), name, batch: rnd(batch, 1), boil: rnd(boil, 0), evap: 3, trub: 1.5, mashLoss: 1, absorb: 1, ratio: 3, infusionTemp: 96, shrink: 4, eff: rnd(eff, 0), mashTunMax: rnd(mashTunMax, 1)};
  D.profiles.push(p); return p.id;
}
function importBeerJSONRecipes(text) {
  let data; try { data = JSON.parse(text); } catch { throw new Error('Fichier JSON invalide.'); }
  const root = data.beerjson || data;
  const recipesArr = root.recipes || [];
  if (!recipesArr.length) throw new Error(t('err_no_recipe_json'));

  return recipesArr.map(rec => {
    const name = rec.name || t('imp_recipe_name');
    const r = newRecipe();
    r.id = uid(); r.name = name;
    r.style = (rec.style && rec.style.name) || '';
    r.profileId = ensureProfileFromBeerJSON(rec, name);
    const prof = D.profiles.find(p => p.id === r.profileId) || DEFAULT_P();
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
    if (steps.length) r.mash = steps.map(s => ({name: s.name || t('palier_label'), temp: rnd(jval(s.step_temperature) ?? 65, 0), time: rnd(jval(s.step_time) ?? 30, 0),
      method: {infusion: 'infusion', temperature: 'direct', decoction: 'decoction'}[String(s.type || '').toLowerCase()] || 'infusion'}));

    const fsteps = (rec.fermentation && rec.fermentation.fermentation_steps) || [];
    if (fsteps.length) r.ferment = fsteps.map(s => ({name: s.name || t('step_label'), temp: rnd(jval(s.start_temperature) ?? 18, 0), days: rnd(jval(s.step_time) ?? 7, 0)}));

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
function copyText(txt) {
  if (navigator.clipboard && navigator.clipboard.writeText) return navigator.clipboard.writeText(txt).then(() => toast(t('toast_copied'))).catch(() => fallbackCopy());
  fallbackCopy();
  function fallbackCopy() { const ta = $('#exptxt'); ta.select(); try { document.execCommand('copy'); toast(t('toast_copied')); } catch { toast(t('toast_copy_manual')); } }
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
    <div class="dlg-actions"><button class="btn ghost" data-act="dlgClose">${t('dlg_close')}</button><button class="btn ghost" id="cpy">Copier</button><button class="btn" id="sv">Enregistrer ${esc(name)}</button></div>`);
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
  const notes = (recipeStyleName(r) ? 'Style : ' + recipeStyleName(r) + '\n' : '') + r.notes;
  // Déclaration BeerXML : OK en tête d’un fichier .xml ; interdit en littéral `<?xml` dans ce .php (short_open_tag=On → page blanche). Voir docs/php-beerxml-short-tags.md
  const xmlDecl = '<' + '?xml version="1.0" encoding="UTF-8"?>';
  return `${xmlDecl}\n<RECIPES>\n<RECIPE>${X('VERSION', 1)}${X('NAME', r.name)}${X('TYPE', r.ferm.some(x => lib('ferm', x.ref)?.type === 'grain') ? 'All Grain' : 'Extract')}${X('BREWER', USER)}
${beerXmlStyle(r, X)}
${X('BATCH_SIZE', rnd(c.V, 2))}${X('BOIL_SIZE', rnd(c.preboil, 2))}${X('BOIL_TIME', P.boil)}${X('EFFICIENCY', rnd(c.effPct, 0))}
${sec('HOPS', hops)}${sec('FERMENTABLES', ferm)}${sec('MISCS', miscs)}${sec('YEASTS', yeasts)}
<EQUIPMENT>${X('NAME', P.name)}${X('VERSION', 1)}${X('BOIL_SIZE', rnd(c.preboil, 2))}${X('BATCH_SIZE', rnd(c.V, 2))}${X('TRUB_CHILLER_LOSS', P.trub)}${X('EVAP_RATE', rnd(P.evap / c.preboil * 100, 2))}${X('BOIL_TIME', P.boil)}</EQUIPMENT>
<MASH>${X('NAME', t('export_mash_name'))}${X('VERSION', 1)}${X('GRAIN_TEMP', N(r.grainTemp))}<MASH_STEPS>${steps.join('')}</MASH_STEPS></MASH>
${X('NOTES', notes)}${X('OG', rnd(c.og, 4))}${X('FG', rnd(c.fg, 4))}${X('IBU', rnd(c.ibu, 1))}${X('EST_ABV', rnd(c.abv, 2))}${X('CARBONATION', N(r.co2))}${X('FERMENTATION_STAGES', r.ferment.length)}
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
    boil: {name: t('p_card_boil'), pre_boil_size: q(c.preboil, 'l'), boil_time: q(P.boil, 'min')},
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
    mash: {name: t('export_mash_name'), grain_temperature: q(N(r.grainTemp), 'C'), mash_steps: r.mash.map((s, i) => ({name: s.name, type: i === 0 ? 'infusion' : {infusion: 'infusion', direct: 'temperature', decoction: 'decoction'}[s.method || 'infusion'], step_temperature: q(N(s.temp), 'C'), step_time: q(N(s.time), 'min')}))},
    fermentation: {name: t('export_ferment_name'), fermentation_steps: r.ferment.map(s => ({name: s.name, start_temperature: q(N(s.temp), 'C'), step_time: q(N(s.days), 'day')}))},
    original_gravity: q(c.og, 'sg'), final_gravity: q(c.fg, 'sg'), alcohol_by_volume: q(c.abv, '%'), ibu_estimate: {value: rnd(c.ibu, 1), unit: 'IBUs'}, color_estimate: q(c.ebc, 'EBC'),
    carbonation: N(r.co2), notes: (recipeStyleName(r) ? 'Style : ' + recipeStyleName(r) + '\n' : '') + r.notes
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
        toast(e.message || t('err_load_data'));
        render();
      }
    } else render();
  } catch (e) {
    USER = D = null;
    toast(e.message || t('err_offline'));
    render();
  }
})();
</script>
</body>
</html>
