<?php
header('Content-Type: application/json; charset=utf-8');
$file = __DIR__ . '/data.json';
if (!file_exists($file)) {
  echo json_encode(["version"=>1,"rooms"=>[],"boxes"=>[],"items"=>[]], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
  exit;
}
readfile($file);
