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
<body>

    <!-- Include Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Section -->
    <main>
        <div class="flex-grow p-10 transition-all duration-300 overflow-y-auto">
            <h1 class="text-3xl mb-8 text-left text-gray-800">Feedback</h1>
            <div class="bg-white rounded-lg p-8 w-[95%] max-w-[3000px] mb-10 text-center shadow-md">
                <table class="w-[90%] mx-auto text-left">
                    <thead>
                        <tr class="border-b border-gray-300">
                            <th class="text-lg py-4 px-2">Author</th>
                            <th class="text-lg py-4 px-2">Rating</th>
                            <th class="text-lg py-4 px-2">Comment</th>
                            <th class="text-lg py-4 px-2">Submitted on</th>
                            <th class="text-lg py-4 px-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feedbacks as $feedback): ?>
                        <tr class="border-b border-gray-200">
                            <td class="py-3 px-2 text-gray-700"><?php echo htmlspecialchars($feedback['student_name']); ?></td>
                            <td class="py-3 px-2">
                                <?php 
                                    $ratingClass = '';
                                    if ($feedback['experience'] >= 4) {
                                        $ratingClass = 'bg-green-100 text-green-800';
                                    } elseif ($feedback['experience'] >= 2) {
                                        $ratingClass = 'bg-orange-100 text-orange-800';
                                    } else {
                                        $ratingClass = 'bg-red-100 text-red-800';
                                    }
                                ?>
                                <span class="font-bold px-3 py-1 rounded-md inline-block <?php echo $ratingClass; ?>">
                                    <?php echo $feedback['experience']; ?>/5
                                </span>
                            </td>
                            <td class="py-3 px-2 text-gray-700 text-sm"><?php echo htmlspecialchars($feedback['suggestion']); ?></td>
                            <td class="py-3 px-2 text-gray-700"><?php echo date('m/d/Y \a\t h:ia', strtotime($feedback['feedback_timestamp'])); ?></td>
                            <td class="py-3 px-2">
                                <button class="remove-btn bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm" 
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