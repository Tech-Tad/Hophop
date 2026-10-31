<?php

require_once __DIR__ . '/storage.php';
require_once __DIR__ . '/user_store.php';

function uid(): string
{
    return substr(bin2hex(random_bytes(5)), 0, 9);
}

function hophop_standalone_auth(): bool
{
    return hophop_auth_mode() === 'standalone';
}

function get_users(): array
{
    return read_json(USERS_FILE, []);
}

function save_users(array $u): void
{
    write_json(USERS_FILE, $u);
}

function current_user(): ?string
{
    if (hophop_auth_mode() === 'phpbb') {
        $forum = hophop_forum_user();

        return $forum ? $forum['username'] : null;
    }

    return $_SESSION['user'] ?? null;
}

/**
 * @return array{user_id: int, username: string}|null
 */
function hophop_user_context(): ?array
{
    if (hophop_auth_mode() === 'phpbb') {
        return hophop_forum_user();
    }

    $username = current_user();

    if ($username === null) {
        return null;
    }

    return ['user_id' => 0, 'username' => $username];
}

function require_login(): void
{
    if (hophop_auth_mode() === 'phpbb' && hophop_forum_is_bot()) {
        json_fail(tt('server_err_robot_blocked'), 403);
    }

    if (!hophop_user_context()) {
        json_fail(tt('server_err_session_expired'), 401);
    }
}

function hophop_session_payload(): array
{
    $username = current_user();
    $payload = [
        'username' => $username,
        'auth_mode' => hophop_auth_mode(),
        'storage' => hophop_storage(),
        'features' => [
            'register' => hophop_standalone_auth(),
            'local_login' => hophop_standalone_auth(),
            'delete_account' => hophop_standalone_auth(),
        ],
    ];

    if (hophop_auth_mode() === 'phpbb') {
        $payload['login_url'] = hophop_forum_login_url();
        $payload['logout_url'] = hophop_forum_logout_url();
        $payload['brewrecipes_share_url'] = hophop_brewrecipes_share_url();
        $payload['brewrecipes_style_guides_url'] = hophop_brewrecipes_style_guides_url();
        $payload['brewrecipes_styles_url'] = hophop_brewrecipes_styles_url();
        $payload['avatar_url'] = hophop_forum_avatar_url();
        $payload['mysql_ready'] = hophop_storage() !== 'mysql' || hophop_mysql_store_ready();
        $usage = hophop_store_usage_counts();
        if ($usage !== null) {
            $payload['brewer_count'] = $usage['brewers'];
            $payload['visitor_count'] = $usage['visitors'];
        }

        if (hophop_forum_is_bot()) {
            $payload['blocked'] = 'robot';
        }
    }

    return $payload;
}

function hophop_reject_if_forum_auth(): void
{
    if (!hophop_standalone_auth()) {
        json_fail(tt('server_err_forum_accounts'), 403);
    }
}

