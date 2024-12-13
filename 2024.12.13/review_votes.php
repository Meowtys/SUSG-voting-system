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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Review Votes</title>
    <link rel="icon" href="asset/susglogo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        * {
            font-family: 'Poppins', sans-serif;
        }
    </style>
    <script src="script/load.js" type="module" defer></script>
</head>

<body class="bg-gray-50">
    <?php include 'header.php'; ?>

    <main class="min-h-screen p-8">
        <div class="max-w-4xl mx-auto">
            <!-- Review Header -->
            <div class="bg-white rounded-xl shadow-xl p-8 mb-8 border-l-4 border-red-600">
                <h1 class="text-3xl font-bold text-gray-800 mb-4">Review Your Votes</h1>
                <div class="text-gray-600">
                    Here's a summary of your submitted votes for the SUSG Election.
                </div>
            </div>

            <!-- Votes Summary Box -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-red-50 px-6 py-4 border-b border-red-100">
                    <h2 class="text-xl font-semibold text-red-800">Your Submitted Votes</h2>
                </div>
                
                <div class="p-6 grid gap-6">
                    <?php foreach ($positions as $position): ?>
                        <?php if (isset($selectedVotes[$position['position_name']])): ?>
                            <div class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow duration-300">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                                    <i class="fas fa-user-tie text-red-600 mr-2"></i>
                                    <?php echo htmlspecialchars($position['position_name']); ?>
                                </h3>
                                
                                <?php if ($selectedVotes[$position['position_name']]['candidate_id'] == 0): ?>
                                    <!-- Abstain Card -->
                                    <div class="flex items-center bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                                        <div class="w-12 h-12 bg-yellow-200 rounded-full flex items-center justify-center">
                                            <i class="fas fa-ban text-yellow-600 text-xl"></i>
                                        </div>
                                        <div class="ml-4">
                                            <h4 class="text-lg font-medium text-yellow-800">Abstain</h4>
                                            <p class="text-sm text-yellow-600">You abstained for this position</p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Candidate Card -->
                                    <div class="flex items-center bg-white rounded-lg p-4 border border-gray-200">
                                        <img class="w-16 h-16 rounded-lg object-cover shadow-sm" 
                                             src="<?php echo htmlspecialchars($candidate_images_map[$selectedVotes[$position['position_name']]['candidate_name']] ?? 'asset/default-candidate.png'); ?>" 
                                             alt="<?php echo htmlspecialchars($selectedVotes[$position['position_name']]['candidate_name']); ?>">
                                        <div class="ml-4">
                                            <h4 class="text-lg font-medium text-gray-800">
                                                <?php echo htmlspecialchars($selectedVotes[$position['position_name']]['candidate_name']); ?>
                                            </h4>
                                            <div class="flex items-center mt-1">
                                                <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">
                                                    <?php echo htmlspecialchars($selectedVotes[$position['position_name']]['college_name']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Back Button -->
            <div class="flex justify-center mt-8">
                <button onclick="navigateTo('countdown.php')" 
                        class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg flex items-center justify-center transition duration-300 transform hover:-translate-y-1">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Countdown
                </button>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script>
        function navigateTo(page) {
            window.location.href = page;
        }
    </script>
</body>
</html>