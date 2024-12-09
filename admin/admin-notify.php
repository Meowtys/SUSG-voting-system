<?php
session_start();
if (!isset($_SESSION['is_comelec_logged_in']) || !$_SESSION['is_comelec_logged_in']) {
    header('Location: ../loginascomelec.php');
    exit();
}

require_once '../connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comelec - Notifications</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../asset/susglogo.png" type="image/png">
    <style>
        .content {
            flex-grow: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            overflow-y: auto;
        }
    
        h1 {
            font-size: 32px;
            margin-bottom: 30px;
            text-align: left;
            color: #333;
        }


</style>
</head>
<body>

    <!-- Include Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Section -->
     <main>
        <h3> View Notifications</h3>
    </main>

</body>
</html>