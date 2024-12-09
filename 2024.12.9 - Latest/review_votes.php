<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: loginasvoter.php');
    exit();
}

$user = $_SESSION['user'];

require_once 'connect.php';

// Fetch positions from the database
$positions_stmt = $pdo->query("SELECT * FROM positions");
$positions = $positions_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch candidate images from the database
$candidate_images_stmt = $pdo->query("SELECT candidate_name, candidate_image FROM candidates");
$candidate_images = $candidate_images_stmt->fetchAll(PDO::FETCH_ASSOC);
$candidate_images_map = [];
foreach ($candidate_images as $candidate_image) {
    $candidate_images_map[$candidate_image['candidate_name']] = $candidate_image['candidate_image'];
}

// Default image for abstain
$default_abstain_image = 'path/to/default_abstain_image.png';

// Retrieve selected votes from the database
$selectedVotes = [];
$votes_stmt = $pdo->prepare("SELECT v.candidate_id, c.candidate_name, c.college_id, p.position_name, col.college_name 
                             FROM votes v
                             JOIN candidates c ON v.candidate_id = c.candidate_id
                             JOIN positions p ON v.position_id = p.position_id
                             JOIN colleges col ON c.college_id = col.college_id
                             WHERE v.student_id = ?");
$votes_stmt->execute([$user['student_id']]);
$votes = $votes_stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($votes as $vote) {
    $selectedVotes[$vote['position_name']] = [
        'candidate_id' => $vote['candidate_id'],
        'candidate_name' => $vote['candidate_name'],
        'college_name' => $vote['college_name']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
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
            padding: 60px 30px;
            min-height: 90vh;
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
            width: 70px;
            height: 70px;
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

        .back-btn {
            background-color: #333;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            margin-top: 20px;
        }

        .back-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .vote-summary {
                flex-direction: column;
            }

            .vote-item {
                width: 100%;
            }

            .back-btn {
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
        <h1 class="title">Review Your Votes</h1>
        <div class="votes-box">
            <h2 class="votes-title">Your Votes</h2>
            <hr class="divider">
            <div class="vote-summary">
                <?php foreach ($positions as $position): ?>
                    <?php if (isset($selectedVotes[$position['position_name']])): ?>
                        <div class="vote-item">
                            <h3 class="position-title"><?php echo htmlspecialchars($position['position_name']); ?></h3>
                            <div class="candidate-summary">
                                <?php if ($selectedVotes[$position['position_name']]['candidate_id'] == 0): ?>
                                    <div class="candidate-info">
                                        <h4>Abstain</h4>
                                    </div>
                                <?php else: ?>
                                    <div class="candidate-photo">
                                        <img src="<?php echo htmlspecialchars($candidate_images_map[$selectedVotes[$position['position_name']]['candidate_name']]); ?>" alt="Candidate Photo" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                    <div class="candidate-info">
                                        <h4><?php echo htmlspecialchars($selectedVotes[$position['position_name']]['candidate_name']); ?></h4>
                                        <p><?php echo htmlspecialchars($selectedVotes[$position['position_name']]['college_name']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <button class="back-btn" onclick="navigateTo('countdown.php')">Back to Countdown</button>
    </main>

    <!-- Footer Section -->
    <?php include 'footer.php'; ?>
    <script>
        function navigateTo(page) {
            window.location.href = page;
        }
    </script>
</body>
</html>