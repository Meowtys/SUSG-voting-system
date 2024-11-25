<?php
// Start output buffering
ob_start();

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$dbname = "susg_project";

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch non-admin voters (is_admin = 0)
$sql = "SELECT student_id, stud_name, college_id FROM students WHERE is_admin = 0";
$result = $conn->query($sql);

$votersData = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $votersData[] = [
            'id' => $row['student_id'],
            'name' => $row['stud_name'],
            'department' => $row['college_id']
        ];
    }
}

$conn->close();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
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
        /* Retain your styles as provided */
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
    </style>
    <script>
        // Pass PHP data to JavaScript
        const votersData = <?php echo json_encode($votersData); ?>;

        document.addEventListener('DOMContentLoaded', function () {
            const tableBody = document.querySelector(".mngment-table tbody");

            // Render the table dynamically
            function renderTable() {
                tableBody.innerHTML = "";
                votersData.forEach((voter, index) => {
                    const row = document.createElement("tr");
                    row.innerHTML = `
                        <td>${voter.id}</td>
                        <td contenteditable="false">${voter.name}</td>
                        <td contenteditable="false">${voter.department}</td>
                        <td><button class="edit-btn" onclick="editVoter(${index})">Edit</button></td>
                        <td><button class="delete-btn" onclick="deleteVoter(${index})">Delete</button></td>
                    `;
                    tableBody.appendChild(row);
                });
            }

            // Edit voter
            window.editVoter = function (index) {
                const row = tableBody.rows[index];
                const nameCell = row.cells[1];
                const departmentCell = row.cells[2];
                const editButton = row.cells[3].querySelector(".edit-btn");

                if (editButton.textContent === "Edit") {
                    nameCell.contentEditable = "true";
                    departmentCell.contentEditable = "true";
                    editButton.textContent = "Save";
                    nameCell.focus();
                } else {
                    nameCell.contentEditable = "false";
                    departmentCell.contentEditable = "false";
                    editButton.textContent = "Edit";

                    // Save updated data back to the array
                    votersData[index].name = nameCell.textContent.trim();
                    votersData[index].department = departmentCell.textContent.trim();
                }
            };

            // Delete voter
            window.deleteVoter = function (index) {
                if (confirm("Are you sure you want to delete this voter?")) {
                    votersData.splice(index, 1);
                    renderTable();
                }
            };

            // Initial render
            renderTable();
        });
    </script>
</head>
<body>

    <!-- Include Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Section -->
    <main>
        <div class="content">
            <h1>Voters</h1>
            <div class="mngment-box">
                <table class="mngment-table">
                    <thead>
                        <tr>
                            <th>SU ID</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Edit</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be dynamically rendered here -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>