<?php
session_start();
require_once '../db_config.php';

// Check authentication
if (!isset($_SESSION['account_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../landing/loginadmin.php");
    exit();
}

$success = '';
$error = '';

// Fetch user data
function getUserData($pdo, $accountId) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM account WHERE account_id = ?");
        $stmt->execute([$accountId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            session_destroy();
            header("Location: ../login/loginpelanggan.php");
            exit();
        }
        return $user;
    } catch(PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}

$user = getUserData($pdo, $_SESSION['account_id']);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phoneNumber = trim($_POST['phone_number']);
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    $hasProfileChanges = (
        $firstName != $user['first_name'] || 
        $lastName != $user['last_name'] || 
        $email != $user['email'] || 
        $phoneNumber != $user['phone_number']
    );
    
    $hasPasswordChanges = !empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword);
    
    $profileSuccess = false;
    $passwordSuccess = false;
    
    // Update Profile
    if ($hasProfileChanges) {
        if (empty($firstName) || empty($lastName) || empty($email) || empty($phoneNumber)) {
            $error = "All profile fields are required!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format!";
        } elseif (!preg_match('/^[0-9]+$/', $phoneNumber)) {
            $error = "Phone number must contain only digits!";
        } elseif (strlen($phoneNumber) < 7 || strlen($phoneNumber) > 15) {
            $error = "Phone number must be between 7 and 15 digits!";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT account_id FROM account WHERE email = ? AND account_id != ?");
                $stmt->execute([$email, $_SESSION['account_id']]);
                
                if ($stmt->rowCount() > 0) {
                    $error = "Email already used by another account!";
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE account 
                        SET first_name = ?, last_name = ?, email = ?, phone_number = ? 
                        WHERE account_id = ?
                    ");
                    
                    if ($stmt->execute([$firstName, $lastName, $email, $phoneNumber, $_SESSION['account_id']])) {
                        $_SESSION['first_name'] = $firstName;
                        $_SESSION['last_name'] = $lastName;
                        $_SESSION['email'] = $email;
                        $_SESSION['phone_number'] = $phoneNumber;
                        
                        $profileSuccess = true;
                        $user = getUserData($pdo, $_SESSION['account_id']);
                    } else {
                        $error = "Failed to update profile!";
                    }
                }
            } catch(PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
    
    // Update Password
    if ($hasPasswordChanges && empty($error)) {
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = "All password fields are required to change password!";
        } elseif (strlen($newPassword) < 6) {
            $error = "New password must be at least 6 characters!";
        } elseif ($newPassword !== $confirmPassword) {
            $error = "New passwords do not match!";
        } elseif ($currentPassword === $newPassword) {
            $error = "New password must be different from current password!";
        } else {
            if (password_verify($currentPassword, $user['password'])) {
                try {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE account SET password = ? WHERE account_id = ?");
                    
                    if ($stmt->execute([$hashedPassword, $_SESSION['account_id']])) {
                        $passwordSuccess = true;
                    } else {
                        $error = "Failed to change password!";
                    }
                } catch(PDOException $e) {
                    $error = "Database error: " . $e->getMessage();
                }
            } else {
                $error = "Current password is incorrect!";
            }
        }
    }
    
    // Set success message and redirect
    if (empty($error) && ($profileSuccess || $passwordSuccess)) {
        if ($profileSuccess && $passwordSuccess) {
            $success = "Profile and password updated successfully!";
        } elseif ($profileSuccess) {
            $success = "Profile updated successfully!";
        } else {
            $success = "Password changed successfully!";
        }
        header("refresh:2;url=profile.php");
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>HTDeal - Edit Profile</title>
  <link rel="stylesheet" href="./style.css">
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

    header {
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
    }

    .container {
      width: 900px;
      margin: 50px auto;
      padding: 0 20px;
    }

    .form-card {
      background: linear-gradient(145deg, #1a1a2e 0%, #16162a 100%);
      border: 2px solid #6e22dd;
      border-radius: 15px;
      padding: 40px;
      box-shadow: 0 8px 32px rgba(110, 34, 221, 0.3);
    }

    .form-header {
      text-align: center;
      margin-bottom: 35px;
    }

    .form-header h1 {
      font-size: 28px;
      font-weight: 700;
      color: #fff;
      margin-bottom: 8px;
    }

    .form-header p {
      color: #aaa;
      font-size: 14px;
    }

    .section-divider {
      display: flex;
      align-items: center;
      margin: 40px 0 30px 0;
      gap: 15px;
    }

    .section-divider-line {
      flex: 1;
      height: 2px;
      background: linear-gradient(90deg, transparent, #6e22dd, transparent);
    }

    .section-divider-text {
      font-size: 16px;
      font-weight: 700;
      color: white;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    .form-group {
      margin-bottom: 25px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #ddd;
      font-size: 14px;
    }

    .form-group label span {
      color: #ff4444;
    }

    .form-group input,
    .form-group select {
      width: 100%;
      padding: 12px 15px;
      background-color: #0a0a0a;
      border: 2px solid #333;
      border-radius: 8px;
      color: #fff;
      font-size: 14px;
      transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus {
      outline: none;
      border-color: #6e22dd;
      box-shadow: 0 0 0 3px rgba(110, 34, 221, 0.2);
    }

    .form-group input::placeholder {
      color: #666;
    }

    .phone-input-container {
      display: flex;
      gap: 10px;
    }

    .phone-input-wrapper {
      flex: 1;
    }

    .btn-group {
      display: flex;
      gap: 15px;
      margin-top: 35px;
    }

    .btn {
      flex: 1;
      padding: 14px;
      border: none;
      border-radius: 8px;
      font-weight: 700;
      font-size: 15px;
      cursor: pointer;
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .btn-primary {
      background: linear-gradient(135deg, #6e22dd 0%, #8b4dff 100%);
      color: white;
      box-shadow: 0 4px 15px rgba(110, 34, 221, 0.4);
    }

    .btn-primary:hover {
      background: linear-gradient(135deg, #5a1bb8 0%, #7a3ee6 100%);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(110, 34, 221, 0.5);
    }

    .btn-secondary {
      background-color: #333;
      color: #fff;
    }

    .btn-secondary:hover {
      background-color: #444;
      transform: translateY(-2px);
    }

    .btn:active {
      transform: translateY(0);
    }

    .btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none;
    }

    .alert {
      padding: 15px 20px;
      border-radius: 8px;
      margin-bottom: 25px;
      font-weight: 500;
    }

    .alert-error {
      background-color: rgba(239, 68, 68, 0.1);
      border: 1px solid #ef4444;
      color: #ef4444;
    }

    .alert-success {
      background-color: rgba(34, 197, 94, 0.1);
      border: 1px solid #22c55e;
      color: #22c55e;
    }

    .form-hint {
      font-size: 12px;
      color: #888;
      margin-top: 5px;
    }

    footer {
      text-align: center;
      padding: 20px;
      background-color: #0a0a0a;
      font-size: 12px;
      color: #666;
      margin-top: auto;
      border-top: 1px solid rgba(110, 34, 221, 0.2);
    }

    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }

    .btn.loading {
      pointer-events: none;
      animation: pulse 1.5s ease-in-out infinite;
    }

    @media (max-width: 768px) {
      .container {
        margin: 20px auto;
        padding: 15px;
      }

      .form-card {
        padding: 25px;
      }

      .form-row {
        grid-template-columns: 1fr;
      }

      .btn-group {
        flex-direction: column;
      }

      .form-header h1 {
        font-size: 24px;
      }

      .nav-links {
        display: none;
      }
    }
  </style>
</head>
<body>
  <?php include 'header.php'; ?>

  <div class="container">
    <?php if (!empty($error)): ?>
      <div class="alert alert-error">
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
      <div class="alert alert-success">
        <?php echo htmlspecialchars($success); ?>
      </div>
    <?php endif; ?>

    <div class="form-card">
      <div class="form-header">
        <h1>Edit Profile</h1>
        <p>Update your personal information and password</p>
      </div>

      <form method="POST" action="" id="editProfileForm">
        <div class="form-row">
          <div class="form-group">
            <label for="first_name">First Name <span>*</span></label>
            <input 
              type="text" 
              id="first_name"
              name="first_name" 
              value="<?php echo htmlspecialchars($user['first_name']); ?>" 
              required 
              maxlength="50"
              placeholder="Enter first name"
            >
          </div>
          
          <div class="form-group">
            <label for="last_name">Last Name <span>*</span></label>
            <input 
              type="text" 
              id="last_name"
              name="last_name" 
              value="<?php echo htmlspecialchars($user['last_name']); ?>" 
              required 
              maxlength="50"
              placeholder="Enter last name"
            >
          </div>
        </div>

        <div class="form-group">
          <label for="email">Email Address <span>*</span></label>
          <input 
            type="email" 
            id="email"
            name="email" 
            value="<?php echo htmlspecialchars($user['email']); ?>" 
            required 
            maxlength="100"
            placeholder="your.email@example.com"
          >
        </div>

        <div class="form-group">
          <label for="phone_number">Phone Number <span>*</span></label>
          <div class="phone-input-container">
            
            <div class="phone-input-wrapper">
              <input 
                type="tel" 
                id="phone_number"
                name="phone_number" 
                value="<?php echo htmlspecialchars($user['phone_number']); ?>" 
                pattern="[0-9]{7,15}" 
                required 
                maxlength="15"
                placeholder="123456789"
              >
            </div>
          </div>
          <div class="form-hint">Enter 7-15 digits only</div>
        </div>

        <div class="section-divider">
          <div class="section-divider-line"></div>
          <div class="section-divider-text">Change Password</div>
          <div class="section-divider-line"></div>
        </div>

        <div class="form-group">
          <label for="current_password">Current Password</label>
          <input 
            type="password" 
            id="current_password"
            name="current_password" 
            autocomplete="current-password"
            placeholder="Enter current password"
          >
          <div class="form-hint">Leave blank if not changing password</div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="new_password">New Password</label>
            <input 
              type="password" 
              id="new_password"
              name="new_password" 
              minlength="6"
              autocomplete="new-password"
              placeholder="Enter new password"
            >
            <div class="form-hint">Minimum 6 characters</div>
          </div>

          <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input 
              type="password" 
              id="confirm_password"
              name="confirm_password" 
              minlength="6"
              autocomplete="new-password"
              placeholder="Re-enter new password"
            >
          </div>
        </div>

        <div class="btn-group">
          <button type="button" class="btn btn-secondary" onclick="window.location.href='profile.php'">
            Cancel
          </button>
          <button type="submit" class="btn btn-primary" id="submitBtn">
            Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>

  <?php include 'footer.php'; ?>

  <script>
    const form = document.getElementById('editProfileForm');
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', function(e) {
      const newPassword = document.getElementById('new_password').value;
      const confirmPassword = document.getElementById('confirm_password').value;
      const currentPassword = document.getElementById('current_password').value;
      
      if ((currentPassword || newPassword || confirmPassword) && 
          !(currentPassword && newPassword && confirmPassword)) {
        e.preventDefault();
        alert('Please fill all password fields to change your password.');
        return false;
      }
      
      if (newPassword && newPassword !== confirmPassword) {
        e.preventDefault();
        alert('New passwords do not match!');
        return false;
      }
      
      submitBtn.disabled = true;
      submitBtn.classList.add('loading');
      submitBtn.textContent = 'Saving...';
      
      return true;
    });

    const phoneInput = document.getElementById('phone_number');
    phoneInput.addEventListener('input', function(e) {
      this.value = this.value.replace(/[^0-9]/g, '');
    });

    const successAlert = document.querySelector('.alert-success');
    if (successAlert) {
      setTimeout(() => {
        successAlert.style.transition = 'opacity 0.5s ease';
        successAlert.style.opacity = '0';
        setTimeout(() => successAlert.remove(), 500);
      }, 3000);
    }

    window.addEventListener('pageshow', function(event) {
      submitBtn.disabled = false;
      submitBtn.classList.remove('loading');
      submitBtn.textContent = 'Save Changes';
    });
  </script>
</body>
</html>