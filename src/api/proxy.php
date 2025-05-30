<?php
// This is an optional proxy to handle CORS issues more robustly
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$targetUrl = $_GET['url'] ?? '';

if (!$targetUrl) {
    http_response_code(400);
    echo json_encode(['error' => 'No target URL provided']);
    exit;
}

// Only allow localhost URLs for security
if (!preg_match('/^https?:\/\/(localhost|127\\.0\\.0\\.1|0\\.0\\.0\\.0)(:[0-9]+)?/', $targetUrl)) {
    http_response_code(403);
    echo json_encode(['error' => 'Only localhost URLs are allowed']);
    exit;
}

// Forward the request
$method = $_SERVER['REQUEST_METHOD'];
$headers = getallheaders();
$body = file_get_contents('php://input');

$context = stream_context_create([
    'http' => [
        'method' => $method,
        'header' => implode("\r\n", array_map(function($k, $v) {
            return "$k: $v";
        }, array_keys($headers), $headers)),
        'content' => $body
    ]
]);

$response = file_get_contents($targetUrl, false, $context);

if ($response === FALSE) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to connect to target URL']);
    exit;
}

// Forward response headers
foreach ($http_response_header as $header) {
    if (!preg_match('/^HTTP\//', $header)) {
        header($header);
    }
}

echo $response;


