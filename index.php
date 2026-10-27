<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/i18n.php';

// Logo SVG (includes/img/logo.svg), inliné côté serveur pour pouvoir le recolorer en CSS (currentColor)
// selon le thème actif — un simple <img src="..."> ne le permettrait pas.
$logoPath = __DIR__ . '/includes/img/logo.svg';
$LOGO_SVG = is_file($logoPath) ? file_get_contents($logoPath) : '';

// ${t('lang_title')} : préférence enregistrée du compte (si connecté) sinon détection du navigateur, repli sur le français.
$AVAILABLE_LANGS = hh_available_langs();
$SAVED_LANG = null;
$CUR_USER0 = current_user();
if ($CUR_USER0) {
    $d0 = read_json(user_file($CUR_USER0), null);
    if (is_array($d0) && isset($d0['prefs']['lang'])) $SAVED_LANG = (string) $d0['prefs']['lang'];
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
.bar{display:flex;flex-wrap:wrap;align-items:center;gap:.6rem;margin-bottom:1rem}
.search{max-width:260px}

.card{background:var(--surface);border:1px solid var(--line);border-radius:14px;padding:1rem;margin-bottom:1rem}
.cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:1rem}
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
let USER = null, D = null;                    // utilisateur courant et ses données
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
  d.stock = (d.stock && typeof d.stock === 'object') ? d.stock : {};
  d.prefs = (d.prefs && typeof d.prefs === 'object') ? d.prefs : {};
  if (!THEMES().some(([k]) => k === d.prefs.theme)) d.prefs.theme = 'vert';
  if (d.prefs.density !== 'compact') d.prefs.density = 'classic';
  if (d.prefs.unitPrimary !== 'brix') d.prefs.unitPrimary = 'sg';
  if (d.prefs.refractFormula !== 'cubic') d.prefs.refractFormula = 'linear';
  if (d.prefs.avatarType !== 'image') d.prefs.avatarType = 'emoji';
  if (!d.prefs.avatarEmoji) d.prefs.avatarEmoji = '🍺';
  if (typeof d.prefs.avatarImage !== 'string') d.prefs.avatarImage = '';
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
  const p = D.prefs;
  return p.avatarType === 'image' && p.avatarImage
    ? `<span class="avatar ${cls || ''}"><img src="${p.avatarImage}" alt=""></span>`
    : `<span class="avatar ${cls || ''}">${esc(p.avatarEmoji || '🍺')}</span>`;
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
  ui.view = 'recipes'; render();
}
async function logout() {
  try { await api('logout'); } catch {}
  USER = D = null; ui.auth = 'login';
  document.documentElement.removeAttribute('data-theme'); document.documentElement.removeAttribute('data-density');
  render();
}

