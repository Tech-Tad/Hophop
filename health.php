<?php
/**
 * Diagnostic HopHop — ouvrir …/hophop/health.php puis retirer ou protéger ce fichier.
 */
define('HOPHOP_DIAGNOSTIC', true);

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
error_reporting(E_ALL);

$report = [
    'php' => PHP_VERSION,
    'hophop_dir' => __DIR__,
    'config_local' => is_file(__DIR__ . '/includes/config.local.php'),
];

try {
    require_once __DIR__ . '/includes/config.php';
    require_once __DIR__ . '/includes/user_store.php';

    $report['auth_mode'] = hophop_auth_mode();
    $report['storage'] = hophop_storage();
    $report['data_dir'] = DATA_DIR;
    $report['data_dir_exists'] = is_dir(DATA_DIR);
    $report['data_dir_writable'] = is_dir(DATA_DIR) && is_writable(DATA_DIR);
    $report['hophop_api_log'] = DATA_DIR . '/hophop-api.log';
    $report['brewrecipes_share_url'] = hophop_brewrecipes_share_url();
    $report['brewrecipes_style_guides_url'] = hophop_brewrecipes_style_guides_url();
    $report['brewrecipes_styles_url'] = hophop_brewrecipes_styles_url();
    $report['import_preview'] = (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'https')
        . '://' . ($_SERVER['HTTP_HOST'] ?? '')
        . rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/hophop')), '/')
        . '/import_preview.php';
    $report['phpbb_root_guess'] = hophop_phpbb_root();

    if (hophop_uses_phpbb()) {
        $boot = hophop_bootstrap_phpbb();
        $report['phpbb_bootstrap'] = $boot;

        if (!$boot) {
            $report['bootstrap_error'] = hophop_last_bootstrap_error();
        } else {
            global $table_prefix;
            $report['session_started'] = false;
            $report['table_prefix'] = $table_prefix ?? '(missing)';
            $report['hophop_table'] = ($table_prefix ?? '') . 'hophop_store';
            $report['mysql_store_ready'] = hophop_storage() === 'mysql' ? hophop_mysql_store_ready() : null;
            global $db;
            $report['db_class'] = isset($db) && is_object($db) ? get_class($db) : null;
            $mysqli = hophop_phpbb_mysqli();
            $report['mysqli_ready'] = $mysqli instanceof \mysqli;
            $report['forum_user'] = '(session non démarrée en mode diagnostic — tester api.php connecté au forum)';
        }
    }
} catch (\Throwable $e) {
    $report['exception'] = $e->getMessage();
    $report['file'] = $e->getFile() . ':' . $e->getLine();
    http_response_code(500);
}

echo json_encode($report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
