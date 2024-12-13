<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: loginasvoter.php');
    exit();
}

// Retrieve user details from the session
$user = $_SESSION['user'];

// Include database connection
require_once 'connect.php';

// Fetch positions from the database
$positionsStmt = $pdo->query("SELECT * FROM positions");
$positions = $positionsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Live Results</title>
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
    }

    body {
        font-family: 'Poppins', sans-serif;
    }

    .results-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 80vh;
        padding: 60px 30px; /* Increased padding */
    }

    h1.title {
        font-size: 42px; /* Increased font size */
        color: #333;
        margin-bottom: 40px; /* Increased margin */
    }

    .results-box {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 30px; /* Increased padding */
        width: 100%;
        max-width: 1000px; /* Increased max-width */
        text-align: center;
    }

    .results-title {
        font-size: 28px; /* Increased font size */
        font-weight: 600;
        color: #333;
        margin-bottom: 20px; /* Increased margin */
    }

    .divider {
        border: 1px solid #e0e0e0;
        width: 90%;
        margin: 20px auto 30px auto; /* Increased margin */
    }

    .results {
        display: flex;
        justify-content: space-between;
        gap: 30px; /* Increased gap */
    }

    .position-column {
        flex: 1;
        text-align: left;
    }

    .position-title {
        font-size: 20px; /* Increased font size */
        font-weight: 600;
        color: #333;
        margin-bottom: 20px; /* Increased margin */
    }

    .candidate-result {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: #f8d0d0;
        padding: 15px; /* Increased padding */
        border-radius: 8px;
        margin-bottom: 15px; /* Increased margin */
        transition: transform 0.3s ease;
        min-height: 80px; /* Increased height */
        width: 100%; /* Ensure uniform width */
    }

    .candidate-result:hover {
        transform: translateY(-5px);
    }

    .candidate-photo {
        width: 60px; /* Increased size */
        height: 60px; /* Increased size */
        border-radius: 50%;
        margin-right: 20px; /* Increased margin */
    }

    .candidate-info {
        display: flex;
        align-items: center;
        flex-grow: 1;
        justify-content: space-between; /* Ensure uniform spacing */
    }

    .candidate-info p {
        flex-grow: 1;
        margin: 0 15px; /* Increased margin */
    }

    .percentage {
        background-color: #d3a5a5;
        padding: 15px; /* Increased padding */
        border-radius: 5px;
        color: white;
        font-weight: 600;
        min-width: 60px; /* Increased width */
        text-align: center;
    }

    .main-result .percentage {
        font-size: 20px; /* Increased font size */
    }

    .main-result p {
        font-size: 20px; /* Increased font size */
    }

    .candidate-result p {
        font-size: 16px; /* Increased font size */
        color: #333;
    }

    .position-buttons {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 15px; /* Increased gap */
        margin-top: 30px; /* Increased margin */
    }

    .position-button {
        padding: 15px 25px; /* Increased padding */
        font-size: 18px; /* Increased font size */
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

    @media (max-width: 768px) {
        .results {
            flex-direction: column;
            gap: 20px;
        }

        .position-column {
            width: 100%;
        }

        .position-buttons {
            flex-direction: column;
            gap: 10px;
        }

        .position-button {
            width: 100%;
        }
        }
</style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const resultsContainer = document.querySelector(".results");
            const currentPositionElement = document.querySelector(".current-position");

            function fetchResults(positionId, positionName) {
                fetch(`admin/fetch_results.php?position_id=${positionId}`)
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
                            <img class="candidate-photo" src="${candidate.candidate_image}" alt="${candidate.candidate_name}">
                            <p>${candidate.candidate_name} (${candidate.candidate_party})</p>
                        </div>
                        <span class="percentage">${candidate.votes}</span>
                    `;
                    resultsContainer.appendChild(resultElement);
                });
            }

            // Initialize with the first position
            fetchResults(<?php echo $positions[0]['position_id']; ?>, "<?php echo $positions[0]['position_name']; ?>");

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

    <!-- Header Section -->
    <?php include 'header.php'; ?>

    <main class="results-container">
        <!-- Main Contents -->
        <h1 class="title">Live Tally</h1>
        <div class="results-box">
            <h2 class="results-title">Live Results (All Position)</h2>
            <div class="position-buttons">
                <?php foreach ($positions as $position): ?>
                    <button class="position-button" data-position-id="<?php echo htmlspecialchars($position['position_id']); ?>">
                        <?php echo htmlspecialchars($position['position_name']); ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <hr class="divider">

            <div class="current-position">
                <!-- Current position will be displayed here -->
            </div>

            <div class="results">
                <!-- Dynamic results will be inserted here -->
            </div>
        </div>
    </main>

    <!-- Footer Section -->
    <?php include 'footer.php'; ?>

</body>
</html>