
<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $candidateId = $data['candidateId'];

    // Delete candidate from the database
    $stmt = $pdo->prepare("DELETE FROM candidates WHERE candidate_id = ?");
    if ($stmt->execute([$candidateId])) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Invalid request!"]);
}
?>