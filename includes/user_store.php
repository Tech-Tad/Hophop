<?php

require_once __DIR__ . '/storage.php';

function hophop_store_table(): string
{
    global $table_prefix;

    return $table_prefix . 'hophop_store';
}

function hophop_last_store_error(): string
{
    return (string) ($GLOBALS['HOPHOP_LAST_STORE_ERROR'] ?? '');
}

function hophop_mysql_store_ready(): bool
{
    if (!hophop_bootstrap_phpbb()) {
        return false;
    }

    global $db;

    if (!isset($db) || !is_object($db)) {
        return false;
    }

    try {
        $table = hophop_store_table();
        $sql = 'SHOW TABLES LIKE \'' . $db->sql_escape($table) . '\'';
        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        return (bool) $row;
    } catch (\Throwable $e) {
        error_log('HopHop mysql_store_ready: ' . $e->getMessage());

        return false;
    }
}

/**
 * @param array{user_id: int, username: string} $ctx
 */
function hophop_user_store_load(array $ctx): ?array
{
    if (hophop_storage() === 'mysql') {
        return hophop_user_store_load_mysql((int) $ctx['user_id'], (string) $ctx['username']);
    }

    return read_json(user_file((string) $ctx['username']), null);
}

/**
 * @param array{user_id: int, username: string} $ctx
 */
function hophop_user_store_save(array $ctx, array $data): bool
{
    if (hophop_storage() === 'mysql') {
        return hophop_user_store_save_mysql((int) $ctx['user_id'], $data);
    }

    return write_json(user_file((string) $ctx['username']), $data);
}

/**
 * @param array{user_id: int, username: string} $ctx
 */
function hophop_user_store_delete(array $ctx): void
{
    if (hophop_storage() === 'mysql') {
        hophop_user_store_delete_mysql((int) $ctx['user_id']);

        return;
    }

    @unlink(user_file((string) $ctx['username']));
}

function hophop_user_store_load_mysql(int $user_id, string $username): ?array
{
    if (!hophop_mysql_store_ready()) {
        return null;
    }

    global $db;

    $user_id = (int) $user_id;
    $sql = 'SELECT store_json
        FROM ' . hophop_store_table() . '
        WHERE user_id = ' . $user_id;
    $result = $db->sql_query($sql);
    $row = $db->sql_fetchrow($result);
    $db->sql_freeresult($result);

    if ($row) {
        $decoded = json_decode((string) $row['store_json'], true);

        return is_array($decoded) ? $decoded : null;
    }

    $legacy = read_json(user_file($username), null);

    if ($legacy !== null && hophop_user_store_save_mysql($user_id, $legacy)) {
        return $legacy;
    }

    return null;
}

