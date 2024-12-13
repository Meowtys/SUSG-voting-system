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
    SELECT 
        c.*, 
        p.position_name,
        pa.party_name as candidate_party,
        (SELECT COUNT(*) FROM votes v WHERE v.candidate_id = c.candidate_id) as vote_count
    FROM candidates c
    JOIN positions p ON c.position_id = p.position_id
    LEFT JOIN parties pa ON c.party_id = pa.party_id
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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        * {
            font-family: 'Poppins', sans-serif;
        }
        .vote-counter {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            padding: 0.5rem 1.25rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            min-width: 100px;
            text-align: center;
        }
        .position-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .position-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .progress-bar {
            height: 8px;
            border-radius: 4px;
            background: #f3f4f6;
            overflow: hidden;
            margin-top: 0.5rem;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%);
            transition: width 0.5s ease-out;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'header.php'; ?>

    <div class="container mx-auto px-4 py-8 mt-16">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-8 text-center">Live Election Results</h1>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($positions as $position): ?>
                    <div class="position-card p-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center border-b pb-4">
                            <?= htmlspecialchars($position['position_name']) ?>
                        </h2>
                        
                        <div class="space-y-6">
                            <?php if (isset($candidatesByPosition[$position['position_id']])): ?>
                                <?php 
                                $candidates = $candidatesByPosition[$position['position_id']];
                                $totalVotes = array_sum(array_column($candidates, 'vote_count'));
                                foreach ($candidates as $index => $candidate): 
                                    $percentage = $totalVotes > 0 ? ($candidate['vote_count'] / $totalVotes) * 100 : 0;
                                ?>
                                    <div class="transform transition-all duration-300">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center space-x-3">
                                                <div class="relative">
                                                    <img src="<?= htmlspecialchars($candidate['candidate_image']) ?>" 
                                                         alt="<?= htmlspecialchars($candidate['candidate_name']) ?>" 
                                                         class="w-12 h-12 rounded-lg object-cover border-2 border-red-600">
                                                    <?php if ($index === 0 && $candidate['vote_count'] > 0): ?>
                                                        <span class="absolute -top-2 -right-2 text-xl">👑</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-gray-800">
                                                        <?= htmlspecialchars($candidate['candidate_name']) ?>
                                                    </p>
                                                    <p class="text-sm text-red-600">
                                                        <?= htmlspecialchars($candidate['candidate_party'] ?? 'Independent') ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="vote-counter">
                                                <span class="text-xl font-bold text-white"><?= $candidate['vote_count'] ?></span>
                                                <span class="text-sm text-white opacity-90">votes</span>
                                            </div>
                                        </div>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?= number_format($percentage, 1) ?>%"></div>
                                        </div>
                                        <div class="text-right text-sm text-gray-500 mt-1">
                                            <?= number_format($percentage, 1) ?>%
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-gray-500 text-center py-4">No candidates found</p>
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