<?php

/**
 * File d’attente BeerXML BrewRecipes → HopHop (miroir de hophop/preview).
 * Stockage fichier : pas de session PHP HopHop en mode phpbb.
 */

function hophop_brewrecipes_import_dir(): string
{
    return DATA_DIR . '/brewrecipes_import';
}

function hophop_brewrecipes_import_path(int $user_id): string
{
    return hophop_brewrecipes_import_dir() . '/' . (int) $user_id . '.json';
}

/**
 * @param array{beerxml: string, brewrecipes_id?: int, brewrecipes_name?: string} $payload
 */
function hophop_brewrecipes_import_stash(int $user_id, array $payload): bool
{
    $user_id = (int) $user_id;

    if ($user_id < 1) {
        return false;
    }

    $dir = hophop_brewrecipes_import_dir();

    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
        return false;
    }

    $row = [
        'beerxml' => (string) ($payload['beerxml'] ?? ''),
        'brewrecipes_id' => (int) ($payload['brewrecipes_id'] ?? 0),
        'brewrecipes_name' => (string) ($payload['brewrecipes_name'] ?? ''),
        'expires' => time() + 600,
    ];

    if ($row['beerxml'] === '') {
        return false;
    }

    $json = json_encode($row, JSON_UNESCAPED_UNICODE);

    return $json !== false
        && @file_put_contents(hophop_brewrecipes_import_path($user_id), $json, LOCK_EX) !== false;
}

/**
 * Lit et consomme (une fois) le BeerXML en attente.
 *
 * @return array{beerxml: string, brewrecipes_id: int, brewrecipes_name: string}|null
 */
function hophop_brewrecipes_import_consume(int $user_id): ?array
{
    $user_id = (int) $user_id;
    $path = hophop_brewrecipes_import_path($user_id);

    if (!is_file($path)) {
        return null;
    }

    $raw = @file_get_contents($path);
    @unlink($path);

    if ($raw === false || $raw === '') {
        return null;
    }

    $row = json_decode($raw, true);

    if (!is_array($row) || empty($row['beerxml']) || !is_string($row['beerxml'])) {
        return null;
    }

    if ((int) ($row['expires'] ?? 0) < time()) {
        return null;
    }

    return [
        'beerxml' => $row['beerxml'],
        'brewrecipes_id' => (int) ($row['brewrecipes_id'] ?? 0),
        'brewrecipes_name' => (string) ($row['brewrecipes_name'] ?? ''),
    ];
}
