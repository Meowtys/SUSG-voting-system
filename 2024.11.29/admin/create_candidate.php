<?php
require_once '../connect.php';

$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    $stmt = $pdo->prepare("INSERT INTO candidates (candidate_name, candidate_party, position_id, college_id, qualified, remarks) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$data['candidateName'], $data['partyName'], $data['position'], $data['college'], $data['qualified'], $data['remarks']]);
    echo "Candidate successfully created!";
} else {
    http_response_code(400);
    echo "Invalid data!";
}
