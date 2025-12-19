<?php
header('Content-Type: application/json; charset=utf-8');
$file = __DIR__ . '/data.json';
$data = file_get_contents('php://input');
if ($data === false || strlen($data) === 0) {
  http_response_code(400);
  echo json_encode(["error"=>"No data received"]);
  exit;
}
file_put_contents($file, $data, LOCK_EX);
echo json_encode(["status"=>"ok"]);
