-- HopHop — stockage JSON par membre forum (préfixe phpBB à adapter).
-- Exemple : phpbb_hophop_store si $table_prefix = 'phpbb_'

-- store_json doit rester MEDIUMTEXT (≈16 Mo) : un compte = tout le JSON HopHop.
-- Si la table a été créée en TEXT (64 Ko), l’import BeerXML peut échouer à l’enregistrement :
-- ALTER TABLE phpbb_hophop_store MODIFY store_json MEDIUMTEXT NOT NULL;
--
-- HopHop bascule temporairement la connexion en utf8mb4 pour les prepared statements
-- (phpBB reste souvent en utf8mb3). Si besoin d’aligner la table à la main :
-- ALTER TABLE phpbb_hophop_store CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS phpbb_hophop_store (
    user_id INT UNSIGNED NOT NULL,
    store_json MEDIUMTEXT NOT NULL,
    updated INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
