<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../start.php");
    exit;
}

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
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

        .dropdown {
            margin-top: 25px;
            display: inline-block;
        }

        .dropdown select {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 200px;
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
            const candidateData = {
                "president": [
                    { name: "James Teves", party: "Tribu Wakwak", votes: 567 },
                    { name: "Rynz Daval", party: "Tribu Akru", votes: 300 },
                    { name: "Danyel Ray", party: "Tribu Treskilion", votes: 150 }
                ],
                "vice_president": [
                    { name: "John Doe", party: "Foam Party", votes: 350 },
                    { name: "Elliot Montgomery", party: "Disco Party", votes: 290 },
                    { name: "Westen Naval", party: "Ozone Party", votes: 210 }
                ],
                "secretary": [
                    { name: "Emilio Aguinaldo", party: "Mason", votes: 450 },
                    { name: "Andres Bonifacio", party: "Katipuneros", votes: 400 },
                    { name: "Jose Rizal", party: "Bayani", votes: 350 }
                ],
                "assistant_secretary": [
                    { name: "Kuya Kim", party: "Matanglawin", votes: 200 },
                    { name: "Vice Ganda", party: "Gandang Gabi Vice", votes: 180 },
                    { name: "Tito Boy", party: "Boy Abunda", votes: 150 }
                ],
                "treasurer": [
                    { name: "James Teves", party: "Tribu Wakwak", votes: 320 },
                    { name: "Rynz Daval", party: "Tribu Akru", votes: 270 },
                    { name: "Danyel Ray", party: "Tribu Treskilion", votes: 120 }
                ],
                "majority_floor_leader": [
                    { name: "John Doe", party: "Foam Party", votes: 380 },
                    { name: "Elliot Montgomery", party: "Disco Party", votes: 360 },
                    { name: "Westen Naval", party: "Ozone Party", votes: 140 }
                ]
            };

            const positionSelect = document.getElementById("position");
            const resultsContainer = document.querySelector(".results");

            positionSelect.addEventListener("change", function () {
                const selectedPosition = positionSelect.value;
                updateResults(selectedPosition);
            });

            function updateResults(position) {
                resultsContainer.innerHTML = ""; // Clear previous results

                const candidates = candidateData[position];
                candidates.forEach(candidate => {
                    const resultElement = document.createElement("div");
                    resultElement.className = "candidate-result";
                    resultElement.innerHTML = `
                        <p>${candidate.name} (${candidate.party})</p>
                        <span class="percentage">${candidate.votes}</span>
                    `;
                    resultsContainer.appendChild(resultElement);
                });
            }

            // Initialize with the first position
            updateResults("president");

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
                <div class="dropdown">
                    <label for="position">Position: </label>
                    <select id="position">
                        <option value="president">President (Speaker)</option>
                        <option value="vice_president">Vice President (Speaker Pro Tempore)</option>
                        <option value="secretary">Secretary</option>
                        <option value="assistant_secretary">Assistant Secretary</option>
                        <option value="treasurer">Treasurer</option>
                        <option value="majority_floor_leader">Majority Floor Leader</option>
                    </select>
                </div>
                <div class="divider"></div>
    
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