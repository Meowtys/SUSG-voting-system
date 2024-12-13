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

// Add database connection if not already included
if (!isset($pdo)) {
    require_once 'connect.php';
}

// Add election status check
$electionStmt = $pdo->query("SELECT * FROM elections WHERE is_current = 1 LIMIT 1");
$currentElection = $electionStmt->fetch(PDO::FETCH_ASSOC);

$electionData = $currentElection ? [
    'start_datetime' => $currentElection['start_datetime'],
    'end_datetime' => $currentElection['end_datetime'],
    'status' => $currentElection['status']
] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Header</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body, html {
            font-family: 'Poppins', sans-serif;
        }
        
        /* Remove the transform styles from CSS */
        .header-menu {
            display: none;
        }
        
        .header-menu.active {
            display: block;
        }

        .popup.active {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="flex items-center bg-[#c41f1f] px-4 md:px-20 py-2">
        <img src="asset/susglogo.png" alt="Logo" class="w-20 md:w-32">
        <span class="text-white text-lg md:text-2xl font-bold ml-4">SUSG Election System</span>
        <?php if ($user): ?>
        <div class="ml-auto">
            <img src="asset/menu.png" alt="Menu" class="w-6 h-6 cursor-pointer" id="header-menu-toggle">
        </div>
        <?php endif; ?>
    </header>

    <!-- Overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden" id="header-overlay"></div>

    <!-- Update Popup Messages -->
    <div class="hidden fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white p-6 rounded-lg shadow-xl z-50 w-80" id="vote-popup">
        <p class="text-gray-800 mb-4">You have already voted.</p>
        <button onclick="closeHeaderPopup('vote-popup')" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700 transition duration-300">Close</button>
    </div>

    <div class="hidden fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white p-6 rounded-lg shadow-xl z-50 w-80" id="election-not-started">
        <p class="text-gray-800 mb-4">The election has not started yet.</p>
        <button onclick="closeHeaderPopup('election-not-started')" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700 transition duration-300">Close</button>
    </div>

    <div class="hidden fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white p-6 rounded-lg shadow-xl z-50 w-80" id="election-ended">
        <p class="text-gray-800 mb-4">The election has ended.</p>
        <button onclick="closeHeaderPopup('election-ended')" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700 transition duration-300">Close</button>
    </div>

    <div class="hidden fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white p-6 rounded-lg shadow-xl z-50 w-80" id="no-election">
        <p class="text-gray-800 mb-4">No election is currently scheduled.</p>
        <button onclick="closeHeaderPopup('no-election')" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700 transition duration-300">Close</button>
    </div>

    <!-- Dropdown Menu -->
    <?php if ($user): ?>
    <nav class="header-menu fixed top-0 right-0 w-64 h-full bg-[#811111] z-50" id="header-side-menu">
        <!-- Student Info Section -->
        <div class="p-6 border-b border-red-700">
            <?php if ($user): ?>
                <div class="text-white">
                    <h3 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($user['student_name']); ?></h3>
                    <p class="text-sm opacity-90 mb-1"><?php echo htmlspecialchars($user['student_id']); ?></p>
                    <p class="text-sm opacity-90 mb-3"><?php echo htmlspecialchars($user['college_name']); ?></p>
                    <div class="<?php echo $user['has_voted'] ? 'bg-green-600' : 'bg-red-600'; ?> text-white text-sm font-semibold py-2 px-4 rounded-full text-center">
                        <?php echo $user['has_voted'] ? 'Voted' : 'Not Voted'; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Navigation Links -->
        <ul class="py-2">
            <li class="hover:bg-[#c41f1f] transition-colors duration-200">
                <a href="homepage.php" class="block px-6 py-3 text-white font-medium">Home</a>
            </li>
            <li class="hover:bg-[#c41f1f] transition-colors duration-200">
                <a href="javascript:void(0);" onclick="handleVoteClickHeader()" class="block px-6 py-3 text-white font-medium">Vote</a>
            </li>
            <li class="hover:bg-[#c41f1f] transition-colors duration-200">
                <a href="liveresult.php" class="block px-6 py-3 text-white font-medium">Live Tally</a>
            </li>
            <li class="hover:bg-[#c41f1f] transition-colors duration-200">
                <a href="countdown.php" class="block px-6 py-3 text-white font-medium">Countdown</a>
            </li>
            <li class="hover:bg-[#c41f1f] transition-colors duration-200">
                <a href="feedback.php" class="block px-6 py-3 text-white font-medium">Leave a Feedback</a>
            </li>
            <li class="hover:bg-[#c41f1f] transition-colors duration-200">
                <a href="logout.php" class="block px-6 py-3 text-white font-medium">Logout</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>

    <!-- JavaScript for toggling menu and popup message -->
    <script>
        // Global variables for election data
        const headerElectionData = <?php echo json_encode($electionData); ?>;
        const headerHasVoted = <?php echo isset($user['has_voted']) ? ($user['has_voted'] ? 'true' : 'false') : 'false'; ?>;

        function handleVoteClickHeader() {
            const now = new Date().getTime();
            
            if (!headerElectionData) {
                showHeaderPopup('no-election');
                return;
            }

            const startTime = new Date(headerElectionData.start_datetime).getTime();
            const endTime = new Date(headerElectionData.end_datetime).getTime();

            if (headerHasVoted) {
                showHeaderPopup('vote-popup');
            } else if (now < startTime) {
                showHeaderPopup('election-not-started');
            } else if (now > endTime) {
                showHeaderPopup('election-ended');
            } else {
                window.location.href = 'votecasting.php';
            }
        }

        function showHeaderPopup(popupId) {
            const popup = document.getElementById(popupId);
            const overlay = document.getElementById('header-overlay');
            if (popup && overlay) {
                popup.classList.remove('hidden');
                overlay.classList.remove('hidden');
                sideMenu.classList.add('hidden'); // Close the menu when showing popup
            }
        }

        function closeHeaderPopup(popupId) {
            const popup = document.getElementById(popupId);
            const overlay = document.getElementById('header-overlay');
            if (popup && overlay) {
                popup.classList.add('hidden');
                overlay.classList.add('hidden');
            }
        }

        // Event Listeners
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('header-menu-toggle');
            const sideMenu = document.getElementById('header-side-menu');
            const overlay = document.getElementById('header-overlay');

            menuToggle.addEventListener('click', function() {
                sideMenu.classList.toggle('hidden');
                sideMenu.classList.toggle('active');
                overlay.classList.toggle('hidden');
            });

            // Close everything when clicking overlay
            overlay.addEventListener('click', function() {
                sideMenu.classList.add('hidden');
                sideMenu.classList.remove('active');
                overlay.classList.add('hidden');
                document.querySelectorAll('.popup').forEach(popup => {
                    popup.classList.add('hidden');
                });
            });

            // Update popup close buttons
            document.querySelectorAll('.popup button').forEach(button => {
                button.onclick = function() {
                    const popupId = this.closest('.popup').id;
                    closeHeaderPopup(popupId);
                };
            });
        });
    </script>
</body>
</html>