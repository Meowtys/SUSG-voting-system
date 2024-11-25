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
    <title>Comelec - Analytics</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../asset/susglogo.png" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .content {
            flex-grow: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.3s ease;
            overflow-y: auto;
        }
    
        h1 {
            font-size: 32px;
            margin-bottom: 30px;
            text-align: center;
            color: #333;
        }

        /* Chart Container */
        .chart-container {
            width: 80%;
            max-width: 800px;
            margin-top: 20px;
        }

        .chart-title {
            font-size: 24px;
            color: #333;
            text-align: center;
            margin-top: 10px;
            font-weight: 600;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebarLinks = document.querySelectorAll('.sidebar a');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function () {
                    sidebarLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Sample data for sentiment analysis
            const feedbackComments = [
                { comment: "I kept encountering errors while submitting my vote.", sentiment: "Negative" },
                { comment: "Had a hard time navigating the website.", sentiment: "Negative" },
                { comment: "Great experience using the system!", sentiment: "Positive" },
                { comment: "Voting process was smooth and easy.", sentiment: "Positive" },
                { comment: "The website crashed multiple times.", sentiment: "Negative" },
                { comment: "The user interface is very confusing.", sentiment: "Negative" },
                { comment: "Well done! I was able to vote without any issues.", sentiment: "Positive" },
                { comment: "Some candidates' information was missing.", sentiment: "Neutral" },
                { comment: "Overall, it was a decent experience.", sentiment: "Neutral" },
                { comment: "The design is good, but it could be improved.", sentiment: "Neutral" }
            ];

            // Count sentiments
            const sentimentCounts = feedbackComments.reduce((counts, feedback) => {
                counts[feedback.sentiment] = (counts[feedback.sentiment] || 0) + 1;
                return counts;
            }, {});

            // Data for the chart
            const labels = Object.keys(sentimentCounts);
            const data = Object.values(sentimentCounts);

            // Render Chart
            const ctx = document.getElementById('sentimentChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Number of Comments',
                        data: data,
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
</head>
<body>

    <!-- Sidebar Include -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Section -->
    <main>
        <div class="content">
            <h1>Analytics</h1>
            <div class="chart-container">
                <h2 class="chart-title">Sentiment Analysis of Feedback Comments</h2>
                <canvas id="sentimentChart"></canvas>
            </div>
        </div>
    </main>
</body>
</html>