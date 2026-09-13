<?php
require_once __DIR__ . '/../../config/session.php';

if (!isset($_SESSION['is_comelec_logged_in']) || !$_SESSION['is_comelec_logged_in']) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

validate_csrf_token();

require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $candidateId = $data['candidateId'];

    // Fetch the candidate's image path
    $stmt = $pdo->prepare("SELECT candidate_image FROM candidates WHERE candidate_id = ?");
    $stmt->execute([$candidateId]);
    $candidate = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($candidate) {
        $candidateImage = $candidate['candidate_image'];

        // Delete candidate from the database
        $stmt = $pdo->prepare("DELETE FROM candidates WHERE candidate_id = ?");
        try {
            $stmt->execute([$candidateId]);

            // Delete the candidate's image file
            if ($candidateImage && file_exists('../' . $candidateImage)) {
                unlink('../' . $candidateImage);
            }
            echo json_encode(["success" => true]);
        } catch (PDOException $exception) {
            if (($exception->errorInfo[0] ?? null) === '23000') {
                http_response_code(409);
                echo json_encode([
                    "success" => false,
                    "message" => "This candidate has existing votes and cannot be deleted"
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    "success" => false,
                    "message" => "Unable to delete candidate"
                ]);
            }
        }
    } else {
        echo json_encode(["success" => false, "message" => "Candidate not found."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Invalid request!"]);
}
?>