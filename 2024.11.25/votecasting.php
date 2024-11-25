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
            min-height: 100vh;
        }

        body {
            font-family: 'Poppins', sans-serif;
        }

        .vote-casting-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
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
            justify-content: space-around;
            gap: 20px;
        }

        .candidate-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #f8d0d0;
            border-radius: 10px;
            padding: 20px;
            width: 30%;
            transition: transform 0.3s ease;
        }

        .candidate-card:hover {
            transform: translateY(-10px);
        }

        .candidate-photo {
            width: 100%;
            height: 150px;
            background-color: #d3a5a5;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .candidate-name {
            font-size: 18px;
            font-weight: 500;
            color: #333;
            margin-bottom: 5px;
        }

        .candidate-party {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }

        .vote-btn {
            background-color: #333;
            color: white;
            padding: 10px 20px;
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
                font-size: 16px;
            }

            .candidate-party {
                font-size: 12px;
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
            <h2 id="positionTitle" class="position-title">Position</h2>
            <hr class="divider">
            <div class="candidates" id="candidatesContainer">
                <!-- Candidates will be loaded here dynamically -->
            </div>
        </div>

        <div class="navigation-buttons">
            <button class="nav-btn back-btn" onclick="goBack()">Back</button>
            <button class="nav-btn abstain-btn" onclick="abstainVote()">Abstain</button>
            <button class="nav-btn next-btn" onclick="goNext()">Next</button>
        </div>
    </main>

    <!-- Footer Section -->
    <?php include 'footer.php'; ?>

    <script>
        const positions = [
            { 
                title: "President (Speaker)", 
                candidates: [
                    { name: "Rynz Daval", party: "Tribu Wakwak" },
                    { name: "Daniel Ray Cal", party: "Tribu Akro" },
                    { name: "James Ald Teves", party: "Tribu Apokalipto" }
                ]
            },
            { 
                title: "Vice President (Speaker Pro Tempore)", 
                candidates: [
                    { name: "Candidate 1", party: "Party A" },
                    { name: "Candidate 2", party: "Party B" },
                    { name: "Candidate 3", party: "Party C" }
                ]
            },
            { 
                title: "Secretary", 
                candidates: [
                    { name: "Candidate 4", party: "Party X" },
                    { name: "Candidate 5", party: "Party Y" }
                ]
            },
            { 
                title: "Assistant Secretary", 
                candidates: [
                    { name: "Candidate 6", party: "Party X" },
                    { name: "Candidate 7", party: "Party Y" }
                ]
            },
            { 
                title: "Treasurer", 
                candidates: [
                    { name: "Candidate 8", party: "Party M" },
                    { name: "Candidate 9", party: "Party N" }
                ]
            },
            { 
                title: "Majority Floor Leader", 
                candidates: [
                    { name: "Candidate 10", party: "Party K" },
                    { name: "Candidate 11", party: "Party L" }
                ]
            }
        ];
    
        let currentPositionIndex = 0;
        const selectedVotes = {};
    
        function displayPosition() {
            const position = positions[currentPositionIndex];
            document.getElementById("mainTitle").textContent = position.title;
            document.getElementById("positionTitle").textContent = position.title;
    
            const candidatesContainer = document.getElementById("candidatesContainer");
            candidatesContainer.innerHTML = "";
    
            position.candidates.forEach((candidate, index) => {
                const candidateCard = document.createElement("div");
                candidateCard.classList.add("candidate-card");
    
                candidateCard.innerHTML = `
                    <div class="candidate-photo"></div>
                    <h3 class="candidate-name">${candidate.name}</h3>
                    <p class="candidate-party">${candidate.party}</p>
                    <button class="vote-btn" onclick="voteForCandidate(${currentPositionIndex}, ${index})">Vote</button>
                `;
    
                candidatesContainer.appendChild(candidateCard);
            });
    
            // Disable voting buttons if abstain was chosen or a candidate was already selected for this position
            updateVoteButtons();
        }
    
        function voteForCandidate(positionIndex, candidateIndex) {
            const position = positions[positionIndex];
    
            // Check if a vote or abstain has already been cast for this position
            if (selectedVotes[position.title]) {
                alert(`You have already voted for ${position.title}.`);
                return;
            }
    
            selectedVotes[position.title] = position.candidates[candidateIndex];
            alert(`You have voted for ${position.candidates[candidateIndex].name} as ${position.title}`);
    
            updateVoteButtons(); // Update button states after voting
        }
    
        function abstainVote() {
            const position = positions[currentPositionIndex];
    
            // Check if a vote or abstain has already been cast for this position
            if (selectedVotes[position.title]) {
                alert(`You have already voted for ${position.title}.`);
                return;
            }
    
            selectedVotes[position.title] = "Abstain";
            alert(`You have abstained from voting for ${position.title}`);
    
            updateVoteButtons(); // Disable all vote buttons for this position
        }
    
        function updateVoteButtons() {
            const position = positions[currentPositionIndex];
            const hasVoted = selectedVotes[position.title] !== undefined;
    
            // Toggle the disabled state of vote buttons based on whether the user has already voted or abstained
            document.querySelectorAll(".vote-btn").forEach(button => {
                button.disabled = hasVoted;
                button.style.opacity = hasVoted ? "0.5" : "1";
                button.style.cursor = hasVoted ? "not-allowed" : "pointer";
            });
    
            // Disable abstain button if a vote has already been cast
            const abstainButton = document.querySelector(".abstain-btn");
            abstainButton.disabled = hasVoted;
            abstainButton.style.opacity = hasVoted ? "0.5" : "1";
            abstainButton.style.cursor = hasVoted ? "not-allowed" : "pointer";
        }
    
        function goNext() {
            if (currentPositionIndex < positions.length - 1) {
                currentPositionIndex++;
                displayPosition();
            } else {
                alert("You have reached the last position.");
            }
        }
    
        function goBack() {
            if (currentPositionIndex > 0) {
                currentPositionIndex--;
                displayPosition();
            } else {
                alert("You are at the first position.");
            }
        }
    
        // Initialize the first position display
        displayPosition();
    </script>    
</body>

</html>