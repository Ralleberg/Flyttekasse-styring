<?php
// reserve_box.php (NEW)
// Returns the next available BoxNr based on current server data.
// Comments in English per project preference.

declare(strict_types=1);

require_once __DIR__ . '/storage.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $fp = open_data_file();
    flock($fp, LOCK_SH);

    $data = read_json_file_locked($fp);
    $next = next_available_boxnr($data);

    flock($fp, LOCK_UN);
    fclose($fp);

    echo json_encode([
        'ok' => true,
        'nextBoxNr' => $next,
        'rev' => (int)($data['rev'] ?? 0)
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'reserve_failed'], JSON_UNESCAPED_UNICODE);
}
