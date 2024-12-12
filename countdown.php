<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: loginasvoter.php');
    exit();
}

// Retrieve user details from the session
$user = $_SESSION['user'];

// Database connection
require_once 'connect.php';

// Fetch the current election's start and end times
$stmt = $pdo->query("SELECT start_datetime, end_datetime FROM elections WHERE is_current = 1 LIMIT 1");
$currentElection = $stmt->fetch(PDO::FETCH_ASSOC);

// Set default values if no current election exists
$startDatetime = $currentElection ? $currentElection['start_datetime'] : null;
$endDatetime = $currentElection ? $currentElection['end_datetime'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Countdown</title>
    <link rel="icon" href="asset/susglogo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @keyframes blink {
            50% { opacity: 0; }
        }
        .blink { animation: blink 1s step-start infinite; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const startDatetime = "<?php echo $startDatetime; ?>";
            const endDatetime = "<?php echo $endDatetime; ?>";

            const daysEl = document.getElementById('days');
            const hoursEl = document.getElementById('hours');
            const minutesEl = document.getElementById('minutes');
            const secondsEl = document.getElementById('seconds');
            const messageEl = document.getElementById('countdown-message');
            const voteBtn = document.getElementById('vote-btn');
            const reviewBtn = document.getElementById('review-btn');

            let targetDate = null;

            // Determine which countdown to use (start or end)
            if (startDatetime && new Date(startDatetime).getTime() > Date.now()) {
                targetDate = new Date(startDatetime).getTime();
                messageEl.textContent = "ELECTION STARTS IN";
            } else if (endDatetime && new Date(endDatetime).getTime() > Date.now()) {
                targetDate = new Date(endDatetime).getTime();
                messageEl.textContent = "CAST YOUR VOTES NOW";
            } else {
                messageEl.textContent = "VOTES ARE CLOSED";
                daysEl.textContent = "00";
                hoursEl.textContent = "00";
                minutesEl.textContent = "00";
                secondsEl.textContent = "00";
                voteBtn.classList.add("disabled");
                reviewBtn.classList.add("disabled");
                voteBtn.disabled = true;
                reviewBtn.disabled = true;
                return; // Exit script since no active countdown exists
            }

            function updateCountdown() {
                const now = new Date().getTime();
                const distance = targetDate - now;

                if (distance > 0) {
                    // Calculate time components
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    // Update the countdown elements
                    daysEl.textContent = days < 10 ? `0${days}` : days;
                    hoursEl.textContent = hours < 10 ? `0${hours}` : hours;
                    minutesEl.textContent = minutes < 10 ? `0${minutes}` : minutes;
                    secondsEl.textContent = seconds < 10 ? `0${seconds}` : seconds;

                    // Enable voting buttons
                    voteBtn.classList.remove("disabled");
                    reviewBtn.classList.remove("disabled");
                    voteBtn.disabled = false;
                    reviewBtn.disabled = false;
                } else {
                    // Countdown finished
                    clearInterval(countdownInterval);
                    messageEl.textContent = "VOTES ARE CLOSED";

                    // Set all countdown values to zero
                    daysEl.textContent = "00";
                    hoursEl.textContent = "00";
                    minutesEl.textContent = "00";
                    secondsEl.textContent = "00";

                    // Disable voting buttons
                    voteBtn.classList.add("disabled");
                    reviewBtn.classList.add("disabled");
                    voteBtn.disabled = true;
                    reviewBtn.disabled = true;
                }
            }

            // Start the interval to update the countdown every second
            const countdownInterval = setInterval(updateCountdown, 1000);
            updateCountdown(); // Call immediately to set initial values
        });
    </script>
