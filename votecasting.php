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

require_once 'connect.php';

// Get current election
$stmt = $pdo->query("SELECT * FROM elections WHERE is_current = 1 LIMIT 1");
$election = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$election) {
    header('Location: homepage.php');
    exit();
}

// Check if user has already voted
if ($user['has_voted']) {
    header('Location: homepage.php');
    exit();
}

// Pass election times to JavaScript
$electionTimes = [
    'start' => $election['start_datetime'],
    'end' => $election['end_datetime']
];

// Fetch candidates from the database
function fetchCandidates($pdo, $position_id) {
    $stmt = $pdo->prepare("
        SELECT candidates.*, colleges.college_name, positions.position_name, candidates.candidate_image, candidates.candidate_party 
        FROM candidates 
        LEFT JOIN colleges ON candidates.college_id = colleges.college_id 
        LEFT JOIN positions ON candidates.position_id = positions.position_id 
        WHERE candidates.position_id = :position_id AND candidates.qualified = 1
    ");
    $stmt->execute(['position_id' => $position_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch positions from the database
$positions_stmt = $pdo->query("SELECT * FROM positions");
$positions = $positions_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Vote Casting</title>
    <link rel="icon" href="asset/susglogo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="script/load.js" type="module" defer></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        * {
            font-family: 'Poppins', sans-serif;
        }
    </style>
    <script>
        // Check if current time is within election period
        const electionTimes = <?php echo json_encode($electionTimes); ?>;
        const now = new Date();
        const startTime = new Date(electionTimes.start);
        const endTime = new Date(electionTimes.end);

        if (now < startTime || now > endTime) {
            window.location.href = 'homepage.php';
        }
    </script>
</head>

<body class="bg-gray-50">
    <?php include 'header.php'; ?>

    <main class="min-h-screen p-8">
        <div class="max-w-7xl mx-auto">
            <!-- Vote Casting Header -->
            <div class="bg-white rounded-xl shadow-xl p-8 mb-8 border-l-4 border-red-600">
                <h1 id="mainTitle" class="text-3xl font-bold text-gray-800 mb-4"></h1>
                <div class="text-gray-600">
                    Please select your candidate for each position carefully.
                </div>
            </div>

            <!-- Voting Area -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <div id="candidatesContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Candidates will be loaded here dynamically -->
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between items-center space-x-4">
                <button class="nav-btn back-btn bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg flex items-center transition duration-300 transform hover:-translate-y-1" onclick="goBack()">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </button>
                <button class="nav-btn abstain-btn bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-3 rounded-lg flex items-center transition duration-300 transform hover:-translate-y-1" onclick="abstainVote()">
                    <i class="fas fa-ban mr-2"></i> Abstain
                </button>
                <button class="nav-btn next-btn bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg flex items-center transition duration-300 transform hover:-translate-y-1" onclick="goNext()">
                    Next <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>

            <!-- Vote Summary -->
            <div id="summaryContainer" class="hidden bg-white rounded-xl shadow-xl p-8 mt-8">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Summary of Your Votes</h2>
                <ul id="summaryList" class="space-y-4">
                    <!-- Summary items will be loaded here dynamically -->
                </ul>
                <div class="mt-8 flex justify-center">
                    <button onclick="submitVotes()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-lg transition duration-300 transform hover:-translate-y-1 flex items-center">
                        <i class="fas fa-check-circle mr-2"></i> Submit Votes
                    </button>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script>
        const positions = <?php echo json_encode($positions); ?>;
        let currentPositionIndex = 0;
        const selectedVotes = {};

        function fetchCandidates(position_id) {
            return fetch(`fetch_candidates.php?position_id=${position_id}`)
                .then(response => response.json())
                .then(candidates => {
                    console.log(candidates); // Log fetched candidates
                    return candidates;
                });
        }

        function displayPosition() {
            const position = positions[currentPositionIndex];
            document.getElementById("mainTitle").textContent = position.position_name;

            fetchCandidates(position.position_id).then(candidates => {
                const candidatesContainer = document.getElementById("candidatesContainer");
                candidatesContainer.innerHTML = "";

                candidates.forEach((candidate, index) => {
                    const candidateCard = document.createElement("div");
                    candidateCard.classList.add(
                        "candidate-card",
                        "bg-white",
                        "rounded-xl",
                        "shadow-lg",
                        "overflow-hidden",
                        "transition-all",
                        "duration-300",
                        "hover:shadow-2xl",
                        "transform",
                        "hover:-translate-y-2",
                        "cursor-pointer",
                        "relative"
                    );
                    candidateCard.dataset.index = index;

                    candidateCard.innerHTML = `
                        <div class="relative aspect-w-4 aspect-h-3">
                            <img class="w-full h-64 object-cover object-center" src="${candidate.candidate_image}" alt="${candidate.candidate_name}">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                            <div class="absolute top-0 right-0 m-2">
                                <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-semibold rounded-full shadow-md">
                                    ${candidate.college_name}
                                </span>
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-2">${candidate.candidate_name}</h3>
                            <p class="text-gray-600">${candidate.candidate_party}</p>
                        </div>
                        <div class="selected-overlay hidden absolute inset-0 bg-red-600/20 rounded-xl">
                            <div class="absolute top-4 right-4 bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center">
                                <i class="fas fa-check text-lg"></i>
                            </div>
                        </div>
                    `;

                    candidateCard.addEventListener("click", () => selectCandidate(candidateCard, candidate, position.position_name));
                    candidatesContainer.appendChild(candidateCard);

                    // Show selection if previously selected
                    if (selectedVotes[position.position_name] && 
                        selectedVotes[position.position_name].candidate_id === candidate.candidate_id) {
                        const overlay = candidateCard.querySelector('.selected-overlay');
                        overlay.classList.remove('hidden');
                        candidateCard.classList.add('ring-4', 'ring-red-600', 'ring-opacity-50');
                    }
                });
            });
        }

        function selectCandidate(candidateCard, candidate, positionName) {
            const candidatesContainer = document.getElementById("candidatesContainer");
            const allCards = candidatesContainer.querySelectorAll('.candidate-card');
            
            // Remove selection from all cards
            allCards.forEach(card => {
                card.querySelector('.selected-overlay').classList.add('hidden');
            });

            // Add selection to clicked card
            const overlay = candidateCard.querySelector('.selected-overlay');
            overlay.classList.remove('hidden');
            
            selectedVotes[positionName] = candidate;
            updateSelectionState();
        }

        function abstainVote() {
            const position = positions[currentPositionIndex].position_name;
            const candidatesContainer = document.getElementById("candidatesContainer");
            const allCards = candidatesContainer.querySelectorAll('.candidate-card');
            
            // Remove selection from all cards
            allCards.forEach(card => {
                card.querySelector('.selected-overlay').classList.add('hidden');
                card.classList.remove('ring-4', 'ring-red-600', 'ring-opacity-50');
            });
            
            selectedVotes[position] = { candidate_id: 0 };
            goNext();
        }

        function updateSelectionState() {
            const position = positions[currentPositionIndex].position_name;
            const candidatesContainer = document.getElementById("candidatesContainer");
            const selectedCard = candidatesContainer.querySelector(".candidate-card.selected");

            if (selectedVotes[position] === "Abstain") {
                document.querySelector(".abstain-btn").classList.add("selected");
                if (selectedCard) {
                    selectedCard.classList.remove("selected");
                }
            } else {
                document.querySelector(".abstain-btn").classList.remove("selected");
            }
        }

        function goNext() {
            const position = positions[currentPositionIndex].position_name;
            if (!selectedVotes[position]) {
                alert("Please select a candidate or choose to abstain before proceeding.");
                return;
            }

            if (currentPositionIndex < positions.length - 1) {
                currentPositionIndex++;
                displayPosition();
            } else {
                console.log("Reached the last position, redirecting to confirmation page"); // Debugging log
                // Store selected votes in session and redirect to confirmation page
                fetch("store_votes.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(selectedVotes)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = "votecastingconfirm.php";
                    } else {
                        alert("Failed to store votes. Please try again.");
                    }
                });
            }
        }

        function goBack() {
            if (currentPositionIndex > 0) {
                currentPositionIndex--;
                displayPosition();
            }
        }

        function showSummary() {
            console.log("Showing summary"); // Debugging log
            document.querySelector(".vote-casting-container").style.display = "none";
            const summaryContainer = document.getElementById("summaryContainer");
            summaryContainer.style.display = "flex";

            const summaryList = document.getElementById("summaryList");
            summaryList.innerHTML = "";

            for (const [position, candidate] of Object.entries(selectedVotes)) {
                const summaryItem = document.createElement("li");
                summaryItem.classList.add(
                    "flex",
                    "justify-between",
                    "items-center",
                    "p-4",
                    "bg-gray-50",
                    "rounded-lg",
                    "border",
                    "border-gray-200"
                );

                summaryItem.innerHTML = `
                    <div>
                        <h4 class="font-semibold text-gray-800">${position}</h4>
                        <p class="text-gray-600">${candidate === "Abstain" ? "Abstain" : candidate.candidate_name}</p>
                    </div>
                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm">
                        ${candidate === "Abstain" ? "Abstained" : "Selected"}
                    </span>
                `;

                summaryList.appendChild(summaryItem);
            }

            console.log("Summary displayed:", summaryList.innerHTML); // Debugging log
        }

        function submitVotes() {
        if (confirm("Are you sure you want to submit your votes? Once submitted, you will not be able to change them.")) {
            fetch("submit_votes.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(selectedVotes)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Votes submitted successfully!");
                    window.location.href = "homepage.php";
                } else {
                    alert("Failed to submit votes. Please try again. Error: " + data.message);
                }
            })
            .catch(error => {
                alert("An error occurred: " + error.message);
            });
        } else {
            // User canceled the confirmation
            alert("Submission canceled. You can review or change your votes.");
        }
        }
        // Initialize the first position display
        displayPosition();
    </script>    
</body>
</html>