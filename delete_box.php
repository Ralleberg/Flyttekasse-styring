<?php
// delete_box.php
// Atomically deletes a box (and its items) so BoxNr becomes reusable.
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

$boxNr = isset($req['boxNr']) ? (int)$req['boxNr'] : 0;
if ($boxNr <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'bad_boxNr'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $fp = open_data_file();
    flock($fp, LOCK_EX);

    $data = read_json_file_locked($fp);

    $beforeBoxes = count($data['boxes'] ?? []);
    $beforeItems = count($data['items'] ?? []);

    $data['boxes'] = array_values(array_filter($data['boxes'] ?? [], function($b) use ($boxNr){
        return (int)($b['boxNr'] ?? 0) !== $boxNr;
    }));

    $data['items'] = array_values(array_filter($data['items'] ?? [], function($i) use ($boxNr){
        return (int)($i['boxNr'] ?? 0) !== $boxNr;
    }));

    $afterBoxes = count($data['boxes']);
    $afterItems = count($data['items']);

    if ($afterBoxes === $beforeBoxes) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'box_not_found'], JSON_UNESCAPED_UNICODE);
        flock($fp, LOCK_UN);
        fclose($fp);
        exit;
    }

    $data['rev'] = (int)($data['rev'] ?? 0) + 1;

    write_json_file_locked($fp, $data);

    $nextAfter = next_available_boxnr($data);

    flock($fp, LOCK_UN);
    fclose($fp);

    echo json_encode([
        'ok' => true,
        'rev' => (int)$data['rev'],
        'nextBoxNr' => $nextAfter
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'delete_box_failed'], JSON_UNESCAPED_UNICODE);
}
