<?php
session_start();
if (!isset($_SESSION['is_comelec_logged_in']) || !$_SESSION['is_comelec_logged_in']) {
    header('Location: ../loginascomelec.php');
    exit();
}

require_once '../connect.php';

// Fetch positions from the database
$positionsStmt = $pdo->query("SELECT * FROM positions");
$positions = $positionsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch the current election from the database
$electionStmt = $pdo->query("SELECT * FROM elections WHERE is_current = 1 LIMIT 1");
$currentElection = $electionStmt->fetch(PDO::FETCH_ASSOC);

// Set default values if no current election exists
$startDatetime = $currentElection ? $currentElection['start_datetime'] : null;
$endDatetime = $currentElection ? $currentElection['end_datetime'] : null;

// Fetch live results from the database
function getLiveResults($pdo, $positionId) {
    $stmt = $pdo->prepare("
        SELECT candidates.candidate_name, candidates.candidate_party, candidates.candidate_image, COUNT(votes.vote_id) AS votes
        FROM votes
        JOIN candidates ON votes.candidate_id = candidates.candidate_id
        WHERE votes.position_id = ?
        GROUP BY candidates.candidate_id
        ORDER BY votes DESC
    ");
    $stmt->execute([$positionId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Start output buffering if needed
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comelec - Live Results</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../asset/susglogo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const resultsContainer = document.querySelector(".results");
            const currentPositionElement = document.querySelector(".current-position");
            const countdownBox = document.querySelector('.countdown-box');

            function fetchResults(positionId, positionName) {
                fetch(`fetch_results.php?position_id=${positionId}`)
                    .then(response => response.json())
                    .then(data => {
                        updateResults(data, positionName);
                    })
                    .catch(error => console.error('Error:', error));
            }

            function updateResults(candidates, positionName) {
                resultsContainer.innerHTML = "";
                currentPositionElement.textContent = positionName;

                candidates.forEach((candidate, index) => {
                    const resultElement = document.createElement("div");
                    resultElement.className = "transform transition-all duration-300 hover:scale-105 bg-white rounded-xl shadow-md mb-4 p-6 border-l-4 border-red-600";
                    
                    const totalVotes = candidates.reduce((sum, c) => sum + parseInt(c.votes), 0);
                    const percentage = totalVotes > 0 ? ((candidate.votes / totalVotes) * 100).toFixed(1) : 0;
                    
                    resultElement.innerHTML = `
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="relative">
                                    <img class="h-16 w-16 rounded-full object-cover border-2 border-red-600" 
                                         src="../${candidate.candidate_image}" 
                                         alt="${candidate.candidate_name}">
                                    ${index === 0 ? '<span class="absolute -top-2 -right-2 text-2xl">👑</span>' : ''}
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">${candidate.candidate_name}</h3>
                                    <p class="text-sm text-gray-600">${candidate.candidate_party}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold text-red-600">${candidate.votes}</div>
                                <div class="text-sm text-gray-500">votes (${percentage}%)</div>
                            </div>
                        </div>
                        <div class="mt-4 w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-red-600 h-2.5 rounded-full transition-all duration-500" 
                                 style="width: ${percentage}%"></div>
                        </div>
                    `;
                    resultsContainer.appendChild(resultElement);
                });
            }

            // Initialize with first position
            fetchResults(<?php echo $positions[0]['position_id']; ?>, "<?php echo $positions[0]['position_name']; ?>");

            // Countdown timer functionality
            <?php if ($startDatetime && $endDatetime): ?>
            const startDatetime = new Date("<?php echo $startDatetime; ?>").getTime();
            const endDatetime = new Date("<?php echo $endDatetime; ?>").getTime();
            <?php else: ?>
            const startDatetimeVar = null;
            const endDatetimeVar = null;
            <?php endif; ?>

            function updateCountdown() {
                if (!startDatetime || !endDatetime) {
                    countdownBox.innerHTML = '<div class="text-2xl font-bold text-gray-600">No election scheduled.</div>';
                    return;
                }

                const now = new Date().getTime();
                let distance;
                let status;

                if (now < startDatetime) {
                    distance = startDatetime - now;
                    status = 'ELECTION STARTS IN';
                } else if (now >= startDatetime && now <= endDatetime) {
                    distance = endDatetime - now;
                    status = 'ELECTION ENDS IN';
                } else {
                    countdownBox.innerHTML = '<div class="text-2xl font-bold text-gray-600">Election has ended.</div>';
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                countdownBox.innerHTML = `
                    <div class="text-xl font-bold text-red-600 mb-4">${status}</div>
                    <div class="grid grid-cols-4 gap-4">
                        <div class="bg-white rounded-xl p-4 text-center shadow-lg float-animation">
                            <span class="text-4xl font-bold text-red-600 block">${String(days).padStart(2, '0')}</span>
                            <span class="text-sm text-gray-600">DAYS</span>
                        </div>
                        <div class="bg-white rounded-xl p-4 text-center shadow-lg float-animation" style="animation-delay: 0.2s">
                            <span class="text-4xl font-bold text-red-600 block">${String(hours).padStart(2, '0')}</span>
                            <span class="text-sm text-gray-600">HOURS</span>
                        </div>
                        <div class="bg-white rounded-xl p-4 text-center shadow-lg float-animation" style="animation-delay: 0.4s">
                            <span class="text-4xl font-bold text-red-600 block">${String(minutes).padStart(2, '0')}</span>
                            <span class="text-sm text-gray-600">MINUTES</span>
                        </div>
                        <div class="bg-white rounded-xl p-4 text-center shadow-lg float-animation" style="animation-delay: 0.6s">
                            <span class="text-4xl font-bold text-red-600 block">${String(seconds).padStart(2, '0')}</span>
                            <span class="text-sm text-gray-600">SECONDS</span>
                        </div>
                    </div>
                `;
            }

            const countdownInterval = setInterval(updateCountdown, 1000);
            updateCountdown();

            // Position button event listeners
            document.querySelectorAll('.position-button').forEach(button => {
                button.addEventListener('click', function() {
                    const positionId = this.getAttribute('data-position-id');
                    const positionName = this.textContent;
                    
                    // Remove active class from all buttons
                    document.querySelectorAll('.position-button').forEach(btn => {
                        btn.classList.remove('bg-red-700', 'ring-2', 'ring-red-600');
                        btn.classList.add('bg-red-600');
                    });
                    
                    // Add active class to clicked button
                    this.classList.remove('bg-red-600');
                    this.classList.add('bg-red-700', 'ring-2', 'ring-red-600');
                    
                    fetchResults(positionId, positionName);
                });
            });
        });
    </script>
</head>
<body class="bg-gray-50">
    <?php include 'sidebar.php'; ?>

    <main class="ml-64 p-8">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-3xl font-bold mb-8 text-gray-800">Live Election Results</h1>

            <!-- Countdown Section -->
            <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-xl shadow-2xl p-8 mb-8">
                <h2 class="text-4xl font-bold mb-8 text-white text-center tracking-wide">Election Timer</h2>
                <div class="countdown-box"></div>
            </div>

            <!-- Results Section -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Position Results</h2>
                
                <!-- Position Buttons -->
                <div class="flex flex-wrap gap-2 mb-8">
                    <?php foreach ($positions as $position): ?>
                        <button class="position-button transition-all duration-300 bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transform hover:scale-105"
                                data-position-id="<?php echo htmlspecialchars($position['position_id']); ?>">
                            <?php echo htmlspecialchars($position['position_name']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Current Position Display -->
                <h3 class="current-position text-xl font-semibold text-red-600 mb-6"></h3>

                <!-- Results Container -->
                <div class="results space-y-4">
                    <!-- Results will be dynamically inserted here -->
                </div>
            </div>
        </div>
    </main>
</body>
</html>