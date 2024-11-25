<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: loginasvoter.php');
    exit();
}

// Retrieve user details from the session
$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Dashboard</title>
    <link rel="icon" href="asset/susglogo.png" type="image/png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body, html {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        .main {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            padding: 40px 0;
            height: 80vh;
        }

        .student-info {
            text-align: center;
            margin-bottom: 40px;
        }

        .voting-status {
            font-size: 14px;
            text-transform: uppercase;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
            margin-bottom: 15px;
            letter-spacing: 1px;
        }

        .voted {
            background-color: #28a745; /* Green */
            color: white;
        }

        .not-voted {
            background-color: #dc3545; /* Red */
            color: white;
        }

        .student-name {
            font-size: 36px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .student-id,
        .student-course {
            font-size: 18px;
            color: #666;
        }

        .action-cards {
            display: flex;
            justify-content: space-between;
            width: 70%;
            max-width: 900px;
        }

        .card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            width: 45%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.3s ease-in-out;
        }

        .card:hover {
            transform: scale(1.05);
        }

        .card-content {
            margin-bottom: 20px;
        }

        .card-content h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #333;
        }

        .card-content p {
            font-size: 18px;
            color: #666;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background-color: #dc3545;
            color: white;
            font-size: 16px;
            font-weight: 500;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .btn:hover {
            background-color: #c82333;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        .vote-btn {
            background-color: #dc3545;
        }

        .vote-btn:hover {
            background-color: #c82333;
        }

        .tally-btn {
            background-color: #28a745;
        }

        .tally-btn:hover {
            background-color: #218838;
        }

        @media (max-width: 768px) {
            .student-name {
                font-size: 28px;
            }

            .student-id, .student-course {
                font-size: 16px;
            }

            .action-cards {
                flex-direction: column;
                width: 100%;
                gap: 15px;
            }

            .card {
                width: 100%;
                margin-bottom: 20px;
            }
        }

        @media (max-width: 480px) {
            .student-name {
                font-size: 24px;
            }

            .student-id, .student-course {
                font-size: 14px;
            }

            .voting-status {
                font-size: 12px;
                padding: 3px 8px;
            }

            .btn {
                padding: 12px;
                font-size: 14px;
            }

            .card-content h3 {
                font-size: 20px;
            }

            .card-content p {
                font-size: 16px;
            }
        }
    </style>
    <script src="script/load.js" type="module" defer></script>
</head>
<body>

    <!-- Placeholder for Header -->
    <?php include 'header.php'; ?>

    <main class="main">
        <!-- Main content -->
        <div class="student-info">
            <span class="voting-status <?php echo $user['has_voted'] ? 'voted' : 'not-voted'; ?>">
                Voting Status: <?php echo $user['has_voted'] ? 'Voted' : 'Not Voted'; ?>
            </span>
            <h1 class="student-name"><?php echo htmlspecialchars($user['name']); ?></h1>
            <p class="student-id"><?php echo htmlspecialchars($user['student_id']); ?></p>
            <p class="student-course"><?php echo htmlspecialchars($user['college_id']); ?></p>
        </div>

        <div class="action-cards">
            <div class="card">
                <div class="card-content">
                    <h3>Current Results</h3>
                    <p>10% Voted</p>
                </div>
                <button class="btn vote-btn" onclick="navigateTo('votecasting.php')">Vote Now</button>
            </div>
            <div class="card">
                <div class="card-content">
                    <h3>Live Tally</h3>
                    <p>John Doe: 45%<br>Jane Doe: 55%</p>
                </div>
                <button class="btn tally-btn" onclick="navigateTo('liveresult.php')">Live Tally</button>
            </div>
        </div>
    </main>

    <!-- Placeholder for Footer -->
    <?php include 'footer.php'; ?>

    <script>
        function navigateTo(page) {
            window.location.href = page;
        }
    </script>
</body>
</html>