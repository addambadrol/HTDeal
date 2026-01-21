<?php
session_start();
require_once '../db_config.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['account_id']) || $_SESSION['role'] != 'penjual') {
    header("Location: ../landing/loginpenjual.php");
    exit();
}

// Get fresh data from database
try {
    $stmt = $pdo->prepare("SELECT * FROM account WHERE account_id = ?");
    $stmt->execute([$_SESSION['account_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        session_destroy();
        header("Location: ../landing/loginpenjual.php");
        exit();
    }
} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - My Profile</title>
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

  .profile-icon img {
    width: 35px;
    height: 35px;
    cursor: pointer;
    margin-left: 20px;
  }

  .profile-container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 20px;
  }

  /* Updated Profile Card - Same as Customer Side */
  .profile-card {
    background: linear-gradient(135deg, #1a1a1a 0%, #252525 100%);
    border-radius: 20px;
    padding: 40px;
    margin-bottom: 40px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
    border: 2px solid #333;
  }

  .profile-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 30px;
  }

  .profile-left {
    display: flex;
    align-items: center;
    gap: 30px;
  }

  .profile-avatar {
    width: 100px;
    height: 100px;
    background: #333;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    border: 3px solid #444;
    overflow: hidden;
  }

  .profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .profile-info {
    flex: 1;
  }

  .profile-info h2 {
    font-size: 28px;
    font-weight: 800;
    margin-bottom: 10px;
    color: #fff;
  }

  .profile-info p {
    font-size: 14px;
    color: #aaa;
    margin: 5px 0;
  }

  .profile-actions {
    display: flex;
    gap: 15px;
  }

  .btn-edit {
    padding: 12px 30px;
    background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
  }

  .btn-edit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(110, 34, 221, 0.4);
  }

  .btn-logout {
    padding: 12px 30px;
    background: #dc2626;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
  }

  .btn-logout:hover {
    background: #b91c1c;
    transform: translateY(-2px);
  }

  .section {
    margin-bottom: 30px;
  }

  .section-title {
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 20px;
    color: #6e22dd;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .referral-card {
    background: linear-gradient(135deg, #1a1a2e 0%, #16162a 100%);
    border: 2px solid #6e22dd;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 8px 25px rgba(110, 34, 221, 0.3);
  }

  .ref-code-display {
    background: rgba(110, 34, 221, 0.1);
    border: 2px solid rgba(110, 34, 221, 0.3);
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    margin-bottom: 20px;
  }

  .ref-label {
    font-size: 11px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 10px;
  }

  .ref-code {
    font-size: 32px;
    font-weight: 800;
    color: #6e22dd;
    letter-spacing: 3px;
    margin-bottom: 15px;
    word-break: break-all;
  }

  .ref-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
  }

  .btn-ref {
    background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 15px rgba(110, 34, 221, 0.3);
  }

  .btn-ref:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(110, 34, 221, 0.5);
  }

  .stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-top: 20px;
  }

  .stat-item {
    background: rgba(110, 34, 221, 0.05);
    border: 1px solid rgba(110, 34, 221, 0.2);
    border-radius: 10px;
    padding: 15px;
    text-align: center;
  }

  .stat-value {
    font-size: 24px;
    font-weight: 800;
    color: #6e22dd;
    margin-bottom: 5px;
  }

  .stat-label {
    font-size: 11px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .info-box {
    background: rgba(34, 197, 94, 0.1);
    border: 2px solid rgba(34, 197, 94, 0.3);
    border-left: 5px solid #22c55e;
    border-radius: 10px;
    padding: 15px 20px;
    margin-top: 20px;
    font-size: 13px;
    line-height: 1.6;
    color: #ccc;
  }

  .info-box strong {
    color: #22c55e;
  }

  footer {
    text-align: center;
    padding: 20px;
    background-color: #121212;
    font-size: 12px;
    color: #666;
    margin-top: auto;
    border-top: 1px solid rgba(110, 34, 221, 0.2);
  }

  .toast {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    color: white;
    padding: 15px 25px;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(34, 197, 94, 0.4);
    font-weight: 600;
    font-size: 14px;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.3s ease;
    z-index: 10000;
  }

  .toast.show {
    opacity: 1;
    transform: translateY(0);
  }

  @media (max-width: 768px) {
    .nav-links {
      display: none;
    }

    .profile-header {
      flex-direction: column;
      text-align: center;
    }

    .profile-left {
      flex-direction: column;
    }

    .profile-actions {
      flex-direction: column;
      width: 100%;
    }

    .btn-edit,
    .btn-logout {
      width: 100%;
    }

    .ref-actions {
      flex-direction: column;
    }

    .btn-ref {
      width: 100%;
      justify-content: center;
    }
  }
</style>
</head>
<body>
  <?php include 'header.php'; ?>

  <div class="profile-container">
    <!-- Profile Card - Same Design as Customer -->
    <div class="profile-card">
      <div class="profile-header">
        <div class="profile-left">
          <div class="profile-avatar">
            <?php if (!empty($user['profile_picture'])): ?>
              <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile" />
            <?php else: ?>
              <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
            <?php endif; ?>
          </div>
          <div class="profile-info">
            <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
            <p><?php echo htmlspecialchars($user['email']); ?></p>
            <p><?php echo htmlspecialchars($user['phone_number']); ?></p>
          </div>
        </div>
        <div class="profile-actions">
          <form method="POST" action="logout.php" style="display: inline;">
            <button type="submit" class="btn-logout">Log Out</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Referral Section -->
    <div class="section">
      <h2 class="section-title">My Referral Code</h2>
      <div class="referral-card">
        <div class="ref-code-display">
          <div class="ref-label">Your Unique Reference Code</div>
          <div class="ref-code" id="refCode"><?php echo $user['reference_code'] ?: 'Not Assigned'; ?></div>
          <div class="ref-actions">
            <button class="btn-ref" onclick="copyCode()">
              Copy Code
            </button>
            <button class="btn-ref" onclick="copyLink()">
              Copy Referral Link
            </button>
          </div>
        </div>

        <div class="stats-row">
          <div class="stat-item">
            <div class="stat-value"><?php echo $user['total_referrals'] ?: 0; ?></div>
            <div class="stat-label">Total Referrals</div>
          </div>
          <div class="stat-item">
            <div class="stat-value">RM <?php echo number_format($user['total_commission'] ?: 0, 2); ?></div>
            <div class="stat-label">Total Earned</div>
          </div>
        </div>

        <div class="info-box">
          <strong>How it works:</strong><br>
          Share your reference code with customers. When they book an appointment using your code, you'll earn <strong>10% commission</strong> on their total purchase once the service is completed!
        </div>
      </div>
    </div>
  </div>

  <div id="toast" class="toast"></div>

  <script>
    function copyCode() {
      const code = document.getElementById('refCode').textContent;
      
      if (code === 'Not Assigned') {
        showToast('No reference code assigned yet');
        return;
      }
      
      navigator.clipboard.writeText(code).then(() => {
        showToast('Reference code copied!');
      }).catch(() => {
        showToast('Failed to copy code');
      });
    }

    function copyLink() {
      const code = document.getElementById('refCode').textContent;
      
      if (code === 'Not Assigned') {
        showToast('No reference code assigned yet');
        return;
      }
      
      const link = `${window.location.origin}/customer/book_appointment.php?ref=${code}`;
      
      navigator.clipboard.writeText(link).then(() => {
        showToast('Referral link copied!');
      }).catch(() => {
        showToast('Failed to copy link');
      });
    }

    function showToast(message) {
      const toast = document.getElementById('toast');
      toast.textContent = message;
      toast.classList.add('show');
      
      setTimeout(() => {
        toast.classList.remove('show');
      }, 3000);
    }
  </script>
</body>
<?php include 'footer.php'; ?>
</html>