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
    
    // Update to Ongoing
    $stmt = $pdo->prepare("
        UPDATE elections 
        SET status = 'Ongoing', updated_at = NOW() 
        WHERE start_datetime <= ? 
        AND end_datetime >= ? 
        AND status != 'Ongoing'
    ");
    $stmt->execute([$now, $now]);

    // Update to Completed
    $stmt = $pdo->prepare("
        UPDATE elections 
        SET status = 'Completed', updated_at = NOW() 
        WHERE end_datetime < ? 
        AND status != 'Completed'
    ");
    $stmt->execute([$now]);

    // Ensure future elections are marked as Scheduled
    $stmt = $pdo->prepare("
        UPDATE elections 
        SET status = 'Scheduled', updated_at = NOW() 
        WHERE start_datetime > ? 
        AND status != 'Scheduled'
    ");
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
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @keyframes blink {
            50% { opacity: 0; }
        }
        .blink { animation: blink 1s step-start infinite; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const countdownBox = document.querySelector('.countdown-box');
            <?php if ($currentElection): ?>
            const startDatetime = new Date("<?php echo $currentElection['start_datetime']; ?>").getTime();
            const endDatetime = new Date("<?php echo $currentElection['end_datetime']; ?>").getTime();
            <?php else: ?>
            const startDatetimeVar = null;
            const endDatetimeVar = null;
            <?php endif; ?>

            function updateCountdown() {
                if (!startDatetime || !endDatetime) {
                    countdownBox.innerHTML = '<div class="text-center text-white text-3xl font-bold py-12">No election scheduled.</div>';
                    return;
                }

                const now = new Date().getTime();
                const status = document.querySelector('.countdown-status span');
                
                // Determine which countdown to show
                let distance;
                if (now < startDatetime) {
                    // Count down to election start
                    distance = startDatetime - now;
                    status.textContent = 'ELECTION STARTS IN';
                    status.className = 'bg-red-500 px-4 py-1 rounded-full';
                } else if (now <= endDatetime) {
                    // Count down to election end
                    distance = endDatetime - now;
                    status.textContent = 'ELECTION TIME REMAINING';
                    status.className = 'bg-green-500 px-4 py-1 rounded-full';
                } else {
                    // Election has ended
                    status.textContent = 'ELECTION ENDED';
                    status.className = 'bg-gray-500 px-4 py-1 rounded-full';
                    document.querySelector('.days').textContent = '00';
                    document.querySelector('.hours').textContent = '00';
                    document.querySelector('.minutes').textContent = '00';
                    document.querySelector('.seconds').textContent = '00';
                    return;
                }

                // Calculate time components
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Update the display with padded numbers
                document.querySelector('.days').textContent = String(days).padStart(2, '0');
                document.querySelector('.hours').textContent = String(hours).padStart(2, '0');
                document.querySelector('.minutes').textContent = String(minutes).padStart(2, '0');
                document.querySelector('.seconds').textContent = String(seconds).padStart(2, '0');
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

            // Function to update election status in the UI
            function updateElectionStatusUI(row) {
                const startDateTime = new Date(row.querySelector('.start-datetime').textContent);
                const endDateTime = new Date(row.querySelector('.end-datetime').textContent);
                const now = new Date();
                
                let newStatus;
                if (now < startDateTime) {
                    newStatus = 'Scheduled';
                } else if (now >= startDateTime && now <= endDateTime) {
                    newStatus = 'Ongoing';
                } else {
                    newStatus = 'Completed';
                }

                const statusCell = row.querySelector('td:nth-child(4) span');
                const currentStatus = statusCell.textContent.trim();

                if (currentStatus !== newStatus) {
                    // Update UI
                    statusCell.textContent = newStatus;
                    statusCell.className = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' + 
                        (newStatus === 'Ongoing' ? 'bg-green-100 text-green-800' : 
                         newStatus === 'Completed' ? 'bg-gray-100 text-gray-800' : 
                         'bg-red-100 text-red-800');

                    // Update database via AJAX
                    const electionId = row.dataset.electionId;
                    fetch('update_election_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `election_id=${electionId}&status=${newStatus}`
                    });
                }
            }

            // Function to update all election statuses
            function updateAllElectionStatuses() {
                const rows = document.querySelectorAll('tbody tr');
                rows.forEach(updateElectionStatusUI);
            }

            // Update statuses immediately and then every minute
            updateAllElectionStatuses();
            setInterval(updateAllElectionStatuses, 60000);
        });
    </script>
</head>
<body class="bg-gray-50">
    <?php include 'sidebar.php'; ?>

    <!-- Main Section -->
    <main class="ml-64 p-8">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-3xl font-bold mb-8 text-gray-800">Admin Home</h1>

            <button id="newElectionBtn" class="mb-8 bg-red-700 hover:bg-red-800 text-white font-bold py-3 px-6 rounded-lg transition duration-300 ease-in-out transform hover:-translate-y-1">
                + Schedule New Election
            </button>

            <!-- Enhanced Countdown Box -->
            <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-xl shadow-2xl p-8 mb-8">
                <h2 class="text-4xl font-bold mb-8 text-white text-center tracking-wide">Election Countdown</h2>
                <?php if ($currentElection): ?>
                    <div class="countdown-status text-white text-xl font-semibold text-center mb-4">
                        <?php 
                        $now = new DateTime();
                        $start = new DateTime($currentElection['start_datetime']);
                        $end = new DateTime($currentElection['end_datetime']);
                        
                        if ($now < $start): ?>
                            <span class="bg-red-500 px-4 py-1 rounded-full">Election Starts In</span>
                        <?php elseif ($now <= $end): ?>
                            <span class="bg-green-500 px-4 py-1 rounded-full">Election Time Remaining</span>
                        <?php else: ?>
                            <span class="bg-gray-500 px-4 py-1 rounded-full">Election Ended</span>
                        <?php endif; ?>
                    </div>
                    <div class="countdown-box flex justify-center gap-8">
                        <div class="bg-white rounded-xl p-8 text-center w-44 transform hover:scale-105 transition-transform duration-300 shadow-lg">
                            <span class="days text-7xl font-bold text-red-700 block mb-3">00</span>
                            <span class="text-base font-semibold text-gray-600 block uppercase tracking-wider">Days</span>
                        </div>
                        <div class="bg-white rounded-xl p-8 text-center w-44 transform hover:scale-105 transition-transform duration-300 shadow-lg">
                            <span class="hours text-7xl font-bold text-red-700 block mb-3">00</span>
                            <span class="text-base font-semibold text-gray-600 block uppercase tracking-wider">Hours</span>
                        </div>
                        <div class="bg-white rounded-xl p-8 text-center w-44 transform hover:scale-105 transition-transform duration-300 shadow-lg">
                            <span class="minutes text-7xl font-bold text-red-700 block mb-3">00</span>
                            <span class="text-base font-semibold text-gray-600 block uppercase tracking-wider">Minutes</span>
                        </div>
                        <div class="bg-white rounded-xl p-8 text-center w-44 transform hover:scale-105 transition-transform duration-300 shadow-lg">
                            <span class="seconds text-7xl font-bold text-red-700 block mb-3">00</span>
                            <span class="text-base font-semibold text-gray-600 block uppercase tracking-wider">Seconds</span>
                        </div>
                    </div>
                    <div class="mt-6 text-center text-white">
                        <div class="text-sm">
                            Start: <span class="font-semibold"><?php echo (new DateTime($currentElection['start_datetime']))->format('F j, Y - g:i A'); ?></span>
                        </div>
                        <div class="text-sm">
                            End: <span class="font-semibold"><?php echo (new DateTime($currentElection['end_datetime']))->format('F j, Y - g:i A'); ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center text-white text-3xl font-bold py-12">No election scheduled.</div>
                <?php endif; ?>
            </div>

            <!-- Current Election Status -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8 border-l-4 border-red-600">
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Current Election Status</h2>
                <?php if ($currentElection): ?>
                    <p class="text-lg mb-2">Name: <span class="font-semibold"><?php echo htmlspecialchars($currentElection['election_name']); ?></span></p>
                    <p class="text-lg">Status: <span class="font-semibold <?php echo strtolower($currentElection['status']) === 'ongoing' ? 'text-red-600 blink' : 'text-red-600'; ?>"><?php echo htmlspecialchars($currentElection['status']); ?></span></p>
                <?php else: ?>
                    <p class="text-lg text-gray-600">No election scheduled.</p>
                <?php endif; ?>
            </div>

            <!-- Scheduled Elections -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Scheduled Elections</h2>
                <div class="w-full">
                    <table class="min-w-full table-fixed">
                        <thead class="bg-red-50">
                            <tr>
                                <th class="w-1/6 px-4 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Election Name</th>
                                <th class="w-1/6 px-4 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Start Date</th>
                                <th class="w-1/6 px-4 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">End Date</th>
                                <th class="w-1/12 px-4 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Status</th>
                                <th class="w-1/6 px-4 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Created</th>
                                <th class="w-1/6 px-4 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Updated</th>
                                <th class="w-1/6 px-4 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($allElections as $election): ?>
                                <tr data-election-id="<?php echo htmlspecialchars($election['election_id']); ?>" 
                                    class="hover:bg-red-50 transition-colors duration-200">
                                    <td class="px-4 py-4 text-sm text-gray-900 truncate election-name">
                                        <?php echo htmlspecialchars($election['election_name']); ?>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-900 truncate start-datetime">
                                        <?php echo htmlspecialchars($election['start_datetime']); ?>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-900 truncate end-datetime">
                                        <?php echo htmlspecialchars($election['end_datetime']); ?>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo strtolower($election['status']) === 'ongoing' ? 'bg-green-100 text-green-800' : 
                                                (strtolower($election['status']) === 'completed' ? 'bg-gray-100 text-gray-800' : 'bg-red-100 text-red-800'); ?>">
                                            <?php echo htmlspecialchars($election['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-500 truncate">
                                        <?php echo htmlspecialchars($election['created_at']); ?>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-500 truncate">
                                        <?php echo htmlspecialchars($election['updated_at']); ?>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col space-y-1">
                                            <button class="edit-button bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm flex items-center justify-center">
                                                <i class="fas fa-edit mr-1"></i> Edit
                                            </button>
                                            <button class="delete-button bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-sm flex items-center justify-center">
                                                <i class="fas fa-trash-alt mr-1"></i> Delete
                                            </button>
                                            <button class="view-button bg-red-700 hover:bg-red-800 text-white px-3 py-1 rounded text-sm flex items-center justify-center">
                                                <i class="fas fa-check-circle mr-1"></i> Set as Current
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Update modal styles to match the new color scheme -->
    <div id="newElectionModal" class="modal hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-red-700">Schedule New Election</h3>
                <span class="close cursor-pointer text-gray-600 text-2xl">&times;</span>
            </div>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Election Name/Title</label>
                    <input type="text" name="election_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Start Date and Time</label>
                    <input type="datetime-local" name="start_datetime" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">End Date and Time</label>
                    <input type="datetime-local" name="end_datetime" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    Save Election
                </button>
            </form>
        </div>
    </div>

    <!-- Edit Election Modal -->
    <div id="editElectionModal" class="modal hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Edit Election</h3>
                <span class="close cursor-pointer text-gray-600 text-2xl">&times;</span>
            </div>
            <form method="POST" class="space-y-4">
                <input type="hidden" id="edit_election_id" name="election_id">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Election Name/Title</label>
                    <input type="text" id="edit_election_name" name="election_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Start Date and Time</label>
                    <input type="datetime-local" id="edit_start_datetime" name="start_datetime" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">End Date and Time</label>
                    <input type="datetime-local" id="edit_end_datetime" name="end_datetime" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <button type="submit" name="edit_election" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Save Changes
                </button>
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