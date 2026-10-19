<?php
/**
 * Copiez ce fichier en includes/config.local.php (non versionné) pour activer
 * l’intégration forum BrassageAmateur.
 *
 * Sans ce fichier, ou avec auth_mode autre que « phpbb », HopHop reste en mode
 * autonome : comptes locaux, stockage JSON, pas de MySQL ni BrewRecipes.
 *
 * @return array<string, mixed>
 */
return [
    // standalone = comptes HopHop locaux (défaut si ce fichier est absent)
    // phpbb     = session forum (débloque MySQL optionnel + BrewRecipes)
    'auth_mode' => 'phpbb',

    // file = JSON dans data/ | mysql = table {prefix}hophop_store
    // Ignoré (forcé à file) si auth_mode n’est pas phpbb.
    'storage' => 'mysql',

    // null = ../ depuis hophop/ (phpBB334/hophop → phpBB334). Chemin absolu optionnel.
    'phpbb_root' => null,

    // Préfixe des tables phpBB (identique à $table_prefix dans config.php du forum).
    'table_prefix' => 'phpbb_',

    // Optionnel : URL POST « Partager sur BrewRecipes » (sinon déduite via phpBB).
    // 'brewrecipes_hophop_preview' => '/forum/app.php/brewrecipes/hophop/preview',
];
