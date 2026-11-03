# HopHop — application auto-hébergée de recettes de bière

*[English version](README.en.md)*

Application 100 % PHP + HTML/CSS/JavaScript vanilla. Aucune base de données :
toutes les données sont stockées dans des fichiers JSON, sous `data/`.

## Installation

1. Copiez tout le dossier `brassin-php/` sur votre hébergement (mutualisé ou
   serveur perso), via FTP/SFTP ou `git`.
2. Assurez-vous que PHP 7.4 ou plus récent est disponible (aucune extension
   particulière requise — `json`, `session` et `hash` sont incluses en
   standard dans PHP).
3. Donnez au serveur web le droit d'écrire dans le dossier `data/`
   (et ses sous-dossiers) :
   ```
   chmod -R 775 data
   chown -R www-data:www-data data   # adaptez l'utilisateur à votre serveur
   ```
4. Ouvrez `https://votre-domaine/chemin/vers/brassin-php/` dans un
   navigateur : la page de connexion s'affiche. Créez un compte, c'est prêt.
5. Importez dans « Ingrédients » les fichiers du répertoire `lib` pour peupler
   vos bibliothèques.
6. Pour obtenir les styles BJCP et BA, importez les fichiers JSON disponibles
   sur [https://github.com/beerjson/bjcp-json/tree/main/styles](https://github.com/beerjson/bjcp-json/tree/main/styles).

Aucune étape de build, aucune dépendance à installer (`composer`, `npm`…) :
les 6 fichiers du dossier suffisent.

## Structure

```
brassin-php/
├── index.php            page unique (interface complète, HTML+CSS+JS)
├── api.php               point d'entrée API (JSON), gère comptes et données
├── includes/
│   ├── config.php        chemins, session
│   ├── storage.php        lecture/écriture JSON avec verrouillage de fichier
│   ├── auth.php           comptes, mots de passe, jeu de données par défaut
│   ├── i18n.php           résolution de langue + chargement des fichiers lang/
│   └── img/logo.svg       logo de l'application (inliné côté serveur, recoloré selon le thème actif)
├── lang/                 fichiers de langue actifs (un fichier PHP par langue)
│   ├── fr.php             français (langue par défaut / repli)
│   └── en.php             anglais
├── lang-available/       gabarits de traduction prêts à activer (non chargés)
│   ├── es.php             espagnol
│   ├── pt.php             portugais
│   ├── it.php              italien
│   └── de.php             allemand
├── data/                 créé et rempli automatiquement — NE PAS exposer
│   ├── users.json         identifiants + mots de passe hachés
│   ├── store/<compte>.json  recettes, brassages, stock, bibliothèque, profils
│   ├── .htaccess           bloque l'accès direct (Apache)
│   └── store/.htaccess
└── includes/.htaccess     bloque l'accès direct au code interne (Apache)
```

Sur **Nginx**, les `.htaccess` sont ignorés : ajoutez plutôt, dans le bloc
`server {}` de votre site, un blocage équivalent :
```nginx
location ~ ^/(data|includes)/ { deny all; return 404; }
```

## Sécurité et bonnes pratiques

- Les mots de passe sont hachés avec `password_hash()` (bcrypt) — jamais
  stockés en clair.
- Servez l'application en HTTPS : le cookie de session est marqué
  automatiquement `Secure` dès que `$_SERVER['HTTPS']` est détecté.
- `data/` contient des données personnelles (recettes, éventuellement des
  identifiants) : vérifiez que le `.htaccess` fonctionne bien (tentez
  d'ouvrir `https://votre-domaine/.../data/users.json` — vous devez obtenir
  une erreur 403/404) ou utilisez la règle Nginx ci-dessus.
- Il n'y a pas de limite de tentatives de connexion intégrée. Si votre
  application est exposée publiquement, envisagez une protection
  complémentaire (fail2ban, limite au niveau du serveur web).
- Sauvegardez régulièrement le dossier `data/` (ou utilisez, depuis l'onglet
  Compte de l'application, l'export JSON par utilisateur).

## Fonctionnalités

Comptes utilisateurs · profils matériel multiples (rendement, pertes,
évaporation…), méthode de brassage classique ou Brew in a Bag (BIAB) ·
éditeur de recette avec calculs en direct (densités, ABV,
IBU, EBC, eau d'empâtage/rinçage, sucre de conditionnement) · bibliothèque
d'ingrédients personnalisable par utilisateur · gestion de stock avec alerte
de manque · redimensionnement de recette · historique de brassage (visé vs
mesuré), avec sous-formulaire optionnel « Autres notes » (quantités réelles,
températures, pH, densités et volumes du jour de brassage — activable dans
Compte) · fiche recette imprimable · export BeerXML 1.0 et BeerJSON 1.0.

Unités européennes partout (L, kg, g, °C, EBC, densité SG).

## Langues (i18n)

L'interface est disponible en **français** et **anglais** dès l'installation.
Le choix se fait depuis l'onglet **Compte**, et la préférence est mémorisée
par compte (champ `prefs.lang`) ; sans compte connecté, la langue du
navigateur est détectée automatiquement (en-tête `Accept-Language`), avec
repli sur le français.

Chaque langue est un simple fichier PHP qui retourne un tableau associatif
`clé => texte`, utilisé à la fois pour l'interface (injecté en JSON côté
client) et pour les messages d'erreur du serveur (API, connexion/inscription).

### Ajouter une langue

1. Copiez un fichier de `lang-available/` (par exemple `lang-available/es.php`
   pour l'espagnol) vers `lang/es.php` — ou dupliquez `lang/en.php` pour
   repartir de zéro et traduisez les valeurs.
2. C'est tout : la nouvelle langue apparaît automatiquement dans le
   sélecteur de l'onglet Compte, sans aucune modification de code. Le nom
   de code du fichier (2 lettres, ex. `es`, `pt`, `it`, `de`) doit suivre la
   norme ISO 639-1.

Les gabarits fournis dans `lang-available/` (espagnol, portugais, italien,
allemand) sont déjà entièrement traduits et prêts à l'emploi ; il suffit de
les déplacer dans `lang/` pour les activer.

## Notes développeurs

- **BeerXML et `<?xml`** : la déclaration `<?xml …?>` en tête des **fichiers `.xml` BeerXML** (export, import, guides) est normale ; **`short_open_tag` ne s’applique pas** à ces fichiers. En revanche, la même ligne **dans le source d’un `.php`** (ex. JS inline de `index.php`) provoque une page blanche si `short_open_tag = On`. HopHop utilise une concaténation JS dans `toBeerXML()`. Voir [`docs/php-beerxml-short-tags.md`](docs/php-beerxml-short-tags.md).

## Limites connues

- Un fichier JSON par compte : convient très bien à un usage personnel ou
  amateur ; pour un très grand nombre de comptes actifs simultanément, une
  vraie base de données serait plus adaptée (au-delà du cadre demandé ici).
- Pas de récupération de mot de passe oublié par e-mail (pas de serveur SMTP
  supposé disponible) : en cas d'oubli, un administrateur du serveur peut
  supprimer l'entrée correspondante dans `data/users.json` pour permettre
  une nouvelle inscription avec le même identifiant.

## Licence

**HopHop** © claude.ai, Penn-Maen, Jean-Luc — [CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/deed.fr)
(Attribution – Pas d'Utilisation Commerciale – Partage dans les Mêmes Conditions).
Texte complet dans [`LICENSE.md`](LICENSE.md).
