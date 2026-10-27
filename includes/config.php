<?php
/**
 * Configuration de l'application HopHop.
 * Stockage 100 % fichiers (JSON) — aucune base de données requise.
 */

// Session : cookie non accessible en JavaScript, SameSite=Lax.
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
if (!empty($_SERVER['HTTPS'])) ini_set('session.cookie_secure', '1');
session_set_cookie_params(['samesite' => 'Lax']);
session_start();

define('DATA_DIR', __DIR__ . '/../data');
define('STORE_DIR', DATA_DIR . '/store');
define('USERS_FILE', DATA_DIR . '/users.json');
define('MAX_SAVE_BYTES', 8 * 1024 * 1024); // garde-fou : 8 Mo par compte

foreach ([DATA_DIR, STORE_DIR] as $d) {
    if (!is_dir($d) && !mkdir($d, 0775, true) && !is_dir($d)) {
        http_response_code(500);
        die(json_encode(['ok' => false, 'error' => "Impossible de créer le dossier de données ($d). Vérifiez les droits d'écriture."]));
    }
}

function username_valid(string $u): bool {
    return (bool) preg_match('/^[A-Za-z0-9_.\-@]{2,40}$/', $u);
}
// Nom de fichier sûr dérivé de l'identifiant (l'identifiant lui-même est déjà restreint par username_valid).
function user_file(string $u): string {
    return STORE_DIR . '/' . preg_replace('/[^A-Za-z0-9_.\-@]/', '_', $u) . '.json';
}