/* ---------- 3. LIBELLÉS & GABARITS ---------- */
// Version affichée de l'application (écran de connexion + pied de page), à incrémenter à chaque publication.
const APP_VERSION = '1.2';
// Mention de licence : affichée sur l'écran de connexion et en pied de page sur toutes les vues de l'application.
const LICENSE_HTML = `HopHop v${APP_VERSION} © claude.ai & Penn-Maen — <a href="https://creativecommons.org/licenses/by-nc-sa/4.0/deed.fr" target="_blank" rel="noopener" style="color:inherit">CC BY-NC-SA 4.0</a>`;
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
  const reg = ui.auth === 'register';
  return `<div class="authscreen"><div class="auth-bg" aria-hidden="true">${LOGO_SVG}</div><div class="wrap"><div class="auth card">
    <h1 style="text-align:center;display:flex;align-items:center;justify-content:center;gap:.5rem"><span class="brand-logo" style="width:2.2rem;height:2.2rem">${LOGO_SVG}</span>HopHop</h1>
    <p class="sub" style="text-align:center">${t('auth_tagline')}</p>
    <div class="seg"><button data-act="authMode" data-m="login" aria-pressed="${!reg}">${t('auth_login_tab')}</button><button data-act="authMode" data-m="register" aria-pressed="${reg}">${t('auth_register_tab')}</button></div>
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
  return `<header class="top"><div class="in"><div class="brand"><span class="brand-logo">${LOGO_SVG}</span>HopHop</div><div class="grow"></div>
    <span class="savestate" id="savestate"></span><span class="who">${avatarHTML()}${esc(USER)}</span><button class="btn ghost sm" data-act="logout">${t('nav_logout')}</button></div>
    <nav class="tabs" aria-label="Navigation principale">${T.map(([v, l]) => `<button data-act="go" data-v="${v}" ${cur === v ? 'aria-current="page"' : ''}>${l}</button>`).join('')}</nav></header>
    <main class="wrap" id="main"></main>
    <footer class="sitefooter noprint"><p class="sub" style="text-align:center;margin:0">${LICENSE_HTML}</p></footer>`;
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
        ${fld(t('fld_style'), `<select data-b="styleId">${styleSelectOpts(r.styleId)}</select>`, 'span2')}
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
    <h3>${t('stats_water')}</h3><dl class="kv"><dt>${t('stats_mash')}</dt><dd>${t('vol_at_temp', {vol: f(c.mashWater, 1), temp: f(c.strike, 0)})}</dd><dt>${t('stats_sparge')}</dt><dd>${c.mashMethod === 'biab' ? t('stats_none_biab') : f(c.sparge, 1) + ' L'}</dd><dt>${t('stats_totalwater')}</dt><dd>${f(c.totalWater, 1)} L</dd>
      <dt>${t('stats_preboil')}</dt><dd>${f(c.preboil, 1)} L · ${gUnit(c.pbSG)}</dd><dt>${t('stats_evap')}</dt><dd>${f(c.evapL, 1)} L</dd></dl>
    ${c.mashMethod === 'biab' ? `<div class="sub" style="margin-top:-.2rem;margin-bottom:.5rem">${t('stats_biab_note')}${c.biabSqueeze ? t('stats_biab_squeeze_note') : ''}</div>` : ''}
    ${c.mashSteps.length ? `<h3>${t('stats_mash_vol')}</h3><dl class="kv">${c.mashSteps.map((m, i) => `<dt>${esc(m.name)} (${f(m.temp, 0)} °C)</dt><dd>${f(m.volume, 1)} L${i > 0 && m.method === 'infusion' && m.infusion > 0 ? ` (+${f(m.infusion, 1)} L)` : ''}${i > 0 && m.method === 'decoction' && m.decoctionVol > 0 ? t('stats_decoct_inline', {vol: f(m.decoctionVol, 1)}) : ''}</dd>`).join('')}</dl>
      <div class="sub" style="margin-top:.2rem">${t('stats_mash_vol_note', {temp: f(c.infusionTemp, 0)})}</div>` : ''}
    ${c.P.mashTunMax > 0 && c.maxMashVol > c.P.mashTunMax ? `<div class="warn">${t('stats_mashtun_overflow', {vol: f(c.maxMashVol, 1), max: f(c.P.mashTunMax, 1)})}</div>` : ''}
    <h3>${t('stats_grains')}</h3><dl class="kv"><dt>${t('stats_total_ferm')}</dt><dd>${f(c.kgTot, 2)} kg</dd><dt>${t('stats_ratio')}</dt><dd>${f(c.ratio, 1)} L/kg</dd><dt>${t('stats_eff')}</dt><dd>${f(c.effPct, 0)} %</dd><dt>${t('stats_attenuation')}</dt><dd>${f(c.att, 0)} %</dd></dl>
    <h3>${t('stats_conditioning')}</h3><dl class="kv"><dt>${t('stats_sucrose')}</dt><dd>${f(c.sugarL, 1)} g/L</dd><dt>${t('stats_total')}</dt><dd>${f(c.sugarTot, 0)} g</dd></dl>
    <h3>${t('stats_cost')}</h3><dl class="kv"><dt>${t('sec_ferm')}</dt><dd>${f(c.costFerm, 2)} €</dd><dt>${t('sec_hops')}</dt><dd>${f(c.costHop, 2)} €</dd><dt>${t('sheet_yeast')}</dt><dd>${f(c.costYeast, 2)} €</dd><dt>${t('sec_misc')}</dt><dd>${f(c.costMisc, 2)} €</dd>
      <dt>${t('stats_total')}</dt><dd>${f(c.costTotal, 2)} €</dd><dt>${t('stats_perliter')}</dt><dd>${f(c.costPerL, 2)} €/L</dd></dl>
    ${c.costTotal === 0 && (r.ferm.length || r.hops.length) ? `<div class="sub" style="margin-top:.3rem">${t('stats_cost_note')}</div>` : ''}
    ${c.hasYeast ? '' : `<div class="warn">${t('stats_no_yeast')}</div>`}
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
function brewCalc(b) {
  const og = b.ogM, fg = b.fgM, has = og > 1 && fg > 0.99;
  const abv = has ? (og - fg) * 131.25 : null, att = has && og > 1 ? (og - fg) / (og - 1) * 100 : null;
  let eff = null;
  if (og > 1 && b.vol > 0 && b.stats.potGrain > 0) eff = (((og - 1) * 1000 * b.vol) - b.stats.potOther) / b.stats.potGrain * 100;
  return {abv, att, eff};
}
function viewBrews() {
  const l = [...D.brews].sort((a, b) => (b.date || '').localeCompare(a.date || ''));
  return `<div class="bar"><h1>${t('brews_title')}</h1><div class="grow"></div><button class="btn" data-act="newBrew">${t('brews_new')}</button></div>
  ${l.length ? `<div class="cards">${l.map(b => { const k = brewCalc(b); return `<div class="card" style="margin:0"><div style="display:flex;justify-content:space-between;gap:.5rem"><h2 style="margin:0">${esc(b.recipeName)}</h2><span class="chip">${STATUS()[b.status]}</span></div>
    <div class="sub">${fdate(b.date)} · ${f(b.vol, 1)} L</div>
    <dl class="kv" style="margin-top:.6rem"><dt>${t('brews_og')}</dt><dd>${b.ogM > 1 ? gUnit(b.ogM) : '–'} <span class="sub">${t('brews_target', {v: gPrimaryText(b.stats.og)})}</span></dd><dt>${t('brews_fg')}</dt><dd>${b.fgM > 0.99 ? gUnit(b.fgM) : '–'} <span class="sub">${t('brews_target', {v: gPrimaryText(b.stats.fg)})}</span></dd>
      <dt>${t('brews_refracto')}</dt><dd>≈ ${f(rawBrixAtFG(b.ogM > 1 ? b.ogM : b.stats.og, b.fgM > 0.99 ? b.fgM : b.stats.fg), 1)} °Bx</dd>
      <dt>${t('brews_real_alcohol')}</dt><dd>${k.abv === null ? '–' : f(k.abv, 1) + ' %'}</dd><dt>${t('stats_attenuation')}</dt><dd>${k.att === null ? '–' : f(k.att, 0) + ' %'}</dd><dt>${t('brews_real_eff')}</dt><dd>${k.eff === null ? '–' : f(k.eff, 0) + ' %'}</dd></dl>
    ${b.notes ? `<p class="sub" style="white-space:pre-wrap">${esc(b.notes)}</p>` : ''}
    <div class="dlg-actions" style="justify-content:flex-start"><button class="btn ghost sm" data-act="editBrew" data-id="${b.id}">${t('ing_edit')}</button><button class="btn danger sm" data-act="delBrew" data-id="${b.id}">${t('btn_delete')}</button></div></div>`; }).join('')}</div>`
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
  <div class="card"><div style="display:flex;gap:.8rem;align-items:center">${avatarHTML('lg')}<div><h2 style="margin:0">${esc(USER)}</h2><p class="sub" style="margin:.1rem 0 0">${t('account_summary', {recipes: D.recipes.length, brews: D.brews.length, profiles: D.profiles.length})}</p></div></div>
    <div class="dlg-actions" style="justify-content:flex-start;flex-wrap:wrap;margin:.8rem 0 0"><button class="btn ghost sm" data-act="logout">${t('account_logout')}</button></div></div>
  <div class="card"><h2>${t('account_language_title')}</h2>
    <div class="seg">${AVAILABLE_LANGS.map(k => `<button data-act="setLang" data-v="${k}" aria-pressed="${D.prefs.lang === k}">${esc(LANG_NATIVE_NAMES[k] || k)}</button>`).join('')}</div>
    <p class="sub" style="margin-bottom:0">${t('account_language_note')}</p></div>
  <div class="card"><h2>${t('avatar_title')}</h2>
    <p class="sub" style="margin-top:0">${t('avatar_note')}</p>
    <div class="avatar-picks">${AVATAR_PICKS.map(e => `<button data-act="setAvatarEmoji" data-v="${e}" aria-pressed="${D.prefs.avatarType === 'emoji' && D.prefs.avatarEmoji === e}">${e}</button>`).join('')}</div>
    <div class="dlg-actions" style="justify-content:flex-start;flex-wrap:wrap;margin:0">
      <button class="btn ghost sm" data-act="avatarUpload">${t('avatar_upload')}</button>
      <input type="file" id="avatarFile" accept="image/*" style="display:none">
      ${D.prefs.avatarType === 'image' ? `<button class="btn ghost sm" data-act="avatarReset">${t('avatar_reset')}</button>` : ''}
    </div></div>
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
  <div class="card"><h2>${t('backup_title')}</h2><p class="sub" style="margin-top:0">${t('backup_note')}</p>
    <div class="dlg-actions" style="justify-content:flex-start;flex-wrap:wrap;margin:0"><button class="btn ghost sm" data-act="backup">${t('backup_export')}</button><button class="btn ghost sm" data-act="restore">${t('backup_import')}</button><input type="file" id="restoreFile" accept=".json,application/json" style="display:none"></div></div>
  <div class="card"><h2>${t('danger_zone')}</h2><button class="btn danger sm" data-act="delAccount">${t('delete_account_btn')}</button></div>`;
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
  go: el => { ui.view = el.dataset.v; renderMain(); window.scrollTo(0, 0); },
  dlgClose: () => closeDlg(), dlgSave: () => dlgSave && dlgSave(),
  newRecipe: () => { const r = newRecipe(); D.recipes.push(r); ui.rid = r.id; ui.view = 'editor'; persist(); renderMain(); window.scrollTo(0, 0); },
  open: el => { ui.rid = el.dataset.id; ui.view = 'editor'; renderMain(); window.scrollTo(0, 0); },
  sheet: () => { ui.view = 'sheet'; renderMain(); window.scrollTo(0, 0); },
  sheetMode: el => { ui.sheetSteps = el.dataset.v === '1'; renderMain(true); },
  toggleSheetCost: el => { ui.sheetCost = el.checked; renderMain(true); },
  print: () => { try { window.print(); } catch { toast(t('toast_print_fallback')); } },
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
    openForm(b.recipeName, [{k: 'date', l: t('brew_edit_date'), t: 'date'}, {k: 'status', l: t('brew_edit_status'), t: 'select', o: Object.entries(STATUS())}, {k: 'ogM', l: t('brew_edit_ogm'), t: 'number'}, {k: 'fgM', l: t('brew_edit_fgm'), t: 'number'},
      {k: 'vol', l: t('brew_edit_vol'), t: 'number'}, {k: 'notes', l: t('brew_edit_notes'), t: 'textarea'}, {k: 'tasting', l: t('brew_edit_tasting'), t: 'textarea'}], b,
      v => { Object.assign(b, v); persist(); renderMain(true); },
      `<p class="sub">${t('brew_edit_note')}</p>`);
  },
  delBrew: el => ask(t('brew_confirm_delete'), t('btn_delete'), () => { D.brews = D.brews.filter(x => x.id !== el.dataset.id); persist(); renderMain(true); }),
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
  return `<?xml version="1.0" encoding="UTF-8"?>\n<RECIPES>\n<RECIPE>${X('VERSION', 1)}${X('NAME', r.name)}${X('TYPE', r.ferm.some(x => lib('ferm', x.ref)?.type === 'grain') ? 'All Grain' : 'Extract')}${X('BREWER', USER)}
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
  const rec = {
    name: r.name, type: r.ferm.some(x => lib('ferm', x.ref)?.type === 'grain') ? 'all grain' : 'extract', author: USER,
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
    if (s.username) await enterApp(s.username);
    else render();
  } catch { render(); }
})();
</script>
</body>
</html>
