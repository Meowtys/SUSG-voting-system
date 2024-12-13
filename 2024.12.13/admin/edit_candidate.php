
<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $candidateId = $_POST['candidateId'];
    $candidateName = $_POST['candidateName'];
    $partyName = $_POST['partyName'];
    $position = $_POST['position'];
    $college = $_POST['college'];
    $qualified = $_POST['qualified'];
    $remarks = $_POST['remarks'];
    $candidateImage = null;

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
            }
        } else {
            echo "<script>alert('Only JPG, JPEG, and PNG files are allowed.'); window.history.back();</script>";
            exit(); // Prevent form submission
        }
    }

    // Update candidate in the database
    $stmt = $pdo->prepare("
        UPDATE candidates 
        SET candidate_name = ?, candidate_party = ?, position_id = ?, college_id = ?, qualified = ?, remarks = ?, candidate_image = COALESCE(?, candidate_image)
        WHERE candidate_id = ?
    ");
    if ($stmt->execute([$candidateName, $partyName, $position, $college, $qualified, $remarks, $candidateImage, $candidateId])) {
        // Redirect back to the candidates page
        header('Location: admin-candidates.php');
        exit();
    } else {
        echo "<script>alert('Failed to update candidate. Please try again.');</script>";
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Invalid data!"]);
}
?>