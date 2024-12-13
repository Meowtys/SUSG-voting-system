<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
} else {
    $user = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Side Bar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../asset/susglogo.png" type="image/png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body, html {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background-color: #f8f9fa;
            height: 100%;
            width: 100%;
        }

        body {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            height: 100vh;
            width: 250px;
            background-color: #b82323;
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: fixed;
            top: 0;
            left: 0;
        }

        .sidebar h2 {
            margin-bottom: 40px;
            font-size: 20px;
        }

        .sidebar a {
            text-decoration: none;
            color: white;
            font-size: 16px;
            margin: 10px 0;
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 4px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .sidebar a:hover {
            background-color: #d9534f;
        }

        .sidebar .active {
            background-color: white;
            color: #b82323;
        }

        .sidebar .section {
            margin-bottom: 20px;
        }

        .sidebar .sections .section:not(:first-child) {
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            padding-top: 20px;
            margin-top: 20px;
        }

        .sidebar .sections .section h3 {
            margin-bottom: 15px;
        }

        .icon {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .sidebar a .fas {
            width: 20px;
        }
        
        main {
            margin-left: 250px; /* Space for the sidebar */
            padding: 20px;
            width: 100%;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <?php
    // Determine the current page for dynamic highlighting
    $current_page = basename($_SERVER['PHP_SELF']);
    ?>
    <!-- Side Bar Content -->
    <div class="sidebar">
        <div class="sections">
            <div class="section">
                <h2>SUSG COMELEC</h2>
                <a href="admin-home.php" class="nav-link <?php echo $current_page == 'admin-home.php' ? 'active' : ''; ?>"><i class="fas fa-home icon"></i>Home</a>
                <a href="admin-liveresults.php" class="nav-link <?php echo $current_page == 'admin-liveresults.php' ? 'active' : ''; ?>"><i class="fas fa-chart-bar icon"></i>Live Results</a>
            </div>
            <div class="section">
                <h3>Management</h3>
                <a href="admin-voters.php" class="nav-link <?php echo $current_page == 'admin-voters.php' ? 'active' : ''; ?>"><i class="fas fa-user-friends icon"></i>Voters</a>
                <a href="admin-candidates.php" class="nav-link <?php echo $current_page == 'admin-candidates.php' ? 'active' : ''; ?>"><i class="fas fa-user-tie icon"></i>Candidates</a>
            </div>
            <div class="section">
                <h3>Feedback</h3>
                <a href="admin-feedback.php" class="nav-link <?php echo $current_page == 'admin-feedback.php' ? 'active' : ''; ?>"><i class="fas fa-comments icon"></i>View Feedback</a>  
            </div>
            <div class="section">
                <h3>Sentiment Analysis</h3>
                <a href="admin-analytics.php" class="nav-link <?php echo $current_page == 'admin-analytics.php' ? 'active' : ''; ?>"><i class="fas fa-chart-pie icon"></i>Analytics</a>
            </div>
            <div class="section">
                <a href="../logout.php?type=comelec" class="nav-link"><i class="fas fa-sign-out-alt icon"></i>Logout</a>
            </div>
        </div>
    </div>
</body>
</html>