</head>
<body class="bg-gray-50">
    <?php include 'header.php'; ?>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Countdown Container -->
        <div class="max-w-4xl mx-auto my-8">
            <!-- Enhanced Countdown Box -->
            <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-xl shadow-2xl p-8 mb-8">
                <h2 class="text-4xl font-bold mb-8 text-white text-center tracking-wide">Election Countdown</h2>
                
                <!-- Countdown Status -->
                <div class="countdown-status text-white text-xl font-semibold text-center mb-4">
                    <span id="countdown-message" class="bg-red-500 px-4 py-1 rounded-full">
                        ELECTION COUNTDOWN
                    </span>
                </div>

                <!-- Countdown Timer Boxes -->
                <div class="countdown-box flex justify-center gap-8">
                    <div class="bg-white rounded-xl p-8 text-center w-32 transform hover:scale-105 transition-transform duration-300 shadow-lg">
                        <span id="days" class="text-6xl font-bold text-red-700 block mb-3">00</span>
                        <span class="text-base font-semibold text-gray-600 block uppercase tracking-wider">Days</span>
                    </div>
                    <div class="bg-white rounded-xl p-8 text-center w-32 transform hover:scale-105 transition-transform duration-300 shadow-lg">
                        <span id="hours" class="text-6xl font-bold text-red-700 block mb-3">00</span>
                        <span class="text-base font-semibold text-gray-600 block uppercase tracking-wider">Hours</span>
                    </div>
                    <div class="bg-white rounded-xl p-8 text-center w-32 transform hover:scale-105 transition-transform duration-300 shadow-lg">
                        <span id="minutes" class="text-6xl font-bold text-red-700 block mb-3">00</span>
                        <span class="text-base font-semibold text-gray-600 block uppercase tracking-wider">Minutes</span>
                    </div>
                    <div class="bg-white rounded-xl p-8 text-center w-32 transform hover:scale-105 transition-transform duration-300 shadow-lg">
                        <span id="seconds" class="text-6xl font-bold text-red-700 block mb-3">00</span>
                        <span class="text-base font-semibold text-gray-600 block uppercase tracking-wider">Seconds</span>
                    </div>
                </div>

                <!-- Election Times -->
                <?php if ($startDatetime && $endDatetime): ?>
                <div class="mt-6 text-center text-white">
                    <div class="text-sm">
                        Start: <span class="font-semibold"><?php echo (new DateTime($startDatetime))->format('F j, Y - g:i A'); ?></span>
                    </div>
                    <div class="text-sm">
                        End: <span class="font-semibold"><?php echo (new DateTime($endDatetime))->format('F j, Y - g:i A'); ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-center gap-4">
                <a href="votecasting.php" id="vote-btn" 
                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-8 rounded-lg transition duration-300 ease-in-out transform hover:-translate-y-1 disabled:bg-gray-400 disabled:cursor-not-allowed 
                    <?php echo ($user['has_voted'] ? 'pointer-events-none opacity-50' : ''); ?>">
                    VOTE NOW
                </a>
                <button id="review-btn"
                    onclick="checkVotingStatus2(event, <?php echo $user['has_voted'] ? 'true' : 'false'; ?>)"
                    class="bg-gray-700 hover:bg-gray-800 text-white font-bold py-4 px-8 rounded-lg transition duration-300 ease-in-out transform hover:-translate-y-1 disabled:bg-gray-400 disabled:cursor-not-allowed">
                    REVIEW VOTES
                </button>
            </div>
        </div>
    </main>

    <!-- Modal/Popup for Vote Status -->
    <div id="vote-popup" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg p-8 max-w-sm w-full">
                <p class="text-xl font-semibold mb-4">You have already voted.</p>
                <button onclick="closeAllPopups()" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Modal/Popup for Review Status -->
    <div id="review-popup" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg p-8 max-w-sm w-full">
                <p class="text-xl font-semibold mb-4">You have not voted yet.</p>
                <button onclick="closeAllPopups()" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700">
                    Close
                </button>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // Update popup handling for Tailwind
        function closeAllPopups() {
            document.getElementById('vote-popup').classList.add('hidden');
            document.getElementById('review-popup').classList.add('hidden');
        }

        // Also close popups when clicking outside the modal
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('fixed')) {
                closeAllPopups();
            }
        });

        function showVotePopup() {
            document.getElementById('vote-popup').classList.remove('hidden');
        }

        function showReviewPopup() {
            document.getElementById('review-popup').classList.remove('hidden');
        }

        // Update voting status checks
        window.checkVotingStatus = function(event, hasVoted) {
            if (hasVoted) {
                showVotePopup();
            } else {
                window.location.href = 'votecasting.php';
            }
        };

        // Simplified voting status checks
        function checkVotingStatus2(event, hasVoted) {
            event.preventDefault();
            if (!hasVoted) {
                showReviewPopup();
            } else {
                window.location.href = 'review_votes.php';
            }
        }
    </script>
</body>
</html>