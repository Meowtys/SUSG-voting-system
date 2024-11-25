<?php
// Include database connection
$host = "localhost";
$username = "root";
$password = "";
$dbname = "susg_project";
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: loginasvoter.php");
    exit;
}

// Fetch the logged-in user's details from the database
$user_id = $_SESSION['user_id'];
$sql = "SELECT 
            s.stud_name, 
            s.student_id, 
            c.college_name, 
            s.has_voted 
        FROM 
            students s 
        JOIN 
            colleges c 
        ON 
            s.college_id = c.college_id 
        WHERE 
            s.student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
    $student_name = $user_data['stud_name'];
    $college_name = $user_data['college_name'];
    $has_voted = $user_data['has_voted'];
    $student_id = $user_data['student_id'];
} else {
    // Handle case where user is not found
    $student_name = "Unknown Student";
    $college_name = "Unknown College";
    $has_voted = 0;
    $student_id = "Unknown ID";
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
            color: #fff;
            text-transform: uppercase;
            background-color: #e9ecef;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
            margin-bottom: 15px;
            letter-spacing: 1px;
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
</head>
<body>

    <!-- Placeholder for Header -->
    <?php include 'header.php'; ?>

    <main class="main">
        <!-- Main content -->
        <div class="student-info">
            <span class="voting-status" style="background-color: <?php echo ($has_voted) ? '#28a745' : '#dc3545'; ?>">
                <?php echo ($has_voted) ? "Voted" : "Not Voted"; ?>
            </span>
            <h1 class="student-name"><?php echo htmlspecialchars($student_name); ?></h1>
            <p class="student-id"><?php echo htmlspecialchars($student_id); ?></p>
            <p class="student-course"><?php echo htmlspecialchars($college_name); ?></p>
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