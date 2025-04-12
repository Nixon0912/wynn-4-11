<?php
// reset_password.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require 'reset_password_config.php';

if (!isset($_GET['token'])) {
    echo "Invalid or missing token.";
    exit;
}

$token = $_GET['token'];

// Verify that the token exists in the password_resets table.
// (Optionally, you could also check for token expiry if you add that field.)
$stmt = $pdo->prepare("SELECT user_id FROM password_resets WHERE token = ?");
$stmt->execute([$token]);
$resetRequest = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resetRequest) {
    echo "Invalid or expired token.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password - FinSight</title>
  <style>
    /* Reset and Body Styling */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: Arial, sans-serif;
      background-color: #f0f0f5;
    }
    /* Header Styling */
    .header {
      background-color: #007bff;
      color: #fff;
      padding: 20px;
      text-align: center;
    }
    .header h1 {
      margin: 0;
      font-size: 24px;
    }
    /* Container to center the card */
    .container {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 80vh;
      padding: 0 20px;
    }
    /* Card Styling */
    .reset-password-card {
      background-color: #fff;
      width: 360px;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .reset-password-card h2 {
      text-align: center;
      margin-bottom: 20px;
    }
    /* Form Styling */
    .reset-password-card label {
      display: block;
      margin-bottom: 6px;
      font-weight: bold;
      font-size: 14px;
    }
    .reset-password-card input[type="password"] {
      width: 100%;
      padding: 10px;
      font-size: 14px;
      border: 1px solid #ccc;
      border-radius: 4px;
      margin-bottom: 20px;
    }
    .reset-password-card button[type="submit"] {
      width: 100%;
      padding: 10px;
      font-size: 16px;
      font-weight: bold;
      border: none;
      border-radius: 4px;
      color: #fff;
      background-color: #007bff;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .reset-password-card button[type="submit"]:hover {
      background-color: #0056b3;
    }
    /* Footer Styling */
    .footer {
      text-align: center;
      padding: 10px;
      font-size: 12px;
      color: #999;
    }
  </style>
</head>
<body>
  <!-- Header -->
  <div class="header">
    <h1>FinSight</h1>
  </div>

  <!-- Main Container -->
  <div class="container">
    <div class="reset-password-card">
      <h2>Reset Your Password</h2>
      <form id="resetForm" action="process_reset_password.php" method="post">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <label for="password">Enter New Password:</label>
        <input type="password" name="password" id="password" required>
        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" name="confirm_password" id="confirm_password" required>
        <button type="submit">Reset Password</button>
      </form>
    </div>
  </div>

  <!-- Footer -->
  <div class="footer">
    &copy; 2025 FinSight. All rights reserved.
  </div>

  <!-- JavaScript for Password Validation -->
  <script>
    // Reference the form element
    const resetForm = document.getElementById('resetForm');

    resetForm.addEventListener('submit', function(e) {
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirm_password').value;
      
      // Validate minimum length
      if (password.length < 8) {
        alert('Password must be at least 8 characters long.');
        e.preventDefault();
        return;
      }
      
      // Validate at least one uppercase letter
      const uppercasePattern = /[A-Z]/;
      if (!uppercasePattern.test(password)) {
        alert('Password must include at least one uppercase letter.');
        e.preventDefault();
        return;
      }
      
      // Validate matching confirmation
      if (password !== confirmPassword) {
        alert('Passwords do not match.');
        e.preventDefault();
        return;
      }
    });
  </script>
</body>
</html>
