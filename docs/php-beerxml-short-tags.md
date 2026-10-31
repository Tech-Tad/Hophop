# BeerXML, `<?xml` et `short_open_tag` (PHP)

Un fichier **BeerXML** valide commence toujours par une déclaration XML :

```xml
<?xml version="1.0" encoding="UTF-8"?>
<RECIPES>…
```

Cette ligne est **normale et requise** dans les fichiers **`.xml`** (export téléchargé, guides BJCP, POST `application/xml` vers `import_preview.php`, etc.) : PHP ne les exécute pas, **`short_open_tag` ne s’applique pas**.

Le piège concerne les **fichiers `.php`** qui **contiennent la même déclaration en source** — par exemple en collant un BeerXML dans un script, un heredoc, ou du JavaScript inline qui génère l’export. Là, PHP scanne tout le `.php` et, avec **`short_open_tag = On`**, interprète `<?xml` comme une ouverture PHP.

HopHop sert l’interface dans **`index.php`** : bootstrap PHP, puis HTML/CSS et un gros bloc **JavaScript** inline. L’export BeerXML y construit cette déclaration dans `toBeerXML()` ; c’est ce cas qui a provoqué des pages blanches en prod.

## Symptôme

Sur certains serveurs, la page HopHop devient **blanche** (erreur fatale PHP) dès qu’on charge l’application, **sans message** si `display_errors` est désactivé en production.

Cause typique : une ligne JavaScript du type :

```javascript
return `<?xml version="1.0" encoding="UTF-8"?>
<RECIPES>…`;
```

PHP **analyse tout le fichier `.php`**, y compris l’intérieur des balises `<script>`, à la recherche d’ouvertures de code PHP.

## Lien avec `short_open_tag` (`php.ini`)

| Directive | Effet sur `<?xml` dans un `.php` |
|-----------|----------------------------------|
| **`short_open_tag = Off`** (défaut **PHP 8.x**, recommandé) | Seuls `<?php` et `<?=` ouvrent du PHP. La séquence `<?xml` dans du HTML/JS est traitée comme du **texte** et n’exécute pas de PHP. |
| **`short_open_tag = On`** (encore fréquent sur **hébergement mutualisé** ancien) | Toute séquence **`<?`** ouvre un bloc PHP. `<?xml version="1.0" …` est lu comme du PHP commençant par `xml version=…` → **parse error** (souvent ligne ~2300 de `index.php`). |

Autres tags (`asp_tags`, etc.) ne concernent pas HopHop ; le cas réel rencontré en prod est **`short_open_tag = On`**.

Vérifier sur le serveur :

```bash
php -i | grep short_open_tag
# ou un phpinfo() temporaire
```

Recommandation déploiement : **`short_open_tag = Off`** (comme phpBB 3.3 et PHP 8). Cela n’excuse pas d’écrire `<?xml` en clair dans un `.php` : le code doit rester portable.

## Correctif dans le code (obligatoire pour HopHop)

Ne jamais mettre la déclaration XML BeerXML en littéral commençant par `<?` dans `index.php`. Utiliser une **concaténation** pour que le scanner PHP ne voie pas `<?` :

```javascript
const xmlDecl = '<' + '?xml version="1.0" encoding="UTF-8"?>';
return `${xmlDecl}\n<RECIPES>…`;
```

Emplacement actuel : `toBeerXML()` dans `index.php` (export recette, partage BrewRecipes, téléchargement BeerXML).

## Fichiers BeerXML : ce qui est concerné ou non

| Fichier / flux | Déclaration `<?xml` | `short_open_tag` |
|----------------|---------------------|------------------|
| **`.xml` BeerXML** sur disque ou en téléchargement | Oui, obligatoire en tête de fichier | **Non** (fichier non interprété comme PHP) |
| **Corps POST** BeerXML (`import_preview.php`, partage BrewRecipes) | Oui, dans le payload HTTP | **Non** (données reçues, pas du code source `.php`) |
| **Source d’un `.php`** (JS inline, exemple collé, template) | À **ne pas** écrire en littéral `<?xml` | **Oui** si `short_open_tag = On` |
| **`.js` / `.html` purs** | Peut contenir `<?xml` si besoin | **Non** |

En résumé : tous les **échanges BeerXML** du produit (export, import, bibliothèques) utilisent la déclaration XML standard ; seule son **présence dans le code source PHP** déclenche le conflit avec les short tags.

## Règle pour les contributeurs

- Dans tout fichier **`.php`**, éviter `<?` en dehors de `<?php` / `<?=` / `<?php echo … ?>` (y compris dans commentaires HTML, templates JS, chaînes PHP si elles sont dans une zone interprétée).
- **Ne pas copier-coller** un fichier BeerXML entier (ou sa première ligne) dans un `.php` sans casser la séquence `<?` (concaténation, lecture depuis un `.xml` externe, génération côté client comme dans HopHop).
- Même motif à risque pour d’autres formats XML (SVG inline, etc.) dans un `.php`.
- Les fichiers **`.js` / `.html` purs** (hors PHP) ne posent pas ce problème ; `help.html` n’est pas concerné.

## Références

- [PHP : short_open_tag](https://www.php.net/manual/fr/ini.core.php#ini.short-open-tag)
- Issue upstream Penn-Maen : [Tech-Tad/Hophop#2](https://github.com/Tech-Tad/Hophop/issues/2) (annexe Print+ / piège `<?xml`)
