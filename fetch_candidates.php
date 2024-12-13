<?php
require_once 'connect.php';

if (isset($_GET['position_id'])) {
    $stmt = $pdo->prepare("
        SELECT c.*, 
               colleges.college_name, 
               positions.position_name,
               parties.party_name  /* Add party_name from parties table */
        FROM candidates c
        LEFT JOIN colleges ON c.college_id = colleges.college_id 
        LEFT JOIN positions ON c.position_id = positions.position_id
        LEFT JOIN parties ON c.party_id = parties.party_id  /* Join with parties table */
        WHERE c.position_id = ? 
        AND c.qualified = 1
    ");
    $stmt->execute([$_GET['position_id']]);
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($candidates);
}