<?php
session_start();
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "wynn_fyp";
$days_to_track = 7;
$top_n_topics = 10;

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}
$conn->set_charset("utf8mb4");

$date_limit = date('Y-m-d', strtotime("-{$days_to_track} days + 1 day"));

$all_topic_counts = [];
$daily_topic_counts = [];
$totalArticles = 0;
$period_dates_full = [];
for ($i = 0; $i < $days_to_track; $i++) {
    $period_dates_full[] = date('Y-m-d', strtotime("-$i days"));
}
$period_dates_full = array_reverse($period_dates_full); // Oldest to newest

$sql = "SELECT Content, DATE(created_time) as article_date FROM topics_file WHERE created_time >= ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    die(json_encode(['error' => 'SQL prepare failed: ' . $conn->error]));
}
$stmt->bind_param("s", $date_limit);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $totalArticles++;
        $current_date = $row['article_date'];
        if (!isset($daily_topic_counts[$current_date])) {
            $daily_topic_counts[$current_date] = [];
        }
        $keywords = explode(',', $row['Content'] ?? '');
        foreach ($keywords as $keyword) {
            $keyword = trim($keyword);
            if (!empty($keyword)) {
                $all_topic_counts[$keyword] = ($all_topic_counts[$keyword] ?? 0) + 1;
                $daily_topic_counts[$current_date][$keyword] = ($daily_topic_counts[$current_date][$keyword] ?? 0) + 1;
            }
        }
    }
    $stmt->close();
} else {
    http_response_code(500);
    die(json_encode(['error' => 'SQL execute failed: ' . $stmt->error]));
}

// Sidebar Trending Topics: Top N overall topics
arsort($all_topic_counts);
$sidebar_topics_data = array_slice($all_topic_counts, 0, $top_n_topics, true);
$sidebar_topics_output = array_map(function ($topic, $count) {
    return ['Topic' => $topic, 'article_count' => $count];
}, array_keys($sidebar_topics_data), $sidebar_topics_data);

// Prepare topic trends for line and scatter charts using only top topics
$top_topic_names = array_keys($sidebar_topics_data);
$topic_trends_output = [];
foreach ($top_topic_names as $topic) {
    $topic_trends_output[$topic] = array_fill(0, $days_to_track, 0);
}
$days_labels = [];
foreach ($period_dates_full as $index => $date_str) {
    $days_labels[] = $date_str;
    if (isset($daily_topic_counts[$date_str])) {
        foreach ($top_topic_names as $topic) {
            if (isset($daily_topic_counts[$date_str][$topic])) {
                $topic_trends_output[$topic][$index] = $daily_topic_counts[$date_str][$topic];
            }
        }
    }
}

// Prepare Daily Top Topic for Bar Chart
$daily_top_topic_output = [];
foreach ($period_dates_full as $date_str) {
    $top_topic = "N/A";
    $max_count = 0;
    if (isset($daily_topic_counts[$date_str]) && !empty($daily_topic_counts[$date_str])) {
        arsort($daily_topic_counts[$date_str]);
        $top_topic = key($daily_topic_counts[$date_str]);
        $max_count = current($daily_topic_counts[$date_str]);
    }
    $daily_top_topic_output[] = [
        "day" => date('D', strtotime($date_str)),
        "topic" => $top_topic,
        "count" => $max_count
    ];
}

// Fetch User Preferences (if available)
$user_preferences = null;
if (isset($_SESSION["user_id"])) {
    $user_id = (int) $_SESSION["user_id"];
    $stmt_pref = $conn->prepare("SELECT visible_chart_types FROM user_dashboard_preferences WHERE User_ID = ?");
    if ($stmt_pref) {
        $stmt_pref->bind_param("i", $user_id);
        $stmt_pref->execute();
        $result_pref = $stmt_pref->get_result();
        if ($row_pref = $result_pref->fetch_assoc()) {
            $user_preferences = [
                "visible_chart_types" => $row_pref["visible_chart_types"],
            ];
        }
        $stmt_pref->close();
    }
}

$output = [
    'trending_topics'  => $sidebar_topics_output,
    'total_articles'   => $totalArticles,
    'last_update_time' => date('Y-m-d H:i:s'),
    'chart_data' => [
        'days_labels'      => $days_labels,
        'topic_trends'     => $topic_trends_output,
        'daily_top_topic'  => $daily_top_topic_output
    ],
    'user_preferences' => $user_preferences
];

echo json_encode($output, JSON_PRETTY_PRINT);
$conn->close();
?>
