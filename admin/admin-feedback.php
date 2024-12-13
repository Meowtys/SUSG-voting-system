<?php
session_start();
if (!isset($_SESSION['is_comelec_logged_in']) || !$_SESSION['is_comelec_logged_in']) {
    header('Location: ../loginascomelec.php');
    exit();
}

require_once '../connect.php';

// Fetch feedbacks from the database
$stmt = $pdo->prepare("SELECT f.*, s.student_name FROM feedbacks f JOIN students s ON f.student_id = s.student_id ORDER BY f.feedback_timestamp DESC");
$stmt->execute();
$feedbacks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comelec - Voter's Feedback</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../asset/susglogo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Include Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Section -->
    <main class="ml-64 p-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">Voter's Feedback</h1>
                <div class="text-sm text-gray-600">
                    Total Feedbacks: <?php echo count($feedbacks); ?>
                </div>
            </div>

            <!-- Feedback Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <?php
                $totalRating = 0;
                $ratings = array_column($feedbacks, 'experience');
                $avgRating = count($ratings) > 0 ? array_sum($ratings) / count($ratings) : 0;
                $positiveCount = count(array_filter($ratings, function($r) { return $r >= 4; }));
                $negativeCount = count(array_filter($ratings, function($r) { return $r <= 2; }));
                ?>
                <!-- Average Rating Card -->
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="text-sm font-medium text-gray-500 mb-1">Average Rating</div>
                    <div class="text-2xl font-bold text-gray-900"><?php echo number_format($avgRating, 1); ?>/5.0</div>
                </div>
                <!-- Positive Feedback Card -->
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="text-sm font-medium text-gray-500 mb-1">Positive Feedback</div>
                    <div class="text-2xl font-bold text-green-600"><?php echo $positiveCount; ?></div>
                </div>
                <!-- Negative Feedback Card -->
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="text-sm font-medium text-gray-500 mb-1">Needs Improvement</div>
                    <div class="text-2xl font-bold text-red-600"><?php echo $negativeCount; ?></div>
                </div>
            </div>

            <!-- Feedback Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full whitespace-nowrap">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comment</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted on</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($feedbacks as $feedback): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($feedback['student_name']); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php 
                                    $ratingClass = '';
                                    if ($feedback['experience'] >= 4) {
                                        $ratingClass = 'bg-green-100 text-green-800';
                                    } elseif ($feedback['experience'] >= 2) {
                                        $ratingClass = 'bg-yellow-100 text-yellow-800';
                                    } else {
                                        $ratingClass = 'bg-red-100 text-red-800';
                                    }
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $ratingClass; ?>">
                                        <?php echo $feedback['experience']; ?>/5
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 max-w-md truncate"><?php echo htmlspecialchars($feedback['suggestion']); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-500"><?php echo date('M d, Y \a\t h:i A', strtotime($feedback['feedback_timestamp'])); ?></div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button class="remove-btn inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-full text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" 
                                            data-id="<?php echo $feedback['feedback_id']; ?>">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <script>
        // JavaScript for removing rows
        document.addEventListener('DOMContentLoaded', function () {
            const removeButtons = document.querySelectorAll('.remove-btn');
            removeButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const feedbackId = this.getAttribute('data-id');
                    if (confirm('Do you really want to delete this feedback?')) {
                        fetch(`remove-feedback.php?id=${feedbackId}`, {
                            method: 'GET'
                        }).then(response => response.json())
                          .then(data => {
                              if (data.success) {
                                  const row = this.parentNode.parentNode;
                                  row.parentNode.removeChild(row);
                              } else {
                                  alert('Failed to remove feedback.');
                              }
                          });
                    }
                });
            });
        });

        // Sidebar active link handling
        document.addEventListener('DOMContentLoaded', function () {
            const sidebarLinks = document.querySelectorAll('.sidebar a');

            // Remove 'active' class from all links
            sidebarLinks.forEach(link => link.classList.remove('active'));

            // Set 'active' class based on current URL
            sidebarLinks.forEach(link => {
                if (link.href === window.location.href) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>