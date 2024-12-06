<?php
session_start();
if (!isset($_SESSION['is_comelec_logged_in']) || !$_SESSION['is_comelec_logged_in']) {
    header('Location: ../loginascomelec.php');
    exit();
}

require_once '../connect.php';

// Function to update election statuses based on current time
function updateElectionStatuses($pdo) {
    $now = date('Y-m-d H:i:s');

    // Set elections to 'Ongoing' if the current time is between start and end times
    $stmt = $pdo->prepare("UPDATE elections SET status = 'Ongoing' WHERE start_datetime <= ? AND end_datetime >= ? AND status = 'Scheduled'");
    $stmt->execute([$now, $now]);

    // Set elections to 'Completed' if the current time is past the end time
    $stmt = $pdo->prepare("UPDATE elections SET status = 'Completed' WHERE end_datetime < ? AND status = 'Ongoing'");
    $stmt->execute([$now]);
}

// Update election statuses
updateElectionStatuses($pdo);

// Fetch current election details
$electionStmt = $pdo->query("SELECT * FROM elections ORDER BY election_id DESC LIMIT 1");
$currentElection = $electionStmt->fetch(PDO::FETCH_ASSOC);

// Fetch all elections
$allElectionsStmt = $pdo->query("SELECT * FROM elections ORDER BY election_id DESC");
$allElections = $allElectionsStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ajax']) && $_POST['ajax'] == 1) {
        // Handle AJAX requests
        if (isset($_POST['set_current_election'])) {
            $electionId = $_POST['election_id'];

            // Reset the current election
            $resetStmt = $pdo->prepare("UPDATE elections SET is_current = 0 WHERE is_current = 1");
            $resetResult = $resetStmt->execute();

            // Set the selected election as current
            $setStmt = $pdo->prepare("UPDATE elections SET is_current = 1 WHERE election_id = ?");
            $setResult = $setStmt->execute([$electionId]);

            if ($resetResult && $setResult) {
                // Fetch the updated current election
                $stmt = $pdo->prepare("SELECT * FROM elections WHERE election_id = ?");
                $stmt->execute([$electionId]);
                $currentElection = $stmt->fetch(PDO::FETCH_ASSOC);

                // Return JSON response
                echo json_encode([
                    'success' => true,
                    'election_name' => htmlspecialchars($currentElection['election_name']),
                    'status' => htmlspecialchars($currentElection['status'])
                ]);
            } else {
                // Handle error
                echo json_encode(['success' => false, 'message' => 'Failed to set current election.']);
            }
            exit();
        }
        // ...handle other AJAX actions if necessary...
    } else {
        // Handle regular POST requests
        if (isset($_POST['election_name']) && !isset($_POST['edit_election'])) {
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
        } elseif (isset($_POST['edit_election'])) {
            $electionId = $_POST['election_id'];
            $electionName = $_POST['election_name'];
            $startDatetime = $_POST['start_datetime'];
            $endDatetime = $_POST['end_datetime'];

            $stmt = $pdo->prepare("UPDATE elections SET election_name = ?, start_datetime = ?, end_datetime = ? WHERE election_id = ?");
            $stmt->execute([$electionName, $startDatetime, $endDatetime, $electionId]);

            header('Location: admin-home.php');
            exit();
        } elseif (isset($_POST['delete_election'])) {
            $electionId = $_POST['election_id'];

            $stmt = $pdo->prepare("DELETE FROM elections WHERE election_id = ?");
            $stmt->execute([$electionId]);

            header('Location: admin-home.php');
            exit();
        }
    }
}

