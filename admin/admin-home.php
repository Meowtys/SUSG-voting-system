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
        </div>
    </main>
</body>
</html>