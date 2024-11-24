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
            <div class="rating-section">
                How would you rate your experience?
            </div>
            <div class="rating-options">
                <div class="rating-option">1</div>
                <div class="rating-option">2</div>
                <div class="rating-option">3</div>
                <div class="rating-option">4</div>
                <div class="rating-option">5</div>
            </div>
            <div class="rating-description">
                <span>1 - Bad</span>
                <span>5 - Excellent</span>
            </div>
            <div class="suggestion-section">
                Do you have any suggestions to make the website or the service better?
            </div>
            <textarea class="suggestion-box" placeholder="Type here..."></textarea>
            <div class="actions">
                <button class="btn cancel-btn">CANCEL</button>
                <button class="btn submit-btn">SUBMIT</button>
            </div>
        </div>
    </main>

    <!-- Footer Section -->
    <?php include 'footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ratingOptions = document.querySelectorAll('.rating-option');
            const suggestionBox = document.querySelector('.suggestion-box');
            const submitBtn = document.querySelector('.submit-btn');
            const cancelBtn = document.querySelector('.cancel-btn');
            const successMessage = document.createElement('div');

            // Add success message element
            successMessage.className = 'success-message';
            successMessage.textContent = "Thank you for your feedback!";
            document.querySelector('.feedback-container').appendChild(successMessage);

            // Rating selection event
            ratingOptions.forEach(option => {
                option.addEventListener('click', function () {
                    ratingOptions.forEach(o => o.classList.remove('selected'));
                    option.classList.add('selected');
                });
            });

            // Submit button event
            submitBtn.addEventListener('click', function (event) {
                event.preventDefault(); // Prevent form submission
                
                // Clear the feedback fields
                suggestionBox.value = '';
                ratingOptions.forEach(option => option.classList.remove('selected'));

                // Display success message
                successMessage.style.display = 'block';

                // Hide the message after 3 seconds
                setTimeout(() => {
                    successMessage.style.display = 'none';
                }, 3000);
            });

            // Cancel button event
            cancelBtn.addEventListener('click', function () {
                // Remove the selected class from rating options
                ratingOptions.forEach(option => option.classList.remove('selected'));

                // Clear the suggestion box
                suggestionBox.value = '';
            });
        });
    </script>
</body>
</html>