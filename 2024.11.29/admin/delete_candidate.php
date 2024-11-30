<?php
require_once '../connect.php'; // Ensure this points to the correct connection file

// Get JSON data from the fetch call
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['candidateId'])) {
    $candidateId = $data['candidateId'];

    // Prepare the DELETE query
    $stmt = $pdo->prepare("DELETE FROM candidates WHERE candidate_id = ?");
    $stmt->execute([$candidateId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['message' => 'Candidate deleted successfully.']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Candidate not found.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request.']);
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);