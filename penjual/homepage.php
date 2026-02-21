<?php
session_start();
require_once '../db_config.php';

// Check if user is logged in as seller
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../login/loginpenjual.php");
    exit();
}

$current_seller_id = $_SESSION['account_id'];

try {
    // Get seller's referral stats
    $sellerStmt = $pdo->prepare("
        SELECT reference_code, total_referrals, total_commission, first_name, last_name 
        FROM account 
        WHERE account_id = ?
    ");
    $sellerStmt->execute([$current_seller_id]);
    $seller = $sellerStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get this month's stats
    $monthStmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_referrals,
            SUM(CASE WHEN a.status = 'completed' THEN 1 ELSE 0 END) as completed_referrals,
            SUM(CASE WHEN a.status = 'pending' THEN 1 ELSE 0 END) as pending_referrals,
            SUM(CASE WHEN rc.commission_status = 'paid' THEN rc.commission_amount ELSE 0 END) as paid_commission,
            SUM(CASE WHEN rc.commission_status = 'pending' THEN rc.commission_amount ELSE 0 END) as pending_commission
        FROM appointments a
        LEFT JOIN referral_commissions rc ON a.appointment_id = rc.appointment_id
        WHERE a.referrer_id = ?
        AND MONTH(a.created_at) = MONTH(CURRENT_DATE())
        AND YEAR(a.created_at) = YEAR(CURRENT_DATE())
    ");
    $monthStmt->execute([$current_seller_id]);
    $monthStats = $monthStmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - Seller Dashboard</title>
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

  .page-header {
    text-align: center;
    padding: 80px 20px 120px;
    background: linear-gradient(180deg, rgba(110, 34, 221, 0.2) 0%, transparent 100%);
    position: relative;
    overflow: hidden;
  }

  .page-header h1 {
    font-size: 48px;
    font-weight: 900;
    background: linear-gradient(135deg, #8b4dff, #6e22dd);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 15px;
    letter-spacing: 1px;
    text-transform: uppercase;
  }

  .page-header p {
    font-size: 18px;
    color: #bbb;
    font-weight: 400;
  }

  .container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px 60px;
  }

  /* Referral Dashboard */
  .referral-dashboard {
    background: linear-gradient(135deg, #1a1a2e 0%, #16162a 100%);
    border: 2px solid #6e22dd;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 40px;
    box-shadow: 0 8px 30px rgba(110, 34, 221, 0.3);
  }

  .dashboard-title {
    font-size: 24px;
    font-weight: 800;
    color: #6e22dd;
    margin-bottom: 25px;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .reference-code-section {
    background: rgba(110, 34, 221, 0.1);
    border: 2px solid rgba(110, 34, 221, 0.3);
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 25px;
    text-align: center;
  }

  .code-label {
    font-size: 12px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 10px;
  }

  .reference-code {
    font-size: 28px;
    font-weight: 800;
    color: #6e22dd;
    letter-spacing: 2px;
    margin-bottom: 15px;
  }

  .code-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
  }

  .btn-code {
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
  }

  .btn-code:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(110, 34, 221, 0.5);
  }

  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
  }

  .stat-box {
    background: rgba(110, 34, 221, 0.05);
    border: 1px solid rgba(110, 34, 221, 0.2);
    border-radius: 12px;
    padding: 20px;
    text-align: center;
  }

  .stat-icon {
    font-size: 32px;
    margin-bottom: 10px;
  }

  .stat-value {
    font-size: 28px;
    font-weight: 800;
    color: #6e22dd;
    margin-bottom: 8px;
  }

  .stat-label {
    font-size: 12px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .commission-summary {
    background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
    border-radius: 15px;
    padding: 25px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
  }

  .commission-item {
    text-align: center;
  }

  .commission-label {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.7);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 10px;
  }

  .commission-value {
    font-size: 32px;
    font-weight: 800;
    color: #fff;
  }

  /* Main Cards */
  .cards-container {
    display: flex;
    gap: 30px;
    justify-content: center;
    flex-wrap: wrap;
  }

  .card {
    background: linear-gradient(135deg, #1a1a1a 0%, #252525 100%);
    border: 2px solid #6e22dd;
    border-radius: 20px;
    padding: 30px 20px;
    width: 260px;
    text-align: center;
    text-decoration: none;
    color: #fff;
    display: block;
    transition: all 0.4s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
  }

  .card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(110, 34, 221, 0.1) 0%, transparent 100%);
    opacity: 0;
    transition: opacity 0.4s ease;
  }

  .card:hover::before {
    opacity: 1;
  }

  .card:hover {
    border-color: #8b4dff;
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(110, 34, 221, 0.5);
  }

  .card > * {
    position: relative;
  }

  .card-icon {
    width: 90px;
    height: 90px;
    object-fit: contain;
    margin: 0 auto 20px;
    filter: drop-shadow(0 4px 10px rgba(110, 34, 221, 0.4));
    transition: transform 0.4s ease;
  }

  .card:hover .card-icon {
    transform: scale(1.1);
  }

  .title {
    font-weight: 800;
    font-size: 18px;
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .desc {
    font-weight: 400;
    font-size: 14px;
    line-height: 1.5;
    color: #bbb;
  }

  footer {
    text-align: center;
    padding: 30px 20px;
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

    .page-header h1 {
      font-size: 32px;
    }

    .stats-grid {
      grid-template-columns: 1fr;
    }

    .commission-summary {
      grid-template-columns: 1fr;
    }

    .cards-container {
      padding: 20px;
    }

    .card {
      width: 100%;
      max-width: 350px;
    }

    .code-actions {
      flex-direction: column;
    }

    .btn-code {
      width: 100%;
      justify-content: center;
    }
  }
  </style>
</head>
<body>
  <?php include 'header.php'; ?>

  <div class="page-header">
    <h1>Welcome Back, <?php echo htmlspecialchars($seller['first_name'] . ' ' . $seller['last_name']); ?>!</h1>
    <p>Manage your business operations and track your referrals</p>
  </div>

  <div class="container">
    <!-- Referral Dashboard -->
    <div class="referral-dashboard">
      <h2 class="dashboard-title">REFERRAL DASHBOARD</h2>
      
      <div class="reference-code-section">
        <div class="code-label">Your Reference Code</div>
        <div class="reference-code" id="referenceCode"><?php echo $seller['reference_code'] ?: 'Not Assigned'; ?></div>
        <div class="code-actions">
          <button class="btn-code" onclick="copyCode()">
            Copy Code
          </button>
          <button class="btn-code" onclick="shareLink()">
            Share Link
          </button>
        </div>
      </div>

      <div class="stats-grid">
        <div class="stat-box">
          <div class="stat-icon">🎯</div>
          <div class="stat-value"><?php echo $monthStats['total_referrals'] ?: 0; ?></div>
          <div class="stat-label">This Month</div>
        </div>
        
        <div class="stat-box">
          <div class="stat-icon">✅</div>
          <div class="stat-value"><?php echo $monthStats['completed_referrals'] ?: 0; ?></div>
          <div class="stat-label">Completed</div>
        </div>
        
        <div class="stat-box">
          <div class="stat-icon">⏳</div>
          <div class="stat-value"><?php echo $monthStats['pending_referrals'] ?: 0; ?></div>
          <div class="stat-label">Pending</div>
        </div>
        
        <div class="stat-box">
          <div class="stat-icon">🏆</div>
          <div class="stat-value"><?php echo $seller['total_referrals'] ?: 0; ?></div>
          <div class="stat-label">Total Referrals</div>
        </div>
      </div>

      <div class="commission-summary">
        <div class="commission-item">
          <div class="commission-label">Total Earned</div>
          <div class="commission-value">RM <?php echo number_format($seller['total_commission'] ?: 0, 2); ?></div>
        </div>
        
        <div class="commission-item">
          <div class="commission-label">Commission (This Month)</div>
          <div class="commission-value">RM <?php echo number_format($monthStats['paid_commission'] ?: 0, 2); ?></div>
        </div>
        
        
      </div>
    </div>

    <!-- Main Navigation Cards -->
    <div class="cards-container">
      <a href="appointment.php" class="card">
        <img src="../picture/appointment.png" alt="Appointments" class="card-icon" />
        <div class="title">Appointments</div>
        <div class="desc">Manage referral appointments</div>
      </a>

      <a href="invoices.php" class="card">
        <img src="../picture/invoice.png" alt="Invoices" class="card-icon" />
        <div class="title">Invoices</div>
        <div class="desc">View all invoice list</div>
      </a>

      <a href="review.php" class="card">
        <img src="../picture/review.png" alt="Reviews" class="card-icon" />
        <div class="title">Reviews</div>
        <div class="desc">Manage customer reviews</div>
      </a>
    </div>
  </div>

  <div id="toast" class="toast"></div>

  <?php include 'footer.php'; ?>

  <script>
    function copyCode() {
      const code = document.getElementById('referenceCode').textContent;
      
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

    function shareLink() {
      const code = document.getElementById('referenceCode').textContent;
      
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
</html>