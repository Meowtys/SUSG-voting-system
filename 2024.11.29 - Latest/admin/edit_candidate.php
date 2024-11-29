<?php
require_once '../connect.php';

$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    $stmt = $pdo->prepare("UPDATE candidates SET candidate_name = ?, candidate_party = ? WHERE candidate_id = ?");
    $stmt->execute([$data['newName'], $data['newParty'], $data['candidateId']]);
    echo "Candidate successfully updated!";
} else {
    http_response_code(400);
    echo "Invalid data!";
}
