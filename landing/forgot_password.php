<?php
session_start();
$message = '';
$message_type = '';

if (isset($_SESSION['reset_message'])) {
    $message = $_SESSION['reset_message'];
    $message_type = $_SESSION['reset_message_type'];
    unset($_SESSION['reset_message']);
    unset($_SESSION['reset_message_type']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>HTDeal - Forgot Password</title>
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

  /* header {
    background-color: #6e22dd;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 4px 20px rgba(110, 34, 221, 0.4);
    transition: all 0.3s ease;
}

.navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;  
    width: 100%;
}

.logo img {
    flex: 0 0 auto;
}

.nav-links {
    flex: 1; 
    display: flex;
    gap: 35px;
    justify-content: center;
}

.nav-links a {
    color: #fff;
    text-decoration: none;
    font-weight: 600;
    font-size: 15px;
    transition: all 0.3s ease;
    position: relative;
    padding: 5px 0;
}

.nav-links a:hover::after {
    width: 100%;
}

.nav-links a::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: #fff;
    transition: width 0.3s ease;
}

.nav-links a:hover {
    color: #ccc;
}

.profile-icon {
    flex: 0 0 auto;
}

.profile-icon img {
    width: 40px;
    height: 40px;
    cursor: pointer;
    margin-left: 25px;
    border-radius: 50%;
    border: 2px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
}

.profile-icon img:hover {
    border-color: #fff;
    transform: scale(1.1);
} */
  
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
  
  .forgot-container {
    width: 320px;
  }
  
  /* Profile image fixed saiz */
  .profile-image {
    width: 160px;
    height: 160px;
    margin-bottom: 10px;
  }
  
  .forgot-container p {
    color: #aaa;
    margin-bottom: 20px;
    font-size: 14px;
    line-height: 1.6;
  }
  
  input[type="email"] {
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
    display: inline-block;
    background-color: #6a42e6; /* Contoh warna ungu, ikut warna asal */
    color: white;
    width: 100%;
    padding: 15px 80px;
    border: none;
    border-radius: 15px;
    text-align: center;
    text-decoration: none; /* Hilangkan underline untuk <a> */
    font-weight: bold;
    cursor: pointer;
    font-family: inherit;
    font-size: 14px;
    transition: background-color 0.3s ease;
  }
  .btn:hover {
    background-color: #5429cc;
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
  
  .success-message {
    color: #22c55e;
    font-size: 14px;
    margin: 10px 0;
    padding: 10px;
    background-color: rgba(34, 197, 94, 0.1);
    border: 2px solid #22c55e;
    border-radius: 8px;
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
</style>
</head>
<body>
  <?php include 'header.php'; ?>
  <br><br><br>
  <main>
    <img src="../picture/profileicon2.png" alt="Profile" class="profile-image" />
    <h2>Forgot Password?</h2>
    
    <?php if (!empty($message)): ?>
      <div class="<?php echo $message_type == 'success' ? 'success-message' : 'error-message'; ?>">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>
    
    <div class="forgot-container">
      <br>
      <p>No worries! Enter your email address and we'll send you a link to reset your password.</p>
      
      <form method="POST" action="send_reset_email.php">
        <input 
          type="email" 
          name="email" 
          placeholder="Enter your email address" 
          required 
          autocomplete="email"
        />
        <button type="submit" class="btn">Send Reset Link</button>
      </form>
      
      <div class="back-to-login">
        <a href="loginpelanggan.php">Back to Login</a>
      </div>
    </div>
  </main>
  <br><br><br>
  <?php include 'footer.php'; ?>
</body>
</html>