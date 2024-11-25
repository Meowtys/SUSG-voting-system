<?php
// Start session to get logged-in user data
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: loginasvoter.php");
    exit;
}

// Include database connection
$host = "localhost";
$username = "root";
$password = "";
$dbname = "susg_project";
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$successMessage = "";
$errorMessage = "";

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id']; // Logged-in user's ID
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comment = isset($_POST['comment']) ? $conn->real_escape_string($_POST['comment']) : '';

    if ($rating >= 1 && $rating <= 5) {
        // Insert feedback into the database
        $sql = "INSERT INTO feedback (user_id, rating, comment) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $user_id, $rating, $comment);

        if ($stmt->execute()) {
            $successMessage = "Thank you for your feedback!";
        } else {
            $errorMessage = "An error occurred. Please try again.";
        }

        $stmt->close();
    } else {
        $errorMessage = "Please select a valid rating.";
    }
}

// Close the database connection
$conn->close();
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
            margin-top: 65px;
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
    </style>
</head>
<body>
    <!-- Header Section -->
    <?php include 'header.php'; ?>

    <!-- Main Section -->
    <main>
        <div class="feedback-container">
            <div class="feedback-title">Leave us your feedback!</div>
            <form method="POST" action="feedback.php">
                <div class="rating-section">
                    How would you rate your experience?
                </div>
                <div class="rating-options">
                    <label>
                        <input type="radio" name="rating" value="1"> 1
                    </label>
                    <label>
                        <input type="radio" name="rating" value="2"> 2
                    </label>
                    <label>
                        <input type="radio" name="rating" value="3"> 3
                    </label>
                    <label>
                        <input type="radio" name="rating" value="4"> 4
                    </label>
                    <label>
                        <input type="radio" name="rating" value="5"> 5
                    </label>
                </div>
                <textarea name="comment" class="suggestion-box" placeholder="Type here..."></textarea>
                <div class="actions">
                    <button type="reset" class="btn cancel-btn">CANCEL</button>
                    <button type="submit" class="btn submit-btn">SUBMIT</button>
                </div>
            </form>
            <?php if ($successMessage): ?>
                <p class="success-message"><?php echo $successMessage; ?></p>
            <?php elseif ($errorMessage): ?>
                <p class="error-message"><?php echo $errorMessage; ?></p>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer Section -->
    <?php include 'footer.php'; ?>
</body>
</html>