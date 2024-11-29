<?php
require_once '../connect.php';

$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
    $stmt->execute([$data['studentId']]);
    echo "Voter deleted successfully!";
} else {
    http_response_code(400);
    echo "Invalid data!";
}