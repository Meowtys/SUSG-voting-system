<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../start.php");
    exit;
}

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
    <title>Comelec - Candidates Management</title>
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

        .status {
            font-weight: bold;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .qualified {
            background-color: green;
        }

        .disqualified {
            background-color: red;
        }

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

        .edit-btn {
            padding: 8px 12px;
            border: none;
            cursor: pointer;
            font-size: 15px;
            border-radius: 4px;
            background-color: #4CAF50; 
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
</head>
<body>

    <!-- Include Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Section -->
    <main>
        <div class="content">
            <h1>Candidates</h1>
            <button class="add-btn" id="openAddModalBtn">File New Candidates</button>
            <div class="mngment-box">
                <table class="mngment-table">
                    <thead>
                        <tr>
                            <th>Candidate Name</th>
                            <th>Party</th>
                            <th>Position</th>
                            <th>Status</th>
                            <th>Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>James Teves</td>
                            <td>Tribu Wakwak</td>
                            <td>President</td>
                            <td><span class="status qualified">Qualified</span></td>
                            <td><button class="edit-btn">Edit</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add Modal Structure -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeAddModal">&times;</span>
            <h2>File New Candidate</h2>
            <form class="modal-form" id="addCandidateForm">
                <label for="candidateName">Candidate Name:</label>
                <input type="text" id="candidateName" name="candidateName" required>

                <label for="partyName">Party Name:</label>
                <input type="text" id="partyName" name="partyName" required>

                <label for="position">Position:</label>
                <select id="position" name="position" required>
                    <option value="">Select Position</option>
                    <option value="President">President</option>
                    <option value="Vice President">Vice President</option>
                    <option value="Secretary">Secretary</option>
                </select>

                <label for="status">Status:</label>
                <select id="status" name="status" required>
                    <option value="Qualified">Qualified</option>
                    <option value="Disqualified">Disqualified</option>
                </select>

                <button type="submit">Submit</button>
            </form>
        </div>
    </div>

    <!-- Edit Modal Structure -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeEditModal">&times;</span>
            <h2>Edit Candidate</h2>
            <form class="modal-form" id="editCandidateForm">
                <label for="editCandidateName">Candidate Name:</label>
                <input type="text" id="editCandidateName" name="editCandidateName" required>

                <label for="editPartyName">Party Name:</label>
                <input type="text" id="editPartyName" name="editPartyName" required>

                <label for="editPosition">Position:</label>
                <select id="editPosition" name="editPosition" required>
                    <option value="President">President (Speaker) </option>
                    <option value="Vice President">Vice President (Speaker Pro Tempore) </option>
                    <option value="Secretary">Secretary</option>
                    <option value="Assistant Secretary">Assistant Secretary</option>
                    <option value="Treasurer">Treasurer</option>
                    <option value="Majority Floor Leader">Majority Floor Leader</option>
                </select>

                <label for="editStatus">Status:</label>
                <select id="editStatus" name="editStatus" required>
                    <option value="Qualified">Qualified</option>
                    <option value="Disqualified">Disqualified</option>
                </select>

                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        // Add Modal Functionality
        const addModal = document.getElementById("addModal");
        const openAddModalBtn = document.getElementById("openAddModalBtn");
        const closeAddModal = document.getElementById("closeAddModal");
        const addForm = document.getElementById("addCandidateForm");

        openAddModalBtn.addEventListener("click", () => {
            addModal.style.display = "block";
        });

        closeAddModal.addEventListener("click", () => {
            addModal.style.display = "none";
        });

        window.addEventListener("click", (event) => {
            if (event.target === addModal) {
                addModal.style.display = "none";
            }
        });

        addForm.addEventListener("submit", (event) => {
            event.preventDefault();

            const candidateName = document.getElementById("candidateName").value;
            const partyName = document.getElementById("partyName").value;
            const position = document.getElementById("position").value;
            const status = document.getElementById("status").value;

            const table = document.querySelector(".mngment-table tbody");
            const newRow = table.insertRow();

            newRow.innerHTML = `
                <td>${candidateName}</td>
                <td>${partyName}</td>
                <td>${position}</td>
                <td><span class="status ${status.toLowerCase()}">${status}</span></td>
                <td><button class="edit-btn">Edit</button></td>
            `;

            addModal.style.display = "none";
            addForm.reset();
        });

        // Edit Modal Functionality
        const editModal = document.getElementById("editModal");
        const closeEditModal = document.getElementById("closeEditModal");
        const editForm = document.getElementById("editCandidateForm");

        document.querySelectorAll(".edit-btn").forEach((btn) => {
            btn.addEventListener("click", (e) => {
                editModal.style.display = "block";

                const row = e.target.closest("tr");
                const candidateName = row.children[0].innerText;
                const partyName = row.children[1].innerText;
                const position = row.children[2].innerText;
                const status = row.children[3].innerText;

                document.getElementById("editCandidateName").value = candidateName;
                document.getElementById("editPartyName").value = partyName;
                document.getElementById("editPosition").value = position;
                document.getElementById("editStatus").value = status;

                editForm.onsubmit = (event) => {
                    event.preventDefault();
                    row.children[0].innerText = document.getElementById("editCandidateName").value;
                    row.children[1].innerText = document.getElementById("editPartyName").value;
                    row.children[2].innerText = document.getElementById("editPosition").value;
                    const statusCell = row.children[3];
                    const newStatus = document.getElementById("editStatus").value;
                    statusCell.innerHTML = `<span class="status ${newStatus.toLowerCase()}">${newStatus}</span>`;
                    editModal.style.display = "none";
                };
            });
        });

        closeEditModal.addEventListener("click", () => {
            editModal.style.display = "none";
        });

        window.addEventListener("click", (event) => {
            if (event.target === editModal) {
                editModal.style.display = "none";
            }
        });
    </script>
</body>
</html>