
<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = $_POST['studentId'];
    $studentName = $_POST['studentName'];
    $college = $_POST['college'];
    $hasVoted = $_POST['hasVoted'];

    // Update student in the database
    $stmt = $pdo->prepare("
        UPDATE students 
        SET student_name = ?, college_id = ?, has_voted = ?
        WHERE student_id = ?
    ");
    if ($stmt->execute([$studentName, $college, $hasVoted, $studentId])) {
        // Redirect back to the voters page
        header('Location: admin-voters.php');
        exit();
    } else {
        echo "<script>alert('Failed to update student. Please try again.');</script>";
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Invalid data!"]);
}
?>