function hophop_user_store_save_mysql(int $user_id, array $data): bool
{
    $GLOBALS['HOPHOP_LAST_STORE_ERROR'] = '';

    if (!hophop_mysql_store_ready()) {
        $GLOBALS['HOPHOP_LAST_STORE_ERROR'] = 'table MySQL absente ou phpBB non chargé.';

        return false;
    }

    global $db;

    $user_id = (int) $user_id;
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

    if ($json === false) {
        $GLOBALS['HOPHOP_LAST_STORE_ERROR'] = 'json_encode : ' . json_last_error_msg();

        return false;
    }

    $max_mediumtext = 16 * 1024 * 1024;

    if (strlen($json) > $max_mediumtext) {
        $GLOBALS['HOPHOP_LAST_STORE_ERROR'] = 'Données trop volumineuses pour store_json (MEDIUMTEXT).';

        return false;
    }

    $now = time();
    $table = hophop_store_table();

    if (!hophop_store_table_valid($table)) {
        $GLOBALS['HOPHOP_LAST_STORE_ERROR'] = 'Nom de table invalide.';

        return false;
    }

    $mysqli = hophop_phpbb_mysqli();

    if ($mysqli instanceof \mysqli) {
        // phpBB est souvent en utf8mb3 ; la table HopHop est utf8mb4 → bind_param refuse sinon.
        $charset_prev = $mysqli->character_set_name() ?: 'utf8mb3';

        try {
            hophop_store_debug_log(
                'save_mysql prepared uid=' . $user_id
                . ' len=' . strlen($json)
                . ' charset=' . $charset_prev . '→utf8mb4'
            );

            if (!$mysqli->set_charset('utf8mb4')) {
                throw new \RuntimeException('set_charset utf8mb4 : ' . $mysqli->error);
            }

            $check = $mysqli->prepare('SELECT user_id FROM ' . $table . ' WHERE user_id = ?');

            if ($check === false) {
                throw new \RuntimeException('prepare SELECT : ' . $mysqli->error);
            }

            $check->bind_param('i', $user_id);
            $check->execute();
            $exists = (bool) $check->get_result()->fetch_assoc();
            $check->close();

            if ($exists) {
                $stmt = $mysqli->prepare(
                    'UPDATE ' . $table . ' SET store_json = ?, updated = ? WHERE user_id = ?'
                );

                if ($stmt === false) {
                    throw new \RuntimeException('prepare UPDATE : ' . $mysqli->error);
                }

                $stmt->bind_param('sii', $json, $now, $user_id);
            } else {
                $stmt = $mysqli->prepare(
                    'INSERT INTO ' . $table . ' (user_id, store_json, updated) VALUES (?, ?, ?)'
                );

                if ($stmt === false) {
                    throw new \RuntimeException('prepare INSERT : ' . $mysqli->error);
                }

                $stmt->bind_param('isi', $user_id, $json, $now);
            }

            if (!$stmt->execute()) {
                $err = $stmt->error;
                $stmt->close();
                throw new \RuntimeException('execute : ' . $err);
            }

            $stmt->close();
            hophop_store_debug_log('save_mysql OK uid=' . $user_id);

            return true;
        } catch (\Throwable $e) {
            $GLOBALS['HOPHOP_LAST_STORE_ERROR'] = $e->getMessage();
            error_log('HopHop store save (mysqli): ' . $e->getMessage());
            hophop_store_debug_log('save_mysql FAIL prepared ' . $e->getMessage());

            return false;
        } finally {
            if ($charset_prev !== '' && strcasecmp($charset_prev, 'utf8mb4') !== 0) {
                @$mysqli->set_charset($charset_prev);
            }
        }
    }

    // sql_build_array / sql_escape sur un gros JSON provoque souvent un fatal PHP (503 HTML) :
    // sans mysqli préparé, on refuse clairement plutôt que de retenter ce chemin.
    $GLOBALS['HOPHOP_LAST_STORE_ERROR'] = 'Connexion MySQLi phpBB introuvable (requêtes préparées indisponibles).';
    hophop_store_debug_log('save_mysql FAIL no_mysqli uid=' . $user_id . ' db=' . (isset($db) ? get_class($db) : 'none'));

    return false;
}

function hophop_store_debug_log(string $line): void
{
    $entry = gmdate('c') . ' ' . $line . "\n";

    if (defined('DATA_DIR')) {
        if (!is_dir(DATA_DIR)) {
            @mkdir(DATA_DIR, 0775, true);
        }

        if (is_dir(DATA_DIR) && is_writable(DATA_DIR)) {
            if (@file_put_contents(DATA_DIR . '/hophop-api.log', $entry, FILE_APPEND | LOCK_EX) !== false) {
                return;
            }
        }
    }

    error_log('HopHop: ' . $line);
}

