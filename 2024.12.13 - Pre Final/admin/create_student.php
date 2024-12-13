<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = $_POST['studentId'];
    $studentName = $_POST['studentName'];
    $college = $_POST['college'];
    $hasVoted = $_POST['hasVoted'];

    // Get the current election ID
    $stmt = $pdo->query("SELECT election_id FROM elections WHERE is_current = 1 LIMIT 1");
    $currentElection = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$currentElection) {
        die("No current election found. Please set a current election first.");
    }

    // Generate a random password
    $password = bin2hex(random_bytes(4)); // 8-character random password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new student into the database
    $stmt = $pdo->prepare("
        INSERT INTO students (student_id, student_name, college_id, has_voted, password, election_id) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    if ($stmt->execute([$studentId, $studentName, $college, $hasVoted, $hashedPassword, $currentElection['election_id']])) {
        // Redirect back to the voters page
        header('Location: admin-voters.php');
        exit();
    } else {
        echo "<script>alert('Failed to create student. Please try again.');</script>";
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Invalid data!"]);
}
?>