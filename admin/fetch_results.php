<?php
require_once '../connect.php';

header('Content-Type: application/json');

try {
    $position_id = $_GET['position_id'] ?? null;
    $election_id = $_GET['election_id'] ?? null;

    if (!$position_id || !$election_id) {
        throw new Exception('Missing required parameters');
    }

    $stmt = $pdo->prepare("
        SELECT 
            c.candidate_id,
            c.candidate_name,
            c.candidate_image,
            col.college_name,
            p.party_name,
            COUNT(v.vote_id) as vote_count,
            (
                SELECT COUNT(DISTINCT student_id) 
                FROM votes 
                WHERE position_id = :position_id 
                AND election_id = :election_id
            ) as total_votes
        FROM candidates c
        INNER JOIN colleges col ON c.college_id = col.college_id
        LEFT JOIN parties p ON c.party_id = p.party_id
        LEFT JOIN votes v ON c.candidate_id = v.candidate_id 
            AND v.election_id = :election_id
        WHERE c.position_id = :position_id 
        AND c.election_id = :election_id
        GROUP BY 
            c.candidate_id, 
            c.candidate_name, 
            c.candidate_image,
            col.college_name,
            p.party_name
        ORDER BY vote_count DESC
    ");

    $stmt->execute([
        ':position_id' => $position_id,
        ':election_id' => $election_id
    ]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate percentages
    $totalVotes = 0;
    foreach ($results as $result) {
        $totalVotes += (int)$result['vote_count'];
    }

    foreach ($results as &$result) {
        $result['vote_count'] = (int)$result['vote_count'];
        $result['percentage'] = $totalVotes > 0 ? 
            round(($result['vote_count'] / $totalVotes) * 100, 1) : 0;
    }

    echo json_encode($results);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>