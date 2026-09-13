<?php
require_once __DIR__ . '/../../config/session.php';

if (!isset($_SESSION['is_comelec_logged_in']) || !$_SESSION['is_comelec_logged_in']) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

validate_csrf_token();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/SentimentCache.php';

header('Content-Type: application/json');

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['key']) || !isset($data['value']) || !isset($data['election_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required data']);
    exit;
}

try {
    $cache = new SentimentCache();
    $cache->set($data['key'], $data['value'], $data['election_id']);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
