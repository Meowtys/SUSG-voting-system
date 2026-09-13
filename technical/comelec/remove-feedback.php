<?php
require_once __DIR__ . '/../../config/session.php';

if (!isset($_SESSION['is_comelec_logged_in']) || !$_SESSION['is_comelec_logged_in']) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

validate_session_activity('comelec', '../../comelec/login.php', true);

validate_csrf_token(true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$feedbackId = $data['id'] ?? null;

if ($feedbackId !== null) {
    // Delete feedback from the database
    $stmt = $pdo->prepare("DELETE FROM feedbacks WHERE feedback_id = :id");
    $stmt->execute(['id' => $feedbackId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}
?>