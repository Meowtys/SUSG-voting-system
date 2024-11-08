<?php
// Mock function to simulate casting a vote
function castVote($studentId, $votes) {
    echo "Starting vote casting process for Student ID: $studentId\n";

    // Simulate database transaction
    echo "Transaction started...\n";

    // Loop through each position and candidate ID in the votes array
    foreach ($votes as $position => $candidateId) {
        // Simulate inserting each vote into the Votes table
        echo "Recording vote for $position: Candidate ID $candidateId\n";
    }

    // Simulate marking the student as having voted
    echo "Updating student record to indicate vote has been cast.\n";

    // Simulate committing the transaction
    echo "Transaction committed.\n";

    echo "Vote successfully cast!\n";
}

// Main script
echo "Enter Student ID: ";
$studentId = trim(fgets(STDIN));

// List of candidates for each position
$presidentCandidates = [
    'A123' => 'John Doe',
    'A124' => 'Jane Smith',
    'A125' => 'Mary Johnson'
];

$vicePresidentCandidates = [
    'B123' => 'Chris Lee',
    'B124' => 'Patricia Brown',
    'B125' => 'Michael Davis'
];

$representativeCandidates = [
    'C123' => 'Laura White',
    'C124' => 'David Clark',
    'C125' => 'Sarah Miller'
];

// Display the candidates and their IDs
echo "\n--- PRESIDENTIAL CANDIDATES ---\n";
foreach ($presidentCandidates as $id => $name) {
    echo "$id: $name\n";
}

echo "\n--- VICE PRESIDENTIAL CANDIDATES ---\n";
foreach ($vicePresidentCandidates as $id => $name) {
    echo "$id: $name\n";
}

echo "\n--- REPRESENTATIVE CANDIDATES ---\n";
foreach ($representativeCandidates as $id => $name) {
    echo "$id: $name\n";
}

$votes = [];

// Ask for the candidate ID for each position
echo "\nEnter candidate ID for President: ";
$presidentId = trim(fgets(STDIN));
$votes['President'] = $presidentId;

echo "Enter candidate ID for Vice President: ";
$vicePresidentId = trim(fgets(STDIN));
$votes['Vice President'] = $vicePresidentId;

echo "Enter candidate ID for Representative: ";
$representativeId = trim(fgets(STDIN));
$votes['Representative'] = $representativeId;

// Print vote summary
echo "\n--- VOTE SUMMARY ---\n";
echo "Student ID: $studentId\n";
foreach ($votes as $position => $candidateId) {
    // Look up the candidate names based on their ID for each position
    switch ($position) {
        case 'President':
            $candidateName = $presidentCandidates[$candidateId] ?? 'Unknown';
            break;
        case 'Vice President':
            $candidateName = $vicePresidentCandidates[$candidateId] ?? 'Unknown';
            break;
        case 'Representative':
            $candidateName = $representativeCandidates[$candidateId] ?? 'Unknown';
            break;
        default:
            $candidateName = 'Unknown';
            break;
    }
    echo "$position: $candidateName (ID: $candidateId)\n";
}

// Cast the vote
castVote($studentId, $votes);
?>
