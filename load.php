<?php
// load.php (NEW)
// Returns the current data.json and supports a lightweight meta mode.
// Comments in English per project preference.

declare(strict_types=1);

require_once __DIR__ . '/storage.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $fp = open_data_file();
    flock($fp, LOCK_SH);

    $data = read_json_file_locked($fp);
    $rev = (int)($data['rev'] ?? 0);

    // ETag based on revision.
    header('ETag: "rev-' . $rev . '"');

    $ifNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
    if ($ifNoneMatch === '"rev-' . $rev . '"') {
        http_response_code(304);
        flock($fp, LOCK_UN);
        fclose($fp);
        exit;
    }

    $meta = isset($_GET['meta']) && $_GET['meta'] === '1';
    if ($meta) {
        echo json_encode(['rev' => $rev], JSON_UNESCAPED_UNICODE);
    } else {
        // Ensure rev is included in payload.
        $data['rev'] = $rev;
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    flock($fp, LOCK_UN);
    fclose($fp);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'load_failed'], JSON_UNESCAPED_UNICODE);
}
