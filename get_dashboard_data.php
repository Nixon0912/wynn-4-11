<?php
// Start the session at the top of the file.
session_start();

// Check if the user is logged in.
// If not, return a JSON error message and stop processing.
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    die(json_encode(['error' => 'User not logged in.']));
}

// Database configuration
$servername = "localhost";  // Define the server name
$username = "root";         // Replace with your database username if different
$password = "";             // Replace with your database password if different
$dbname = "wynn_fyp";

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check for a connection error
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// --- Fetch trending topics and total articles ---
$sql = "SELECT Title, Content FROM topics_file";
$result = $conn->query($sql);

$trendingTopics = [];
$totalArticles = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Aggregate word counts using the comma-delimited keywords in Content field.
        $words = explode(',', $row['Content']);
        foreach ($words as $word) {
            $word = trim($word); // Remove extra spaces
            if (!empty($word)) {
                $trendingTopics[$word] = ($trendingTopics[$word] ?? 0) + 1;
            }
        }
        // Count total articles
        $totalArticles++;
    }
}

// Sort topics by frequency in descending order and get top 10 topics.
arsort($trendingTopics);
$trendingTopics = array_slice($trendingTopics, 0, 10, true);

// --- Fetch user dashboard preferences ---
$user_preferences = null;
if (isset($_SESSION["user_id"])) {
    $user_id = (int) $_SESSION["user_id"];
    $stmt_pref = $conn->prepare("SELECT visible_chart_types, data_timeframe FROM user_dashboard_preferences WHERE User_ID = ?");
    if ($stmt_pref) {
        $stmt_pref->bind_param("i", $user_id);
        $stmt_pref->execute();
        $result_pref = $stmt_pref->get_result();
        if ($row_pref = $result_pref->fetch_assoc()) {
            // The preferences are fetched as stored. If your column is a SET type or comma‑separated text,
            // you may format it as needed before sending.
            $user_preferences = [
                "visible_chart_types" => $row_pref["visible_chart_types"],
                "data_timeframe" => $row_pref["data_timeframe"]
            ];
        }
        $stmt_pref->close();
    }
}

// --- Prepare the final JSON output ---
$output = [
    'trending_topics' => array_map(function ($topic, $count) {
        return ['Topic' => $topic, 'article_count' => $count];
    }, array_keys($trendingTopics), $trendingTopics),
    'total_articles'  => $totalArticles,
    'user_preferences'=> $user_preferences  // This will be null if no record was found.
];

echo json_encode($output);

$conn->close();
?>
