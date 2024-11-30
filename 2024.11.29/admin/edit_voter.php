<?php
header('Content-Type: application/json');
require_once '../connect.php';

// Get the JSON input
$data = json_decode(file_get_contents('php://input'), true);

$studentId = $data['studentId'];
$updatedName = $data['updatedName'];
$updatedDepartment = $data['updatedDepartment'];

if (!$studentId || !$updatedName || !$updatedDepartment) {
    echo json_encode(['success' => false, 'error' => 'Invalid input data.']);
    exit();
}

// Update the voter in the database
$stmt = $pdo->prepare("UPDATE students SET student_name = ?, college_id = ? WHERE student_id = ?");
$success = $stmt->execute([$updatedName, $updatedDepartment, $studentId]);

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update voter.']);
}
?>