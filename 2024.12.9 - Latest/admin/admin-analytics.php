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
                const apiKey = '8c21a308d6edef953c49c0e87b30222e'; // MeaningCloud API key

                if (!feedbacks || feedbacks.length === 0) {
                    alert("No feedback data available!");
                    return;
                }

                // Function to handle API errors
                async function handleApiResponse(response) {
                    if (!response.ok) {
                        throw new Error(`API request failed: ${response.status}`);
                    }
                    const data = await response.json();
                    if (data.status.code !== '0') {
                        throw new Error(`MeaningCloud API error: ${data.status.msg}`);
                    }
                    return data;
                }

                // Function to get detailed sentiment analysis
                async function getSentiment(text) {
                    try {
                        const params = new URLSearchParams();
                        params.append('key', apiKey);
                        params.append('txt', text);
                        params.append('lang', 'en');

                        const response = await fetch('https://api.meaningcloud.com/sentiment-2.1', {
                            method: 'POST',
                            body: params
                        });

                        const data = await handleApiResponse(response);
                        return {
                            score_tag: data.score_tag,
                            confidence: parseInt(data.confidence),
                            agreement: data.agreement,
                            subjectivity: data.subjectivity
                        };
                    } catch (error) {
                        console.error('Sentiment analysis failed:', error);
                        return {
                            score_tag: 'NEU',
                            confidence: 0,
                            agreement: 'DISAGREEMENT',
                            subjectivity: 'OBJECTIVE'
                        };
                    }
                }

                const sentimentCounts = { Positive: 0, Neutral: 0, Negative: 0 };
                const experienceCounts = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 };
                const experienceSentiments = {
                    1: { Positive: 0, Neutral: 0, Negative: 0 },
                    2: { Positive: 0, Neutral: 0, Negative: 0 },
                    3: { Positive: 0, Neutral: 0, Negative: 0 },
                    4: { Positive: 0, Neutral: 0, Negative: 0 },
                    5: { Positive: 0, Neutral: 0, Negative: 0 }
                };

                // Analyze feedback data
                for (const feedback of feedbacks) {
                    const sentimentData = await getSentiment(feedback.suggestion);
                    const experience = parseInt(feedback.experience);
                    experienceCounts[experience]++;

                    // Determine sentiment category with confidence threshold
                    let sentimentCategory;
                    if (sentimentData.confidence >= 70) { // Only consider high confidence results
                        if (sentimentData.score_tag === 'P+' || sentimentData.score_tag === 'P') {
                            sentimentCategory = 'Positive';
                        } else if (sentimentData.score_tag === 'N' || sentimentData.score_tag === 'N+') {
                            sentimentCategory = 'Negative';
                        } else {
                            sentimentCategory = 'Neutral';
                        }
                    } else {
                        sentimentCategory = 'Neutral';
                    }

                    sentimentCounts[sentimentCategory]++;
                    experienceSentiments[experience][sentimentCategory]++;
                }

                // Add new charts and visualizations
                const experienceCtx = document.getElementById('experienceChart').getContext('2d');
                new Chart(experienceCtx, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(experienceCounts),
                        datasets: [{
                            label: 'Experience Ratings Distribution',
                            data: Object.values(experienceCounts),
                            backgroundColor: '#4caf50'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Experience Ratings Distribution'
                            }
                        },
                        scales: {
                            x: { 
                                title: { 
                                    display: true, 
                                    text: 'Rating (1-5)' 
                                }
                            },
                            y: { 
                                beginAtZero: true,
                                title: { 
                                    display: true, 
                                    text: 'Number of Responses' 
                                }
                            }
                        }
                    }
                });

                // Correlation Matrix Chart
                const correlationCtx = document.getElementById('correlationChart').getContext('2d');
                new Chart(correlationCtx, {
                    type: 'bar',
                    data: {
                        labels: ['1', '2', '3', '4', '5'],
                        datasets: [
                            {
                                label: 'Positive',
                                data: [1,2,3,4,5].map(exp => experienceSentiments[exp].Positive),
                                backgroundColor: '#4caf50'
                            },
                            {
                                label: 'Neutral',
                                data: [1,2,3,4,5].map(exp => experienceSentiments[exp].Neutral),
                                backgroundColor: '#ffce56'
                            },
                            {
                                label: 'Negative',
                                data: [1,2,3,4,5].map(exp => experienceSentiments[exp].Negative),
                                backgroundColor: '#f44336'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Experience Rating vs Sentiment Analysis'
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Experience Rating'
                                },
                                stacked: true
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Number of Feedbacks'
                                },
                                stacked: true
                            }
                        }
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
            <h1>AI Integrated Analytics</h1>
            <div class="chart-container">
                <canvas id="experienceChart"></canvas>
            </div>
            <div class="chart-container">
                <canvas id="sentimentChart"></canvas>
            </div>
            <div class="chart-container">
                <canvas id="correlationChart"></canvas>
            </div>
            <div class="chart-container line-chart-container">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </main>
</body>
</html>