/** Connexion MySQLi sous-jacente phpBB (requêtes préparées, gros JSON). */
function hophop_phpbb_mysqli(): ?\mysqli
{
    global $db, $phpbb_container;

    // API publique (factory et driver) — $db global est souvent phpbb\db\driver\factory.
    if (isset($db) && is_object($db) && method_exists($db, 'get_db_connect_id')) {
        $link = $db->get_db_connect_id();

        if ($link instanceof \mysqli) {
            return $link;
        }
    }

    // Driver réel derrière la factory DI.
    if (isset($phpbb_container) && is_object($phpbb_container)
        && method_exists($phpbb_container, 'has')
        && $phpbb_container->has('dbal.conn.driver')
    ) {
        try {
            $driver = $phpbb_container->get('dbal.conn.driver');

            if (is_object($driver) && method_exists($driver, 'get_db_connect_id')) {
                $link = $driver->get_db_connect_id();

                if ($link instanceof \mysqli) {
                    return $link;
                }
            }

            $from_prop = hophop_mysqli_from_object_property($driver, ['db_connect_id', 'conn']);

            if ($from_prop instanceof \mysqli) {
                return $from_prop;
            }
        } catch (\Throwable $e) {
            hophop_store_debug_log('mysqli via container: ' . $e->getMessage());
        }
    }

    if (isset($db) && is_object($db)) {
        // factory::$driver (protected) puis db_connect_id sur le driver.
        $inner = hophop_mysqli_from_object_property($db, ['driver']);

        if ($inner instanceof \mysqli) {
            return $inner;
        }

        if (is_object($inner)) {
            if (method_exists($inner, 'get_db_connect_id')) {
                $link = $inner->get_db_connect_id();

                if ($link instanceof \mysqli) {
                    return $link;
                }
            }

            $from_prop = hophop_mysqli_from_object_property($inner, ['db_connect_id', 'conn']);

            if ($from_prop instanceof \mysqli) {
                return $from_prop;
            }
        }

        $from_prop = hophop_mysqli_from_object_property($db, ['db_connect_id', 'conn']);

        if ($from_prop instanceof \mysqli) {
            return $from_prop;
        }
    }

    return null;
}

/**
 * @param object|null $object
 * @param list<string> $props
 * @return mixed
 */
function hophop_mysqli_from_object_property($object, array $props)
{
    if (!is_object($object)) {
        return null;
    }

    foreach ($props as $prop) {
        try {
            $ref = new \ReflectionObject($object);

            if (!$ref->hasProperty($prop)) {
                continue;
            }

            $property = $ref->getProperty($prop);
            $property->setAccessible(true);

            return $property->getValue($object);
        } catch (\ReflectionException $e) {
            continue;
        }
    }

    return null;
}

function hophop_store_table_valid(string $table): bool
{
    return (bool) preg_match('/^[A-Za-z0-9_]+$/', $table);
}

/**
 * Brasseurs = lignes avec au moins une recette.
 * Visiteurs = toutes les autres lignes (total − brasseurs).
 *
 * @return array{brewers: int, visitors: int}|null
 */
function hophop_store_usage_counts(): ?array
{
    if (hophop_storage() !== 'mysql' || !hophop_mysql_store_ready()) {
        return null;
    }

    global $db;

    try {
        $sql = 'SELECT COUNT(*) AS total,
            COALESCE(SUM(
                CASE
                    WHEN JSON_VALID(store_json) = 0 THEN 0
                    WHEN JSON_LENGTH(store_json, \'$.recipes\') >= 1 THEN 1
                    ELSE 0
                END
            ), 0) AS brewers
            FROM ' . hophop_store_table();
        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        $brewers = (int) ($row['brewers'] ?? 0);
        $total = (int) ($row['total'] ?? 0);

        return [
            'brewers' => $brewers,
            'visitors' => max(0, $total - $brewers),
        ];
    } catch (\Throwable $e) {
        error_log('HopHop store usage counts: ' . $e->getMessage());

        return null;
    }
}

function hophop_user_store_delete_mysql(int $user_id): void
{
    if (!hophop_mysql_store_ready()) {
        return;
    }

    global $db;

    $db->sql_query(
        'DELETE FROM ' . hophop_store_table() . ' WHERE user_id = ' . (int) $user_id
    );
}
