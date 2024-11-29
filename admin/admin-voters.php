<?php
session_start();
if (!isset($_SESSION['is_comelec_logged_in']) || !$_SESSION['is_comelec_logged_in']) {
    header('Location: ../loginascomelec.php');
    exit();
}

require_once '../connect.php';

// Fetch students from the database
$stmt = $pdo->query("
    SELECT students.*, colleges.college_name 
    FROM students 
    LEFT JOIN colleges ON students.college_id = colleges.college_id
");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch colleges from the database, excluding "Abstain"
$collegesStmt = $pdo->query("SELECT * FROM colleges WHERE college_name != 'Abstain'");
$colleges = $collegesStmt->fetchAll(PDO::FETCH_ASSOC);

// Start output buffering
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comelec - Voters Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../asset/susglogo.png" type="image/png">

    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
        }

        .content {
            flex-grow: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
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

        .mngment-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
            font-size: 17px;
            text-align: left;
        }

        .mngment-table tr {
            border-bottom: 1px solid #cfcfcf;
        }

        .mngment-table th, .mngment-table td {
            padding: 10px;
        }

        /* Buttons */
        .add-btn {
            padding: 8px 12px;
            border: none;
            cursor: pointer;
            font-size: 17px;
            border-radius: 4px;
            background-color: #b82323; 
            color: white;
            width: 200px;
            float: right;
            margin-bottom: 12px;
        }

        .edit-btn, .delete-btn {
            padding: 8px 12px;
            border: none;
            cursor: pointer;
            font-size: 15px;
            border-radius: 4px;
        }

        .edit-btn {
            background-color: #4CAF50; 
            color: white;
        }

        .delete-btn {
            background-color: #f44336; 
            color: white;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            border-radius: 10px;
            position: relative;
        }

        .close {
            color: #aaa;
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: black;
        }

        .modal-form {
            display: flex;
            flex-direction: column;
        }

        .modal-form label {
            margin-top: 15px;
            font-size: 16px;
        }

        .modal-form input, .modal-form select {
            padding: 10px;
            margin-top: 5px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .modal-form button {
            margin-top: 20px;
            padding: 12px 20px;
            font-size: 17px;
            background-color: #b82323;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        @media screen and (max-width: 768px) {
            .modal-content {
                width: 90%;
                padding: 20px;
            }
        }
    </style>
    <script src="../script/adminload.js" type="module" defer></script>
</head>
<body>

    <!-- Include Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Section -->
    <main>
        <div class="content">
            <h1>Voters</h1>
            <button class="add-btn" id="openModalBtn">Add New Student</button>
            <div class="mngment-box">
                <table class="mngment-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>College</th>
                            <th>Has Voted</th>
                            <th>Edit</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['college_name']); ?></td>
                            <td><?php echo $student['has_voted'] ? 'Yes' : 'No'; ?></td>
                            <td><button class="edit-btn" data-student='<?php echo json_encode($student); ?>'>Edit</button></td>
                            <td><button class="delete-btn" data-student-id="<?php echo $student['student_id']; ?>">Delete</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Structure -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Add New Student</h2>
            <form class="modal-form" id="studentForm" method="POST" action="create_student.php">
                <input type="hidden" id="studentFormId" name="studentFormId">
                <label for="studentId">Student ID:</label>
                <input type="text" id="studentId" name="studentId" required>

                <label for="studentName">Student Name:</label>
                <input type="text" id="studentName" name="studentName" required>

                <label for="college">College/Department:</label>
                <select id="college" name="college" required>
                    <option value="">Select College</option>
                    <?php foreach ($colleges as $college): ?>
                        <option value="<?php echo htmlspecialchars($college['college_id']); ?>">
                            <?php echo htmlspecialchars($college['college_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="hasVoted">Has Voted:</label>
                <select id="hasVoted" name="hasVoted" required>
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                </select>

                <button type="submit">Submit</button>
            </form>
        </div>
    </div>

    <script>
        // Modal functionality
        const modal = document.getElementById("myModal");
        const openModalBtn = document.getElementById("openModalBtn");
        const closeBtns = document.querySelectorAll(".close");

        openModalBtn.addEventListener("click", () => {
            document.getElementById('studentForm').action = 'create_student.php';
            document.getElementById('studentFormId').value = '';
            document.getElementById('studentId').value = '';
            document.getElementById('studentName').value = '';
            document.getElementById('college').value = '';
            document.getElementById('hasVoted').value = '0';
            modal.style.display = "block";
        });
        closeBtns.forEach(btn => btn.addEventListener("click", () => modal.style.display = "none"));
        window.addEventListener("click", (event) => {
            if (event.target == modal) modal.style.display = "none";
        });

        // Edit button functionality
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const student = JSON.parse(this.getAttribute('data-student'));
                document.getElementById('studentForm').action = 'edit_student.php';
                document.getElementById('studentFormId').value = student.student_id;
                document.getElementById('studentId').value = student.student_id;
                document.getElementById('studentName').value = student.student_name;
                document.getElementById('college').value = student.college_id;
                document.getElementById('hasVoted').value = student.has_voted;
                modal.style.display = "block";
            });
        });

        // Delete button functionality
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const studentId = this.getAttribute('data-student-id');
                if (confirm('Are you sure you want to delete this student?')) {
                    fetch('delete_student.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ studentId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Failed to delete student. Please try again.');
                        }
                    })
                    .catch(error => console.error('Error:', error));
                }
            });
        });
    </script>
</body>
</html>