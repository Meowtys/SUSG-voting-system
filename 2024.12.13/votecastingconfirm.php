<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: loginasvoter.php');
    exit();
}

$user = $_SESSION['user'];

// Retrieve selected votes from session
$selectedVotes = isset($_SESSION['selectedVotes']) ? $_SESSION['selectedVotes'] : [];

require_once 'connect.php';

// Fetch positions from the database
$positions_stmt = $pdo->query("SELECT * FROM positions");
$positions = $positions_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Confirmation Page</title>
    <link rel="icon" href="asset/susglogo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        * {
            font-family: 'Poppins', sans-serif;
        }
    </style>
    <script src="script/load.js" type="module" defer></script>
</head>

<body class="bg-gray-50">

    <!-- Header Section -->
    <?php include 'header.php'; ?>

    <main class="min-h-screen p-8">
        <div class="max-w-4xl mx-auto">
            <!-- Vote Confirmation Header -->
            <div class="bg-white rounded-xl shadow-xl p-8 mb-8 border-l-4 border-red-600">
                <h1 class="text-3xl font-bold text-gray-800 mb-4">Review Your Votes</h1>
                <div class="text-gray-600">
                    Please review your selections carefully before submitting your final vote.
                </div>
            </div>

            <!-- Vote Summary Box -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-red-50 px-6 py-4 border-b border-red-100">
                    <h2 class="text-xl font-semibold text-red-800">Your Selected Candidates</h2>
                </div>
                
                <div class="p-6 grid gap-6">
                    <?php foreach ($positions as $position): ?>
                        <?php if (isset($selectedVotes[$position['position_name']])): ?>
                            <div class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow duration-300">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                                    <i class="fas fa-user-tie text-red-600 mr-2"></i>
                                    <?php echo htmlspecialchars($position['position_name']); ?>
                                </h3>
                                
                                <?php if ($selectedVotes[$position['position_name']]['candidate_id'] == 0): ?>
                                    <!-- Abstain Card -->
                                    <div class="flex items-center bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                                        <div class="w-12 h-12 bg-yellow-200 rounded-full flex items-center justify-center">
                                            <i class="fas fa-ban text-yellow-600 text-xl"></i>
                                        </div>
                                        <div class="ml-4">
                                            <h4 class="text-lg font-medium text-yellow-800">Abstain</h4>
                                            <p class="text-sm text-yellow-600">You chose to abstain for this position</p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Candidate Card -->
                                    <div class="flex items-center bg-white rounded-lg p-4 border border-gray-200">
                                        <img class="w-16 h-16 rounded-lg object-cover shadow-sm" 
                                             src="<?php echo htmlspecialchars($selectedVotes[$position['position_name']]['candidate_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($selectedVotes[$position['position_name']]['candidate_name']); ?>">
                                        <div class="ml-4">
                                            <h4 class="text-lg font-medium text-gray-800">
                                                <?php echo htmlspecialchars($selectedVotes[$position['position_name']]['candidate_name']); ?>
                                            </h4>
                                            <div class="flex items-center mt-1">
                                                <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">
                                                    <?php echo htmlspecialchars($selectedVotes[$position['position_name']]['college_name']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between mt-8 gap-4">
                <button onclick="returnToVoting()" 
                        class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg flex items-center justify-center transition duration-300 transform hover:-translate-y-1">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Return to Voting
                </button>
                <button onclick="submitVotes()" 
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg flex items-center justify-center transition duration-300 transform hover:-translate-y-1">
                    <i class="fas fa-check-circle mr-2"></i>
                    Submit Final Vote
                </button>
            </div>

            <!-- Success Message -->
            <div id="successMessage" class="hidden mt-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-400 mr-2"></i>
                    <p>Your votes have been successfully submitted!</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Success Modal with Redirect -->
    <div id="successModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4 text-center">
            <div class="mb-6">
                <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-check-circle text-4xl text-green-500"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Vote Submitted Successfully!</h2>
                <p class="text-gray-600">Thank you for participating in the SUSG Election.</p>
            </div>
            <div class="text-center mb-6">
                <p class="text-gray-600">Redirecting to homepage in <span id="redirectTimer" class="font-bold text-red-600">5</span> seconds...</p>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    <div id="redirectProgress" class="bg-red-600 h-2 rounded-full transition-all duration-1000" style="width: 0%"></div>
                </div>
            </div>
            <button onclick="redirectNow()" 
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300">
                Go to Homepage Now
            </button>
        </div>
    </div>

    <!-- Footer Section -->
    <?php include 'footer.php'; ?>
    <script>
        function returnToVoting() {
            window.location.href = "votecasting.php";
        }

        function submitVotes() {
            if (confirm("Are you sure you want to submit your final votes? This action cannot be undone.")) {
                // Disable both buttons immediately to prevent further interaction
                const submitButton = document.querySelector('button:last-child');
                const returnButton = document.querySelector('button:first-child');
                submitButton.disabled = true;
                returnButton.disabled = true;
                
                // Add visual feedback that buttons are disabled
                submitButton.classList.add('opacity-50', 'cursor-not-allowed');
                returnButton.classList.add('opacity-50', 'cursor-not-allowed');
                
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';

                fetch("submit_votes.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(<?php echo json_encode($selectedVotes); ?>)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccessAndRedirect();
                    } else {
                        // Only re-enable buttons if submission fails
                        alert("Failed to submit votes. Please try again.");
                        submitButton.disabled = false;
                        returnButton.disabled = false;
                        submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                        returnButton.classList.remove('opacity-50', 'cursor-not-allowed');
                        submitButton.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Submit Final Vote';
                    }
                })
                .catch(error => {
                    // Handle any errors and re-enable buttons
                    alert("An error occurred: " + error.message);
                    submitButton.disabled = false;
                    returnButton.disabled = false;
                    submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                    returnButton.classList.remove('opacity-50', 'cursor-not-allowed');
                    submitButton.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Submit Final Vote';
                });
            }
        }

        function showSuccessAndRedirect() {
            const modal = document.getElementById('successModal');
            const timerDisplay = document.getElementById('redirectTimer');
            const progressBar = document.getElementById('redirectProgress');
            let timeLeft = 5;
            
            modal.classList.remove('hidden');
            
            // Start countdown
            const countdown = setInterval(() => {
                timeLeft--;
                timerDisplay.textContent = timeLeft;
                progressBar.style.width = `${(5 - timeLeft) * 20}%`;
                
                if (timeLeft <= 0) {
                    clearInterval(countdown);
                    redirectNow();
                }
            }, 1000);
        }

        function redirectNow() {
            window.location.href = 'homepage.php';
        }
    </script>
</body>
</html>