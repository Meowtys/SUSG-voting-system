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
        padding: 40px 20px;
    }

    h1.title {
        font-size: 36px;
        color: #333;
        margin-bottom: 30px;
    }

    .results-box {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 20px;
        width: 100%;
        max-width: 900px;
        text-align: center;
    }

    .results-title {
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

    .results {
        display: flex;
        justify-content: space-between;
        gap: 20px;
    }

    .position-column {
        flex: 1;
        text-align: left;
    }

    .position-title {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        margin-bottom: 15px;
    }

    .candidate-result {
        display: flex;
        align-items: center;
        gap: 10px;
        background-color: #f8d0d0;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 10px;
        transition: transform 0.3s ease;
    }

    .candidate-result:hover {
        transform: translateY(-5px);
    }

    .percentage {
        background-color: #d3a5a5;
        padding: 10px;
        border-radius: 5px;
        color: white;
        font-weight: 600;
        min-width: 50px;
        text-align: center;
    }

    .main-result .percentage {
        font-size: 18px;
    }

    .main-result p {
        font-size: 18px;
    }

    .candidate-result p {
        font-size: 14px;
        color: #333;
    }

    .navigation-buttons {
        display: flex;
        justify-content: space-between;
        width: 100%;
        max-width: 600px;
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
    }

    .back-btn {
        background-color: #a5a5a5;
        color: white;
    }

    .next-btn {
        background-color: #333;
        color: white;
    }

    .nav-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    @media (max-width: 768px) {
        .results {
            flex-direction: column;
            gap: 20px;
        }

        .position-column {
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
    </style>
    <script src="script/load.js" type="module" defer></script>
</head>
<body>

    <!-- Header Section -->
    <?php include 'header.php'; ?>

    <main class="results-container">
        <!-- Main Contents -->
        <h1 class="title">Results</h1>
        <div class="results-box">
            <h2 class="results-title">Live Results (All Position)</h2>
            <hr class="divider">

            <div class="results">
                <div class="position-column">
                    <h3 class="position-title">PRESIDENT:</h3>
                    <div class="candidate-result main-result">
                        <div class="percentage">60%</div>
                        <p>Candidate A</p>
                    </div>
                    <div class="candidate-result">
                        <div class="percentage">23%</div>
                        <p>Candidate B</p>
                    </div>
                    <div class="candidate-result">
                        <div class="percentage">17%</div>
                        <p>Candidate C</p>
                    </div>
                </div>

                <div class="position-column">
                    <h3 class="position-title">VICE-PRESIDENT:</h3>
                    <div class="candidate-result main-result">
                        <div class="percentage">73%</div>
                        <p>Candidate D</p>
                    </div>
                    <div class="candidate-result">
                        <div class="percentage">10%</div>
                        <p>Candidate E</p>
                    </div>
                    <div class="candidate-result">
                        <div class="percentage">7%</div>
                        <p>Candidate F</p>
                    </div>
                </div>

                <div class="position-column">
                    <h3 class="position-title">DEPARTMENT REP 1:</h3>
                    <div class="candidate-result main-result">
                        <div class="percentage">42%</div>
                        <p>Candidate G</p>
                    </div>
                    <div class="candidate-result">
                        <div class="percentage">31%</div>
                        <p>Candidate H</p>
                    </div>
                    <div class="candidate-result">
                        <div class="percentage">27%</div>
                        <p>Candidate I</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="navigation-buttons">
            <button class="nav-btn back-btn">Back</button>
            <button class="nav-btn next-btn">Next</button>
        </div>
    </main>

    <!-- Footer Section -->
    <?php include 'footer.php'; ?>

</body>
</html>