<?php
require_once '../db_config.php';

// Initialize variables
$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $phoneNumber = trim($_POST['phoneNumber']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    
    // Validation
    if (empty($firstName) || empty($lastName) || empty($phoneNumber) || empty($email) || empty($password)) {
        $error = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } elseif (!preg_match('/^[0-9]+$/', $phoneNumber)) {
        $error = "Phone number must contain only digits!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match!";
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT account_id FROM account WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Email already registered!";
            } else {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert into database
                $stmt = $pdo->prepare("INSERT INTO account (first_name, last_name, email, password, phone_number, role, status) VALUES (?, ?, ?, ?, ?, 'pelanggan', 'active')");
                
                if ($stmt->execute([$firstName, $lastName, $email, $hashedPassword, $phoneNumber])) {
                    $success = "Registration successful! Please login with your credentials.";
                    // Don't auto login - redirect to login page after 2 seconds
                    echo '<meta http-equiv="refresh" content="2;url=loginpelanggan.php">';
                } else {
                    $error = "Registration failed. Please try again.";
                }
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
<title>HTDeal - Sign Up</title>
<link rel="stylesheet" href="./style.css" />
<style>
  body {
    background-color: #0a0a0a;
    font-family: Arial, sans-serif;
    color: #fff;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    margin: 0;
  }
  main {
    flex-grow: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    padding: 20px;
    text-align: center;
  }
  .login-container {
    width: 360px;
    background-color: transparent;
  }
  input[type="text"],
  input[type="email"],
  input[type="password"],
  input[type="tel"] {
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
  .phone-input-container {
    position: relative;
    display: flex;
    gap: 5px;
    margin: 10px 0;
  }
  .country-select-wrapper {
    position: relative;
    width: 100px;
  }
  .country-select {
    width: 100%;
    padding: 12px 10px;
    border-radius: 15px;
    border: none;
    font-size: 16px;
    background-color: #fff;
    cursor: pointer;
    text-align: center;
  }
  .phone-input-wrapper {
    flex: 1;
    position: relative;
  }
  .phone-input-wrapper input {
    padding-left: 20px;
    margin: 0;
  }
  button {
    width: 100%;
    background-color: #6610f2;
    color: white;
    border: none;
    border-radius: 10px;
    padding: 15px 0;
    font-size: 16px;
    cursor: pointer;
    margin-top: 15px;
  }
  button:hover {
    background-color: #520dc1;
  }
  .signup-info {
    font-size: 12px;
    color: #aaa;
    margin-top: 8px;
  }
  .signup-info a {
    color: white;
    text-decoration: none;
    cursor: pointer;
  }
  .signup-info a:hover {
    text-decoration: underline;
  }
  footer {
    text-align: center;
    padding: 10px 0;
    background-color: #1a1a1a;
    font-size: 14px;
    color: #777;
  }
  .profile-image {
    width: 160px;
    height: 160px;
    margin-bottom: 10px;
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
    color: #4CAF50;
    font-size: 14px;
    margin: 10px 0;
    padding: 10px;
    background-color: rgba(76, 175, 80, 0.1);
    border-radius: 8px;
    display: <?php echo !empty($success) ? 'block' : 'none'; ?>;
  }
</style>
</head>
<body>
  <?php include 'header.php'; ?>
<main>
  <img src="../picture/profileicon2.png" alt="Profile" class="profile-image" />
  <h2>Sign Up To HTDeal</h2>
  
  <?php if (!empty($error)): ?>
    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  
  <?php if (!empty($success)): ?>
    <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>
  
  <div class="login-container">
    <form method="POST" action="" id="signupForm">
      <input type="text" name="firstName" id="firstName" placeholder="First Name" required value="<?php echo isset($_POST['firstName']) ? htmlspecialchars($_POST['firstName']) : ''; ?>" />
      
      <input type="text" name="lastName" id="lastName" placeholder="Last Name" required value="<?php echo isset($_POST['lastName']) ? htmlspecialchars($_POST['lastName']) : ''; ?>" />
      
      <div class="phone-input-container">
        
        <div class="phone-input-wrapper">
          <input type="tel" name="phoneNumber" id="phoneNumber" placeholder="Number Phone" required pattern="[0-9]+" value="<?php echo isset($_POST['phoneNumber']) ? htmlspecialchars($_POST['phoneNumber']) : ''; ?>" />
        </div>
      </div>
      
      <input type="email" name="email" id="email" placeholder="E-mail" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
      
      <input type="password" name="password" id="password" placeholder="Password" required minlength="6" />
      
      <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Re-Confirm Password" required />
      
      <button type="submit">SIGN UP</button>
      
      <div class="signup-info" style="margin-top: 10px;">
        Already have an account? <a href="loginpelanggan.php">Sign In</a>
      </div>
    </form><br><br>
  </div>
</main>
<script>
// Update select options to show country names
document.addEventListener('DOMContentLoaded', function() {
  
  
  // Client-side validation
  const form = document.getElementById('signupForm');
  form.addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const phoneNumber = document.getElementById('phoneNumber').value;
    
    if (!/^[0-9]+$/.test(phoneNumber)) {
      alert('Phone number must contain only digits!');
      e.preventDefault();
      return false;
    }
    
    if (password !== confirmPassword) {
      alert('Passwords do not match!');
      e.preventDefault();
      return false;
    }
    
    if (password.length < 6) {
      alert('Password must be at least 6 characters!');
      e.preventDefault();
      return false;
    }
  });
});
</script>
<?php include 'footer.php'; ?>
</body>
</html>