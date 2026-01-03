<?php
// storage.php
// Shared helpers for safe concurrent read/write on data.json.
// Comments in English per project preference.

declare(strict_types=1);

const DATA_FILE = __DIR__ . '/data.json';

function default_data(): array {
    return [
        'projectName' => '',
        'version' => 1,
        'rev' => 0,
        'rooms' => [],
        'boxes' => [],
        'items' => [],
    ];
}

function read_json_file_locked($fp): array {
    // $fp must be opened and locked (flock) by caller.
    rewind($fp);
    $raw = stream_get_contents($fp);
    if ($raw === false || trim($raw) === '') {
        return default_data();
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return default_data();
    }

    // Backward compatibility if rev doesn't exist.
    if (!isset($data['rev']) || !is_int($data['rev'])) {
        $data['rev'] = 0;
    }
    if (!isset($data['version']) || !is_int($data['version'])) {
        $data['version'] = 1;
    }
    if (!isset($data['projectName']) || !is_string($data['projectName'])) {
        $data['projectName'] = '';
    }
    if (!isset($data['rooms']) || !is_array($data['rooms'])) $data['rooms'] = [];
    if (!isset($data['boxes']) || !is_array($data['boxes'])) $data['boxes'] = [];
    if (!isset($data['items']) || !is_array($data['items'])) $data['items'] = [];

    return $data;
}

function write_json_file_locked($fp, array $data): void {
    // $fp must be opened and locked (flock) by caller.
    // Ensure required keys exist.
    $data = array_merge(default_data(), $data);

    rewind($fp);
    ftruncate($fp, 0);

    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        throw new RuntimeException('Could not encode JSON');
    }

    $ok = fwrite($fp, $json);
    if ($ok === false) {
        throw new RuntimeException('Could not write JSON');
    }

    fflush($fp);
}

function open_data_file(): mixed {
    // Create if missing.
    if (!file_exists(DATA_FILE)) {
        file_put_contents(DATA_FILE, json_encode(default_data(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    $fp = fopen(DATA_FILE, 'c+');
    if ($fp === false) {
        throw new RuntimeException('Could not open data file');
    }
    return $fp;
}

function next_available_boxnr(array $data): int {
    $used = [];
    foreach (($data['boxes'] ?? []) as $b) {
        if (isset($b['boxNr'])) {
            $n = (int)$b['boxNr'];
            if ($n > 0) $used[$n] = true;
        }
    }
    $n = 1;
    while (isset($used[$n])) $n++;
    return $n;
}

function new_id(): string {
    // 16 hex bytes => 32 chars
    return bin2hex(random_bytes(16));
}
