<?php
require_once '../connect.php';

$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    $stmt = $pdo->prepare("INSERT INTO students (student_id, student_name, college_id, password, has_voted) VALUES (?, ?, ?, ?, 0)");
    $stmt->execute([$data['voterId'], $data['voterName'], $data['department'], password_hash('password123', PASSWORD_DEFAULT)]);
    echo "Voter created successfully!";
} else {
    http_response_code(400);
    echo "Invalid data!";
}
