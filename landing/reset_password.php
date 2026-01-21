<?php
session_start();
require_once '../db_config.php';

$error = '';
$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$token_valid = false;
$user_data = null;

if (!empty($token)) {
    try {
        // Verify token
        $stmt = $pdo->prepare("
            SELECT pr.*, a.first_name, a.last_name 
            FROM password_resets pr
            JOIN account a ON pr.account_id = a.account_id
            WHERE pr.token = ? AND pr.expires_at > NOW()
        ");
        $stmt->execute([$token]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user_data) {
            $token_valid = true;
        } else {
            $error = "This password reset link is invalid or has expired. Please request a new one.";
        }
    } catch (PDOException $e) {
        $error = "An error occurred. Please try again.";
        error_log("Reset Password Error: " . $e->getMessage());
    }
} else {
    $error = "Invalid reset link.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>HTDeal - Reset Password</title>
<link rel="stylesheet" href="./style.css" />
<style>
  /* Body sebagai flex container, column */
  body {
    background-color: #121212;
    font-family: Arial, sans-serif;
    color: #fff;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    margin: 0;
  }
  
  /* Main content ambil ruang berbaki */
  main {
    flex-grow: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    padding: 20px;
    text-align: center;
  }
  
  .reset-container {
    width: 320px;
  }
  
  /* Profile image fixed saiz */
  .profile-image {
    width: 160px;
    height: 160px;
    margin-bottom: 10px;
  }
  
  .reset-container p {
    color: #aaa;
    margin-bottom: 20px;
    font-size: 14px;
    line-height: 1.6;
  }
  
  input[type="password"] {
    width: 100%;
    padding: 12px 20px;
    margin: 10px 0;
    box-sizing: border-box;
    border-radius: 15px;
    border: none;
    font-size: 16px;
  }
  
  input::placeholder {
    color: #999;
  }
  
  .btn {
    width: 100%;
    background-color: #6610f2;
    color: white;
    border: none;
    border-radius: 15px;
    padding: 15px 0;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    margin-top: 20px;
    transition: background-color 0.3s ease;
  }
  
  .btn:hover {
    background-color: #520dc1;
  }
  
  .btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
  
  .back-to-login {
    margin-top: 15px;
    font-size: 12px;
    color: #aaa;
  }
  
  .back-to-login a {
    color: white;
    text-decoration: none;
    cursor: pointer;
  }
  
  .back-to-login a:hover {
    text-decoration: underline;
  }
  
  /* Footer sentiasa di bawah */
  footer {
    text-align: center;
    padding: 10px 0;
    background-color: #1a1a1a;
    font-size: 14px;
    color: #777;
  }
  
  .error-message {
    color: #ff4444;
    font-size: 14px;
    margin: 10px 0;
    padding: 10px;
    background-color: rgba(255, 68, 68, 0.1);
    border: 2px solid #ff4444;
    border-radius: 8px;
  }
  
  .password-requirements {
    text-align: left;
    background-color: rgba(102, 16, 242, 0.1);
    border-radius: 8px;
    padding: 15px;
    margin: 15px 0;
    font-size: 13px;
  }
  
  .password-requirements h4 {
    color: #6610f2;
    margin-bottom: 10px;
    font-size: 14px;
    font-weight: bold;
  }
  
  .password-requirements ul {
    margin: 0;
    padding-left: 20px;
    color: #aaa;
  }
  
  .password-requirements li {
    margin: 5px 0;
  }
</style>
</head>
<body>
  <?php include 'header.php'; ?>
  
  <main>
    <?php if ($token_valid): ?>
      <img src="../picture/profileicon2.png" alt="Profile" class="profile-image" />
      <h2>Set New Password</h2>
      <br>
      <p>Hello <?php echo htmlspecialchars($user_data['first_name']); ?>! Enter your new password below.</p>
      <br>
      
      <div class="reset-container">
        <form method="POST" action="process_reset.php" id="resetForm">
          <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>" />
          
          <input 
            type="password" 
            name="password" 
            id="password"
            placeholder="New Password" 
            required 
            minlength="6"
          />
          
          <input 
            type="password" 
            name="confirm_password" 
            id="confirm_password"
            placeholder="Confirm New Password" 
            required 
            minlength="6"
          />
          
          <div class="password-requirements">
            <h4>Password must contain:</h4>
            <ul>
              <li>At least 6 characters</li>
            </ul>
          </div>
          
          <button type="submit" class="btn">Reset Password</button>
        </form>
        
        <div class="back-to-login">
          <a href="loginpelanggan.php">Back to Login</a>
        </div>
      </div>
      
    <?php else: ?>
      <img src="../picture/profileicon2.png" alt="Profile" class="profile-image" />
      <h2>Invalid Link</h2>
      
      <div class="error-message">
        <?php echo htmlspecialchars($error); ?>
      </div>
      
      <div class="reset-container">
        <div class="back-to-login">
          <a href="forgot_password.php">← Request New Reset Link</a>
        </div>
        <div class="back-to-login">
          <a href="loginpelanggan.php">Back to Login</a>
        </div>
      </div>
    <?php endif; ?>
  </main>
  
  <?php include 'footer.php'; ?>
  
  <script>
  document.getElementById('resetForm')?.addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
      e.preventDefault();
      alert('Passwords do not match!');
      return false;
    }
    
    // Check password length only
    if (password.length < 6) {
      e.preventDefault();
      alert('Password must be at least 6 characters!');
      return false;
    }
  });
  </script>
</body>
</html>