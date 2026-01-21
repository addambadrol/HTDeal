<?php
session_start();
require_once '../db_config.php';

$error = '';
$success = '';

// Check for success message from password reset
if (isset($_SESSION['login_success'])) {
    $success = $_SESSION['login_success'];
    unset($_SESSION['login_success']);
}

// Process login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password!";
    } else {
        try {
            // Check user in database
            $stmt = $pdo->prepare("SELECT * FROM account WHERE email = ? AND role = 'pelanggan'");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Check if account is active
                if ($user['status'] != 'active') {
                    $error = "Your account is not active. Please contact admin.";
                } else {
                    // Set session variables
                    $_SESSION['account_id'] = $user['account_id'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['phone_number'] = $user['phone_number'];
                    $_SESSION['country_code'] = $user['country_code'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['profile_picture'] = $user['profile_picture'];
                    
                    // Update last login
                    $updateStmt = $pdo->prepare("UPDATE account SET last_login = NOW() WHERE account_id = ?");
                    $updateStmt->execute([$user['account_id']]);
                    
                    // Redirect to homepage
                    header("Location: ../pelanggan/homepage.php");
                    exit();
                }
            } else {
                $error = "Invalid email or password!";
            }
        } catch(PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?> 
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>HTDeal - Log In Customer</title>
<link rel="stylesheet" href="./style.css" />
<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  /* Body sebagai flex container, column */
  body {
    background-color: #121212;
    font-family: Arial, sans-serif;
    color: #fff;
    display: flex;
    flex-direction: column; /* susun mengikut kolum dari atas ke bawah */
    min-height: 100vh; /* tinggi minimum penuh viewport */
    margin: 0;
  }
  /* Main content ambil ruang berbaki */
  main {
    flex-grow: 1; /* kandungan utama ambil ruang yang ada */
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    padding: 20px;
    text-align: center;
  }
  .login-container {
    width: 320px;
  }
  input[type="email"],
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
  .forgot-password {
    font-size: 12px;
    color: #aaa;
    margin: 5px 0 15px;
  }
  button {
    width: 100%;
    background-color: #6610f2; /* Ungu */
    color: white;
    border: none;
    border-radius: 15px;
    padding: 15px 0;
    font-size: 16px;
    cursor: pointer;
  }
  button:hover {
    background-color: #520dc1;
  }
  .create-account, .extra-login {
    font-size: 12px;
    color: #aaa;
    margin-top: 15px;
  }
  .extra-login p {
    margin: 5px 0;
  }
  /* Footer sentiasa di bawah */
  footer {
    text-align: center;
    padding: 10px 0;
    background-color: #1a1a1a;
    font-size: 14px;
    color: #777;
  }
  /* Profile image fixed saiz */
  .profile-image {
    width: 160px;
    height: 160px;
    margin-bottom: 10px;
  }
  .forgot-password a,
  .create-account a,
  .extra-login a {
    color: white;       /* Warna putih */
    text-decoration: none; /* Buang underline */
    cursor: pointer;
  }
  .forgot-password a:hover,
  .create-account a:hover,
  .extra-login a:hover {
    text-decoration: underline; /* Opsional: underline bila hover */
  }
  .btn {
    display: inline-block;
    background-color: #6a42e6; /* Contoh warna ungu, ikut warna asal */
    color: white;
    padding: 10px 130px;
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
  .error-message {
    color: #ff4444;
    font-size: 14px;
    margin: 10px 0;
    padding: 10px;
    background-color: rgba(255, 68, 68, 0.1);
    border-radius: 8px;
    display: <?php echo !empty($error) ? 'block' : 'none'; ?>;
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
</style>
</head>
<body>
  <?php include 'header.php'; ?>
<main>
  <br><br><br>
  <img src="../picture/profileicon2.png" alt="Profile" class="profile-image" />
  <h2>Log In To HTDeal</h2>

  <?php if (!empty($success)): ?>
  <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
  
  <?php if (!empty($error)): ?>
    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  
  <div class="login-container">
    <form method="POST" action="">
      <input type="email" name="email" placeholder="E-Mail" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
      <input type="password" name="password" placeholder="Password" required />
      <div class="forgot-password"><a href="forgot_password.php">Forgotten your password?</a></div>
      <button type="submit" class="btn">LOG IN</button>
    </form>
    
    <div class="create-account">
      Don't have an account? <a href="signuppelanggan.php">Create one now.</a>
    </div>
    <div class="extra-login">
      <p>Are you a seller? <a href="loginpenjual.php">Log In now.</a></p>
      <p>Are you an admin? <a href="loginadmin.php">Log In now.</a></p>
    </div>
  </div>
  <br><br><br>
</main>
<?php include 'footer.php'; ?>
</body>
</html>