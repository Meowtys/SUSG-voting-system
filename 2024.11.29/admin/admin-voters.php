<?php
session_start();
if (!isset($_SESSION['is_comelec_logged_in']) || !$_SESSION['is_comelec_logged_in']) {
    header('Location: ../start.php');
    exit();
}

require_once '../connect.php'; // Ensure this file establishes a valid PDO connection

// Fetch data from students table
$stmt = $pdo->query("
    SELECT students.student_id, students.student_name, colleges.college_name, students.has_voted 
    FROM students 
    LEFT JOIN colleges ON students.college_id = colleges.college_id
");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Output buffering
ob_start();

// Fetch colleges for the dropdown
$colleges = $pdo->query("SELECT college_id, college_name FROM colleges")->fetchAll(PDO::FETCH_ASSOC);
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
        .content {
            flex-grow: 1;
            padding: 40px;
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
            width: 90%;
            border-collapse: collapse;
            margin: 0 auto;
            font-size: 17px;
            text-align: left;
        }

        .mngment-table th, .mngment-table td {
            padding: 10px;
            border-bottom: 1px solid #cfcfcf;
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

        .edit-btn:hover, .delete-btn:hover {
            opacity: 0.8;
        }

        .sidebar .active {
            background-color: white;
            color: #b82323;
        }

        .add-btn {
            display: inline-block; /* Ensure the button is treated like a block under the title */
            margin: 12px 0; /* Add spacing between the title and the button */
            margin-top: -2px;
            padding: 8px 12px; /* Adjust padding for a consistent size */
            background-color: #b82323; /* Button background color */
            color: #fff; /* Button text color */
            font-size: 17px; /* Font size for better readability */
            border: none; /* Remove default browser border */
            border-radius: 5px; /* Rounded corners */
            cursor: pointer; /* Change cursor to pointer on hover */
            text-align: center; /* Center the text */
            width: 200px;
        }
        .add-btn:hover {
            background-color: #a71f1f; /* Slightly darker red for hover effect */
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
</head>
<body>

<!-- Include Sidebar -->
<?php include 'sidebar.php'; ?>

<!-- Main Section -->
<main>
    <div class="content">
        <h1>Voters</h1>
        <!-- Add Button -->
        <button class="add-btn" id="openModalBtn">Create New Voter</button>
        <div class="mngment-box">
            <table class="mngment-table">
                <thead>
                    <tr>
                        <th>SU ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Has Voted</th>
                        <th>Edit</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody id="votersTableBody">
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['student_id']) ?></td>
                            <td><?= htmlspecialchars($student['student_name']) ?></td>
                            <td><?= htmlspecialchars($student['college_name']) ?></td>
                            <td><?= $student['has_voted'] ? 'Yes' : 'No' ?></td>
                            <td><button class="edit-btn" onclick="editVoter('<?= htmlspecialchars($student['student_id']) ?>')">Edit</button></td>
                            <td><button class="delete-btn" onclick="deleteVoter('<?= htmlspecialchars($student['student_id']) ?>')">Delete</button></td>
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
        <h2>Create New Voter</h2>
        <form class="modal-form" id="newVoterForm">
            <label for="voterId">Student ID:</label>
            <input type="text" id="voterId" name="voterId" required>

            <label for="voterName">Name:</label>
            <input type="text" id="voterName" name="voterName" required>

            <label for="department">Department:</label>
            <select id="department" name="department" required>
                <?php
                $colleges = $pdo->query("SELECT college_id, college_name FROM colleges")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($colleges as $college):
                ?>
                    <option value="<?= $college['college_id'] ?>"><?= htmlspecialchars($college['college_name']) ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Submit</button>
        </form>
    </div>
</div>

<script>
    // Modal functionality
    const modal = document.getElementById("myModal");
    const openModalBtn = document.getElementById("openModalBtn");
    const closeBtn = document.querySelector(".close");

    openModalBtn.addEventListener("click", () => modal.style.display = "block");
    closeBtn.addEventListener("click", () => modal.style.display = "none");
    window.addEventListener("click", (event) => {
        if (event.target == modal) modal.style.display = "none";
    });

    // Create voter functionality
    document.getElementById("newVoterForm").addEventListener("submit", async (event) => {
        event.preventDefault();
        const voterId = document.getElementById("voterId").value;
        const voterName = document.getElementById("voterName").value;
        const department = document.getElementById("department").value;

        const response = await fetch("create_voter.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ voterId, voterName, department })
        });

        if (response.ok) {
            alert("Voter successfully created!");
            location.reload();
        } else {
            alert("Failed to create voter!");
        }
    });

    // Edit voter functionality
    function editVoter(studentId) {
        const newName = prompt("Enter new name for the voter:");
        const newDepartment = prompt("Enter new department ID for the voter:");

        if (newName && newDepartment) {
            fetch("edit_voter.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ studentId, updatedName: newName, updatedDepartment: newDepartment })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Voter successfully updated!");
                    location.reload();
                } else {
                    alert(data.error || "Failed to update voter!");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred while updating the voter.");
            });
        } else {
            alert("Name and department cannot be empty!");
        }
    }


    // Delete voter functionality
    function deleteVoter(studentId) {
        if (confirm("Are you sure you want to delete this voter?")) {
            fetch("delete_voter.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ studentId })
            }).then(response => {
                if (response.ok) {
                    alert("Voter successfully deleted!");
                    location.reload();
                } else {
                    alert("Failed to delete voter!");
                }
            });
        }
    }
</script>

</body>
</html>
