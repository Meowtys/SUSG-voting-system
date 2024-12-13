<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $candidateName = $_POST['candidateName'];
    $partyName = $_POST['partyName'];
    $position = $_POST['position'];
    $college = $_POST['college'];
    $qualified = $_POST['qualified'];
    $remarks = $_POST['remarks'];

    // Handle file upload
    if (isset($_FILES['candidateImage']) && $_FILES['candidateImage']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['candidateImage']['tmp_name'];
        $fileName = $_FILES['candidateImage']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Restrict file formats to jpg and png
        $allowedfileExtensions = ['jpg', 'jpeg', 'png'];
        if (in_array($fileExtension, $allowedfileExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = '../candidate_images/';
            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $candidateImage = 'candidate_images/' . $newFileName;
            } else {
                $candidateImage = null;
            }
        } else {
            echo "<script>alert('Only JPG, JPEG, and PNG files are allowed.'); window.history.back();</script>";
            exit(); // Prevent form submission
        }
    } else {
        $candidateImage = null;
    }

    // Insert new candidate into the database
    $stmt = $pdo->prepare("
        INSERT INTO candidates (candidate_name, candidate_party, position_id, college_id, qualified, remarks, candidate_image) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    if ($stmt->execute([$candidateName, $partyName, $position, $college, $qualified, $remarks, $candidateImage])) {
        // Redirect back to the candidates page
        header('Location: admin-candidates.php');
        exit();
    } else {
        echo "<script>alert('Failed to create candidate. Please try again.');</script>";
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Invalid data!"]);
}
?>