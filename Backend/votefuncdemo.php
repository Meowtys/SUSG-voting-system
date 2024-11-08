<?php
// Mock function to simulate casting a vote
function castVote($studentId, $votes) {
    echo "Starting vote casting process for Student ID: $studentId\n";

    // Simulate database transaction
    echo "Transaction started...\n";

    // Loop through each position and candidate in the votes array
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

$votes = [];

// Prompt for each position vote
while (true) {
    echo "Enter position (or type 'done' to finish): ";
    $position = trim(fgets(STDIN));
    if (strtolower($position) == 'done') {
        break;
    }

    echo "Enter candidate ID for $position: ";
    $candidateId = trim(fgets(STDIN));

    $votes[$position] = $candidateId;
}

// Cast the vote
castVote($studentId, $votes);
?>
