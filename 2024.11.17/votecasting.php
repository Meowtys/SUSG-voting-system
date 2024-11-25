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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        main {
            background-color: #f5e8e7;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 90vh; /* Reduced min-height */
        }

        body {
            font-family: 'Poppins', sans-serif;
        }

        .vote-casting-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px; /* Increased padding */
        }

        .title {
            font-size: 36px;
            color: #333;
            margin-bottom: 20px;
        }

        .voting-box {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 100%;
            max-width: 800px;
            text-align: center;
        }

        .position-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }

        .divider {
            border: 1px solid #e0e0e0;
            width: 90%;
            margin: 10px auto 20px auto;
        }

        .candidates {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .candidate-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #f8d0d0;
            border-radius: 10px;
            padding: 20px; /* Increased padding */
            width: 250px; /* Increased width */
            transition: transform 0.3s ease, background-color 0.3s ease;
            cursor: pointer;
        }

        .candidate-card.selected {
            background-color: #d3a5a5;
        }

        .candidate-card:hover {
            transform: translateY(-10px);
        }

        .candidate-photo {
            width: 100%;
            height: 200px; /* Increased height */
            background-color: #d3a5a5;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .candidate-name {
            font-size: 20px; /* Increased font size */
            font-weight: 600; /* Increased font weight */
            color: #333;
            margin-bottom: 5px;
        }

        .candidate-party {
            font-size: 16px; /* Increased font size */
            color: #666;
            margin-bottom: 10px;
        }

        .vote-btn {
            background-color: #333;
            color: white;
            padding: 8px 16px; /* Adjusted padding */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .vote-btn:hover {
            background-color: #000;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            width: 100%;
            max-width: 800px;
            margin-top: 20px;
        }

        .nav-btn {
            padding: 10px 20px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            color: white;
        }

        .back-btn,
        .next-btn {
            background-color: #333;
        }

        .abstain-btn {
            background-color: #dc3545;
        }

        .nav-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .summary-container {
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .summary-title {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        .summary-list {
            list-style: none;
            padding: 0;
            margin: 0;
            width: 100%;
            max-width: 800px;
        }

        .summary-item {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .summary-item span {
            font-size: 18px;
            color: #333;
        }

        .submit-btn {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .submit-btn:hover {
            background-color: #218838;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .candidates {
                flex-direction: column;
                gap: 20px;
            }

            .candidate-card {
                width: 100%;
            }

            .navigation-buttons {
                flex-direction: column;
                gap: 10px;
            }

            .nav-btn {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .title {
                font-size: 28px;
            }

            .position-title {
                font-size: 20px;
            }

            .candidate-photo {
                height: 100px;
            }

            .candidate-name {
                font-size: 14px;
            }

            .candidate-party {
                font-size: 10px;
            }

            .vote-btn {
                padding: 8px 16px;
                font-size: 14px;
            }
        }
    </style>
    <script src="script/load.js" type="module" defer></script>
</head>

<body>
    
    <!-- Header Section -->
    <?php include 'header.php'; ?>

    <main class="vote-casting-container">
        <h1 class="title" id="mainTitle">Vote Casting</h1>
        <div class="voting-box">
            <div class="candidates" id="candidatesContainer">
                <!-- Candidates will be loaded here dynamically -->
            </div>
        </div>

        <div class="navigation-buttons">
            <button class="nav-btn back-btn" onclick="goBack()">Back</button>
            <button class="nav-btn abstain-btn" onclick="abstainVote()">Abstain</button>
            <button class="nav-btn next-btn" onclick="goNext()">Next</button>
        </div>

        <div class="summary-container" id="summaryContainer">
            <h2 class="summary-title">Summary of Your Votes</h2>
            <ul class="summary-list" id="summaryList">
                <!-- Summary items will be loaded here dynamically -->
            </ul>
            <button class="submit-btn" onclick="submitVotes()">Submit Votes</button>
        </div>
    </main>

    <!-- Footer Section -->
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
                    candidateCard.classList.add("candidate-card");
                    candidateCard.dataset.index = index;

                    candidateCard.innerHTML = `
                        <img class="candidate-photo" src="${candidate.candidate_image}" alt="${candidate.candidate_name}">
                        <h3 class="candidate-name">${candidate.candidate_name}</h3>
                        <p class="candidate-party">${candidate.candidate_party}</p>
                    `;

                    candidateCard.addEventListener("click", () => selectCandidate(candidateCard, candidate, position.position_name));
                    candidatesContainer.appendChild(candidateCard);

                    // Highlight previously selected candidate
                    if (selectedVotes[position.position_name] && selectedVotes[position.position_name].candidate_id === candidate.candidate_id) {
                        candidateCard.classList.add("selected");
                    }
                });

                // Update the selection state of the candidate cards
                updateSelectionState();
            });
        }

        function selectCandidate(candidateCard, candidate, positionName) {
            const candidatesContainer = document.getElementById("candidatesContainer");
            const selectedCard = candidatesContainer.querySelector(".candidate-card.selected");

            if (selectedCard) {
                selectedCard.classList.remove("selected");
            }

            if (selectedCard !== candidateCard) {
                candidateCard.classList.add("selected");
                selectedVotes[positionName] = candidate;
            } else {
                delete selectedVotes[positionName];
            }

            updateSelectionState();
        }

        function abstainVote() {
            const position = positions[currentPositionIndex].position_name;
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
                summaryItem.classList.add("summary-item");

                summaryItem.innerHTML = `
                    <span>${position}</span>
                    <span>${candidate === "Abstain" ? "Abstain" : candidate.candidate_name}</span>
                `;

                summaryList.appendChild(summaryItem);
            }

            console.log("Summary displayed:", summaryList.innerHTML); // Debugging log
        }

        function submitVotes() {
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
        }

        // Initialize the first position display
        displayPosition();
    </script>    
</body>

</html>