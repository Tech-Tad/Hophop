# HopHop — self-hosted beer brewing recipe app

*[Version française](README.md)*

100 % PHP + vanilla HTML/CSS/JavaScript application. No database: all data
is stored in JSON files, under `data/`.

## Installation

1. Copy the whole `brassin-php/` folder to your hosting (shared or your own
   server), via FTP/SFTP or `git`.
2. Make sure PHP 7.4 or newer is available (no special extension required —
   `json`, `session` and `hash` are included in PHP by default).
3. Give the web server write access to the `data/` folder (and its
   subfolders):
   ```
   chmod -R 775 data
   chown -R www-data:www-data data   # adapt the user to your server
   ```
4. Open `https://your-domain/path/to/brassin-php/` in a browser: the login
   page appears. Create an account, you're ready to go.
5. Import the files from the `lib` directory into "Ingredients" to populate
   your libraries.
6. To get the BJCP and BA styles, import the JSON files available at
   [https://github.com/beerjson/bjcp-json/tree/main/styles](https://github.com/beerjson/bjcp-json/tree/main/styles).

No build step, no dependency to install (`composer`, `npm`…): the files in
the folder are enough as-is.

## Structure

```
brassin-php/
├── index.php            single page (full interface, HTML+CSS+JS)
├── api.php               API entry point (JSON), handles accounts and data
├── includes/
│   ├── config.php        paths, session
│   ├── storage.php        JSON read/write with file locking
│   ├── auth.php           accounts, passwords, default data set
│   ├── i18n.php           language resolution + loading of lang/ files
│   └── img/logo.svg       app logo (inlined server-side, recolored to match the active theme)
├── lang/                 active language files (one PHP file per language)
│   ├── fr.php             French (default / fallback language)
│   └── en.php             English
├── lang-available/       translation templates ready to enable (not loaded)
│   ├── es.php             Spanish
│   ├── pt.php             Portuguese
│   ├── it.php              Italian
│   └── de.php             German
├── data/                 created and populated automatically — DO NOT expose
│   ├── users.json         credentials + hashed passwords
│   ├── store/<account>.json  recipes, brews, stock, library, profiles
│   ├── .htaccess           blocks direct access (Apache)
│   └── store/.htaccess
└── includes/.htaccess     blocks direct access to internal code (Apache)
```

On **Nginx**, `.htaccess` files are ignored: add an equivalent block instead,
in your site's `server {}` block:
```nginx
location ~ ^/(data|includes)/ { deny all; return 404; }
```

## Security and best practices

- Passwords are hashed with `password_hash()` (bcrypt) — never stored in
  plain text.
- Serve the application over HTTPS: the session cookie is automatically
  marked `Secure` as soon as `$_SERVER['HTTPS']` is detected.
- `data/` contains personal data (recipes, possibly credentials): check
  that `.htaccess` works correctly (try opening
  `https://your-domain/.../data/users.json` — you should get a 403/404
  error) or use the Nginx rule above.
- There is no built-in login attempt limit. If your application is exposed
  publicly, consider additional protection (fail2ban, a limit at the web
  server level).
- Back up the `data/` folder regularly (or use the per-user JSON export,
  from the app's Account tab).

## Features

User accounts · multiple equipment profiles (efficiency, losses,
evaporation…), classic or Brew in a Bag (BIAB) brewing method · recipe
editor with live calculations (gravities, ABV, IBU, EBC, mash/sparge water,
priming sugar) · per-user customizable ingredient library · stock
management with low-stock alerts · recipe scaling · brewing history
(targeted vs measured), with an optional "Extra notes" sub-form (actual
quantities, temperatures, pH, gravities and volumes on brew day — enabled
from Account) · printable recipe sheet · BeerXML 1.0 and BeerJSON 1.0
export.

European units everywhere (L, kg, g, °C, EBC, SG gravity).

## Languages (i18n)

The interface is available in **French** and **English** out of the box.
The choice is made from the **Account** tab, and the preference is
remembered per account (`prefs.lang` field); with no account logged in, the
browser's language is detected automatically (`Accept-Language` header),
falling back to French.

Each language is a simple PHP file returning a `key => text` associative
array, used both for the interface (injected as JSON client-side) and for
server error messages (API, login/registration).

### Adding a language

1. Copy a file from `lang-available/` (for example `lang-available/es.php`
   for Spanish) to `lang/es.php` — or duplicate `lang/en.php` to start from
   scratch and translate the values.
2. That's it: the new language appears automatically in the Account tab's
   selector, with no code changes. The file's code name (2 letters, e.g.
   `es`, `pt`, `it`, `de`) must follow the ISO 639-1 standard.

The templates provided in `lang-available/` (Spanish, Portuguese, Italian,
German) are already fully translated and ready to use; just move them into
`lang/` to enable them.

## Known limitations

- One JSON file per account: works very well for personal or hobbyist use;
  for a very large number of simultaneously active accounts, a real
  database would be more appropriate (beyond the scope intended here).
- No forgotten-password recovery by e-mail (no SMTP server assumed to be
  available): if forgotten, a server administrator can remove the
  corresponding entry in `data/users.json` to allow re-registering with the
  same username.

## License

**HopHop** © claude.ai, Penn-Maen, Jean-Luc — [CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/deed.en)
(Attribution – NonCommercial – ShareAlike).
Full text in [`LICENSE.md`](LICENSE.md).
