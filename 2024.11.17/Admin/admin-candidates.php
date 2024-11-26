<?php
session_start();
if (!isset($_SESSION['is_comelec_logged_in']) || !$_SESSION['is_comelec_logged_in']) {
    header('Location: ../loginascomelec.php');
    exit();
}

require_once '../connect.php';

// Fetch candidates from the database
$stmt = $pdo->query("
    SELECT candidates.*, colleges.college_name, positions.position_name 
    FROM candidates 
    LEFT JOIN colleges ON candidates.college_id = colleges.college_id 
    LEFT JOIN positions ON candidates.position_id = positions.position_id
");
$candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Start output buffering
ob_start();
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
            <h1>Candidates</h1>
            <button class="add-btn" id="openModalBtn">File New Candidates</button>
            <div class="mngment-box">
                <table class="mngment-table">
                    <thead>
                        <tr>
                            <th>Candidate Name</th>
                            <th>Party</th>
                            <th>Position</th>
                            <th>College</th>
                            <th>Qualified</th>
                            <th>Remarks</th>
                            <th>Edit</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($candidates as $candidate): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($candidate['candidate_name']); ?></td>
                            <td><?php echo htmlspecialchars($candidate['candidate_party']); ?></td>
                            <td><?php echo htmlspecialchars($candidate['position_name']); ?></td>
                            <td><?php echo htmlspecialchars($candidate['college_name']); ?></td>
                            <td><?php echo $candidate['qualified'] ? 'Yes' : 'No'; ?></td>
                            <td><?php echo htmlspecialchars($candidate['remarks']); ?></td>
                            <td><button class="edit-btn">Edit</button></td>
                            <td><button class="delete-btn">Delete</button></td>
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
            <h2>File New Candidacy</h2>
            <form class="modal-form" id="newCandidateForm">
                <label for="candidateName">Candidate Name:</label>
                <input type="text" id="candidateName" name="candidateName" required>

                <label for="partyName">Party Name:</label>
                <input type="text" id="partyName" name="partyName" required>

                <label for="position">Position:</label>
                <select id="position" name="position" required>
                    <option value="">Select Position</option>
                    <option value="President">President</option>
                    <option value="Vice President">Vice President</option>
                    <option value="Secretary">Representative</option>
                </select>

                <label for="college">College/Department:</label>
                <select id="college" name="college" required>
                    <option value="">Select College</option>
                    <option value="CCS">CCS</option>
                    <option value="AGRI">AGRI</option>
                    <option value="CAS">CAS</option>
                    <option value="CBA">CBA</option>
                    <option value="EDUC">EDUC</option>
                    <option value="CED">CED</option>
                    <option value="LAW">LAW</option>
                    <option value="CMC">CMC</option>
                    <option value="CON">CON</option>
                    <option value="COPVA">COPVA</option>
                    <option value="ICLS">ICLS</option>
                    <option value="IEMS">IEMS</option>
                    <option value="IRS">IRS</option>
                    <option value="JHS">JHS</option>
                    <option value="MEDICAL SCHOOL">MEDICAL SCHOOL</option>
                    <option value="SPAG">SPAG</option>
                    <option value="SHS">SHS</option>
                </select>

                <label for="candidateImage">Candidate Image:</label>
                <input type="file" id="candidateImage" name="candidateImage" accept="image/*" required>

                <label for="qualified">Qualified:</label>
                <select id="qualified" name="qualified" required>
                    <option value="">Select Qualification</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>

                <label for="remarks">Remarks:</label>
                <input type="text" id="remarks" name="remarks">

                <button type="submit">Submit</button>
            </form>
        </div>
    </div>

    <script>
        // Modal functionality
        const modal = document.getElementById("myModal");
        const openModalBtn = document.getElementById("openModalBtn");
        const closeBtn = document.querySelector(".close");

        // Open modal
        openModalBtn.addEventListener("click", () => {
            modal.style.display = "block";
        });

        // Close modal
        closeBtn.addEventListener("click", () => {
            modal.style.display = "none";
        });

        // Close modal on outside click
        window.addEventListener("click", (event) => {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        });

        // Add candidate functionality
        const form = document.getElementById("newCandidateForm");
        form.addEventListener("submit", (event) => {
            event.preventDefault();

            const candidateName = document.getElementById("candidateName").value;
            const partyName = document.getElementById("partyName").value;
            const position = document.getElementById("position").value;
            const college = document.getElementById("college").value;
            const qualified = document.getElementById("qualified").value;
            const remarks = document.getElementById("remarks").value;

            const table = document.querySelector(".mngment-table tbody");
            const newRow = table.insertRow();

            newRow.innerHTML = `
                <td>${candidateName}</td>
                <td>${partyName}</td>
                <td>${position}</td>
                <td>${college}</td>
                <td>${qualified}</td>
                <td>${remarks}</td>
                <td><button class="edit-btn">Edit</button></td>
                <td><button class="delete-btn">Delete</button></td>
            `;

            modal.style.display = "none";
            form.reset();
        });
    </script>
</body>
</html>