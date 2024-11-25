<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Confirmation Page</title>
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

        .vote-summary-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            min-height: 80vh;
        }

        h1.title {
            font-size: 36px;
            color: #333;
            margin-bottom: 30px;
        }

        .votes-box {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 100%;
            max-width: 800px;
            text-align: center;
        }

        .votes-title {
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

        .vote-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .vote-item {
            flex: 0 0 48%;
            margin-bottom: 20px;
            text-align: left;
        }

        .position-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .candidate-summary {
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: #f8d0d0;
            padding: 10px;
            border-radius: 8px;
            transition: transform 0.3s ease;
        }

        .candidate-summary:hover {
            transform: translateY(-5px);
        }

        .candidate-photo {
            width: 50px;
            height: 50px;
            background-color: #d3a5a5;
            border-radius: 5px;
        }

        .candidate-info h4 {
            font-size: 14px;
            color: #333;
            margin-bottom: 5px;
        }

        .candidate-info p {
            font-size: 12px;
            color: #666;
        }

        .vote-buttons {
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

        .return-btn {
            background-color: #333;
            color: white;
        }

        .submit-btn {
            background-color: #dc3545;
            color: white;
        }

        .nav-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .success-message {
            color: green;
            font-size: 18px;
            margin-top: 20px;
            display: none;
        }

        @media (max-width: 768px) {
            .vote-summary {
                flex-direction: column;
            }

            .vote-item {
                width: 100%;
            }

            .vote-buttons {
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

    <main class="vote-summary-container">
        <h1 class="title">Vote Casting</h1>
        <div class="votes-box">
            <h2 class="votes-title">Your Votes</h2>
            <hr class="divider">
            <div class="vote-summary">
                <!-- Adding all positions -->
                <div class="vote-item">
                    <h3 class="position-title">PRESIDENT (Speaker)</h3>
                    <div class="candidate-summary">
                        <div class="candidate-photo"></div>
                        <div class="candidate-info">
                            <h4>Candidate C. Pres</h4>
                            <p>Saturday Party</p>
                        </div>
                    </div>
                </div>
                <div class="vote-item">
                    <h3 class="position-title">VICE PRESIDENT (Speaker Pro Tempore)</h3>
                    <div class="candidate-summary">
                        <div class="candidate-photo"></div>
                        <div class="candidate-info">
                            <h4>Candidate V. Pres</h4>
                            <p>Unity Party</p>
                        </div>
                    </div>
                </div>
                <div class="vote-item">
                    <h3 class="position-title">SECRETARY</h3>
                    <div class="candidate-summary">
                        <div class="candidate-photo"></div>
                        <div class="candidate-info">
                            <h4>Candidate S. Secretary</h4>
                            <p>Unity Party</p>
                        </div>
                    </div>
                </div>
                <div class="vote-item">
                    <h3 class="position-title">ASSISTANT SECRETARY</h3>
                    <div class="candidate-summary">
                        <div class="candidate-photo"></div>
                        <div class="candidate-info">
                            <h4>Candidate A. Secretary</h4>
                            <p>Saturday Party</p>
                        </div>
                    </div>
                </div>
                <div class="vote-item">
                    <h3 class="position-title">TREASURER</h3>
                    <div class="candidate-summary">
                        <div class="candidate-photo"></div>
                        <div class="candidate-info">
                            <h4>Candidate T. Treasurer</h4>
                            <p>Unity Party</p>
                        </div>
                    </div>
                </div>
                <!-- Add remaining positions similarly -->
                <!-- Example for each additional position -->
                <div class="vote-item">
                    <h3 class="position-title">MAJORITY FLOOR LEADER</h3>
                    <div class="candidate-summary">
                        <div class="candidate-photo"></div>
                        <div class="candidate-info">
                            <h4>Candidate M. Leader</h4>
                            <p>Saturday Party</p>
                        </div>
                    </div>
                </div>
                <!-- Repeat for each position -->

            </div>
        </div>

        <!-- Success message -->
        <div id="successMessage" class="success-message">You have successfully voted.</div>

        <div class="vote-buttons">
            <button class="nav-btn return-btn" onclick="returnToVoting()">Return to Voting</button>
            <button class="nav-btn submit-btn" id="submitBtn" onclick="submitVotes()">Submit Votes</button>
        </div>
    </main>

    <!-- Footer Section -->
    <?php include 'footer.php'; ?>
    <script>
        function returnToVoting() {
            // Navigate back to the vote casting page
            window.location.href = "votecasting.php";
        }

        function submitVotes() {
            // Display the success message
            document.getElementById("successMessage").style.display = "block";

            // Disable the "Submit Votes" button
            const submitBtn = document.getElementById("submitBtn");
            submitBtn.disabled = true;
            submitBtn.style.backgroundColor = "gray";
            submitBtn.style.cursor = "not-allowed";
        }
    </script>
</body>
</html>