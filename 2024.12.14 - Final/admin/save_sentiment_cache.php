
<?php
session_start();
if (!isset($_SESSION['is_comelec_logged_in']) || !$_SESSION['is_comelec_logged_in']) {
    http_response_code(403);
    exit('Unauthorized');
}

require_once '../cache/SentimentCache.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['key']) && isset($data['value'])) {
    $cache = new SentimentCache();
    $cache->set($data['key'], $data['value']);
    echo json_encode(['success' => true]);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data']);
}
?>