<?php
/**
 * API HopHop — POST JSON : {"action": "...", ...}
 */
define('HOPHOP_API', true);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

register_shutdown_function(static function (): void {
    $err = error_get_last();

    if ($err === null) {
        return;
    }

    $fatal = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];

    if (!in_array($err['type'], $fatal, true)) {
        return;
    }

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
    }

    echo json_encode([
        'ok' => false,
        'error' => 'Fatal PHP : ' . $err['message'] . ' (' . basename($err['file']) . ':' . $err['line'] . ')',
    ], JSON_UNESCAPED_UNICODE);
});

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/brewrecipes_import.php';

function json_fail(string $msg, int $code = 400): void
{
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

function json_ok(array $extra = []): void
{
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
if (!is_array($in)) {
    $in = [];
}
$action = $in['action'] ?? '';

hh_set_server_lang(hh_lang_strings(hh_resolve_lang($in['lang'] ?? null, hh_available_langs())));

try {
    switch ($action) {

        case 'ping':
            json_ok([
                'php' => PHP_VERSION,
                'phpbb_bootstrapped' => defined('HOPHOP_PHPBB_BOOTSTRAPPED'),
                'username' => current_user(),
                'mysql_store_ready' => hophop_storage() === 'mysql' ? hophop_mysql_store_ready() : null,
            ]);
            break;

        case 'register':
            register((string) ($in['username'] ?? ''), (string) ($in['password'] ?? ''));
            json_ok(['username' => current_user()] + hophop_session_payload());
            break;

        case 'login':
            do_login((string) ($in['username'] ?? ''), (string) ($in['password'] ?? ''));
            json_ok(['username' => current_user()] + hophop_session_payload());
            break;

        case 'logout':
            $extra = hophop_session_payload();
            do_logout();
            json_ok($extra);
            break;

        case 'session':
            json_ok(hophop_session_payload());
            break;

        case 'load':
            require_login();
            json_ok(['data' => hophop_load_user_data()]);
            break;

        case 'save':
            require_login();
            if (!isset($in['data']) || !is_array($in['data'])) {
                json_fail(tt('server_err_data_invalid'));
            }
            if (strlen(json_encode($in['data'])) > MAX_SAVE_BYTES) {
                json_fail(tt('server_err_data_too_large'), 413);
            }
            hophop_save_user_data($in['data']);
            json_ok();
            break;

        case 'consume_brewrecipes_import':
            require_login();
            $ctx = hophop_user_context();
            $uid = (int) ($ctx['user_id'] ?? 0);

            if ($uid < 1) {
                $uid = (int) sprintf('%u', crc32((string) ($ctx['username'] ?? '')));

                if ($uid < 1) {
                    $uid = 1;
                }
            }

            $pending = hophop_brewrecipes_import_consume($uid);

            if ($pending === null) {
                json_fail(tt('server_err_br_import_pending'), 404);
            }

            json_ok($pending);
            break;

        case 'delete_account':
            hophop_reject_if_forum_auth();
            require_login();
            $u = current_user();
            $users = get_users();
            unset($users[$u]);
            save_users($users);
            $ctx = hophop_user_context();
            if ($ctx) {
                hophop_user_store_delete($ctx);
            }
            do_logout();
            json_ok();
            break;

        default:
            json_fail(tt('server_err_unknown_action'), 404);
    }
} catch (\Throwable $e) {
    error_log('HopHop api.php ' . $action . ': ' . $e->getMessage());
    json_fail(tt('server_err_generic', ['detail' => $e->getMessage()]), 500);
}
