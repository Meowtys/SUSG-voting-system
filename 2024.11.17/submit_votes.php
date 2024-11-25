<?php
require_once 'connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user']['student_id'];

try {
    $pdo->beginTransaction();

    foreach ($data as $position => $candidate) {
        if ($candidate === "Abstain") {
            $stmt = $pdo->prepare("INSERT INTO votes (student_id, position_id, candidate_id, vote_timestamp) VALUES (:student_id, (SELECT position_id FROM positions WHERE position_name = :position), NULL, NOW())");
        } else {
            $stmt = $pdo->prepare("INSERT INTO votes (student_id, position_id, candidate_id, vote_timestamp) VALUES (:student_id, (SELECT position_id FROM positions WHERE position_name = :position), :candidate_id, NOW())");
            $stmt->bindParam(':candidate_id', $candidate['candidate_id']);
        }
        $stmt->bindParam(':student_id', $user_id);
        $stmt->bindParam(':position', $position);
        $stmt->execute();
    }

    // Update the has_voted column in the students table
    $updateStmt = $pdo->prepare("UPDATE students SET has_voted = 1 WHERE student_id = :student_id");
    $updateStmt->bindParam(':student_id', $user_id);
    $updateStmt->execute();

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}