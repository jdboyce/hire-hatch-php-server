<?php

header("Content-Type: application/json");

$url = isset($_GET['url']) ? trim($_GET['url'], '/') : '';
$segments = explode('/', $url);

switch ($segments[0] ?? '') {
    case 'jobs':
        require 'jobs.php';
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}
?>
