<?php
session_start();
require_once '../db_config.php';

// Check if user is logged in as customer
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'pelanggan') {
    header("Location: ../landing/loginpelanggan.php");
    exit();
}

$account_id = $_SESSION['account_id'];

// Fetch the most recent warranty claim for this user
try {
    $stmt = $pdo->prepare("
        SELECT wc.*, a.invoice_number, a.total_amount
        FROM warranty_claims wc
        LEFT JOIN appointments a ON wc.invoice_number = a.invoice_number
        WHERE wc.account_id = ?
        ORDER BY wc.created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$account_id]);
    $claim = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// If no claim found, redirect to repair page
if (!$claim) {
    header("Location: repair_warranty.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - Warranty Claim Submitted</title>
  <link rel="stylesheet" href="./style.css" />
  <style>
  /* Reset */
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

  /* Navbar */
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
    display: flex;
    align-items: center;
    gap: 10px;
    margin-left: 20px;
  }

  .profile-icon img {
    width: 35px;
    height: 35px;
    cursor: pointer;
  }

  /* Main Content */
  main {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
  }

  .success-container {
    max-width: 700px;
    width: 100%;
    text-align: center;
  }

  /* Success Icon Animation */
  .success-icon-wrapper {
    margin-bottom: 30px;
    animation: scaleIn 0.5s ease-out;
  }

  @keyframes scaleIn {
    from {
      transform: scale(0);
      opacity: 0;
    }
    to {
      transform: scale(1);
      opacity: 1;
    }
  }

  .success-icon {
    width: 120px;
    height: 120px;
    margin: 0 auto;
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 60px;
    box-shadow: 0 10px 40px rgba(34, 197, 94, 0.4);
    position: relative;
  }

  .success-icon::before {
    content: '';
    position: absolute;
    width: 140px;
    height: 140px;
    border: 3px solid rgba(34, 197, 94, 0.3);
    border-radius: 50%;
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0%, 100% {
      transform: scale(1);
      opacity: 1;
    }
    50% {
      transform: scale(1.1);
      opacity: 0.5;
    }
  }

  /* Title Section */
  .success-title {
    font-size: 42px;
    font-weight: 800;
    color: #22c55e;
    margin-bottom: 15px;
    text-transform: uppercase;
    letter-spacing: 1px;
    animation: fadeInUp 0.6s ease-out 0.2s both;
  }

  .success-subtitle {
    font-size: 18px;
    color: #aaa;
    margin-bottom: 40px;
    animation: fadeInUp 0.6s ease-out 0.4s both;
  }

  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Claim Details Card */
  .claim-details-card {
    background: linear-gradient(145deg, #1a1a1a 0%, #252525 100%);
    border: 2px solid #6e22dd;
    border-radius: 20px;
    padding: 35px;
    margin-bottom: 30px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
    animation: fadeInUp 0.6s ease-out 0.6s both;
  }

  .claim-id-section {
    background: rgba(110, 34, 221, 0.1);
    padding: 20px;
    border-radius: 15px;
    border: 2px solid rgba(110, 34, 221, 0.3);
    margin-bottom: 30px;
  }

  .claim-id-label {
    font-size: 13px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 8px;
  }

  .claim-id-value {
    font-size: 32px;
    font-weight: 800;
    color: #6e22dd;
    letter-spacing: 2px;
  }

  .detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 25px;
  }

  .detail-item {
    text-align: left;
    padding: 15px;
    background: rgba(110, 34, 221, 0.05);
    border-radius: 12px;
    border: 1px solid rgba(110, 34, 221, 0.2);
  }

  .detail-label {
    font-size: 12px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 8px;
    display: block;
  }

  .detail-value {
    font-size: 16px;
    color: #fff;
    font-weight: 700;
  }

  .status-badge {
    display: inline-block;
    padding: 8px 20px;
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
    color: #000;
    border-radius: 25px;
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  /* Next Steps Section */
  .next-steps-section {
    background: rgba(110, 34, 221, 0.05);
    border: 2px solid rgba(110, 34, 221, 0.3);
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    text-align: left;
    animation: fadeInUp 0.6s ease-out 0.8s both;
  }

  .next-steps-title {
    font-size: 20px;
    font-weight: 800;
    color: #6e22dd;
    margin-bottom: 20px;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .steps-list {
    list-style: none;
    counter-reset: step-counter;
  }

  .step-item {
    counter-increment: step-counter;
    position: relative;
    padding-left: 60px;
    margin-bottom: 25px;
    min-height: 50px;
    display: flex;
    align-items: center;
  }

  .step-item:last-child {
    margin-bottom: 0;
  }

  .step-item::before {
    content: counter(step-counter);
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 800;
    color: #fff;
    box-shadow: 0 4px 15px rgba(110, 34, 221, 0.4);
  }

  .step-content {
    flex: 1;
  }

  .step-title {
    font-size: 15px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 5px;
  }

  .step-description {
    font-size: 13px;
    color: #aaa;
    line-height: 1.5;
  }

  /* Action Buttons */
  .action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
    animation: fadeInUp 0.6s ease-out 1s both;
  }

  .btn {
    padding: 16px 32px;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
  }

  .btn-primary {
    background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
    color: #fff;
    box-shadow: 0 6px 20px rgba(110, 34, 221, 0.4);
  }

  .btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(110, 34, 221, 0.6);
  }

  .btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    border: 2px solid rgba(255, 255, 255, 0.3);
  }

  .btn-secondary:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.5);
    transform: translateY(-2px);
  }

  /* Footer */
  footer {
    text-align: center;
    padding: 30px 20px;
    background-color: #0a0a0a;
    font-size: 12px;
    color: #666;
    border-top: 1px solid rgba(110, 34, 221, 0.2);
  }

  /* Responsive */
  @media (max-width: 768px) {
    .nav-links {
      display: none;
    }

    .success-title {
      font-size: 32px;
    }

    .success-subtitle {
      font-size: 16px;
    }

    .claim-details-card {
      padding: 25px 20px;
    }

    .detail-grid {
      grid-template-columns: 1fr;
    }

    .claim-id-value {
      font-size: 24px;
    }

    .next-steps-section {
      padding: 20px;
    }

    .action-buttons {
      flex-direction: column;
    }

    .btn {
      width: 100%;
      justify-content: center;
    }
  }
  </style>
