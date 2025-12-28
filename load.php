<?php
// load.php

header("Content-Type: application/json");

$file = __DIR__ . "/data.json";

if (!file_exists($file)) {
    echo json_encode([]);
    exit;
}

readfile($file);