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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../asset/susglogo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.tailwind.min.js"></script>
    <style>
        /* Custom DataTables Styling */
        .dataTables_wrapper {
            padding: 1rem;
        }
        
        table.dataTable thead .sorting,
        table.dataTable thead .sorting_asc,
        table.dataTable thead .sorting_desc {
            position: relative;
            background-image: none !important;
        }

        table.dataTable thead .sorting:after,
        table.dataTable thead .sorting_asc:after,
        table.dataTable thead .sorting_desc:after {
            position: absolute;
            right: 8px;
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            font-size: 0.8em;
        }

        table.dataTable thead .sorting:after {
            content: "\f0dc";
            color: #ddd;
        }

        table.dataTable thead .sorting_asc:after {
            content: "\f0de";
            color: #666;
        }

        table.dataTable thead .sorting_desc:after {
            content: "\f0dd";
            color: #666;
        }

        .dataTables_info {
            margin-top: 1rem;
            padding-top: 0.5rem !important;
            color: #6b7280;
        }

        .dataTables_paginate {
            margin-top: 1rem !important;
            padding-top: 0.5rem !important;
        }

        .dataTables_wrapper .dataTables_length, 
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }
    </style>
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <?php
                $ratings = array_column($feedbacks, 'experience');
                $avgRating = count($ratings) > 0 ? array_sum($ratings) / count($ratings) : 0;
                
                // Read sentiment cache
                require_once dirname(__FILE__) . '/../cache/SentimentCache.php';
                $sentimentCache = new SentimentCache();
                $overallSentiment = $sentimentCache->get('overall_sentiment');
                $sentimentScore = $overallSentiment ? $overallSentiment['score'] : 0;

                ?>
                <!-- Average Rating Card -->
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="text-sm font-medium text-gray-500 mb-1">Average Rating</div>
                    <div class="text-2xl font-bold text-gray-900"><?php echo number_format($avgRating, 1); ?>/5.0</div>
                    <div class="text-sm text-gray-600 mt-2">Based on all feedback</div>
                </div>
                <!-- Latest Activity Card -->
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="text-sm font-medium text-gray-500 mb-1">Latest Activity</div>
                    <div class="text-sm text-gray-600 mt-2">
                        <?php
                        if (count($feedbacks) > 0) {
                            $latestFeedback = $feedbacks[0];
                            $latestFeedbackDate = date('M d, Y \a\t h:i A', strtotime($latestFeedback['feedback_timestamp']));
                            $latestFeedbackAuthor = htmlspecialchars($latestFeedback['student_name']);
                            $latestFeedbackComment = htmlspecialchars($latestFeedback['suggestion']);
                            $latestFeedbackRating = $latestFeedback['experience'];

                            $ratingClass = '';
                            if ($latestFeedbackRating >= 4) {
                                $ratingClass = 'bg-green-100 text-green-800';
                            } elseif ($latestFeedbackRating >= 2) {
                                $ratingClass = 'bg-yellow-100 text-yellow-800';
                            } else {
                                $ratingClass = 'bg-red-100 text-red-800';
                            }

                            echo "<div class='text-sm font-medium text-gray-900'>Author: $latestFeedbackAuthor</div>";
                            echo "<div class='text-sm text-gray-600'>Comment: $latestFeedbackComment</div>";
                            echo "<div class='text-sm text-gray-600'>Rating: <span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium $ratingClass'>$latestFeedbackRating/5</span></div>";
                            echo "<div class='text-sm text-gray-500'>Submitted on: $latestFeedbackDate</div>";
                        } else {
                            echo "<div class='text-sm text-gray-600'>No feedback available.</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Feedback Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden p-4">
                <div class="overflow-x-auto">
                    <table id="feedbackTable" class="w-full whitespace-nowrap">
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
                                <td class="px-6 py-4" data-order="<?php echo $feedback['experience']; ?>">
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
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($feedback['suggestion']); ?></div>
                                </td>
                                <td class="px-6 py-4" data-order="<?php echo strtotime($feedback['feedback_timestamp']); ?>">
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
        // Initialize DataTable
        $(document).ready(function() {
            $('#feedbackTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[3, 'desc']], // Sort by date by default
                columnDefs: [
                    { orderable: false, targets: 4 }, // Disable sorting for action column
                    { 
                        targets: 2,
                        render: function(data, type, row) {
                            if (type === 'display' && data.length > 100) {
                                return data.substr(0, 100) + '...';
                            }
                            return data;
                        }
                    }
                ],
                dom: '<"flex flex-col sm:flex-row justify-between items-center"lf><"overflow-x-auto"rt><"flex flex-col sm:flex-row justify-between items-center"ip>',
                language: {
                    search: "Search feedback:",
                    lengthMenu: "Show _MENU_ entries per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ feedback entries",
                },
                drawCallback: function() {
                    // Re-apply Tailwind classes to DataTables elements
                    $('.dataTables_length select').addClass('rounded-lg border-gray-300 mx-2');
                    $('.dataTables_filter input').addClass('rounded-lg border-gray-300 ml-2');
                    $('.dataTables_paginate .paginate_button').addClass('px-3 py-1 mx-1 rounded-lg hover:bg-gray-100');
                    $('.dataTables_paginate .paginate_button.current').addClass('bg-blue-500 text-white hover:bg-blue-600');
                    $('.dataTables_info').addClass('text-sm text-gray-600');
                }
            });
        });

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