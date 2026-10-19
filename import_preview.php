<?php
/**
 * Import BeerXML depuis BrewRecipes → file temporaire + redirection HopHop.
 * Miroir de POST …/brewrecipes/hophop/preview.
 *
 * POST application/xml (corps = BeerXML), cookies forum requis.
 * Query optionnelle : brewrecipes_id, brewrecipes_name.
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/brewrecipes_import.php';

header('X-Content-Type-Options: nosniff');

if (hophop_auth_mode() !== 'phpbb') {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Import BrewRecipes indisponible (HopHop en mode autonome).';
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Méthode non autorisée.';
    exit;
}

if (hophop_forum_is_bot()) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Les robots du forum ne peuvent pas utiliser HopHop.';
    exit;
}

$ctx = hophop_user_context();

if (!$ctx) {
    http_response_code(401);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Connectez-vous au forum pour ouvrir la recette dans HopHop.';
    exit;
}

$user_id = (int) ($ctx['user_id'] ?? 0);

if ($user_id < 1 && hophop_auth_mode() === 'phpbb') {
    http_response_code(401);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Session forum invalide.';
    exit;
}

// Standalone : clé numérique stable dérivée du pseudo (pas de user_id forum).
if ($user_id < 1) {
    $user_id = (int) sprintf('%u', crc32((string) $ctx['username']));

    if ($user_id < 1) {
        $user_id = 1;
    }
}

$raw = file_get_contents('php://input');

if ($raw === false) {
    $raw = '';
}

if (strlen($raw) > MAX_SAVE_BYTES) {
    http_response_code(413);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'BeerXML trop volumineux.';
    exit;
}

$content_type = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));

if (!(str_contains($content_type, 'xml') || str_starts_with(ltrim($raw), '<'))) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'BeerXML attendu (application/xml).';
    exit;
}

if (trim($raw) === '' || stripos($raw, '<RECIPE') === false) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'BeerXML invalide ou sans recette.';
    exit;
}

$brewrecipes_id = isset($_GET['brewrecipes_id']) ? (int) $_GET['brewrecipes_id'] : 0;
$brewrecipes_name = isset($_GET['brewrecipes_name'])
    ? substr((string) $_GET['brewrecipes_name'], 0, 200)
    : '';

if (!hophop_brewrecipes_import_stash($user_id, [
    'beerxml' => $raw,
    'brewrecipes_id' => $brewrecipes_id,
    'brewrecipes_name' => $brewrecipes_name,
])) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Impossible d’enregistrer le BeerXML pour import.';
    exit;
}

$q = ['brewrecipes_import' => '1'];

if ($brewrecipes_id > 0) {
    $q['brewrecipes_id'] = (string) $brewrecipes_id;
}

$target = './?' . http_build_query($q);

header('Location: ' . $target, true, 303);
exit;
