<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start(); // Start session for feedback messages

require_once 'admin_db_connect.php'; // Adjust path if needed

// Attempt to get User ID from GET first, fall back to session
$user_id = null;
if (isset($_GET['user_id']) && filter_var($_GET['user_id'], FILTER_VALIDATE_INT)) {
    $user_id = (int)$_GET['user_id'];
} elseif (isset($_SESSION['user_id']) && filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT)) {
    $user_id = (int)$_SESSION['user_id'];
} else {
    error_log("Customization page access attempt without valid User ID.");
    die("Error: User ID not specified or invalid. Please access via your dashboard.");
}

// --- Initialize variables ---
$current_prefs = [
    'visible_chart_types' => ['line', 'bar', 'scatter'], // Default charts
];
$message = '';
$message_type = ''; // 'success' or 'error'
$fetch_error = null;

// --- Check for feedback messages from save action ---
if (isset($_SESSION['form_message'])) {
    $message = $_SESSION['form_message'];
    $message_type = $_SESSION['form_message_type'] ?? 'error'; // Default to error if type not set
    unset($_SESSION['form_message']); // Clear message after retrieving
    unset($_SESSION['form_message_type']);
}

// --- Fetch Current Preferences ---
if (!$conn) {
    $fetch_error = 'Database connection object not created.';
    error_log("Customization Error: db_connect.php failed to provide a connection object.");
} elseif ($conn->connect_error) {
    $fetch_error = 'Database connection failed: ' . $conn->connect_error;
    error_log("Customization DB Connection Error: " . $conn->connect_error);
} else {
    $stmt_fetch = $conn->prepare("SELECT visible_chart_types FROM user_dashboard_preferences WHERE User_ID = ?");
    if ($stmt_fetch) {
        $stmt_fetch->bind_param("i", $user_id);
        $stmt_fetch->execute();
        $result = $stmt_fetch->get_result();
        if ($pref_data = $result->fetch_assoc()) {
            // Since visible_chart_types is stored as a comma-separated string,
            // we split it into an array.
            $charts_from_db = $pref_data['visible_chart_types'] ?? '';
            if (!empty($charts_from_db)) {
                $decoded_charts = explode(',', $charts_from_db);
                // Trim any extra whitespace from each chart type.
                $decoded_charts = array_map('trim', $decoded_charts);
                $current_prefs['visible_chart_types'] = $decoded_charts;
            } else {
                error_log("Customization Warning: No chart preferences found for User_ID $user_id. Using defaults.");
            }

        } else {
            // No prefs found, defaults are in effect.
            // echo "<!-- DEBUG: No preferences found for user $user_id -->";
        }
        $stmt_fetch->close();
    } else {
        $fetch_error = "Error preparing preferences statement: " . $conn->error;
        error_log("Customization Error preparing preferences statement for User_ID $user_id: " . $conn->error);
    }
    $conn->close(); // Close connection after fetching
}

// If there was a fetch error, display it (if no other message exists)
if ($fetch_error && !$message) {
    $message = "Warning: Could not load current preferences ({$fetch_error}). Displaying defaults.";
    $message_type = "error";
}

// --- Helper functions for form ---
function isChartSelected($type, $selected_array) {
    return in_array($type, $selected_array);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>FinSight – Dashboard Customization</title>
  <style>
    /* --- Your existing CSS from customization.html --- */
    body { margin: 0; padding: 0; font-family: Arial, sans-serif; display: flex; flex-direction: column; min-height: 100vh; background-color: #f8f9fa; }
    header { background-color: #0A74DA; color: #fff; padding: 1rem; text-align: center; }
    .container { max-width: 800px; margin: 2rem auto; text-align: center; padding: 0 1rem; flex: 1; }
    .top-buttons { display: flex; justify-content: space-between; margin-bottom: 1.5rem; }
    .top-buttons a { background-color: #0A74DA; color: #fff; text-decoration: none; border: none; padding: 0.5rem 1rem; border-radius: 4px; font-weight: bold; cursor: pointer; transition: background-color 0.2s; }
    .top-buttons a:hover { background-color: #085aab; }
    form { display: inline-block; width: 100%; max-width: 550px; text-align: left; margin-top: 1rem; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    form h3 { color: #0A74DA; margin-bottom: 1rem; margin-top: 1.5rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; }
    form h3:first-of-type { margin-top: 0; }
    label { display: block; margin: 0.8rem 0; cursor: pointer; font-weight: 500; }
    input[type="checkbox"], input[type="radio"] { margin-right: 0.7rem; vertical-align: middle; transform: scale(1.1); }
    button[type="submit"] { display: block; width: 100%; background-color: #28a745; color: #fff; border: none; padding: 0.8rem 1.5rem; border-radius: 4px; font-weight: bold; cursor: pointer; margin-top: 2rem; font-size: 1.1rem; transition: background-color 0.2s; }
    button[type="submit"]:hover { background-color: #218838; }
    footer { background-color: #f4f4f4; text-align: center; padding: 1rem; margin-top: 3rem; border-top: 1px solid #ddd; }
    /* Message Styles */
    .message { padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px; border: 1px solid transparent; text-align: center; font-weight: 500; }
    .message.success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
    .message.error { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
    .form-note { font-size: 0.85em; color: #6c757d; margin-left: 1.5rem; }
  </style>
</head>
<body>

  <header>
    <h1>Dashboard Customization</h1>
  </header>

  <main class="container">
    <div class="top-buttons">
      <!-- Link back to the specific user's dashboard -->
      <a href="dashboard.php?user_id=<?php echo $user_id; ?>">Back to Dashboard</a>
    </div>

     <?php // Display feedback message if set ?>
     <?php if ($message): ?>
      <div class="message <?php echo htmlspecialchars($message_type); ?>">
          <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <form action="save_customization.php" method="post">
      <!-- Hidden field to pass the user ID -->
      <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

      <p style="text-align: center; margin-bottom: 1.5rem; color: #555;">
          Select the charts and data time frame you wish to see on your dashboard.
      </p>

      <h3>Choose Charts to Display</h3>
      <label>
        <input type="checkbox" name="chart_types[]" value="line"
          <?php if (isChartSelected('line', $current_prefs['visible_chart_types'])) echo 'checked'; ?>>
        Line Chart <span class="form-note">(Article Trend)</span>
      </label>
      <label>
        <input type="checkbox" name="chart_types[]" value="bar"
          <?php if (isChartSelected('bar', $current_prefs['visible_chart_types'])) echo 'checked'; ?>>
        Bar Chart <span class="form-note">(Article Distribution)</span>
      </label>
      <label>
        <input type="checkbox" name="chart_types[]" value="scatter"
          <?php if (isChartSelected('scatter', $current_prefs['visible_chart_types'])) echo 'checked'; ?>>
        Scatter Plot Chart <span class="form-note">(Topic Distribution)</span>
      </label>


      <button type="submit">Save Preferences</button>
    </form>
  </main>

  <footer>
    <p>© 2025 FinSight. All rights reserved.</p>
  </footer>

</body>
</html>
