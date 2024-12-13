<?php
session_start();

// Check which type of logout was requested
if (isset($_GET['type'])) {
    if ($_GET['type'] === 'voter') {
        // Only clear voter session data
        unset($_SESSION['user']);
        header("Location: loginasvoter.php");
    } elseif ($_GET['type'] === 'comelec') {
        // Only clear comelec session data
        unset($_SESSION['comelec_name']);
        unset($_SESSION['is_comelec_logged_in']);
        header("Location: loginascomelec.php");
    } else {
        // Invalid logout type - logout completely
        session_unset();
        session_destroy();
        header("Location: start.php");
    }
} else {
    // No type specified - logout completely
    session_unset();
    session_destroy();
    header("Location: start.php");
}
exit();
?>



