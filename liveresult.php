<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: loginasvoter.php');
    exit();
}

require_once 'connect.php';

// Get current election
$stmt = $pdo->query("
    SELECT * FROM elections 
    WHERE is_current = 1 
    LIMIT 1
");
$currentElection = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentElection) {
    die("No active election found.");
}

// Get student's college
$collegeStmt = $pdo->prepare("
    SELECT c.college_id, c.college_name 
    FROM colleges c 
    JOIN students s ON c.college_id = s.college_id 
    WHERE s.student_id = ?
");
$collegeStmt->execute([$_SESSION['user']['student_id']]);
$userCollege = $collegeStmt->fetch(PDO::FETCH_ASSOC);

// Fetch positions
$positionsStmt = $pdo->query("SELECT * FROM positions ORDER BY position_id");
$positions = $positionsStmt->fetchAll(PDO::FETCH_ASSOC);

// Modified query to get candidates with vote counts for current election, excluding abstain
// and filtering representatives by college
$candidatesStmt = $pdo->prepare("
    SELECT 
        c.*, 
        p.position_name,
        pa.party_name as candidate_party,
        col.college_name,
        (SELECT COUNT(*) FROM votes v WHERE v.candidate_id = c.candidate_id AND v.election_id = ?) as vote_count,
        (SELECT COUNT(*) FROM votes v2 
         JOIN candidates c2 ON v2.candidate_id = c2.candidate_id 
         WHERE c2.position_id = c.position_id 
         AND v2.election_id = ?
         AND (p.position_name != 'Representative' OR 
             (p.position_name = 'Representative' AND c2.college_id = ?))
        ) as total_position_votes
    FROM candidates c
    JOIN positions p ON c.position_id = p.position_id
    LEFT JOIN parties pa ON c.party_id = pa.party_id
    LEFT JOIN colleges col ON c.college_id = col.college_id
    WHERE c.election_id = ? 
    AND c.candidate_name != 'Abstain'
    AND (p.position_name != 'Representative' OR 
        (p.position_name = 'Representative' AND c.college_id = ?))
    ORDER BY p.position_id, vote_count DESC
");

$candidatesStmt->execute([
    $currentElection['election_id'],
    $currentElection['election_id'],
    $userCollege['college_id'],
    $currentElection['election_id'],
    $userCollege['college_id']
]);
$candidates = $candidatesStmt->fetchAll(PDO::FETCH_ASSOC);

// Group candidates by position
$candidatesByPosition = [];
foreach ($candidates as $candidate) {
    if (!isset($candidatesByPosition[$candidate['position_id']])) {
        $candidatesByPosition[$candidate['position_id']] = [];
    }
    // Calculate percentage
    $totalVotes = $candidate['total_position_votes'] > 0 ? $candidate['total_position_votes'] : 1;
    $candidate['percentage'] = number_format(($candidate['vote_count'] / $totalVotes) * 100, 1);
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
            padding: 0.25rem 0.75rem;
            border-radius: 0.75rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            min-width: 80px;
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
            
            <!-- Election Info -->
            <div class="bg-red-50 rounded-xl p-6 mb-8">
                <h2 class="text-2xl font-bold text-red-800 mb-2">
                    <?php echo htmlspecialchars($currentElection['election_name']); ?>
                </h2>
                <p class="text-red-600">
                    <?php echo (new DateTime($currentElection['start_datetime']))->format('F j, Y - g:i A'); ?>
                    to
                    <?php echo (new DateTime($currentElection['end_datetime']))->format('F j, Y - g:i A'); ?>
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($positions as $position): ?>
                    <div class="position-card p-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center border-b pb-4">
                            <?= htmlspecialchars($position['position_name']) ?>
                        </h2>
                        
                        <div class="space-y-6">
                            <?php if (isset($candidatesByPosition[$position['position_id']])): ?>
                                <?php foreach ($candidatesByPosition[$position['position_id']] as $index => $candidate): ?>
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
                                                    <div class="flex flex-wrap gap-2 mt-1">
                                                        <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">
                                                            <?= htmlspecialchars($candidate['college_name']) ?>
                                                        </span>
                                                        <?php if ($candidate['candidate_party']): ?>
                                                            <span class="px-2 py-1 <?= 
                                                                $candidate['candidate_party'] === 'CAUSE' ? 'bg-green-100 text-green-800' :
                                                                ($candidate['candidate_party'] === 'SURE' ? 'bg-blue-100 text-blue-800' :
                                                                'bg-red-100 text-red-800')
                                                            ?> text-xs font-medium rounded-full">
                                                                <?= htmlspecialchars($candidate['candidate_party']) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="vote-counter">
                                                <span class="text-xl font-bold text-white"><?= $candidate['vote_count'] ?></span>
                                                <span class="text-xs text-white opacity-90">votes</span>
                                            </div>
                                        </div>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?= $candidate['percentage'] ?>%"></div>
                                        </div>
                                        <div class="text-right text-sm text-gray-500 mt-1">
                                            <?= $candidate['percentage'] ?>%
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

        // Add a visual countdown timer
        function updateCountdown() {
            let countdown = 30;
            const timerDiv = document.createElement('div');
            timerDiv.className = 'fixed bottom-4 right-4 bg-red-600 text-white px-4 py-2 rounded-full shadow-lg';
            timerDiv.innerHTML = `Refreshing in: ${countdown}s`;
            document.body.appendChild(timerDiv);

            const timer = setInterval(() => {
                countdown--;
                timerDiv.innerHTML = `Refreshing in: ${countdown}s`;
                if (countdown <= 0) {
                    clearInterval(timer);
                }
            }, 1000);
        }

        // Initialize countdown on page load
        document.addEventListener('DOMContentLoaded', updateCountdown);
    </script>
</body>
</html>