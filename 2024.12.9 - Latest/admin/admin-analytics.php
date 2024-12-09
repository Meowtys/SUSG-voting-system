<?php
session_start();
if (!isset($_SESSION['is_comelec_logged_in']) || !$_SESSION['is_comelec_logged_in']) {
    header('Location: ../loginascomelec.php');
    exit();
}

require_once '../connect.php';

try {
    // Fetch data from feedbacks table with error checking
    $stmt = $pdo->prepare("SELECT f.*, s.student_name 
                          FROM feedbacks f 
                          JOIN students s ON f.student_id = s.student_id 
                          ORDER BY f.feedback_timestamp DESC");
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        $feedbacks = [];
        echo "<script>console.log('No feedbacks found in database');</script>";
    } else {
        $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<script>console.log('Fetched " . count($feedbacks) . " feedbacks');</script>";
    }
} catch (PDOException $e) {
    echo "<script>console.error('Database error: " . addslashes($e->getMessage()) . "');</script>";
    $feedbacks = [];
}

// Encode feedback data into JSON format with error checking
try {
    $feedbacksJSON = json_encode($feedbacks, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception(json_last_error_msg());
    }
} catch (Exception $e) {
    echo "<script>console.error('JSON encoding error: " . addslashes($e->getMessage()) . "');</script>";
    $feedbacksJSON = '[]';
}
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
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .analytics-container {
            background: #f8f9fa;
            min-height: 100vh;
            padding: 2rem;
        }
        .chart-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        .chart-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }
        .chart-card:hover {
            transform: translateY(-5px);
        }
        .chart-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 1rem;
            text-align: left;
        }
        .stats-summary {
            display: flex;
            justify-content: space-around;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            min-width: 150px;
        }
        .comments-section {
            margin-top: 2rem;
            padding: 2rem;
        }
        .sentiment-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .sentiment-tab {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .sentiment-tab.active {
            transform: translateY(-2px);
        }
        .comments-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
        }
        .comment-card {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .comment-text {
            font-size: 0.9rem;
            color: #4b5563;
            margin-bottom: 0.5rem;
        }
        .comment-meta {
            font-size: 0.8rem;
            color: #6b7280;
        }
    </style>
    <script>
        // Define showComments in global scope
        function showComments(sentiment) {
            const containers = document.querySelectorAll('.comments-container');
            const tabs = document.querySelectorAll('.sentiment-tab');
            
            containers.forEach(container => container.style.display = 'none');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            document.getElementById(`${sentiment.toLowerCase()}-comments`).style.display = 'grid';
            document.querySelector(`[data-sentiment="${sentiment}"]`).classList.add('active');
        }

        document.addEventListener('DOMContentLoaded', async function () {
            try {
                const feedbacks = <?= $feedbacksJSON ?>;
                console.log('Raw feedbacks data:', feedbacks);
                
                // Add loading indicator
                const mainContent = document.querySelector('.analytics-container');
                mainContent.innerHTML = '<div class="text-center p-4">Loading analytics...</div>' + mainContent.innerHTML;

                if (!feedbacks || feedbacks.length === 0) {
                    mainContent.innerHTML = `
                        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
                            <p class="font-bold">No Feedback Data</p>
                            <p>There are currently no feedbacks in the database.</p>
                        </div>
                    ` + mainContent.innerHTML;
                    return;
                }

                const apiKey = '8c21a308d6edef953c49c0e87b30222e'; // MeaningCloud API key
                
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
                        if (!text || text.trim().length === 0) {
                            console.log('Empty text provided for sentiment analysis');
                            return {
                                score_tag: 'NEU',
                                confidence: 0,
                                agreement: 'DISAGREEMENT',
                                subjectivity: 'OBJECTIVE'
                            };
                        }

                        const params = new URLSearchParams();
                        params.append('key', apiKey);
                        params.append('txt', text);
                        params.append('lang', 'en');

                        console.log('Analyzing sentiment for text:', text.substring(0, 50) + '...');
                        
                        const response = await fetch('https://api.meaningcloud.com/sentiment-2.1', {
                            method: 'POST',
                            body: params
                        });

                        if (!response.ok) {
                            throw new Error(`API request failed: ${response.status} - ${await response.text()}`);
                        }

                        const data = await response.json();
                        console.log('API Response:', data);

                        if (data.status.code !== '0') {
                            throw new Error(`MeaningCloud API error: ${data.status.msg}`);
                        }

                        return {
                            score_tag: data.score_tag,
                            confidence: parseInt(data.confidence),
                            agreement: data.agreement,
                            subjectivity: data.subjectivity
                        };
                    } catch (error) {
                        console.error('Sentiment analysis error:', error);
                        console.error('Failed text:', text);
                        throw error; // Re-throw to handle in main try-catch
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
                const commentsBySentiment = {
                    Positive: [],
                    Neutral: [],
                    Negative: []
                };

                // Single pass analysis for both charts and comments
                let processedCount = 0;
                const totalFeedbacks = feedbacks.length;

                for (const feedback of feedbacks) {
                    try {
                        processedCount++;
                        console.log(`Processing feedback ${processedCount}/${totalFeedbacks}`);
                        
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
                        commentsBySentiment[sentimentCategory].push({
                            text: feedback.suggestion,
                            timestamp: feedback.feedback_timestamp,
                            rating: feedback.experience
                        });
                    } catch (error) {
                        console.error('Error processing feedback:', feedback);
                        console.error('Error details:', error);
                        // Continue with next feedback instead of stopping completely
                        continue;
                    }
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

                // Update statistics summary
                document.getElementById('totalFeedbacks').textContent = feedbacks.length;
                const avgRating = (Object.entries(experienceCounts).reduce((acc, [key, value]) => 
                    acc + (key * value), 0) / feedbacks.length).toFixed(1);
                document.getElementById('avgRating').textContent = avgRating;
                const sentimentScore = ((sentimentCounts.Positive * 100) / 
                    (sentimentCounts.Positive + sentimentCounts.Neutral + sentimentCounts.Negative)).toFixed(0) + '%';
                document.getElementById('sentimentScore').textContent = sentimentScore;

                // Update the comments display section
                const commentsSection = document.querySelector('.comments-section');
                if (commentsSection) {
                    Object.keys(commentsBySentiment).forEach(sentiment => {
                        const comments = commentsBySentiment[sentiment];
                        const container = document.getElementById(`${sentiment.toLowerCase()}-comments`);
                        const count = comments.length;
                        
                        // Add count to tab
                        const tab = document.querySelector(`[data-sentiment="${sentiment}"]`);
                        tab.innerHTML = `${sentiment} Comments (${count})`;
                        
                        container.innerHTML = ''; // Clear existing content
                        comments.forEach(comment => {
                            container.innerHTML += `
                                <div class="comment-card">
                                    <p class="comment-text">${comment.text}</p>
                                    <div class="comment-meta">
                                        Rating: ${comment.rating}/5 • ${new Date(comment.timestamp).toLocaleDateString()}
                                    </div>
                                </div>
                            `;
                        });
                    });
                    
                    // Call showComments after all tabs are initialized
                    setTimeout(() => showComments('Positive'), 100);
                }

                // Remove loading indicator
                mainContent.querySelector('.text-center')?.remove();

            } catch (error) {
                console.error("Error:", error);
                console.error('Detailed error:', error);
                console.error('Stack trace:', error.stack);
                alert("An error occurred while loading analytics.");
                
                // Show error in UI
                const mainContent = document.querySelector('.analytics-container');
                mainContent.innerHTML = `
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                        <p class="font-bold">Error Loading Analytics</p>
                        <p>${error.message}</p>
                        <button onclick="location.reload()" class="mt-2 bg-red-500 text-white px-4 py-2 rounded">
                            Retry
                        </button>
                    </div>
                ` + mainContent.innerHTML;
            }
        });
    </script>
</head>
<body class="bg-gray-100">
    <?php include 'sidebar.php'; ?>
    
    <main class="analytics-container">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Analytics Dashboard</h1>
        
        <div class="stats-summary">
            <div class="stat-card">
                <h3 class="text-lg">Total Feedbacks</h3>
                <p class="text-2xl font-bold" id="totalFeedbacks">0</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #059669);">
                <h3 class="text-lg">Average Rating</h3>
                <p class="text-2xl font-bold" id="avgRating">0</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <h3 class="text-lg">Sentiment Score</h3>
                <p class="text-2xl font-bold" id="sentimentScore">0</p>
            </div>
        </div>

        <div class="chart-grid">
            <div class="chart-card">
                <div class="chart-title">Experience Ratings Distribution</div>
                <canvas id="experienceChart"></canvas>
            </div>
            <div class="chart-card">
                <div class="chart-title">Sentiment Analysis</div>
                <canvas id="sentimentChart"></canvas>
            </div>
            <div class="chart-card">
                <div class="chart-title">Experience vs Sentiment Correlation</div>
                <canvas id="correlationChart"></canvas>
            </div>
            <div class="chart-card">
                <div class="chart-title">Feedback Trends</div>
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <div class="comments-section">
            <h2 class="text-2xl font-bold mb-4">Feedback Comments Analysis</h2>
            
            <div class="sentiment-tabs">
                <button class="sentiment-tab" data-sentiment="Positive" 
                        onclick="showComments('Positive')"
                        style="background: linear-gradient(135deg, #4caf50, #45a049); color: white;">
                    Positive Comments
                </button>
                <button class="sentiment-tab" data-sentiment="Neutral"
                        onclick="showComments('Neutral')"
                        style="background: linear-gradient(135deg, #ffce56, #ffc107); color: white;">
                    Neutral Comments
                </button>
                <button class="sentiment-tab" data-sentiment="Negative"
                        onclick="showComments('Negative')"
                        style="background: linear-gradient(135deg, #f44336, #e53935); color: white;">
                    Negative Comments
                </button>
            </div>

            <div id="positive-comments" class="comments-container" style="display: none;"></div>
            <div id="neutral-comments" class="comments-container" style="display: none;"></div>
            <div id="negative-comments" class="comments-container" style="display: none;"></div>
        </div>
    </main>
</body>
</html>