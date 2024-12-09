<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['voter'])) {
    header('Location: loginasvoter.php');
    exit();
}

// Retrieve user details from the session
$user = $_SESSION['voter'];

// Include database connection
require_once 'connect.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $experience = isset($_POST['experience']) ? (int)$_POST['experience'] : 0;
    $suggestion = isset($_POST['suggestion']) ? trim($_POST['suggestion']) : '';

    // Debugging: Check received data
    error_log("Received experience: $experience, suggestion: $suggestion");

    // Insert feedback into the database
    $stmt = $pdo->prepare("INSERT INTO feedbacks (student_id, experience, suggestion, feedback_timestamp) VALUES (:student_id, :experience, :suggestion, NOW())");
    $stmt->execute([
        'student_id' => $user['student_id'],
        'experience' => $experience,
        'suggestion' => $suggestion
    ]);

    // Debugging: Check if insertion was successful
    if ($stmt->rowCount() > 0) {
        error_log("Feedback inserted successfully.");
    } else {
        error_log("Failed to insert feedback.");
    }

    // Return a success response
    echo json_encode(['success' => true]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Leave a Feedback</title>
    <link rel="icon" href="asset/susglogo.png" type="image/png">
    
    <style>
        /* Global styling */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body, html {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Full-width header and footer */
        #header, #footer {
            width: 100%;
        }

        /* Ensures header and footer span full width */
        header, footer {
            width: 100%;
            box-sizing: border-box;
        }

        /* Main section for centering feedback form */
        main {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-grow: 1;
            width: 100%;
            padding: 20px;
            box-sizing: border-box;
            margin-top: 50px;
            margin-bottom: 25px;
        }

        /* Feedback container styling */
        .feedback-container {
            background-color: #fff;
            width: 400px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .feedback-title {
            background-color: #c41f1f;
            color: white;
            padding: 10px;
            border-radius: 8px 8px 0 0;
            font-size: 18px;
            font-weight: bold;
        }

        .rating-section {
            margin: 20px 0;
            font-size: 16px;
            font-weight: 500;
            color: #333;
        }

        .rating-options {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        .rating-option {
            font-size: 20px;
            color: #333;
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #333;
            border-radius: 50%;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .rating-option.selected {
            background-color: #c41f1f;
            color: white;
            border-color: #c41f1f;
        }

        .rating-description {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #666;
            margin-top: 10px;
        }

        .suggestion-section {
            font-size: 14px;
            font-weight: 500;
            color: #333;
            margin-top: 20px;
        }

        .suggestion-box {
            width: 100%;
            height: 80px;
            margin-top: 10px;
            padding: 10px;
            font-size: 14px;
            border-radius: 5px;
            border: 1px solid #ccc;
            resize: none;
            box-sizing: border-box;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .btn {
            width: 48%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .cancel-btn {
            background-color: #333;
            color: white;
        }

        .submit-btn {
            background-color: #c41f1f;
            color: white;
        }

        .submit-btn:hover,
        .cancel-btn:hover {
            opacity: 0.8;
        }

        /* Success message styling */
        .success-message {
            color: green;
            font-size: 16px;
            margin-top: 15px;
            display: none;
        }

        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            z-index: 1002;
            border-radius: 10px;
            text-align: center;
        }

        .popup.active {
            display: block;
        }

        .popup button {
            margin-top: 10px;
            padding: 10px 20px;
            background-color: #c41f1f;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        /* Overlay styling */
        .header-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .header-overlay.active {
            display: block;
        }
    </style>
    <script src="script/load.js" type="module" defer></script>
</head>
<body>

    <!-- Header Section -->
    <?php include 'header.php'; ?>

    <!-- Main Section -->
    <main>
        <div class="feedback-container">
            <div class="feedback-title">Leave us your feedback!</div>
            <form id="feedback-form">
                <div class="rating-section">
                    How would you rate your experience?
                </div>
                <div class="rating-options">
                    <div class="rating-option" data-value="1">1</div>
                    <div class="rating-option" data-value="2">2</div>
                    <div class="rating-option" data-value="3">3</div>
                    <div class="rating-option" data-value="4">4</div>
                    <div class="rating-option" data-value="5">5</div>
                </div>
                <input type="hidden" name="experience" id="experience" value="0">
                <div class="rating-description">
                    <span>1 - Bad</span>
                    <span>5 - Excellent</span>
                </div>
                <div class="suggestion-section">
                    Do you have any suggestions to make the website or the service better?
                </div>
                <textarea class="suggestion-box" name="suggestion" placeholder="Type here..."></textarea>
                <div class="actions">
                    <button type="button" class="btn cancel-btn" onclick="navigateTo('homepage.php')">CANCEL</button>
                    <button type="submit" class="btn submit-btn">SUBMIT</button>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer Section -->
    <?php include 'footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ratingOptions = document.querySelectorAll('.rating-option');
            const experienceInput = document.getElementById('experience');
            const feedbackForm = document.getElementById('feedback-form');

            ratingOptions.forEach(option => {
                option.addEventListener('click', function () {
                    ratingOptions.forEach(opt => opt.classList.remove('selected'));
                    option.classList.add('selected');
                    experienceInput.value = option.getAttribute('data-value');
                });
            });

            feedbackForm.addEventListener('submit', function (event) {
                event.preventDefault();

                const experience = experienceInput.value;
                const suggestion = feedbackForm.querySelector('textarea[name="suggestion"]').value.trim();

                if (experience === "0" || suggestion === "") {
                    alert("Please provide a rating and a suggestion.");
                    return;
                }

                const formData = new FormData(feedbackForm);

                fetch('feedback.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Feedback submitted. Thank you!");
                        feedbackForm.reset();
                        ratingOptions.forEach(opt => opt.classList.remove('selected'));
                        experienceInput.value = "0";
                    }
                });
            });
        });

        function navigateTo(page) {
            window.location.href = page;
        }
    </script>
</body>
</html>