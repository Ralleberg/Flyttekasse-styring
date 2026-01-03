<?php
// save.php (NEW)
// Implements optimistic concurrency via rev and returns JSON.
// Comments in English per project preference.

declare(strict_types=1);

require_once __DIR__ . '/storage.php';

header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
if ($raw === false || trim($raw) === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'no_data'], JSON_UNESCAPED_UNICODE);
    exit;
}

$incoming = json_decode($raw, true);
if (!is_array($incoming)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'bad_json'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Client rev can be supplied via header or in payload.
$clientRevHeader = $_SERVER['HTTP_X_REV'] ?? null;
$clientRev = null;
if ($clientRevHeader !== null && $clientRevHeader !== '') {
    $clientRev = (int)$clientRevHeader;
} elseif (isset($incoming['rev'])) {
    $clientRev = (int)$incoming['rev'];
}

try {
    $fp = open_data_file();
    flock($fp, LOCK_EX);

    $current = read_json_file_locked($fp);
    $serverRev = (int)($current['rev'] ?? 0);

    // If client rev missing, treat as old client; allow overwrite but still bump rev.
    if ($clientRev !== null && $clientRev !== $serverRev) {
        http_response_code(409);
        echo json_encode([
            'ok' => false,
            'error' => 'conflict',
            'rev' => $serverRev,
            'data' => $current
        ], JSON_UNESCAPED_UNICODE);
        flock($fp, LOCK_UN);
        fclose($fp);
        exit;
    }

    // Bump revision.
    $incoming['rev'] = $serverRev + 1;

    write_json_file_locked($fp, $incoming);

    flock($fp, LOCK_UN);
    fclose($fp);

    echo json_encode(['ok' => true, 'rev' => $incoming['rev']], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'save_failed'], JSON_UNESCAPED_UNICODE);
}
