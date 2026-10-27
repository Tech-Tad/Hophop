<?php
/**
 * i18n HopHop — fichiers de langue externes (dossier lang/, un fichier PHP par langue : fr.php, en.php…).
 * Chaque fichier retourne un tableau associatif clé => texte, utilisé à la fois côté client (chaîne JS
 * injectée en JSON dans index.php) et côté serveur (messages d'erreur de l'API).
 *
 * Pour ajouter une langue : copiez un fichier depuis lang-available/ (ou dupliquez lang/en.php) vers
 * lang/<code>.php (code ISO 639-1 à 2 lettres, ex. "es"), traduisez les valeurs. Elle apparaît
 * automatiquement dans le sélecteur de langue (onglet Compte), aucune autre configuration requise.
 */

/** Codes de langue disponibles = fichiers *.php présents dans lang/ (hors gabarits). */
function hh_available_langs(): array {
    $dir = __DIR__ . '/../lang';
    $out = [];
    foreach (glob($dir . '/*.php') ?: [] as $f) {
        $code = basename($f, '.php');
        if (preg_match('/^[a-z]{2}$/', $code)) $out[] = $code;
    }
    sort($out);
    return $out ?: ['fr'];
}

/** Devine la langue depuis l'en-tête HTTP Accept-Language du navigateur. */
function hh_detect_browser_lang(array $available): string {
    $hdr = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    foreach (explode(',', $hdr) as $part) {
        $code = strtolower(substr(trim(explode(';', $part)[0]), 0, 2));
        if ($code && in_array($code, $available, true)) return $code;
    }
    return in_array('fr', $available, true) ? 'fr' : $available[0];
}

/** Résout la langue à utiliser : préférence explicite (si valide) sinon détection navigateur. */
function hh_resolve_lang(?string $requested, array $available): string {
    if ($requested && in_array($requested, $available, true)) return $requested;
    return hh_detect_browser_lang($available);
}

/** Charge le tableau de traductions d'une langue (repli sur fr.php si le fichier est illisible). */
function hh_lang_strings(string $lang): array {
    $file = __DIR__ . "/../lang/{$lang}.php";
    if (!is_file($file)) $file = __DIR__ . '/../lang/fr.php';
    $arr = is_file($file) ? include $file : [];
    return is_array($arr) ? $arr : [];
}

/**
 * Traductions utilisées côté serveur (messages d'erreur de api.php / includes/auth.php).
 * hh_set_server_lang() est appelé une fois par requête (api.php), à partir de la langue envoyée par le
 * client (champ "lang" du POST JSON) ou, à défaut, de la détection navigateur. tt() lit ensuite ce tableau
 * — repli sur la clé elle-même si elle est absente, pour ne jamais planter sur une langue incomplète.
 */
function hh_set_server_lang(array $strings): void {
    $GLOBALS['__hh_server_lang'] = $strings;
}
function tt(string $key, array $vars = []): string {
    $str = $GLOBALS['__hh_server_lang'][$key] ?? $key;
    foreach ($vars as $k => $v) $str = str_replace('{' . $k . '}', (string) $v, $str);
    return $str;
}
