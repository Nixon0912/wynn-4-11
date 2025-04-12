<?php
session_start();

// Optional: If the user is not logged in, redirect back to login page
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>FinSight – Dashboard</title>
  <style>
    /* Basic Reset */
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      color: #333;
      background-color: #f2f2f2;
    }
    /* Header */
    header {
      display: flex;
      flex-direction: column;
      align-items: center;
      background: linear-gradient(135deg, #0A74DA 0%, #0570b8 100%);
      color: #fff;
      padding: 1rem 2rem;
    }
    header h1 {
      margin: 0;
      font-weight: 600;
      letter-spacing: 1px;
      margin-bottom: 0.5rem;
      text-align: center;
    }
    .header-nav {
      display: flex;
      gap: 15px;
      justify-content: flex-end;
      align-items: center;
      width: 100%;
    }
    .header-nav a {
      text-decoration: none;
      background-color: #0A74DA;
      color: #fff;
      padding: 0.5rem 1rem;
      border-radius: 5px;
      font-weight: 600;
      transition: background-color 0.2s;
    }
    .header-nav a:hover {
      background-color: #065c9c;
    }
    .header-nav a.help-button {
      background-color: #065c9c;
    }
    /* Layout: Sidebar + Main Content */
    .layout { display: flex; }
    aside.sidebar {
      position: fixed;
      top: 150px;
      left: 0;
      width: 320px;
      height: calc(100vh - 80px);
      background-color: transparent;
      padding: 1rem;
      overflow-y: auto;
    }
    .sidebar h2 {
      margin-bottom: 1rem;
      color: #0A74DA;
      font-size: 1.2rem;
    }
    .sidebar p {
      font-size: 0.9rem;
      color: #555;
      margin-bottom: 1rem;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background-color: #fff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    table thead {
      background-color: #eef6ff;
    }
    table th, table td {
      text-align: left;
      padding: 0.75rem 1rem;
      border-bottom: 1px solid #eee;
      font-size: 0.9rem;
    }
    table thead th {
      font-weight: 700;
      text-transform: uppercase;
      color: #333;
    }
    table tbody tr:last-child td {
      border-bottom: none;
    }
    .topic-link {
      color: #0A74DA;
      text-decoration: none;
      font-weight: 600;
    }
    .topic-link:hover {
      text-decoration: underline;
    }
    /* Main Content */
    main.content {
      margin-left: 280px;
      padding: 2rem;
      flex: 1;
      min-height: calc(100vh - 80px);
    }
    .dashboard-controls {
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .dashboard-controls button {
      padding: 0.5rem 1rem;
      border: none;
      background-color: #0A74DA;
      color: #fff;
      border-radius: 5px;
      cursor: pointer;
      font-weight: 600;
      transition: background-color 0.2s;
    }
    .dashboard-controls button:hover {
      background-color: #065c9c;
    }
    .dashboard-controls .last-update {
      color: grey;
      font-size: 0.8rem;
    }
    /* Stats Row */
    .stats-row {
      display: flex;
      gap: 1rem;
      margin-bottom: 2rem;
      flex-wrap: wrap;
    }
    .stat-card {
      flex: 1 1 200px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      text-align: center;
      padding: 1rem;
      min-width: 200px;
    }
    .stat-card h2 {
      font-size: 2rem;
      margin-bottom: 0.5rem;
      color: #0A74DA;
    }
    .stat-card p {
      color: #666;
    }
    /* Charts Section */
    .charts-section {
      display: flex;
      flex-direction: column;
      gap: 1rem;
      justify-content: center;
      align-items: center;
    }
    .chart-container {
      width: 100%;
      max-width: 1000px;
      height: 600px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      padding: 1rem;
      text-align: center;
      position: relative;
      margin-bottom: 1rem;
    }
    .chart-container h3 {
      margin-bottom: 0.5rem;
      color: #0A74DA;
    }
    .chart-canvas {
      width: 100% !important;
      height: 90% !important;
    }
    /* Footer */
    footer {
      background-color: #fff;
      padding: 1rem;
      text-align: center;
      font-size: 0.9rem;
      color: #666;
      border-top: 1px solid #ddd;
    }
  </style>
</head>
<body>
  <!-- Header -->
  <header>
    <h1>FinSight Dashboard</h1>
    <nav class="header-nav">
      <a href="customization.php">Dashboard Customization</a>
      <a href="logout.html">Logout</a>
      <a href="help.html" class="help-button">Help</a>
    </nav>
  </header>
  <!-- Main Layout: Sidebar + Content -->
  <div class="layout">
    <!-- Sidebar (Fixed) -->
    <aside class="sidebar">
      <h2>Trending Topics</h2>
      <p>Explore the articles and a summary by clicking the topics below.</p>
      <table>
        <thead>
          <tr>
            <th>Topic</th>
            <th># Articles</th>
          </tr>
        </thead>
        <tbody id="trending-topics-body">
          <!-- Dynamically populated -->
        </tbody>
      </table>
    </aside>
    <!-- Main Content Area -->
    <main class="content">
      <!-- Dashboard Controls -->
      <div class="dashboard-controls">
        <button onclick="location.reload()">Refresh</button>
        <span id="lastUpdate" class="last-update"></span>
      </div>
      <!-- Stats Row -->
      <div class="stats-row">
        <div class="stat-card">
          <h2 id="articles-tracked">0</h2>
          <p>Articles Tracked</p>
        </div>
      </div>
      <!-- Charts Section -->
      <div class="charts-section">
        <!-- Line Chart: Weekly Trend (only topics with ≥100 articles) -->
        <div class="chart-container" id="lineChartContainer">
          <h3>Weekly Trend (≥ 10 Articles)</h3>
          <canvas id="lineChart" class="chart-canvas"></canvas>
        </div>
        <!-- Scatter Plot: Topic Hotness -->
        <div class="chart-container" id="scatterChartContainer">
          <h3>Topic Hotness Distribution</h3>
          <canvas id="scatterChart" class="chart-canvas"></canvas>
        </div>
        <!-- Bar Chart: Daily Top Topic -->
        <div class="chart-container" id="barChartContainer">
          <h3>Daily Top Topic</h3>
          <canvas id="barChart" class="chart-canvas"></canvas>
        </div>
      </div>
    </main>
  </div>
  <!-- Footer -->
  <footer>
    <p>&copy; 2025 FinSight. All rights reserved.</p>
  </footer>
  
  <!-- Include Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
  <!-- Include the Date Adapter for time scales -->
  <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
  
  <script>
    // Helper: generate a random color (for line chart series)
    function getRandomColor() {
      const letters = '0123456789ABCDEF';
      let color = '#';
      for(let i = 0; i < 6; i++){
        color += letters[Math.floor(Math.random() * 16)];
      }
      return color;
    }

    // Update Line Chart:
    // topicTrends is an object { topic: [count_day1, ..., count_day7], ... }
    // daysLabels is an array of 7 day strings.
    function updateLineChart(daysLabels, topicTrends) {
      const datasets = [];
      const benchmark = 10;
      Object.keys(topicTrends).forEach(topic => {
        const counts = topicTrends[topic];
        const total = counts.reduce((sum, count) => sum + count, 0);
        if (total >= benchmark) {
          datasets.push({
            label: topic,
            data: counts,
            borderColor: getRandomColor(),
            backgroundColor: 'rgba(0,0,0,0)',
            tension: 0.1
          });
        }
      });
      if (datasets.length === 0) {
        document.getElementById('lineChartContainer').innerHTML = "<p>No topics meet the benchmark of " + benchmark + " articles.</p>";
        return;
      }
      const ctxLine = document.getElementById('lineChart').getContext('2d');
      new Chart(ctxLine, {
        type: 'line',
        data: {
          labels: daysLabels,
          datasets: datasets
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: { beginAtZero: true, title: { display: true, text: '# Articles' } },
            x: { title: { display: true, text: 'Day' } }
          }
        }
      });
    }

    // Update Scatter Chart:
    // For each topic in topicTrends, compute the day where it peaked.
    // Each data point is {x: peakDay, y: peakCount, label: topic}.
    function updateScatterChart(daysLabels, topicTrends) {
      const dataPoints = [];
      Object.keys(topicTrends).forEach(topic => {
        const counts = topicTrends[topic];
        let maxCount = Math.max(...counts);
        let peakIndex = counts.indexOf(maxCount);
        let peakDay = daysLabels[peakIndex];
        
        // Color coding based on the peak count (adjust thresholds as needed)
        let pointColor = '#95a5a6'; // default
        if (maxCount >= 10 && maxCount <= 20) {
          pointColor = '#3498db';
        } else if (maxCount >= 50 && maxCount <= 60) {
          pointColor = '#f1c40f';
        } else if (maxCount > 100) {
          pointColor = '#e74c3c';
        }
        dataPoints.push({
          x: peakDay,
          y: maxCount,
          label: topic,
          backgroundColor: pointColor
        });
      });
      const ctxScatter = document.getElementById('scatterChart').getContext('2d');
      new Chart(ctxScatter, {
        type: 'scatter',
        data: {
          datasets: [{
            label: 'Topic Hotness',
            data: dataPoints,
            pointRadius: 6,
            pointHoverRadius: 8
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            x: {
              type: 'time',
              time: { unit: 'day' },
              title: { display: true, text: 'Day' }
            },
            y: {
              beginAtZero: true,
              title: { display: true, text: '# Articles' }
            }
          },
          plugins: {
            tooltip: {
              callbacks: {
                label: function(context) {
                  let label = context.raw.label || '';
                  return label + ': ' + context.parsed.y + ' articles on ' + context.raw.x;
                }
              }
            }
          }
        }
      });
    }

    // Update Bar Chart:
    // Uses daily_top_topic data (an array of objects: {day, topic, count})
    function updateBarChart(labels, data, topTopics) {
      const ctxBar = document.getElementById('barChart').getContext('2d');
      new Chart(ctxBar, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
            label: 'Daily Top Topic',
            data: data,
            backgroundColor: ['#0A74DA', '#f39c12', '#2ecc71', '#e74c3c', '#9b59b6', '#34495e', '#16a085']
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: { beginAtZero: true, title: { display: true, text: '# Articles' } },
            x: { title: { display: true, text: 'Day' } }
          },
          plugins: {
            tooltip: {
              callbacks: {
                label: function(context) {
                  let index = context.dataIndex;
                  let topic = topTopics[index] || '';
                  return topic + ': ' + context.parsed.y + ' articles';
                }
              }
            },
            legend: { display: false }
          }
        }
      });
    }

    document.addEventListener("DOMContentLoaded", function () {
      fetch('get_dashboard_data.php')
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(data => {
          console.log('Fetched Data:', data);
          // Validate data structure
          if (!data || !Array.isArray(data.trending_topics) || typeof data.total_articles !== 'number') {
            throw new Error('Invalid data structure');
          }
          // Update Sidebar Trending Topics
          const tbody = document.querySelector('#trending-topics-body');
          tbody.innerHTML = data.trending_topics.map(topic => `
            <tr>
              <td><a href="trending_topics.html?topic=${encodeURIComponent(topic.Topic)}" class="topic-link">${topic.Topic}</a></td>
              <td>${topic.article_count}</td>
            </tr>
          `).join('');
          // Update Articles Count and Last Update Time
          document.getElementById('articles-tracked').textContent = data.total_articles;
          document.getElementById('lastUpdate').textContent = 'Last updated: ' + data.last_update_time;
          
          // Retrieve chart data from backend
          const daysLabels = data.chart_data.days_labels;           // Array of 7 day strings
          const topicTrends = data.chart_data.topic_trends;           // Object: { topic: [count_day1,...,count_day7], ... }
          const dailyTop = data.chart_data.daily_top_topic;           // Array of objects: { day, topic, count }
          
          // Prepare bar chart arrays
          const barLabels = dailyTop.map(item => item.day);
          const barData = dailyTop.map(item => item.count);
          const topTopics = dailyTop.map(item => item.topic);
          
          // Render charts
          updateLineChart(daysLabels, topicTrends);
          updateScatterChart(daysLabels, topicTrends);
          updateBarChart(barLabels, barData, topTopics);
          
          // Check user preferences for visible chart types, if provided
          if (data.user_preferences && data.user_preferences.visible_chart_types) {
            const visibleCharts = data.user_preferences.visible_chart_types.split(',').map(item => item.trim().toLowerCase());
            if (!visibleCharts.includes('line')) {
              document.getElementById('lineChartContainer').style.display = 'none';
            }
            if (!visibleCharts.includes('scatter')) {
              document.getElementById('scatterChartContainer').style.display = 'none';
            }
            if (!visibleCharts.includes('bar')) {
              document.getElementById('barChartContainer').style.display = 'none';
            }
          }
        })
        .catch(error => {
          console.error('Error fetching dashboard data:', error);
          alert('Failed to load dashboard data. Please try again.');
        });
    });
  </script>
  <!-- Optionally show user preferences -->
  <div id="userPreferences" style="margin: 20px; text-align: center;"></div>
</body>
</html>
