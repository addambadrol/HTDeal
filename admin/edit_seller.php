<?php
// edit_seller.php
session_start();
require_once '../db_config.php';

// Check if user is logged in as admin
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../landing/loginadmin.php");
    exit();
}

// Get seller ID from URL
$seller_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$seller_id) {
    $_SESSION['error'] = "Invalid seller ID!";
    header("Location: seller.php");
    exit();
}

// Fetch seller data
try {
    $stmt = $pdo->prepare("SELECT * FROM account WHERE account_id = ? AND role = 'penjual'");
    $stmt->execute([$seller_id]);
    $seller = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$seller) {
        $_SESSION['error'] = "Seller not found!";
        header("Location: seller.php");
        exit();
    }
} catch(PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: seller.php");
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $full_phone = $country_code . $phone_number;
    
    // Password reset (optional)
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone_number)) {
        $errors[] = "All basic fields are required!";
    }
    
    // Check if email already exists (except current seller)
    $stmt = $pdo->prepare("SELECT account_id FROM account WHERE email = ? AND account_id != ?");
    $stmt->execute([$email, $seller_id]);
    if ($stmt->fetch()) {
        $errors[] = "Email already exists for another account!";
    }
    
    // Password validation (if attempting to change)
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password)) {
            $errors[] = "Current password is required to change password!";
        } elseif (!password_verify($current_password, $seller['password'])) {
            $errors[] = "Current password is incorrect!";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters!";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match!";
        }
    }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Update basic info
            $stmt = $pdo->prepare("
                UPDATE account 
                SET first_name = ?, 
                    last_name = ?, 
                    email = ?, 
                    phone_number = ?
                WHERE account_id = ?
            ");
            $stmt->execute([$first_name, $last_name, $email, $full_phone, $seller_id]);
            
            // Update password if provided
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE account SET password = ? WHERE account_id = ?");
                $stmt->execute([$hashed_password, $seller_id]);
            }
            
            $pdo->commit();
            
            $_SESSION['success'] = "Seller profile updated successfully!";
            header("Location: seller.php");
            exit();
            
        } catch(PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Error updating seller: " . $e->getMessage();
        }
    }
    
    $error_message = implode("<br>", $errors);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - Edit Seller</title>
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
  .profile-icon {
    margin-left: 20px;
  }
  .profile-icon img {
    width: 35px;
    height: 35px;
    cursor: pointer;
  }
  
  .container {
    max-width: 900px;
    margin: 40px auto;
    padding: 0 20px;
  }
  
  .page-title {
    font-size: 32px;
    font-weight: 800;
    background: linear-gradient(135deg, #8b4dff 0%, #6e22dd 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 30px;
    text-align: center;
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
  
  .edit-card {
    background: linear-gradient(145deg, #1a1a2e 0%, #16162a 100%);
    border: 2px solid #6e22dd;
    border-radius: 15px;
    padding: 35px;
    margin-bottom: 25px;
    box-shadow: 0 8px 32px rgba(110, 34, 221, 0.3);
    width: 850px;
  }
  
  .section-title {
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 25px;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  
  .section-icon {
    font-size: 24px;
  }
  
  .seller-info-badge {
    background: rgba(110, 34, 221, 0.2);
    border: 1px solid #6e22dd;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .info-item {
    text-align: center;
  }
  
  .info-label {
    font-size: 11px;
    color: #aaa;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 5px;
  }
  
  .info-value {
    font-size: 16px;
    font-weight: 700;
    color: #fff;
  }
  
  .reference-code-display {
    background: rgba(34, 197, 94, 0.1);
    color: #22c55e;
    padding: 8px 15px;
    border-radius: 8px;
    font-family: 'Courier New', monospace;
    font-weight: 700;
    letter-spacing: 1px;
    border: 1px solid rgba(34, 197, 94, 0.3);
  }
  
  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
  }
  
  .form-group {
    margin-bottom: 20px;
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
  
  .phone-input-group {
    display: flex;
    gap: 10px;
  }
  
  .phone-input-group select {
    width: 130px;
    flex-shrink: 0;
  }
  
  .phone-input-group input {
    flex: 1;
  }
  
  .info-notice {
    background: rgba(59, 130, 246, 0.1);
    border: 2px solid #3b82f6;
    border-left: 6px solid #3b82f6;
    border-radius: 8px;
    padding: 12px 15px;
    margin-top: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: #ccc;
  }
  
  .info-notice-icon {
    font-size: 18px;
  }
  
  .btn-group {
    display: flex;
    gap: 15px;
    margin-top: 30px;
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
  
  .btn-back {
    background-color: #444;
    color: white;
  }
  
  .btn-back:hover {
    background-color: #555;
    transform: translateY(-2px);
  }
  
  .btn-save {
    background: linear-gradient(135deg, #6e22dd 0%, #8b4dff 100%);
    color: white;
  }
  
  .btn-save:hover {
    background: linear-gradient(135deg, #5a1bb8 0%, #7a3ee6 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(110, 34, 221, 0.5);
  }
  
  @media (max-width: 768px) {
    .form-row {
      grid-template-columns: 1fr;
    }
    
    .phone-input-group {
      flex-direction: column;
    }
    
    .phone-input-group select {
      width: 100%;
    }
    
    .edit-card {
      padding: 25px;
    }
    
    .seller-info-badge {
      flex-direction: column;
      gap: 15px;
    }
  }
</style>
</head>
<body>
  <?php include 'header.php'; ?>

  <div class="container">
    <h1 class="page-title">EDIT SELLER ACCOUNT</h1>
    
    <?php if(isset($error_message)): ?>
      <div class="alert alert-error"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
      
      <!-- Edit Profile Info Section -->
      <div class="edit-card">
        <h2 class="section-title">
          <span class="section-icon"></span>
          Profile Information
        </h2>
        
        <div class="form-row">
          <div class="form-group">
            <label>First Name <span>*</span></label>
            <input type="text" name="first_name" value="<?php echo htmlspecialchars($seller['first_name']); ?>" required />
          </div>
          
          <div class="form-group">
            <label>Last Name <span>*</span></label>
            <input type="text" name="last_name" value="<?php echo htmlspecialchars($seller['last_name']); ?>" required />
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label>E-Mail <span>*</span></label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($seller['email']); ?>" required />
          </div>
          
          <div class="form-group">
            <label>Number Phone <span>*</span></label>
            <div class="phone-input-group">
              
              <input type="tel" name="phone_number" value="<?php echo htmlspecialchars($seller['phone_number'] ?? ''); ?>" required />
            </div>
          </div>
        </div>
      </div>
      
      <!-- Reset Password Section -->
      <div class="edit-card">
        <h2 class="section-title">
          <span class="section-icon"></span>
          Change Password (Optional)
        </h2>
        
        <div class="form-group">
          <label>Current Password</label>
          <input type="password" name="current_password" placeholder="Enter current password" />
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" placeholder="Minimum 6 characters" />
          </div>
          
          <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" placeholder="Re-enter new password" />
          </div>
        </div>
        
        <div class="info-notice">
          <span class="info-notice-icon">ℹ️</span>
          <span>Leave password fields empty if you don't want to change the password.</span>
        </div>
      </div>
      
      <!-- Buttons -->
      <div class="btn-group">
        <button type="button" class="btn btn-back" onclick="window.location.href='seller.php'">
          Back to Sellers
        </button>
        <button type="submit" class="btn btn-save">
          Save Changes
        </button>
      </div>
    </form>
  </div>

  <script>
    // Auto-hide alerts after 3 seconds
    setTimeout(function() {
      const alerts = document.querySelectorAll('.alert');
      alerts.forEach(function(alert) {
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.3s ease';
        setTimeout(function() {
          alert.remove();
        }, 300);
      });
    }, 3000);
  </script>
  <?php include 'footer.php'; ?>
</body>
</html>