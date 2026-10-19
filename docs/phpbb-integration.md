# Intégration phpBB optionnelle (proposition de contribution)

Cette branche part de **v1.1 / BIAB** (`main` Tech-Tad) et ajoute une couche **optionnelle** pour les forums phpBB. Sans fichier local de config, le comportement reste celui du dépôt standalone (comptes JSON, pas de MySQL, pas de BrewRecipes).

## Activation

1. Copier `includes/config.local.example.php` → `includes/config.local.php` (non versionné).
2. `auth_mode` = `phpbb`, `storage` = `file` ou `mysql`.
3. Si MySQL : exécuter `docs/sql/hophop_store.sql` sur la base du forum (préfixe de tables phpBB).

## Fichiers principaux

| Fichier | Rôle |
|---------|------|
| `includes/forum.php` | Bootstrap phpBB (chemin `phpbb_root`, session SSO) |
| `includes/user_store.php` | Persistance recettes (JSON ou table `{prefix}hophop_store`) |
| `includes/brewrecipes_import.php` | File d’attente BeerXML (import depuis BrewRecipes) |
| `import_preview.php` | Point d’entrée POST BrewRecipes → HopHop |
| `health.php` | Diagnostic déploiement (auth, stockage, URLs BrewRecipes) |

## Compteurs (mode phpBB)

En pied de page, si l’auth forum est active et que le stockage MySQL est disponible : nombre de **brasseurs** (au moins une recette en base) et de **visiteurs** (comptes forum sans recette HopHop). Calcul côté serveur (`includes/user_store.php`), exposé dans la session (`brewer_count`, `visitor_count`).

## Autres notes (brassins)

Fonctionnalité **standalone ou forum** : sous-formulaire optionnel à l’édition d’un brassage (quantités réelles, températures, pH, densités, volumes). Activable dans **Compte → Brassins** ; documentée dans `help.html`. Les données vivent dans le blob JSON de l’utilisateur (fichier ou MySQL).

## BrewRecipes « débrayable »

- **Mode autonome** : aucune URL BrewRecipes n’est exposée ; l’UI masque partage et sélecteur de styles.
- **Mode phpBB** : les URLs sont construites via `app.php/brewrecipes/…` si l’extension est présente ; sinon les URLs restent vides et les boutons ne s’affichent pas.
- Overrides optionnels dans `config.local.php` (clés `brewrecipes_*` documentées dans l’exemple).

## Compatibilité standalone

- `includes/config.local.php` est ignoré par git (`.gitignore`).
- `auth_mode` absent ou `standalone` → `includes/config.php` force stockage fichier et auth locale, comme aujourd’hui.
- Les bibliothèques `lib/*.json` du dépôt upstream sont **inchangées**.

## Contexte

Développé pour [BrassageAmateur](https://www.brassageamateur.com) ; proposé en PR pour revue et éventuelle inclusion upstream.
