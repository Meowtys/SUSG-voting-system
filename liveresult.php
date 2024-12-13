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
    <title>Live Election Results - SUSG</title>
    <link rel="icon" href="asset/susglogo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        * {
            font-family: 'Poppins', sans-serif;
        }
        .gradient-background {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
        }
        .progress-bar {
            transition: width 1s ease-in-out;
        }
        .candidate-card {
            animation: fadeIn 0.5s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .live-indicator {
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .scroll-fade {
            mask-image: linear-gradient(to bottom, black 80%, transparent 100%);
            -webkit-mask-image: linear-gradient(to bottom, black 80%, transparent 100%);
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'header.php'; ?>

    <div class="container mx-auto px-4 py-8 mt-16">
        <!-- Election Header -->
        <div class="gradient-background rounded-2xl shadow-2xl p-8 mb-8 text-white">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-4xl font-bold"><?php echo htmlspecialchars($currentElection['election_name']); ?></h1>
                <div class="flex items-center space-x-2">
                    <span class="live-indicator flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-3 w-3 rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                    </span>
                    <span class="text-sm font-medium">LIVE RESULTS</span>
                </div>
            </div>
            <p class="text-gray-100">
                <?php echo (new DateTime($currentElection['start_datetime']))->format('F j, Y g:i A'); ?> - 
                <?php echo (new DateTime($currentElection['end_datetime']))->format('F j, Y g:i A'); ?>
            </p>
        </div>

        <!-- Results Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($positions as $position): ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover">
                    <!-- Position Header -->
                    <div class="gradient-background p-4">
                        <h2 class="text-xl font-bold text-white">
                            <?= htmlspecialchars($position['position_name']) ?>
                        </h2>
                    </div>

                    <!-- Candidates List -->
                    <div class="p-4 space-y-4 scroll-fade max-h-[500px] overflow-y-auto">
                        <?php if (isset($candidatesByPosition[$position['position_id']])): ?>
                            <?php foreach ($candidatesByPosition[$position['position_id']] as $index => $candidate): ?>
                                <div class="candidate-card bg-gray-50 rounded-lg p-4 transition-all duration-300 hover:bg-gray-100">
                                    <div class="flex items-center space-x-4">
                                        <!-- Candidate Image and Crown -->
                                        <div class="relative">
                                            <img src="<?= htmlspecialchars($candidate['candidate_image']) ?>" 
                                                 alt="<?= htmlspecialchars($candidate['candidate_name']) ?>" 
                                                 class="w-16 h-16 rounded-lg object-cover border-2 <?= $index === 0 ? 'border-yellow-400' : 'border-gray-200' ?>">
                                            <?php if ($index === 0 && $candidate['vote_count'] > 0): ?>
                                                <span class="absolute -top-2 -right-2 text-2xl">👑</span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Candidate Details -->
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-800">
                                                <?= htmlspecialchars($candidate['candidate_name']) ?>
                                            </h3>
                                            <div class="flex flex-wrap gap-2 mt-1">
                                                <?php if ($candidate['candidate_party']): ?>
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                                        <?php
                                                        echo match($candidate['candidate_party']) {
                                                            'CAUSE' => 'bg-green-100 text-green-800',
                                                            'SURE' => 'bg-blue-100 text-blue-800',
                                                            default => 'bg-gray-100 text-gray-800'
                                                        };
                                                        ?>">
                                                        <?= htmlspecialchars($candidate['candidate_party']) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">
                                                    <?= htmlspecialchars($candidate['college_name']) ?>
                                                </span>
                                            </div>

                                            <!-- Progress Bar -->
                                            <div class="mt-3">
                                                <div class="flex justify-between text-sm text-gray-600 mb-1">
                                                    <span><?= $candidate['vote_count'] ?> votes</span>
                                                    <span><?= $candidate['percentage'] ?>%</span>
                                                </div>
                                                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                                    <div class="progress-bar h-full rounded-full 
                                                        <?php
                                                        echo match(true) {
                                                            $index === 0 => 'bg-gradient-to-r from-yellow-400 to-yellow-600',
                                                            $index === 1 => 'bg-gradient-to-r from-gray-400 to-gray-600',
                                                            $index === 2 => 'bg-gradient-to-r from-amber-600 to-amber-800',
                                                            default => 'bg-gradient-to-r from-red-400 to-red-600'
                                                        };
                                                        ?>"
                                                        style="width: <?= $candidate['percentage'] ?>%">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-user-slash text-4xl mb-3"></i>
                                <p>No candidates found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // Auto-refresh functionality
        const refreshInterval = 30000; // 30 seconds
        let timeLeft = refreshInterval / 1000;

        function updateTimer() {
            const timerElement = document.createElement('div');
            timerElement.className = 'fixed bottom-4 right-4 bg-red-600 text-white px-4 py-2 rounded-full shadow-lg z-50';
            timerElement.innerHTML = `Refreshing in ${timeLeft}s`;
            document.body.appendChild(timerElement);

            const timer = setInterval(() => {
                timeLeft--;
                timerElement.innerHTML = `Refreshing in ${timeLeft}s`;
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    location.reload();
                }
            }, 1000);
        }

        // Initialize auto-refresh
        setTimeout(() => location.reload(), refreshInterval);
        updateTimer();

        // Add smooth animations for progress bars
        document.addEventListener('DOMContentLoaded', () => {
            const progressBars = document.querySelectorAll('.progress-bar');
            progressBars.forEach(bar => {
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = bar.getAttribute('data-width') + '%';
                }, 100);
            });
        });
    </script>
</body>
</html>