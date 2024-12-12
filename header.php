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

// Add election status check
if (isset($pdo)) {
    $electionStmt = $pdo->query("SELECT * FROM elections WHERE is_current = 1 LIMIT 1");
    $currentElection = $electionStmt->fetch(PDO::FETCH_ASSOC);
    
    $electionData = $currentElection ? [
        'start_datetime' => $currentElection['start_datetime'],
        'end_datetime' => $currentElection['end_datetime'],
        'status' => $currentElection['status']
    ] : null;
} else {
    $currentElection = null;
    $electionData = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Header</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            scroll-behavior: smooth;
        }

        .header {
            display: flex;
            align-items: center;
            background-color: #c41f1f; 
            padding: 10px 20px;
            padding-left: 250px; 
        }

        .header-icons {
            margin-right: 55px;
            display: flex;
            align-items: center;
            margin-left: auto;
        }

        .header-icons img {
            width: 24px;
            height: 24px;
            margin-right: 220px;
            cursor: pointer;
        }

        .header-menu-icon {
            display: block;
        }

        .header-menu {
            display: none;
            position: fixed;
            top: 0;
            right: 0;
            background-color: #811111;
            width: 250px;
            height: 100%;
            z-index: 1001;
            padding-top: 20px;
            transition: transform 0.3s ease;
            transform: translateX(100%);
            overflow-y: auto; /* Enable vertical scroll */
        }

        .header-menu.active {
            transform: translateX(0);
            display: block;
        }

        .header-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
            text-align: center;
        }

        .header-student-info {
            background-color: #811111;
            padding: 15px;
            color: white;
            text-align: left;
            border-bottom: 1px solid #c41f1f;
        }

        .header-student-info .header-name {
            font-size: 18px;
            font-weight: bold;
        }

        .header-student-info .header-id,
        .header-student-info .header-department {
            font-size: 14px;
            margin-top: 5px;
        }

        .header-voting-status {
            border: none;
            padding: 8px 16px;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
            width: 100%;
            text-align: center;
            margin-top: 10px;
            border-radius: 5px;
        }

        .voted {
            background-color: #28a745; /* Use the same green color as the homepage */
            color: white;
        }

        .not-voted {
            background-color: #dc3545; /* Use the same red color as the homepage */
            color: white;
        }

        .header-menu ul li {
            padding: 15px 0;
            font-size: 18px;
            font-weight: bold;
            color: white;
            border-bottom: 1px solid #c41f1f;
        }

        .header-menu ul li a {
            text-decoration: none;
            color: white;
            display: block;
            width: 100%;
            padding: 10px 0;
        }

        .header-menu ul li a:hover {
            background-color: #c41f1f;
        }

        .header-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .header-overlay.active {
            display: block;
        }

        .header-hidden {
            display: none;
        }

        .header-logo {
            width: 120px;
            height: 120px;
        }

        .header-title {
            color: white;
            font-size: 24px;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .header {
                padding-left: 20px; 
                justify-content: flex-start; 
            }

            .header-logo {
                width: 80px;
                height: auto;
            }

            .header-title {
                font-size: 18px;
            }
        }

        .header-main {
            padding-top: 150px;
            height: auto;
        }

        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            z-index: 1002;
            border-radius: 10px;
            text-align: center;
        }

        .popup.active {
            display: block;
        }

        .popup button {
            margin-top: 10px;
            padding: 10px 20px;
            background-color: #c41f1f;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <img src="asset/susglogo.png" alt="Logo" class="header-logo">
        <span class="header-title">SUSG Election System</span>
        <?php if ($user): ?>
        <div class="header-icons">
            <img src="asset/menu.png" alt="Menu" class="header-menu-icon" id="header-menu-toggle">
        </div>
        <?php endif; ?>
    </header>

    <!-- Overlay -->
    <div class="header-overlay" id="header-overlay"></div>

    <!-- Popup Messages -->
    <div class="popup" id="vote-popup">
        <p>You have already voted.</p>
        <button onclick="closePopup('vote-popup')">Close</button>
    </div>

    <div class="popup" id="election-not-started">
        <p>The election has not started yet.</p>
        <button onclick="closePopup('election-not-started')">Close</button>
    </div>

    <div class="popup" id="election-ended">
        <p>The election has ended.</p>
        <button onclick="closePopup('election-ended')">Close</button>
    </div>

    <div class="popup" id="no-election">
        <p>No election is currently scheduled.</p>
        <button onclick="closePopup('no-election')">Close</button>
    </div>

    <!-- Dropdown Menu -->
    <?php if ($user): ?>
    <nav class="header-menu" id="header-side-menu">
        <div class="header-student-info">
            <?php if ($user): ?>
                <div class="header-name"><?php echo htmlspecialchars($user['student_name']); ?></div>
                <div class="header-id"><?php echo htmlspecialchars($user['student_id']); ?></div>
                <div class="header-department"><?php echo htmlspecialchars($user['college_name']); ?></div>
                <button class="header-voting-status <?php echo $user['has_voted'] ? 'voted' : 'not-voted'; ?>">
                    <?php echo $user['has_voted'] ? 'Voted' : 'Not Voted'; ?>
                </button>
            <?php else: ?>
                <div class="header-name">Guest</div>
                <div class="header-id">N/A</div>
                <div class="header-department">N/A</div>
                <button class="header-voting-status not-voted">N/A</button>
            <?php endif; ?>
        </div>
        <ul>
            <li><a href="homepage.php">Home</a></li>
            <li><a href="#" onclick="handleVoteClick(event)">Vote</a></li>
            <li><a href="liveresult.php">Live Tally</a></li>
            <li><a href="countdown.php">Countdown</a></li>
            <!-- <li><a href="faq.php">FAQ</a></li> -->
            <li><a href="feedback.php">Leave a Feedback</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
    <?php endif; ?>

    <!-- JavaScript for toggling menu and popup message -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const menuToggle = document.getElementById('header-menu-toggle');
            const sideMenu = document.getElementById('header-side-menu');
            const overlay = document.getElementById('header-overlay');
            const votePopup = document.getElementById('vote-popup');

            menuToggle.addEventListener('click', function () {
                sideMenu.classList.toggle('active');
                overlay.classList.toggle('active');
                document.body.classList.toggle('header-overlay-active');
            });

            overlay.addEventListener('click', function () {
                sideMenu.classList.remove('active');
                overlay.classList.remove('active');
                document.body.classList.remove('header-overlay-active');
            });

            // Add election data to JavaScript
            const electionData = <?php echo json_encode($electionData); ?>;
            const hasVoted = <?php echo isset($user['has_voted']) ? ($user['has_voted'] ? 'true' : 'false') : 'false'; ?>;

            function handleVoteClick(event) {
                event.preventDefault();
                
                if (!electionData) {
                    showPopup('no-election');
                    return;
                }

                const now = new Date().getTime();
                const startTime = new Date(electionData.start_datetime).getTime();
                const endTime = new Date(electionData.end_datetime).getTime();

                if (hasVoted) {
                    showPopup('vote-popup');
                } else if (now < startTime) {
                    showPopup('election-not-started');
                } else if (now > endTime) {
                    showPopup('election-ended');
                } else {
                    window.location.href = 'votecasting.php';
                }
            }

            function showPopup(popupId) {
                const popup = document.getElementById(popupId);
                popup.classList.add('active');
                document.getElementById('header-overlay').classList.add('active');
            }

            function closePopup(popupId) {
                const popup = document.getElementById(popupId);
                popup.classList.remove('active');
                document.getElementById('header-overlay').classList.remove('active');
            }

            // Update the checkVotingStatus function
            window.checkVotingStatus = function(event, hasVoted) {
                event.preventDefault();
                handleVoteClick(event);
            };
        });
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            scroll-behavior: smooth;
        }

        .header {
            display: flex;
            align-items: center;
            background-color: #c41f1f; 
            padding: 10px 20px;
            padding-left: 250px; 
        }

        .header-icons {
            margin-right: 55px;
            display: flex;
            align-items: center;
            margin-left: auto;
        }

        .header-icons img {
            width: 24px;
            height: 24px;
            margin-right: 220px;
            cursor: pointer;
        }

        .header-menu-icon {
            display: block;
        }

        .header-menu {
            display: none;
            position: fixed;
            top: 0;
            right: 0;
            background-color: #811111;
            width: 250px;
            height: 100%;
            z-index: 1001;
            padding-top: 20px;
            transition: transform 0.3s ease;
            transform: translateX(100%);
            overflow-y: auto; /* Enable vertical scroll */
        }

        .header-menu.active {
            transform: translateX(0);
            display: block;
        }

        .header-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
            text-align: center;
        }

        .header-student-info {
            background-color: #811111;
            padding: 15px;
            color: white;
            text-align: left;
            border-bottom: 1px solid #c41f1f;
        }

        .header-student-info .header-name {
            font-size: 18px;
            font-weight: bold;
        }

        .header-student-info .header-id,
        .header-student-info .header-department {
            font-size: 14px;
            margin-top: 5px;
        }

        .header-voting-status {
            border: none;
            padding: 8px 16px;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
            width: 100%;
            text-align: center;
            margin-top: 10px;
            border-radius: 5px;
        }

        .voted {
            background-color: #28a745; /* Use the same green color as the homepage */
            color: white;
        }

        .not-voted {
            background-color: #dc3545; /* Use the same red color as the homepage */
            color: white;
        }

        .header-menu ul li {
            padding: 15px 0;
            font-size: 18px;
            font-weight: bold;
            color: white;
            border-bottom: 1px solid #c41f1f;
        }

        .header-menu ul li a {
            text-decoration: none;
            color: white;
            display: block;
            width: 100%;
            padding: 10px 0;
        }

        .header-menu ul li a:hover {
            background-color: #c41f1f;
        }

        .header-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .header-overlay.active {
            display: block;
        }

        .header-hidden {
            display: none;
        }

        .header-logo {
            width: 120px;
            height: 120px;
        }

        .header-title {
            color: white;
            font-size: 24px;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .header {
                padding-left: 20px; 
                justify-content: flex-start; 
            }

            .header-logo {
                width: 80px;
                height: auto;
            }

            .header-title {
                font-size: 18px;
            }
        }

        .header-main {
            padding-top: 150px;
            height: auto;
        }

        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1002;
            text-align: center;
            min-width: 300px;
        }

        .popup.active {
            display: block;
        }

        .popup p {
            margin-bottom: 15px;
            color: #333;
            font-size: 16px;
        }

        .popup button {
            background-color: #c41f1f;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .popup button:hover {
            background-color: #a01818;
        }
    </style>
</body>
</html>