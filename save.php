<?php
// save.php

header("Content-Type: text/plain");

// Read raw JSON input
$data = file_get_contents("php://input");

if ($data === false || trim($data) === "") {
    http_response_code(400);
    exit("No data received");
}

// Absolute path to data.json (IMPORTANT on Linux)
$file = __DIR__ . "/data.json";

// Write JSON to file
$result = file_put_contents($file, $data);

if ($result === false) {
    http_response_code(500);
    exit("Could not write file");
}

echo "OK";