<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$valid_api_keys = [
    'key123' => 'UserA',
    'key456' => 'UserB'
];

function log_request($status_code, $api_key, $request_path) {
    $log_file = 'logs/gateway.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $api_key = $api_key ?: 'None';
    $log_entry = "[$timestamp] - IP: $ip - API Key: $api_key - Path: $request_path - Status: $status_code\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

$api_key = null;
$headers = getallheaders();
if (isset($headers['X-API-Key'])) {
    $api_key = $headers['X-API-Key'];
}

if (!$api_key || !array_key_exists($api_key, $valid_api_keys)) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid or missing API Key']);
    log_request(401, $api_key, $_GET['request_path'] ?? '');
    exit;
}

$rate_limit_file = "ratelimit_data/{$api_key}.json";
if (file_exists($rate_limit_file)) {
    $data = json_decode(file_get_contents($rate_limit_file), true);
    $current_time = time();
    if ($current_time - $data['timestamp'] > 60) {
        $data = ['timestamp' => $current_time, 'count' => 1];
    } else {
        if ($data['count'] >= 10) {
            http_response_code(429);
            echo json_encode(['error' => 'Rate limit exceeded']);
            log_request(429, $api_key, $_GET['request_path'] ?? '');
            exit;
        } else {
            $data['count']++;
        }
    }
} else {
    $data = ['timestamp' => time(), 'count' => 1];
}
file_put_contents($rate_limit_file, json_encode($data));

$request = $_GET['request_path'] ?? '';
switch ($request) {
    case 'users':
        include 'services/service_users.php';
        log_request(200, $api_key, $request);
        break;
    case 'products':
        include 'services/service_products.php';
        log_request(200, $api_key, $request);
        break;
    default:
        http_response_code(404);
        echo json_encode(["error" => "Unknown endpoint"]);
        log_request(404, $api_key, $request);
}
?>
