<?php
require_once 'connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['voter']['student_id'];

try {
    $pdo->beginTransaction();

    foreach ($data as $position => $candidate) {
        $position_id_stmt = $pdo->prepare("SELECT position_id FROM positions WHERE position_name = :position");
        $position_id_stmt->bindParam(':position', $position);
        $position_id_stmt->execute();
        $position_id = $position_id_stmt->fetchColumn();

        if ($candidate['candidate_id'] == 0) {
            $stmt = $pdo->prepare("INSERT INTO votes (student_id, position_id, candidate_id, vote_timestamp) VALUES (:student_id, :position_id, 0, NOW())");
        } else {
            $stmt = $pdo->prepare("INSERT INTO votes (student_id, position_id, candidate_id, vote_timestamp) VALUES (:student_id, :position_id, :candidate_id, NOW())");
            $stmt->bindParam(':candidate_id', $candidate['candidate_id']);
        }
        $stmt->bindParam(':student_id', $user_id);
        $stmt->bindParam(':position_id', $position_id);
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
    error_log("Vote submission failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}