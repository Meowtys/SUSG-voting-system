<?php
session_start();
if (!isset($_SESSION['is_comelec_logged_in']) || !$_SESSION['is_comelec_logged_in']) {
    header('Location: ../loginascomelec.php');
    exit();
}

require_once '../connect.php';

// Fetch data from feedbacks table
$stmt = $pdo->query("SELECT * FROM feedbacks");
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Encode feedback data into JSON format
$feedbacksJSON = json_encode($feedbacks, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
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
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/toxicity"></script>
    <style>
        .content {
            flex-grow: 1;
            padding: 40px;
            text-align: center;
        }
        .chart-container {
            width: 80%;
            max-width: 800px;
            margin: auto;
            margin-top: 100px;
        }
        .line-chart-container {
            margin-top: 50px;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', async function () {
            try {
                const feedbacks = <?= $feedbacksJSON; ?>;

                if (!feedbacks || feedbacks.length === 0) {
                    alert("No feedback data available!");
                    return;
                }

                // Load TensorFlow.js Toxicity Model
                const threshold = 0.9; // Adjust confidence threshold as needed
                const model = await toxicity.load(threshold);

                // Prepare feedback suggestions for analysis
                const sentences = feedbacks.map(f => f.suggestion);

                // Analyze Sentiments using the Toxicity Model
                const predictions = await model.classify(sentences);

                const sentimentCounts = { Positive: 0, Neutral: 0, Negative: 0 };

                // Classify each feedback
                sentences.forEach((sentence, index) => {
                    const isPositive = predictions.some(p => p.label === 'identity_attack' && p.results[index].match);
                    const isNegative = predictions.some(p => p.label === 'insult' && p.results[index].match);

                    if (isPositive) {
                        sentimentCounts.Positive++;
                    } else if (isNegative) {
                        sentimentCounts.Negative++;
                    } else {
                        sentimentCounts.Neutral++;
                    }
                });

                // Render Bar Chart
                const ctx = document.getElementById('sentimentChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(sentimentCounts),
                        datasets: [{
                            label: 'Number of Comments',
                            data: Object.values(sentimentCounts),
                            backgroundColor: ['#4caf50', '#ffce56', '#f44336'],
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: { y: { beginAtZero: true } }
                    }
                });

                // Prepare data for feedback trends
                const feedbackDates = feedbacks.map(f => f.feedback_timestamp.split(" ")[0]); // Extract dates
                const trendData = feedbackDates.reduce((acc, date) => {
                    acc[date] = (acc[date] || 0) + 1;
                    return acc;
                }, {});

                const sortedDates = Object.keys(trendData).sort();
                const trendValues = sortedDates.map(date => trendData[date]);

                // Render Line Chart
                const lineCtx = document.getElementById('trendChart').getContext('2d');
                new Chart(lineCtx, {
                    type: 'line',
                    data: {
                        labels: sortedDates,
                        datasets: [{
                            label: 'Feedback Over Time',
                            data: trendValues,
                            borderColor: '#36A2EB',
                            fill: false,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Feedback Trend Over Time'
                            }
                        },
                        scales: {
                            x: { title: { display: true, text: 'Date' } },
                            y: { title: { display: true, text: 'Number of Feedbacks' }, beginAtZero: true }
                        }
                    }
                });

            } catch (error) {
                console.error("Error:", error);
                alert("An error occurred while loading analytics.");
            }
        });
    </script>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main>
        <div class="content">
            <h1>AI Integrated Analytics <br> Sentiment Analysis </h1>
            <div class="chart-container">
                <canvas id="sentimentChart"></canvas>
            </div>
            <div class="chart-container line-chart-container">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </main>
</body>
</html>