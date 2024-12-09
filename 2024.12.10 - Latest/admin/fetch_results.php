<?php
require_once '../connect.php';

if (isset($_GET['position_id'])) {
    $positionId = $_GET['position_id'];

    $stmt = $pdo->prepare("
        SELECT candidates.candidate_name, candidates.candidate_party, candidates.candidate_image, COUNT(votes.vote_id) AS votes
        FROM votes
        JOIN candidates ON votes.candidate_id = candidates.candidate_id
        WHERE votes.position_id = ?
        GROUP BY candidates.candidate_id
        ORDER BY votes DESC
    ");
    $stmt->execute([$positionId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($results);
} else {
    http_response_code(400);
    echo json_encode(["message" => "Invalid request!"]);
}
?>