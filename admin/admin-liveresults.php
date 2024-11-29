<?php
require_once '../connect.php';

// Fetch positions from the database
$positionsStmt = $pdo->query("SELECT * FROM positions");
$positions = $positionsStmt->fetchAll(PDO::FETCH_ASSOC);

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
    
    <style>
        .content {
            flex-grow: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow-y: auto;
        }

        h1 {
            font-size: 36px;
            margin-bottom: 30px;
            text-align: center;
            color: #333;
        }

        .results-box {
            background-color: #fff;
            border-radius: 10px;
            padding: 30px 20px;
            width: 90%;
            max-width: 900px;
            margin-bottom: 40px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .results-title {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
        }

        .current-position {
            font-size: 24px;
            font-weight: 500;
            color: #b82323;
            margin-bottom: 20px;
        }

        .divider {
            border: 1px solid #e0e0e0;
            width: 90%;
            margin: 20px auto 30px auto;
        }

        .results {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        .candidate-result {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #f8d0d0;
            padding: 20px;
            border-radius: 8px;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            transition: transform 0.3s ease;
        }

        .candidate-result:hover {
            transform: translateY(-5px);
        }

        .candidate-photo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
        }

        .candidate-info {
            display: flex;
            align-items: center;
        }

        .percentage {
            background-color: #d3a5a5;
            padding: 15px;
            border-radius: 5px;
            color: white;
            font-weight: 600;
            font-size: 20px;
            min-width: 60px;
            text-align: center;
        }

        .position-buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin-top: 25px;
        }

        .position-button {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            background-color: #b82323; 
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .position-button:hover {
            background-color: #d9534f; 
            transform: scale(1.05);
        }

        .election-countdown {
            text-align: center;
            max-width: 900px;
            margin-top: -40px;
        }

        .countdown-title {
            font-size: 28px;
            margin-bottom: 10px;
            margin-top: 10px;
            color: #333;
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
    <script src="../script/adminload.js" type="module" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const resultsContainer = document.querySelector(".results");
            const currentPositionElement = document.querySelector(".current-position");

            function fetchResults(positionId, positionName) {
                fetch(`fetch_results.php?position_id=${positionId}`)
                    .then(response => response.json())
                    .then(data => {
                        updateResults(data, positionName);
                    })
                    .catch(error => console.error('Error:', error));
            }

            function updateResults(candidates, positionName) {
                resultsContainer.innerHTML = ""; // Clear previous results
                currentPositionElement.textContent = `Position: ${positionName}`;

                candidates.forEach(candidate => {
                    const resultElement = document.createElement("div");
                    resultElement.className = "candidate-result";
                    resultElement.innerHTML = `
                        <div class="candidate-info">
                            <img class="candidate-photo" src="../${candidate.candidate_image}" alt="${candidate.candidate_name}">
                            <p>${candidate.candidate_name} (${candidate.candidate_party})</p>
                        </div>
                        <span class="percentage">${candidate.votes}</span>
                    `;
                    resultsContainer.appendChild(resultElement);
                });
            }

            // Initialize with the first position
            fetchResults(<?php echo $positions[0]['position_id']; ?>, "<?php echo $positions[0]['position_name']; ?>");

            // Countdown timer functionality
            function startCountdown(duration) {
                let timer = duration, days, hours, minutes, seconds;
                const countdownBox = document.querySelector('.countdown-box');

                setInterval(() => {
                    days = Math.floor(timer / (24 * 60 * 60));
                    hours = Math.floor((timer % (24 * 60 * 60)) / 3600);
                    minutes = Math.floor((timer % 3600) / 60);
                    seconds = Math.floor(timer % 60);

                    countdownBox.innerHTML = `
                        <div>${days} DAYS</div>
                        <div>${hours} HOURS</div>
                        <div>${minutes} MINUTES</div>
                        <div>${seconds} SECONDS</div>
                    `;

                    if (--timer < 0) {
                        timer = duration;
                    }
                }, 1000);
            }

            // Set countdown to 5 days (in seconds)
            startCountdown(5 * 24 * 60 * 60);

            // Add event listeners to position buttons
            document.querySelectorAll('.position-button').forEach(button => {
                button.addEventListener('click', function() {
                    const positionId = this.getAttribute('data-position-id');
                    const positionName = this.textContent;
                    fetchResults(positionId, positionName);
                });
            });
        });
    </script>
</head>
<body>

    <!-- Include Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Section -->
    <main>
        <div class="content">
            <h1>Live Results</h1>
            <div class="results-box">
                <h2 class="results-title">Candidate Results</h2>
                <div class="position-buttons">
                    <?php foreach ($positions as $position): ?>
                        <button class="position-button" data-position-id="<?php echo htmlspecialchars($position['position_id']); ?>">
                            <?php echo htmlspecialchars($position['position_name']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <div class="divider"></div>
    
                <div class="current-position">
                    <!-- Current position will be displayed here -->
                </div>
    
                <div class="results">
                    <!-- Dynamic results will be inserted here -->
                </div>
            </div>
    
            <div class="election-countdown">
                <h2 class="countdown-title">Election Countdown</h2>
                <div class="countdown-box">
                    <!-- Countdown will dynamically populate here -->
                </div>
            </div>
        </div>
    </main>
</body>
</html>