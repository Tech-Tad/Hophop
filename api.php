<?php
/**
 * API de HopHop — un seul point d'entrée, actions en POST JSON : {"action": "...", ...}
 * Réponses : {"ok": true, ...} ou {"ok": false, "error": "..."}
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function json_fail(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}
function json_ok(array $extra = []): void {
    echo json_encode(['ok' => true] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    hh_set_server_lang(hh_lang_strings(hh_resolve_lang(null, hh_available_langs())));
    json_fail(tt('server_err_method'), 405);
}

$raw = file_get_contents('php://input');
if (strlen($raw) > MAX_SAVE_BYTES) {
    hh_set_server_lang(hh_lang_strings(hh_resolve_lang(null, hh_available_langs())));
    json_fail(tt('server_err_too_large'), 413);
}
$in = json_decode($raw, true);
if (!is_array($in)) $in = [];
$action = $in['action'] ?? '';

hh_set_server_lang(hh_lang_strings(hh_resolve_lang($in['lang'] ?? null, hh_available_langs())));

switch ($action) {

    case 'register':
        register((string) ($in['username'] ?? ''), (string) ($in['password'] ?? ''));
        json_ok(['username' => current_user()]);
        break;

    case 'login':
        do_login((string) ($in['username'] ?? ''), (string) ($in['password'] ?? ''));
        json_ok(['username' => current_user()]);
        break;

    case 'logout':
        do_logout();
        json_ok();
        break;

    case 'session':
        json_ok(['username' => current_user()]);
        break;

    case 'load':
        require_login();
        $data = read_json(user_file(current_user()), null);
        if ($data === null) $data = seed_data(); // compte existant mais fichier absent : on repart d'un jeu par défaut
        json_ok(['data' => $data]);
        break;

    case 'save':
        require_login();
        if (!isset($in['data']) || !is_array($in['data'])) json_fail(tt('server_err_data_invalid'));
        if (strlen(json_encode($in['data'])) > MAX_SAVE_BYTES) json_fail(tt('server_err_data_too_large'), 413);
        if (!write_json(user_file(current_user()), $in['data'])) json_fail(tt('server_err_save_failed'), 500);
        json_ok();
        break;

    case 'delete_account':
        require_login();
        $u = current_user();
        $users = get_users();
        unset($users[$u]);
        save_users($users);
        @unlink(user_file($u));
        do_logout();
        json_ok();
        break;

    default:
        json_fail(tt('server_err_unknown_action'), 404);
}