// Fetch current election details again after any changes
$electionStmt = $pdo->prepare("SELECT * FROM elections WHERE is_current = 1 LIMIT 1");
$electionStmt->execute();
$currentElection = $electionStmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comelec - Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../asset/susglogo.png" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            /* Change table-layout to auto */
            table-layout: auto;
        }

        /* Remove fixed widths to allow columns to size based on content */
        .election-list th, .election-list td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
            word-wrap: break-word;
        }

        /* Optionally, set a minimum width for the Actions column */
        .election-list th:nth-child(7),
        .election-list td:nth-child(7) {
            min-width: 150px;
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
            gap: 5px;
            flex-wrap: wrap;
        }

        .action-buttons button {
            flex: 1;
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

            // Modal functionality for edit
            const editButtons = document.querySelectorAll('.edit-button');
            editButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const row = button.closest('tr');
                    const electionId = row.dataset.electionId;
                    const electionName = row.querySelector('.election-name').textContent;
                    const startDatetime = row.querySelector('.start-datetime').textContent;
                    const endDatetime = row.querySelector('.end-datetime').textContent;

                    document.getElementById('edit_election_id').value = electionId;
                    document.getElementById('edit_election_name').value = electionName;
                    document.getElementById('edit_start_datetime').value = startDatetime;
                    document.getElementById('edit_end_datetime').value = endDatetime;

                    document.getElementById('editElectionModal').style.display = 'block';
                });
            });

            // Modal functionality for delete
            const deleteButtons = document.querySelectorAll('.delete-button');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const electionId = button.closest('tr').dataset.electionId;
                    if (confirm('Are you sure you want to delete this election?')) {
                        document.getElementById('delete_election_id').value = electionId;
                        document.getElementById('deleteElectionForm').submit();
                    }
                });
            });

            // Modify the Set as Current button to use AJAX
            const setCurrentButtons = document.querySelectorAll('.view-button');
            setCurrentButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const electionId = button.closest('tr').dataset.electionId;
                    if (confirm('Are you sure you want to set this election as current?')) {
                        // Send AJAX request
                        fetch('admin-home.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `set_current_election=1&election_id=${electionId}&ajax=1`
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log(data); // For debugging
                            if (data.success) {
                                // Reload the page
                                location.reload();
                            } else {
                                alert(data.message || 'Failed to update current election.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while setting the current election.');
                        });
                    }
                });
            });

            // Close modal
            const closeModalButtons = document.querySelectorAll('.close');
            closeModalButtons.forEach(button => {
                button.addEventListener('click', function () {
                    button.closest('.modal').style.display = 'none';
                });
            });

            window.onclick = function(event) {
                if (event.target.classList.contains('modal')) {
                    event.target.style.display = 'none';
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

            <div class="countdown-box">
                    <!-- Countdown will dynamically populate here -->
            </div>

            <!-- Add the Current Election Status section -->
            <div class="election-status">
                <h2>Current Election Status</h2>
                <?php if ($currentElection): ?>
                    <p>Name: <?php echo htmlspecialchars($currentElection['election_name']); ?></p>
                    <p>Status: <?php echo htmlspecialchars($currentElection['status']); ?></p>
                <?php else: ?>
                    <p>No election scheduled.</p>
                <?php endif; ?>
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
                            <tr data-election-id="<?php echo htmlspecialchars($election['election_id']); ?>">
                                <td class="election-name"><?php echo htmlspecialchars($election['election_name']); ?></td>
                                <td class="start-datetime"><?php echo htmlspecialchars($election['start_datetime']); ?></td>
                                <td class="end-datetime"><?php echo htmlspecialchars($election['end_datetime']); ?></td>
                                <td class="status-<?php echo strtolower($election['status']); ?>">
                                    <?php echo htmlspecialchars($election['status']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($election['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($election['updated_at']); ?></td>
                                <td class="action-buttons">
                                    <button class="edit-button">Edit</button>
                                    <button class="delete-button">Delete</button>
                                    <button class="view-button">Set as Current</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Edit Election Modal -->
    <div id="editElectionModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <form method="POST" class="election-form">
                <input type="hidden" id="edit_election_id" name="election_id">
                <div class="form-group">
                    <label for="edit_election_name">Election Name/Title</label>
                    <input type="text" id="edit_election_name" name="election_name" required>
                </div>
                <div class="form-group">
                    <label for="edit_start_datetime">Start Date and Time</label>
                    <input type="datetime-local" id="edit_start_datetime" name="start_datetime" required>
                </div>
                <div class="form-group">
                    <label for="edit_end_datetime">End Date and Time</label>
                    <input type="datetime-local" id="edit_end_datetime" name="end_datetime" required>
                </div>
                <div class="form-group">
                    <button type="submit" name="edit_election">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Election Form -->
    <form id="deleteElectionForm" method="POST" style="display: none;">
        <input type="hidden" id="delete_election_id" name="election_id">
        <input type="hidden" name="delete_election" value="1">
        <button type="submit">Delete Election</button>
    </form>

    <!-- Set Current Election Form -->
    <form id="setCurrentElectionForm" method="POST" style="display: none;">
        <input type="hidden" id="set_current_election_id" name="election_id">
        <button type="submit" name="set_current_election">Set Current Election</button>
    </form>
</body>
</html>