/** Jeu d'ingrédients et de valeurs par défaut proposé à la création d'un compte (librement modifiable ensuite). */
function seed_data(): array
{
    $I = function (array $rows) {
        return array_map(function ($r) {
            return array_merge(['id' => uid(), 'note' => ''], $r);
        }, $rows);
    };

    return [
        'ver' => 1,
        'profiles' => [['id' => uid(), 'name' => 'Cuve 30 L (exemple)', 'batch' => 20, 'boil' => 60, 'evap' => 3, 'trub' => 1.5, 'mashLoss' => 1, 'absorb' => 1, 'ratio' => 3, 'infusionTemp' => 96, 'shrink' => 4, 'eff' => 72]],
        'ing' => [
            'ferm' => $I([
                ['name' => 'Malt Pilsner', 'type' => 'grain', 'ebc' => 3.5, 'extract' => 81],
                ['name' => 'Malt Pale Ale', 'type' => 'grain', 'ebc' => 6, 'extract' => 80],
                ['name' => 'Malt Maris Otter', 'type' => 'grain', 'ebc' => 5.5, 'extract' => 81],
                ['name' => 'Malt Vienne', 'type' => 'grain', 'ebc' => 8, 'extract' => 80],
                ['name' => 'Malt Munich', 'type' => 'grain', 'ebc' => 15, 'extract' => 79],
                ['name' => 'Malt de blé', 'type' => 'grain', 'ebc' => 4, 'extract' => 82],
                ['name' => 'Cara Pils / Dextrine', 'type' => 'grain', 'ebc' => 4, 'extract' => 74],
                ['name' => 'Caramel 150 EBC', 'type' => 'grain', 'ebc' => 150, 'extract' => 74],
                ['name' => 'Malt chocolat', 'type' => 'grain', 'ebc' => 900, 'extract' => 68],
                ['name' => 'Orge torréfiée', 'type' => 'grain', 'ebc' => 1100, 'extract' => 65],
                ["name" => "Flocons d'avoine", 'type' => 'grain', 'ebc' => 3, 'extract' => 70],
                ['name' => 'Sucre blanc (saccharose)', 'type' => 'sugar', 'ebc' => 0, 'extract' => 100],
                ['name' => 'Dextrose', 'type' => 'sugar', 'ebc' => 0, 'extract' => 91],
                ['name' => 'Extrait de malt sec pâle', 'type' => 'extract', 'ebc' => 8, 'extract' => 96],
            ]),
            'hop' => $I([
                ['name' => 'Saaz', 'alpha' => 3.5, 'form' => 'Pellet'], ['name' => 'Hallertau Mittelfrüh', 'alpha' => 4, 'form' => 'Pellet'],
                ['name' => 'Tettnang', 'alpha' => 4, 'form' => 'Pellet'], ['name' => 'Strisselspalt', 'alpha' => 2.5, 'form' => 'Pellet'],
                ['name' => 'East Kent Goldings', 'alpha' => 5, 'form' => 'Pellet'], ['name' => 'Cascade', 'alpha' => 6, 'form' => 'Pellet'],
                ['name' => 'Centennial', 'alpha' => 9.5, 'form' => 'Pellet'], ['name' => 'Citra', 'alpha' => 12, 'form' => 'Pellet'],
                ['name' => 'Mosaic', 'alpha' => 11.5, 'form' => 'Pellet'], ['name' => 'Magnum', 'alpha' => 14, 'form' => 'Pellet'],
            ]),
            'yeast' => $I([
                ['name' => 'SafAle US-05', 'type' => 'ale', 'form' => 'dry', 'att' => 81, 'tmin' => 15, 'tmax' => 22],
                ['name' => 'SafAle S-04', 'type' => 'ale', 'form' => 'dry', 'att' => 75, 'tmin' => 15, 'tmax' => 20],
                ['name' => 'Lallemand Nottingham', 'type' => 'ale', 'form' => 'dry', 'att' => 77, 'tmin' => 14, 'tmax' => 21],
                ['name' => 'Lallemand Belle Saison', 'type' => 'ale', 'form' => 'dry', 'att' => 88, 'tmin' => 15, 'tmax' => 35],
                ['name' => 'SafLager W-34/70', 'type' => 'lager', 'form' => 'dry', 'att' => 82, 'tmin' => 9, 'tmax' => 15],
                ['name' => 'SafLager S-23', 'type' => 'lager', 'form' => 'dry', 'att' => 82, 'tmin' => 9, 'tmax' => 15],
            ]),
            'misc' => $I([
                ["name" => "Mousse d'Irlande", 'unit' => 'g'], ['name' => 'Gypse', 'unit' => 'g'], ['name' => 'Chlorure de calcium', 'unit' => 'g'],
                ['name' => 'Acide lactique 80 %', 'unit' => 'mL'], ['name' => 'Coriandre (graines)', 'unit' => 'g'], ["name" => "Zeste d'orange", 'unit' => 'g'],
            ]),
            'style' => [],
        ],
        'recipes' => [], 'brews' => [], 'stock' => new stdClass(),
    ];
}

function register(string $username, string $password): void
{
    hophop_reject_if_forum_auth();
    $username = trim($username);
    if (!username_valid($username)) {
        json_fail(tt('server_err_username_invalid'));
    }
    if (strlen($password) < 6) {
        json_fail(tt('server_err_password_short'));
    }
    $users = get_users();
    if (isset($users[$username])) {
        json_fail(tt('server_err_username_taken'));
    }
    $users[$username] = ['hash' => password_hash($password, PASSWORD_DEFAULT), 'created' => time()];
    save_users($users);
    write_json(user_file($username), seed_data());
    $_SESSION['user'] = $username;
}

function do_login(string $username, string $password): void
{
    hophop_reject_if_forum_auth();
    $users = get_users();
    $username = trim($username);
    if (!isset($users[$username]) || !password_verify($password, $users[$username]['hash'])) {
        json_fail(tt('server_err_login_invalid'));
    }
    $_SESSION['user'] = $username;
}

function do_logout(): void
{
    if (!hophop_standalone_auth()) {
        return;
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function hophop_load_user_data(): array
{
    $ctx = hophop_user_context();
    if (!$ctx) {
        json_fail(tt('server_err_session_expired'), 401);
    }

    if (hophop_storage() === 'mysql' && !hophop_mysql_store_ready()) {
        json_fail(tt('server_err_mysql_not_configured'), 503);
    }

    $data = hophop_user_store_load($ctx);

    if ($data === null) {
        $data = seed_data();
        if (!hophop_user_store_save($ctx, $data)) {
            json_fail(tt('server_err_init_data'), 500);
        }
    }

    return $data;
}

function hophop_save_user_data(array $data): void
{
    $ctx = hophop_user_context();
    if (!$ctx) {
        json_fail(tt('server_err_session_expired'), 401);
    }

    hophop_store_debug_log(
        'save requested uid=' . (int) $ctx['user_id']
        . ' storage=' . hophop_storage()
        . ' bytes=' . strlen(json_encode($data, JSON_UNESCAPED_UNICODE) ?: '')
    );

    if (hophop_storage() === 'mysql' && !hophop_mysql_store_ready()) {
        json_fail(tt('server_err_mysql_not_configured'), 503);
    }

    if (!hophop_user_store_save($ctx, $data)) {
        $detail = hophop_last_store_error();
        json_fail(
            tt('server_err_save_failed') . ($detail !== '' ? ' ' . $detail : ''),
            500
        );
    }
}
