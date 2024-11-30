<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$data = json_decode(file_get_contents('php://input'), true);
$_SESSION['selectedVotes'] = $data;

echo json_encode(['success' => true]);