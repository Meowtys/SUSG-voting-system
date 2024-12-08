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
    <style>
        .content {
            flex-grow: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            overflow-y: auto;
        }
    
        h1 {
            font-size: 32px;
            margin-bottom: 30px;
            text-align: left;
            color: #333;
        }

        /* Management CSS */
        .mngment-box {
            background-color: #fff;
            border-radius: 10px;
            padding: 30px 20px;
            width: 95%;
            max-width: 3000px;
            margin-bottom: 40px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .mngment-title {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
        }

        .divider {
            border: 1px solid #e0e0e0;
            width: 90%;
            margin: 20px auto 30px auto;
        }

        .mngment-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
            width: 90%;
            font-size: 16px;
            flex-direction: column;
            text-align: left;
        }

        .comment {
            font-size: 15px;
            padding: 10px; 
        }

        .mngment-table tr {
            border-bottom: 1px solid #cfcfcf;
        }

        .mngment-table th {
            font-size: 18px;
            padding: 15px 5px;
        }

        .mngment-table td {
            padding: 10px; 
            color: #4f4f4f;
        }

        /* Buttons */
        .remove-btn {
            padding: 8px 12px;
            border: none;
            cursor: pointer;
            font-size: 15px;
            border-radius: 4px;
            background-color: #f44336; 
            color: white;
        }

        .remove-btn:hover {
            opacity: 0.8;
        }
</style>
</head>
<body>

    <!-- Include Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Section -->
    <main>
        <div class="content">
            <h1>Feedback</h1>
            <div class="mngment-box">
                <table class="mngment-table">
                    <thead>
                        <tr>
                            <th>Author</th>
                            <th>Comment</th>
                            <th>Submitted on</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feedbacks as $feedback): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($feedback['student_name']); ?></td>
                            <td class="comment"><?php echo htmlspecialchars($feedback['suggestion']); ?></td>
                            <td><?php echo date('m/d/Y \a\t h:ia', strtotime($feedback['feedback_timestamp'])); ?></td>
                            <td><button class="remove-btn" data-id="<?php echo $feedback['feedback_id']; ?>">Remove</button></td>
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