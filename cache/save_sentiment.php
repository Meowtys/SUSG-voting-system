<?php
session_start();
require_once 'SentimentCache.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required data
if (!$data || !isset($data['key']) || !isset($data['value'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required data']);
    exit;
}

// Check authentication
$isAdmin = isset($_SESSION['is_comelec_logged_in']) && $_SESSION['is_comelec_logged_in'];
if (!$isAdmin && !isset($data['election_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Election ID required for non-admin requests']);
    exit;
}

try {
    $cache = new SentimentCache();
    if ($isAdmin) {
        $cache->set($data['key'], $data['value'], null);
    } else {
        $cache->set($data['key'], $data['value'], $data['election_id']);
    }
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
