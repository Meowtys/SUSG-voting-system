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
    die("Please set a current election first before managing voters.");
}

// Modify the query to include password
$query = "
    SELECT s.*, c.college_name 
    FROM students s 
    JOIN colleges c ON s.college_id = c.college_id 
    WHERE s.election_id = :election_id
    ORDER BY s.student_id
";
$stmt = $pdo->prepare($query);
$stmt->execute(['election_id' => $currentElection['election_id']]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get colleges for the dropdown
$collegesQuery = "SELECT * FROM colleges WHERE college_id != 0 ORDER BY college_name";
$collegesStmt = $pdo->query($collegesQuery);
$colleges = $collegesStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics for current election
$totalStudents = count($students);
$votedStudents = array_reduce($students, function($carry, $student) {
    return $carry + ($student['has_voted'] ? 1 : 0);
}, 0);
$notVotedStudents = $totalStudents - $votedStudents;
$votingPercentage = $totalStudents > 0 ? round(($votedStudents / $totalStudents) * 100, 2) : 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comelec - Voters Management</title>
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
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Manage Voters</h1>
                <div class="text-sm text-gray-600">
                    Current Election: <span class="font-semibold text-red-600"><?php echo htmlspecialchars($currentElection['election_name']); ?></span>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Total Voters</h3>
                    <p class="text-2xl font-bold text-gray-800"><?php echo $totalStudents; ?></p>
                </div>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Voted</h3>
                    <p class="text-2xl font-bold text-green-600"><?php echo $votedStudents; ?></p>
                </div>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Not Voted</h3>
                    <p class="text-2xl font-bold text-red-600"><?php echo $notVotedStudents; ?></p>
                </div>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Voting Percentage</h3>
                    <p class="text-2xl font-bold text-blue-600"><?php echo $votingPercentage; ?>%</p>
                </div>
            </div>
            
            <!-- Add margin-bottom to create space between button and table container -->
            <div class="mb-8 text-right">
                <button class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 transform hover:-translate-y-1" id="openModalBtn">
                    <i class="fas fa-plus-circle mr-2"></i> Add New Student
                </button>
            </div>

            <!-- Increased padding and added more shadow -->
            <div class="bg-white rounded-xl shadow-xl p-8">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-red-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider">Student ID</th>
                            <th class="px-6 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider">Student Name</th>
                            <th class="px-6 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider">College</th>
                            <th class="px-6 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider">Password</th>
                            <th class="px-6 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider">Has Voted</th>
                            <th class="px-6 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider"></th>
                            <th class="px-6 py-4 text-left text-sm font-medium text-red-700 uppercase tracking-wider"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($students as $student): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($student['student_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($student['college_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($student['password']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full <?php echo $student['has_voted'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $student['has_voted'] ? 'Yes' : 'No'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button class="edit-btn bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition duration-300 transform hover:-translate-y-1" 
                                        data-student='<?php echo json_encode($student); ?>'>
                                    <i class="fas fa-edit mr-2"></i> Edit
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button class="delete-btn bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm transition duration-300 transform hover:-translate-y-1" 
                                        data-student-id="<?php echo $student['student_id']; ?>">
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
                <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">Add New Student</h3>
                <span class="close cursor-pointer text-gray-600 text-2xl">&times;</span>
            </div>
            <form class="space-y-4" id="studentForm" method="POST" action="create_student.php">
                <input type="hidden" id="studentFormId" name="studentFormId">
                
                <div>
                    <label for="studentId" class="block text-sm font-medium text-gray-700">Student ID:</label>
                    <input type="text" id="studentId" name="studentId" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                </div>

                <div>
                    <label for="studentName" class="block text-sm font-medium text-gray-700">Student Name:</label>
                    <input type="text" id="studentName" name="studentName" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
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

                <div>
                    <label for="hasVoted" class="block text-sm font-medium text-gray-700">Has Voted:</label>
                    <select id="hasVoted" name="hasVoted" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password:</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                        <button type="button" class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                                onclick="togglePasswordVisibility()">
                            <i class="fas fa-eye" id="togglePassword"></i>
                        </button>
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
        const modalTitle = document.getElementById("modalTitle");

        openModalBtn.addEventListener("click", () => {
            document.getElementById('studentForm').action = 'create_student.php';
            document.getElementById('studentFormId').value = '';
            document.getElementById('studentId').value = '';
            document.getElementById('studentName').value = '';
            document.getElementById('college').value = '';
            document.getElementById('hasVoted').value = '0';
            document.getElementById('password').value = '';
            modalTitle.textContent = "Add New Student";
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
                document.getElementById('password').value = student.password;
                document.getElementById('college').value = student.college_id;
                document.getElementById('hasVoted').value = student.has_voted;
                modalTitle.textContent = "Edit Student";
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

        // Add password toggle functionality
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePassword');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>