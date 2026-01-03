<?php
// create_box.php (NEW)
// Atomically creates a new box with a unique BoxNr across devices.
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

$req = json_decode($raw, true);
if (!is_array($req)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'bad_json'], JSON_UNESCAPED_UNICODE);
    exit;
}

$roomId = isset($req['roomId']) ? (string)$req['roomId'] : '';
$note = isset($req['note']) ? (string)$req['note'] : '';

if ($roomId === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'missing_roomId'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $fp = open_data_file();
    flock($fp, LOCK_EX);

    $data = read_json_file_locked($fp);

    $rooms = $data['rooms'] ?? [];
    $roomExists = false;
    foreach ($rooms as $r) {
        if (isset($r['id']) && (string)$r['id'] === $roomId) {
            $roomExists = true;
            break;
        }
    }
    if (!$roomExists) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'room_not_found'], JSON_UNESCAPED_UNICODE);
        flock($fp, LOCK_UN);
        fclose($fp);
        exit;
    }

    $boxNr = next_available_boxnr($data);

    $box = [
        'id' => new_id(),
        'boxNr' => $boxNr,
        'roomId' => $roomId,
        'createdAt' => date('c'),
        'note' => $note
    ];

    if (!isset($data['boxes']) || !is_array($data['boxes'])) $data['boxes'] = [];
    $data['boxes'][] = $box;

    $data['rev'] = (int)($data['rev'] ?? 0) + 1;

    write_json_file_locked($fp, $data);

    $nextAfter = next_available_boxnr($data);

    flock($fp, LOCK_UN);
    fclose($fp);

    echo json_encode([
        'ok' => true,
        'rev' => (int)$data['rev'],
        'box' => $box,
        'nextBoxNr' => $nextAfter
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'create_box_failed'], JSON_UNESCAPED_UNICODE);
}
