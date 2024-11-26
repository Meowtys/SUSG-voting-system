<?php
require_once 'connect.php';

$position_id = filter_input(INPUT_GET, 'position_id', FILTER_SANITIZE_NUMBER_INT);

$stmt = $pdo->prepare("
    SELECT candidates.*, colleges.college_name 
    FROM candidates 
    LEFT JOIN colleges ON candidates.college_id = colleges.college_id 
    WHERE candidates.position_id = :position_id AND candidates.qualified = 1
");
$stmt->execute(['position_id' => $position_id]);
$candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($candidates);