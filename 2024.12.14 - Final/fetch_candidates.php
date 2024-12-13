<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'connect.php';

if (isset($_GET['position_id'])) {
    // Get student's college_id from session
    $student_college_id = $_SESSION['user']['college_id'];
    
    // Modify query to filter representatives by college
    $stmt = $pdo->prepare("
        SELECT c.*, 
               colleges.college_name, 
               positions.position_name,
               parties.party_name
        FROM candidates c
        LEFT JOIN colleges ON c.college_id = colleges.college_id 
        LEFT JOIN positions ON c.position_id = positions.position_id
        LEFT JOIN parties ON c.party_id = parties.party_id
        WHERE c.position_id = ? 
        AND c.qualified = 1
        AND (
            positions.position_name != 'Representative' 
            OR 
            (positions.position_name = 'Representative' AND c.college_id = ?)
        )
    ");
    
    $stmt->execute([$_GET['position_id'], $student_college_id]);
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($candidates);
}