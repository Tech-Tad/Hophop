<?php
/**
 * Configuration HopHop — auto-hébergé (fichiers) ou intégré phpBB (SSO + MySQL).
 *
 * Sans includes/config.local.php, ou sans auth_mode = phpbb : mode standalone
 * (comptes locaux + JSON). MySQL et BrewRecipes ne s’activent qu’avec phpbb.
 */

require_once __DIR__ . '/forum.php';

function hophop_local_config(): array
{
    static $cfg = null;

    if ($cfg !== null) {
        return $cfg;
    }

    $cfg = [];
    $local = __DIR__ . '/config.local.php';

    if (is_file($local)) {
        try {
            $loaded = require $local;
        } catch (\Throwable $e) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok' => false,
                'error' => 'config.local.php invalide : ' . $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (is_array($loaded)) {
            $cfg = $loaded;
        }
    }

    return $cfg;
}

/** true si config.local.php demande explicitement l’auth forum. */
function hophop_auth_mode(): string
{
    $mode = (string) (hophop_local_config()['auth_mode'] ?? 'standalone');

    return $mode === 'phpbb' ? 'phpbb' : 'standalone';
}

/**
 * Stockage des recettes. MySQL uniquement si auth phpbb + storage=mysql
 * (sinon pas d’identifiants DB) — sinon fichiers JSON.
 */
function hophop_storage(): string
{
    if (hophop_auth_mode() !== 'phpbb') {
        return 'file';
    }

    $storage = (string) (hophop_local_config()['storage'] ?? 'file');

    return $storage === 'mysql' ? 'mysql' : 'file';
}

/** Intégration forum active (SSO ; éventuellement MySQL + BrewRecipes). */
function hophop_uses_phpbb(): bool
{
    return hophop_auth_mode() === 'phpbb';
}

/** URL POST vers BrewRecipes (aperçu import). Vide hors intégration forum. */
function hophop_brewrecipes_share_url(): string
{
    return hophop_brewrecipes_route_url('hophop/preview', 'brewrecipes_hophop_preview');
}

/** GET JSON — guides de styles BrewRecipes. */
function hophop_brewrecipes_style_guides_url(): string
{
    return hophop_brewrecipes_route_url('hophop/style-guides', 'brewrecipes_style_guides');
}

/** GET JSON — styles d’un guide (?guide=…). */
function hophop_brewrecipes_styles_url(): string
{
    return hophop_brewrecipes_route_url('hophop/styles', 'brewrecipes_styles');
}

/**
 * URL app.php/brewrecipes/{relative} (override config.local optionnel).
 *
 * @param string $relative chemin sous brewrecipes/ (ex. hophop/preview)
 * @param string $config_key clé optionnelle dans config.local.php
 */
function hophop_brewrecipes_route_url(string $relative, string $config_key = ''): string
{
    if (hophop_auth_mode() !== 'phpbb') {
        return '';
    }

    if ($config_key !== '') {
        $override = hophop_local_config()[$config_key] ?? null;

        if (is_string($override) && $override !== '') {
            return $override;
        }
    }

    $relative = ltrim(str_replace('\\', '/', $relative), '/');
    $fallback = '/forum/app.php/brewrecipes/' . $relative;

    if (!hophop_bootstrap_phpbb()) {
        return $fallback;
    }

    try {
        return append_sid(generate_board_url(false) . '/app.php/brewrecipes/' . $relative, '', true);
    } catch (\Throwable $e) {
        return generate_board_url(false) . '/app.php/brewrecipes/' . $relative;
    }
}

if (hophop_uses_phpbb() && !defined('HOPHOP_DIAGNOSTIC') && !hophop_bootstrap_phpbb()) {
    http_response_code(503);
    header('Content-Type: application/json; charset=utf-8');
    $detail = hophop_last_bootstrap_error();
    echo json_encode([
        'ok' => false,
        'error' => 'HopHop : impossible de charger phpBB.'
            . ($detail !== '' ? ' ' . $detail : ' Vérifiez phpbb_root et le chemin phpbb334/hophop.'),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (hophop_auth_mode() === 'standalone') {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    if (!empty($_SERVER['HTTPS'])) {
        ini_set('session.cookie_secure', '1');
    }
    session_set_cookie_params(['samesite' => 'Lax']);
    session_start();
} elseif (session_status() === PHP_SESSION_ACTIVE) {
    // Ancienne session HopHop (standalone) : ne pas laisser un cookie PHP par défaut verrouiller phpBB.
    session_write_close();
}

define('DATA_DIR', __DIR__ . '/../data');
define('STORE_DIR', DATA_DIR . '/store');
define('USERS_FILE', DATA_DIR . '/users.json');
define('MAX_SAVE_BYTES', 8 * 1024 * 1024);

$needs_store_files = hophop_auth_mode() === 'standalone' || hophop_storage() === 'file';

if (!is_dir(DATA_DIR)) {
    @mkdir(DATA_DIR, 0775, true);
}

if (!is_dir(DATA_DIR) && $needs_store_files) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    die(json_encode(['ok' => false, 'error' => 'Impossible de créer le dossier de données (' . DATA_DIR . ').']));
}

if ($needs_store_files) {
    if (!is_dir(STORE_DIR)) {
        @mkdir(STORE_DIR, 0775, true);
    }

    if (!is_dir(STORE_DIR)) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode(['ok' => false, 'error' => 'Impossible de créer le dossier store (' . STORE_DIR . ').']));
    }
}

function username_valid(string $u): bool
{
    return (bool) preg_match('/^[A-Za-z0-9_.\-@]{2,40}$/', $u);
}

function user_file(string $u): string
{
    return STORE_DIR . '/' . preg_replace('/[^A-Za-z0-9_.\-@]/', '_', $u) . '.json';
}
