<?php
require_once '../connect.php';

if (isset($_GET['position_id']) && isset($_GET['election_id'])) {
    $positionId = $_GET['position_id'];
    $electionId = $_GET['election_id'];

    $stmt = $pdo->prepare("
        SELECT 
            c.candidate_name, 
            pa.party_name, 
            c.candidate_image, 
            COUNT(v.vote_id) AS votes
        FROM candidates c
        LEFT JOIN votes v ON v.candidate_id = c.candidate_id AND v.election_id = :election_id
        LEFT JOIN parties pa ON c.party_id = pa.party_id
        WHERE c.position_id = :position_id 
        AND c.election_id = :election_id
        GROUP BY c.candidate_id
        ORDER BY votes DESC
    ");
    
    $stmt->execute([
        'position_id' => $positionId,
        'election_id' => $electionId
    ]);
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($results);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
}
?>