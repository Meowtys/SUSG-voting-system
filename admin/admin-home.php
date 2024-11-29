<?php
session_start();
if (!isset($_SESSION['is_comelec_logged_in']) || !$_SESSION['is_comelec_logged_in']) {
    header('Location: ../loginascomelec.php');
    exit();
}

require_once '../connect.php';

// Fetch current election details
$electionStmt = $pdo->query("SELECT * FROM elections ORDER BY election_id DESC LIMIT 1");
$currentElection = $electionStmt->fetch(PDO::FETCH_ASSOC);

// Fetch all elections
$allElectionsStmt = $pdo->query("SELECT * FROM elections ORDER BY election_id DESC");
$allElections = $allElectionsStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['election_name'])) {
        $electionName = $_POST['election_name'];
        $startDatetime = $_POST['start_datetime'];
        $endDatetime = $_POST['end_datetime'];
        $status = 'Scheduled';

        $stmt = $pdo->prepare("INSERT INTO elections (election_name, start_datetime, end_datetime, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$electionName, $startDatetime, $endDatetime, $status]);

        header('Location: admin-home.php');
        exit();
    } elseif (isset($_POST['toggle_election'])) {
        $electionId = $_POST['election_id'];
        $newStatus = $_POST['new_status'];

        $stmt = $pdo->prepare("UPDATE elections SET status = ? WHERE election_id = ?");
        $stmt->execute([$newStatus, $electionId]);

        header('Location: admin-home.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../asset/susglogo.png" type="image/png">
    <style>
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

        .content {
            flex-grow: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.3s ease;
            overflow-y: auto;
        }

        h1 {
            font-size: 32px;
            margin-bottom: 30px;
            text-align: center;
            color: #333;
        }

        .button {
            display: inline-block;
            margin: 10px;
            padding: 20px 40px;
            font-size: 20px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
            cursor: pointer;
        }

        .button:hover {
            background-color: #0056b3;
        }

        .form-group {
            margin-bottom: 20px;
            width: 100%;
            max-width: 600px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .form-group input[type="datetime-local"] {
            padding: 8px;
        }

        .form-group button {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            background-color: #28a745;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form-group button:hover {
            background-color: #218838;
        }

        .election-status {
            margin-top: 30px;
            text-align: center;
        }

        .election-status h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .election-status p {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .countdown-box {
            display: flex;
            justify-content: center;
            gap: 15px;
            font-size: 24px;
            flex-wrap: wrap;
        }

        .countdown-box div {
            background-color: #b82323;
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            font-weight: bold;
            min-width: 80px;
            text-align: center;
        }

        .toggle-button {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            background-color: #ffc107;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .toggle-button:hover {
            background-color: #e0a800;
        }

        .election-management {
            width: 100%;
            max-width: 600px;
            margin-bottom: 40px;
        }

        .election-management h2 {
            font-size: 28px;
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }

        .election-management form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .election-management .form-group {
            margin-bottom: 0;
        }

        .election-management .form-group select {
            padding: 10px;
        }

        .election-management .form-group button {
            background-color: #007bff;
        }

        .election-management .form-group button:hover {
            background-color: #0056b3;
        }

        .election-list {
            width: 100%;
            max-width: 900px;
            margin-top: 40px;
        }

        .election-list table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .election-list th, .election-list td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .election-list th {
            background-color: #f2f2f2;
            font-weight: 600;
        }

        .election-list td {
            background-color: #fff;
        }

        .election-list tr:nth-child(even) td {
            background-color: #f9f9f9;
        }

        .election-list .status-scheduled {
            color: blue;
        }

        .election-list .status-ongoing {
            color: green;
            animation: blink 1s step-start infinite;
        }

        .election-list .status-completed {
            color: grey;
        }

        @keyframes blink {
            50% {
                opacity: 0;
            }
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .action-buttons button {
            padding: 5px 10px;
            font-size: 14px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .action-buttons .edit-button {
            background-color: #007bff;
            color: white;
        }

        .action-buttons .edit-button:hover {
            background-color: #0056b3;
        }

        .action-buttons .delete-button {
            background-color: #dc3545;
            color: white;
        }

        .action-buttons .delete-button:hover {
            background-color: #c82333;
        }

        .action-buttons .view-button {
            background-color: #28a745;
            color: white;
        }

        .action-buttons .view-button:hover {
            background-color: #218838;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 10px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const countdownBox = document.querySelector('.countdown-box');
            <?php if ($currentElection): ?>
            const startDatetime = new Date("<?php echo $currentElection['start_datetime']; ?>").getTime();
            const endDatetime = new Date("<?php echo $currentElection['end_datetime']; ?>").getTime();
            <?php else: ?>
            const startDatetime = null;
            const endDatetime = null;
            <?php endif; ?>

            function updateCountdown() {
                if (!startDatetime || !endDatetime) {
                    countdownBox.innerHTML = "No election scheduled.";
                    return;
                }

                const now = new Date().getTime();
                let distance = startDatetime - now;

                if (now >= startDatetime && now <= endDatetime) {
                    distance = endDatetime - now;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                countdownBox.innerHTML = `
                    <div>${days} DAYS</div>
                    <div>${hours} HOURS</div>
                    <div>${minutes} MINUTES</div>
                    <div>${seconds} SECONDS</div>
                `;

                if (distance < 0) {
                    clearInterval(countdownInterval);
                    countdownBox.innerHTML = "Election has ended.";
                }
            }

            const countdownInterval = setInterval(updateCountdown, 1000);
            updateCountdown();

            // Modal functionality
            const modal = document.getElementById("newElectionModal");
            const btn = document.getElementById("newElectionBtn");
            const span = document.getElementsByClassName("close")[0];

            btn.onclick = function() {
                modal.style.display = "block";
            }

            span.onclick = function() {
                modal.style.display = "none";
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
        });
    </script>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Section -->
    <main>
        <div class="content">
            <h1>Admin Home</h1>

            <button id="newElectionBtn" class="button">+ Schedule New Election</button>

            <div id="newElectionModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <form method="POST" class="election-form">
                        <div class="form-group">
                            <label for="election_name">Election Name/Title</label>
                            <input type="text" id="election_name" name="election_name" required>
                        </div>
                        <div class="form-group">
                            <label for="start_datetime">Start Date and Time</label>
                            <input type="datetime-local" id="start_datetime" name="start_datetime" required>
                        </div>
                        <div class="form-group">
                            <label for="end_datetime">End Date and Time</label>
                            <input type="datetime-local" id="end_datetime" name="end_datetime" required>
                        </div>
                        <div class="form-group">
                            <button type="submit">Save Election</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="election-status">
                <h2>Current Election Status</h2>
                <?php if ($currentElection): ?>
                    <p>Name: <?php echo htmlspecialchars($currentElection['election_name']); ?></p>
                    <p>Status: <?php echo htmlspecialchars($currentElection['status']); ?></p>
                <?php else: ?>
                    <p>No election scheduled.</p>
                <?php endif; ?>
                <div class="countdown-box">
                    <!-- Countdown will dynamically populate here -->
                </div>
            </div>

            <div class="election-list">
                <h2>Scheduled Elections</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Election Name</th>
                            <th>Start Date and Time</th>
                            <th>End Date and Time</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allElections as $election): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($election['election_name']); ?></td>
                                <td><?php echo htmlspecialchars($election['start_datetime']); ?></td>
                                <td><?php echo htmlspecialchars($election['end_datetime']); ?></td>
                                <td class="status-<?php echo strtolower($election['status']); ?>">
                                    <?php echo htmlspecialchars($election['status']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($election['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($election['updated_at']); ?></td>
                                <td class="action-buttons">
                                    <button class="edit-button">Edit</button>
                                    <button class="delete-button">Delete</button>
                                    <button class="view-button">View</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>