</head>
<body>
  <?php include 'header.php'; ?>

  <main>
    <div class="success-container">
      <!-- Success Icon -->
      <div class="success-icon-wrapper">
        <div class="success-icon">
          ✓
        </div>
      </div>

      <!-- Title -->
      <h1 class="success-title">Claim Submitted!</h1>
      <p class="success-subtitle">Your warranty claim has been successfully submitted and is now under review.</p>

      <!-- Claim Details Card -->
      <div class="claim-details-card">
        <div class="claim-id-section">
          <div class="claim-id-label">Your Claim Reference Number</div>
          <div class="claim-id-value">#<?php echo str_pad($claim['warranty_id'], 6, '0', STR_PAD_LEFT); ?></div>
        </div>

        <div class="detail-grid">
          <div class="detail-item">
            <span class="detail-label">Invoice Number</span>
            <span class="detail-value"><?php echo htmlspecialchars($claim['invoice_number']); ?></span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Submitted On</span>
            <span class="detail-value"><?php echo date('d M Y, h:i A', strtotime($claim['created_at'])); ?></span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Total Amount</span>
            <span class="detail-value">RM <?php echo number_format($claim['total_amount'], 2); ?></span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Current Status</span>
            <span class="status-badge">Pending Review</span>
          </div>
        </div>
      </div>

      <!-- Next Steps -->
      <div class="next-steps-section">
        <h2 class="next-steps-title">
          <span>What Happens Next?</span>
        </h2>
        <ol class="steps-list">
          <li class="step-item">
            <div class="step-content">
              <div class="step-title">Admin Review</div>
              <div class="step-description">Our team will review your claim and uploaded evidence within 24-48 hours.</div>
            </div>
          </li>
          <li class="step-item">
            <div class="step-content">
              <div class="step-title">Claim Decision</div>
              <div class="step-description">You'll receive a notification once your claim is approved or if additional information is needed.</div>
            </div>
          </li>
          <li class="step-item">
            <div class="step-content">
              <div class="step-title">Schedule Appointment</div>
              <div class="step-description">If approved, you can schedule a repair appointment at your convenience.</div>
            </div>
          </li>
          <li class="step-item">
            <div class="step-content">
              <div class="step-title">Bring for Repair</div>
              <div class="step-description">Bring your item to our service center on your scheduled date for repair or replacement.</div>
            </div>
          </li>
        </ol>
      </div>

      <!-- Action Buttons -->
      <div class="action-buttons">
        <a href="warranty_status.php" class="btn btn-primary">
          <span>Track Claim Status</span>
        </a>
        <a href="homepage.php" class="btn btn-secondary">
          <span>Back to Home</span>
        </a>
      </div>
    </div>
  </main>

  <?php include 'footer.php'; ?>
</body>
</html>