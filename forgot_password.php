<!-- forgot_password.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Forgot Password - FinSight</title>
  <style>
    /* Basic reset and body styling */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: Arial, sans-serif;
      background-color: #f0f0f5;
    }

    /* Header styling */
    .header {
      background-color: #007bff; /* FinSight blue; adjust as needed */
      color: #fff;
      padding: 20px;
      text-align: center;
    }
    .header h1 {
      margin: 0;
      font-size: 24px;
    }

    /* Main container centers the form vertically */
    .container {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 80vh; /* Enough height to center the form nicely */
      padding: 0 20px;
    }

    /* Card-style form box */
    .forgot-password-card {
      background-color: #fff;
      width: 360px;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .forgot-password-card h2 {
      text-align: center;
      margin-bottom: 20px;
    }

    /* Label and input fields */
    .forgot-password-card label {
      display: block;
      margin-bottom: 6px;
      font-weight: bold;
      font-size: 14px;
    }
    .forgot-password-card input[type="email"] {
      width: 100%;
      padding: 10px;
      font-size: 14px;
      border: 1px solid #ccc;
      border-radius: 4px;
      margin-bottom: 20px;
    }

    /* Buttons styling */
    .forgot-password-card button {
      width: 100%;
      padding: 10px;
      font-size: 16px;
      font-weight: bold;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      transition: background-color 0.3s ease;
      margin-bottom: 10px;
    }
    .forgot-password-card button[type="submit"] {
      color: #fff;
      background-color: #007bff;
    }
    .forgot-password-card button[type="submit"]:hover {
      background-color: #0056b3;
    }
    .forgot-password-card button.back {
      color: #007bff;
      background-color: #fff;
      border: 2px solid #007bff;
    }
    .forgot-password-card button.back:hover {
      background-color: #007bff;
      color: #fff;
    }

    /* Footer styling */
    .footer {
      text-align: center;
      padding: 10px;
      font-size: 12px;
      color: #999;
    }
  </style>
</head>
<body>

  <!-- Header Section -->
  <div class="header">
    <h1>FinSight</h1>
  </div>

  <!-- Main Container -->
  <div class="container">
    <div class="forgot-password-card">
      <h2>Forgot Password</h2>
      <form action="process_forgot_password.php" method="post">
        <label for="email">Enter your email address:</label>
        <input type="email" name="email" id="email" required />
        <button type="submit">Submit</button>
      </form>
      <!-- Back button -->
      <button type="button" class="back" onclick="window.history.back();">Back</button>
    </div>
  </div>

  <!-- Footer -->
  <div class="footer">
    &copy; 2025 FinSight. All rights reserved.
  </div>

</body>
</html>
