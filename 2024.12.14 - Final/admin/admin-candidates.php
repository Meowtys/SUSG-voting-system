<?php
session_start();
if (!isset($_SESSION['is_comelec_logged_in']) || !$_SESSION['is_comelec_logged_in']) {
    header('Location: ../loginascomelec.php');
    exit();
}

require_once '../connect.php';

// Get current election
$stmt = $pdo->query("SELECT election_id, election_name FROM elections WHERE is_current = 1 LIMIT 1");
$currentElection = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentElection) {
    die("Please set a current election first before managing candidates.");
}

// Fetch candidates with party names
$stmt = $pdo->prepare("
    SELECT c.*, co.college_name, p.position_name, pa.party_name 
    FROM candidates c 
    LEFT JOIN colleges co ON c.college_id = co.college_id 
    LEFT JOIN positions p ON c.position_id = p.position_id
    LEFT JOIN parties pa ON c.party_id = pa.party_id
    WHERE c.election_id = :election_id
");
$stmt->execute(['election_id' => $currentElection['election_id']]);
$candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch positions
$positionsStmt = $pdo->query("SELECT * FROM positions");
$positions = $positionsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch colleges, excluding "Abstain"
$collegesStmt = $pdo->query("SELECT * FROM colleges WHERE college_name != 'Abstain'");
$colleges = $collegesStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch parties
$partiesStmt = $pdo->query("SELECT * FROM parties ORDER BY party_name");
$parties = $partiesStmt->fetchAll(PDO::FETCH_ASSOC);

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
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="../script/adminload.js" type="module" defer></script>
</head>
<body class="bg-gray-50">
    <!-- Include Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Section -->
    <main class="ml-64 p-8">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-4xl font-bold mb-8 text-gray-800">Candidates Management</h1>

            <div class="mb-8 text-right">
                <button class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 transform hover:-translate-y-1" id="openModalBtn">
                    <i class="fas fa-plus-circle mr-2"></i> File New Candidate
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-xl p-8">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-red-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider">Candidate Name</th>
                            <th class="px-6 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider">Party</th>
                            <th class="px-6 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider">Position</th>
                            <th class="px-6 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider">College</th>
                            <th class="px-6 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider">Qualified</th>
                            <th class="px-6 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider">Remarks</th>
                            <th class="px-6 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider"></th>
                            <th class="px-6 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($candidates as $candidate): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($candidate['candidate_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($candidate['party_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($candidate['position_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($candidate['college_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-base leading-5 font-semibold rounded-full <?php echo $candidate['qualified'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $candidate['qualified'] ? 'Yes' : 'No'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($candidate['remarks']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button class="edit-btn bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition duration-300 transform hover:-translate-y-1" 
                                        data-candidate='<?php echo json_encode($candidate); ?>'>
                                    <i class="fas fa-edit mr-2"></i> Edit
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button class="delete-btn bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm transition duration-300 transform hover:-translate-y-1" 
                                        data-candidate-id="<?php echo $candidate['candidate_id']; ?>">
                                    <i class="fas fa-trash-alt mr-2"></i> Delete
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Structure -->
    <div id="myModal" class="modal hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">File New Candidacy</h3>
                <span class="close cursor-pointer text-gray-600 text-2xl">&times;</span>
            </div>
            <form class="space-y-4" id="newCandidateForm" method="POST" action="create_candidate.php" enctype="multipart/form-data">
                <!-- Add hidden input for election_id -->
                <input type="hidden" name="election_id" value="<?php echo $currentElection['election_id']; ?>">
                
                <div>
                    <label for="candidateName" class="block text-sm font-medium text-gray-700">Candidate Name:</label>
                    <input type="text" id="candidateName" name="candidateName" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                </div>

                <div>
                    <label for="partyId" class="block text-sm font-medium text-gray-700">Party:</label>
                    <select id="partyId" name="partyId" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                        <option value="">Select Party</option>
                        <?php foreach ($parties as $party): ?>
                            <option value="<?php echo htmlspecialchars($party['party_id']); ?>">
                                <?php echo htmlspecialchars($party['party_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="position" class="block text-sm font-medium text-gray-700">Position:</label>
                        <select id="position" name="position" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                            <option value="">Select Position</option>
                            <?php foreach ($positions as $position): ?>
                                <option value="<?php echo htmlspecialchars($position['position_id']); ?>">
                                    <?php echo htmlspecialchars($position['position_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="college" class="block text-sm font-medium text-gray-700">College/Department:</label>
                        <select id="college" name="college" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                            <option value="">Select College</option>
                            <?php foreach ($colleges as $college): ?>
                                <option value="<?php echo htmlspecialchars($college['college_id']); ?>">
                                    <?php echo htmlspecialchars($college['college_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="candidateImage" class="block text-sm font-medium text-gray-700">Candidate Image (JPG, JPEG, PNG only):</label>
                    <input type="file" id="candidateImage" name="candidateImage" accept="image/*" required
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="qualified" class="block text-sm font-medium text-gray-700">Qualified:</label>
                        <select id="qualified" name="qualified" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                            <option value="">Select Qualification</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <div>
                        <label for="remarks" class="block text-sm font-medium text-gray-700">Remarks:</label>
                        <input type="text" id="remarks" name="remarks"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    </div>
                </div>

                <button type="submit" 
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300">
                    Submit
                </button>
            </form>
        </div>
    </div>

    <!-- Edit Modal (Similar structure with different ID) -->
    <div id="editModal" class="modal hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Edit Candidacy</h3>
                <span class="close cursor-pointer text-gray-600 text-2xl">&times;</span>
            </div>
            <form class="space-y-4" id="editCandidateForm" method="POST" action="edit_candidate.php" enctype="multipart/form-data">
                <input type="hidden" id="editCandidateId" name="candidateId">
                <input type="hidden" name="election_id" value="<?php echo $currentElection['election_id']; ?>">
                
                <div>
                    <label for="editCandidateName" class="block text-sm font-medium text-gray-700">Candidate Name:</label>
                    <input type="text" id="editCandidateName" name="candidateName" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                </div>

                <div>
                    <label for="editPartyId" class="block text-sm font-medium text-gray-700">Party:</label>
                    <select id="editPartyId" name="partyId" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                        <option value="">Select Party</option>
                        <?php foreach ($parties as $party): ?>
                            <option value="<?php echo htmlspecialchars($party['party_id']); ?>">
                                <?php echo htmlspecialchars($party['party_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="editPosition" class="block text-sm font-medium text-gray-700">Position:</label>
                        <select id="editPosition" name="position" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                            <option value="">Select Position</option>
                            <?php foreach ($positions as $position): ?>
                                <option value="<?php echo htmlspecialchars($position['position_id']); ?>">
                                    <?php echo htmlspecialchars($position['position_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="editCollege" class="block text-sm font-medium text-gray-700">College/Department:</label>
                        <select id="editCollege" name="college" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                            <option value="">Select College</option>
                            <?php foreach ($colleges as $college): ?>
                                <option value="<?php echo htmlspecialchars($college['college_id']); ?>">
                                    <?php echo htmlspecialchars($college['college_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="editCandidateImage" class="block text-sm font-medium text-gray-700">Candidate Image (JPG, JPEG, PNG only):</label>
                    <input type="file" id="editCandidateImage" name="candidateImage" accept="image/*"
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="editQualified" class="block text-sm font-medium text-gray-700">Qualified:</label>
                        <select id="editQualified" name="qualified" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                            <option value="">Select Qualification</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <div>
                        <label for="editRemarks" class="block text-sm font-medium text-gray-700">Remarks:</label>
                        <input type="text" id="editRemarks" name="remarks"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    </div>
                </div>

                <button type="submit" 
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300">
                    Submit
                </button>
            </form>
        </div>
    </div>

    <script>
        // Modal functionality
        const modal = document.getElementById("myModal");
        const openModalBtn = document.getElementById("openModalBtn");
        const closeBtns = document.querySelectorAll(".close");
        const editModal = document.getElementById("editModal");

        openModalBtn.addEventListener("click", () => modal.style.display = "block");
        closeBtns.forEach(btn => btn.addEventListener("click", () => {
            modal.style.display = "none";
            editModal.style.display = "none";
        }));
        window.addEventListener("click", (event) => {
            if (event.target == modal) modal.style.display = "none";
            if (event.target == editModal) editModal.style.display = "none";
        });

        // Client-side validation for image file type
        document.getElementById('newCandidateForm').addEventListener('submit', function(event) {
            const fileInput = document.getElementById('candidateImage');
            const filePath = fileInput.value;
            const allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;

            if (!allowedExtensions.exec(filePath)) {
                alert('Only JPG, JPEG, and PNG files are allowed.');
                fileInput.value = '';
                event.preventDefault();
            }
        });

        document.getElementById('editCandidateForm').addEventListener('submit', function(event) {
            const fileInput = document.getElementById('editCandidateImage');
            const filePath = fileInput.value;
            const allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;

            if (filePath && !allowedExtensions.exec(filePath)) {
                alert('Only JPG, JPEG, and PNG files are allowed.');
                fileInput.value = '';
                event.preventDefault();
            }
        });

        // Edit button functionality
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const candidate = JSON.parse(this.getAttribute('data-candidate'));
                document.getElementById('editCandidateId').value = candidate.candidate_id;
                document.getElementById('editCandidateName').value = candidate.candidate_name;
                document.getElementById('editPartyId').value = candidate.party_id;
                document.getElementById('editPosition').value = candidate.position_id;
                document.getElementById('editCollege').value = candidate.college_id;
                document.getElementById('editQualified').value = candidate.qualified;
                document.getElementById('editRemarks').value = candidate.remarks;
                editModal.style.display = "block";
            });
        });

        // Delete button functionality
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const candidateId = this.getAttribute('data-candidate-id');
                if (confirm('Are you sure you want to delete this candidate?')) {
                    fetch('delete_candidate.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ candidateId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Failed to delete candidate. Please try again.');
                        }
                    })
                    .catch(error => console.error('Error:', error));
                }
            });
        });
    </script>
</body>
</html>