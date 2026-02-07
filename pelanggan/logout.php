<?php
session_start();

// Store role before destroying session
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'pelanggan';

// Destroy all session data
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - Logging Out</title>
  <link rel="stylesheet" href="./style.css" />
  <style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }

  body {
    background-color: #121212;
    color: #fff;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }

  /* header {
    background-color: #6e22dd;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .navbar {
    display: flex;
    align-items: center;
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
  }

  .logo img {
    height: 35px;
  }

  .nav-links {
    display: flex;
    gap: 25px;
    margin-left: auto;
  }

  .nav-links a {
    color: #fff;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: color 0.3s;
  }

  .nav-links a:hover {
    color: #ccc;
  }

  .profile-icon img {
    width: 35px;
    height: 35px;
    cursor: pointer;
    margin-left: 20px;
  } */

  .logout-container {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    padding: 40px 20px;
  }

  .logout-content {
    background-color: #1a1a1a;
    padding: 60px 80px;
    border-radius: 20px;
    border: 3px solid #6e22dd;
    max-width: 500px;
  }

  .logout-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto 30px;
    background-color: #6e22dd;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 50px;
  }

  .logout-title {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 15px;
    color: #fff;
  }

  .logout-message {
    font-size: 16px;
    color: #aaa;
    margin-bottom: 30px;
    line-height: 1.6;
  }

  .spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #333;
    border-top: 5px solid #6e22dd;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto;
  }

  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }

  footer {
    text-align: center;
    padding: 15px 20px;
    background-color: #1a1a1a;
    font-size: 12px;
    color: #777;
    letter-spacing: 0.6px;
  }

  @media (max-width: 768px) {
    .nav-links {
      display: none;
    }

    .logout-content {
      padding: 40px 30px;
    }

    .logout-title {
      font-size: 24px;
    }
  }
  </style>
</head>
<body>
  <?php include 'header.php'; ?>

  <div class="logout-container">
    <div class="logout-content">
      <div class="logout-icon">👋</div>
      <h1 class="logout-title">Logging Out...</h1>
      <p class="logout-message">Thank you for using HTDeal. You will be redirected to the login page shortly.</p>
      <div class="spinner"></div>
    </div>
  </div>

  <?php include 'footer.php'; ?>

  <script>
    // Redirect after 2 seconds
    setTimeout(function() {
      window.location.href = '../landing/homepage.php';
    }, 2000);
  </script>
</body>
</html>