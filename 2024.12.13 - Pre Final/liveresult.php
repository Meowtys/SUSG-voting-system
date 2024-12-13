<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: loginasvoter.php');
    exit();
}

require_once 'connect.php';

// Fetch all positions
$positionsStmt = $pdo->query("SELECT * FROM positions ORDER BY position_id");
$positions = $positionsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all candidates with their positions, excluding abstain
$candidatesStmt = $pdo->query("
    SELECT c.*, p.position_name, 
           (SELECT COUNT(*) FROM votes v WHERE v.candidate_id = c.candidate_id) as vote_count
    FROM candidates c
    JOIN positions p ON c.position_id = p.position_id
    WHERE c.candidate_name != 'ABSTAIN'
    ORDER BY p.position_id, vote_count DESC
");
$candidates = $candidatesStmt->fetchAll(PDO::FETCH_ASSOC);

// Group candidates by position
$candidatesByPosition = [];
foreach ($candidates as $candidate) {
    $candidatesByPosition[$candidate['position_id']][] = $candidate;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUSG Election System - Live Results</title>
    <link rel="icon" href="asset/susglogo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <?php include 'header.php'; ?>

    <div class="container mx-auto px-4 py-8 mt-16">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Live Election Results</h1>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($positions as $position): ?>
                    <div class="bg-white rounded-lg shadow-md p-4">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4 text-center">
                            <?= htmlspecialchars($position['position_name']) ?>
                        </h2>
                        
                        <div class="space-y-4">
                            <?php if (isset($candidatesByPosition[$position['position_id']])): ?>
                                <div class="space-y-2">
                                    <?php foreach ($candidatesByPosition[$position['position_id']] as $candidate): ?>
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                            <div class="flex items-center space-x-3">
                                                <img src="<?= htmlspecialchars($candidate['candidate_image']) ?>" 
                                                     alt="<?= htmlspecialchars($candidate['candidate_name']) ?>" 
                                                     class="w-12 h-12 rounded-full object-cover border-2 border-red-600">
                                                <div>
                                                    <p class="font-medium text-gray-800">
                                                        <?= htmlspecialchars($candidate['candidate_name']) ?>
                                                    </p>
                                                    <p class="text-sm text-red-600">
                                                        <?= htmlspecialchars($candidate['candidate_party']) ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="text-lg font-bold text-red-600">
                                                <?= $candidate['vote_count'] ?>
                                                <span class="text-sm font-normal">votes</span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-gray-500 text-center">No candidates found</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // Auto-refresh every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>