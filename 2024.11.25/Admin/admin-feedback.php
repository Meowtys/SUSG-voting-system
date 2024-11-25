<?php
// Include database connection
$host = "localhost";
$username = "root";
$password = "";
$dbname = "susg_project";

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle feedback removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback_id'])) {
    $feedback_id = intval($_POST['feedback_id']);
    $delete_sql = "DELETE FROM feedback WHERE feedback_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $feedback_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch feedback from the database
$sql = "SELECT 
            f.feedback_id, 
            s.stud_name AS author, 
            f.comment, 
            f.rating, 
            f.submitted_at 
        FROM feedback f 
        JOIN students s ON f.user_id = s.student_id 
        ORDER BY f.submitted_at DESC";
$result = $conn->query($sql);

$conn->close();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
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
        /* Retain the provided styles */
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

        .mngment-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
            font-size: 16px;
            flex-direction: column;
            text-align: left;
        }

        .mngment-table th {
            font-size: 18px;
            padding: 15px 5px;
            background-color: #c41f1f;
            color: white;
        }

        .mngment-table td {
            padding: 10px; 
            color: #4f4f4f;
        }

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
                            <th>Rating</th>
                            <th>Submitted on</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['author']); ?></td>
                                    <td><?php echo htmlspecialchars($row['comment']); ?></td>
                                    <td><?php echo htmlspecialchars($row['rating']); ?></td>
                                    <td><?php echo htmlspecialchars(date('m/d/Y \a\t h:i A', strtotime($row['submitted_at']))); ?></td>
                                    <td>
                                        <form method="POST" action="admin-feedback.php" style="margin: 0;">
                                            <input type="hidden" name="feedback_id" value="<?php echo $row['feedback_id']; ?>">
                                            <button type="submit" class="remove-btn">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #666;">No feedback available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>