<?php
function castVote($studentId, $votes) {
    // Establish a database connection
    $pdo = dbConnect();

    try {
        // Start a transaction
        $pdo->beginTransaction();

        // Loop through each position and candidate in the votes array
        foreach ($votes as $position => $candidateId) {
            // Insert each vote into the Votes table
            $stmt = $pdo->prepare("INSERT INTO Votes (student_id, candidate_id, position) VALUES (:studentId, :candidateId, :position)");
            $stmt->execute([
                ':studentId' => $studentId,
                ':candidateId' => $candidateId,
                ':position' => $position
            ]);
        }

        // Mark the student as having voted
        $stmt = $pdo->prepare("UPDATE Student SET has_voted = 1 WHERE student_id = :studentId");
        $stmt->execute([':studentId' => $studentId]);

        // Commit the transaction
        $pdo->commit();

        return "Vote successfully cast!";
    } catch (Exception $e) {
        // Roll back the transaction if there was an error
        $pdo->rollBack();
        return "Failed to cast vote: " . $e->getMessage();
    }
}
?>
