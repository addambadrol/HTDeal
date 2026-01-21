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
    // Fetch pending REFERRAL appointments (only for current seller)
    $pendingStmt = $pdo->prepare("
        SELECT a.appointment_id,
               a.account_id,
               a.invoice_number,
               a.service_type,
               a.appointment_date,
               a.appointment_time,
               a.status,
               a.total_amount,
               a.created_at,
               a.updated_at,
               a.reference_code,
               a.is_referral,
               acc.first_name, 
               acc.last_name, 
               acc.email, 
               acc.phone_number
        FROM appointments a
        JOIN account acc ON a.account_id = acc.account_id
        WHERE a.status = 'pending'
        AND a.is_referral = 1
        AND a.referrer_id = ?
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
    ");
    $pendingStmt->execute([$current_seller_id]);
    $pending_appointments = $pendingStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch approved REFERRAL appointments
    $approvedStmt = $pdo->prepare("
        SELECT a.appointment_id,
               a.account_id,
               a.invoice_number,
               a.service_type,
               a.appointment_date,
               a.appointment_time,
               a.status,
               a.total_amount,
               a.created_at,
               a.updated_at,
               a.reference_code,
               a.is_referral,
               acc.first_name, 
               acc.last_name, 
               acc.email, 
               acc.phone_number
        FROM appointments a
        JOIN account acc ON a.account_id = acc.account_id
        WHERE a.status = 'approved'
        AND a.is_referral = 1
        AND a.referrer_id = ?
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
    ");
    $approvedStmt->execute([$current_seller_id]);
    $approved_appointments = $approvedStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch completed REFERRAL appointments (recent 10)
    $completedStmt = $pdo->prepare("
        SELECT a.appointment_id,
               a.account_id,
               a.invoice_number,
               a.service_type,
               a.appointment_date,
               a.appointment_time,
               a.status,
               a.total_amount,
               a.created_at,
               a.updated_at,
               a.reference_code,
               a.is_referral,
               acc.first_name, 
               acc.last_name, 
               acc.email, 
               acc.phone_number
        FROM appointments a
        JOIN account acc ON a.account_id = acc.account_id
        WHERE a.status = 'completed'
        AND a.is_referral = 1
        AND a.referrer_id = ?
        ORDER BY a.updated_at DESC
        LIMIT 10
    ");
    $completedStmt->execute([$current_seller_id]);
    $completed_appointments = $completedStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch pending warranty claims
    $warrantyStmt = $pdo->query("
        SELECT wc.*,
               acc.first_name,
               acc.last_name,
               acc.email,
               acc.phone_number,
               a.total_amount,
               a.service_type
        FROM warranty_claims wc
        JOIN account acc ON wc.account_id = acc.account_id
        LEFT JOIN appointments a ON wc.invoice_number = a.invoice_number
        WHERE wc.claim_status = 'pending'
        ORDER BY wc.created_at DESC
    ");
    $pending_warranty_claims = $warrantyStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate stats
    $total_pending = count($pending_appointments);
    $total_approved = count($approved_appointments);
    $total_completed = count($completed_appointments);
    $total_warranty = count($pending_warranty_claims);
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - My Referral Appointments</title>
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
    max-width: 1400px;
    margin: 30px auto 60px;
    padding: 0 20px;
  }
  
  .page-header {
    text-align: center;
    padding: 10px 20px 60px;
    /* background: linear-gradient(180deg, rgba(110, 34, 221, 0.2) 0%, transparent 100%); */
    position: relative;
    overflow: hidden;
  }

  .page-title {
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

  .page-subtitle {
    font-size: 18px;
    color: #bbb;
    font-weight: 400;
  }
  
  .stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
  }
  
  .stat-card {
    background: linear-gradient(145deg, #1a1a2e 0%, #16162a 100%);
    border: 2px solid #6e22dd;
    border-radius: 15px;
    padding: 25px;
    text-align: center;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(110, 34, 221, 0.2);
  }
  
  .stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(110, 34, 221, 0.4);
  }
  
  .stat-card.warranty {
    border-color: #fbbf24;
  }
  
  .stat-icon {
    font-size: 36px;
    margin-bottom: 12px;
  }
  
  .stat-value {
    font-size: 32px;
    font-weight: 800;
    color: #6e22dd;
    margin-bottom: 8px;
  }
  
  .stat-card.warranty .stat-value {
    color: #fbbf24;
  }
  
  .stat-label {
    font-size: 13px;
    color: #aaa;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .info-notice {
    background: rgba(34, 197, 94, 0.1);
    border: 2px solid #22c55e;
    border-left: 6px solid #22c55e;
    border-radius: 12px;
    padding: 18px 20px;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 15px;
  }

  .info-notice-icon {
    font-size: 28px;
  }

  .info-notice-text {
    flex: 1;
    font-size: 14px;
    color: #ccc;
    line-height: 1.6;
  }

  .info-notice-text strong {
    color: #22c55e;
    font-weight: 700;
  }
  
  .section {
    margin-bottom: 45px;
  }
  
  .section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
  }
  
  .section-title {
    font-size: 24px;
    font-weight: 700;
    color: #fff;
  }
  
  .section-count {
    background: rgba(110, 34, 221, 0.2);
    color: #6e22dd;
    padding: 8px 18px;
    border-radius: 25px;
    font-size: 14px;
    font-weight: 700;
    border: 1px solid rgba(110, 34, 221, 0.3);
  }
  
  .appointments-table {
    width: 100%;
    border-collapse: collapse;
    background: #1a0033;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  }
  
  .appointments-table thead {
    background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
  }
  
  .appointments-table th {
    padding: 16px;
    text-align: left;
    font-weight: 700;
    font-size: 13px;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  
  .appointments-table tbody tr {
    border-bottom: 1px solid #5b00a7;
    transition: all 0.2s ease;
    cursor: pointer;
  }
  
  .appointments-table tbody tr:hover {
    background: rgba(110, 34, 221, 0.1);
  }
  
  .appointments-table td {
    padding: 16px;
    font-size: 14px;
    color: #fff;
  }
  
  .appointments-table th:nth-child(1),
  .appointments-table td:nth-child(1) {
    width: 5%;
    text-align: center;
  }
  
  .status-badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 11px;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  
  .status-pending {
    background: linear-gradient(135deg, rgba(251, 191, 36, 0.3) 0%, rgba(251, 191, 36, 0.2) 100%);
    color: #fbbf24;
    border: 1px solid #fbbf24;
  }
  
  .status-approved {
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.3) 0%, rgba(34, 197, 94, 0.2) 100%);
    color: #22c55e;
    border: 1px solid #22c55e;
  }
  
  .status-completed {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.3) 0%, rgba(59, 130, 246, 0.2) 100%);
    color: #3b82f6;
    border: 1px solid #3b82f6;
  }
  
  .service-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    background: rgba(110, 34, 221, 0.2);
    color: #8b4dff;
    border: 1px solid rgba(110, 34, 221, 0.3);
  }
  
  .claim-reason-preview {
    max-width: 300px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 13px;
    color: #aaa;
  }
  
  .evidence-preview {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 15px;
  }
  
  .evidence-thumb {
    width: 80px;
    height: 80px;
    border-radius: 8px;
    object-fit: cover;
    border: 2px solid rgba(110, 34, 221, 0.3);
    cursor: pointer;
    transition: all 0.3s;
  }
  
  .evidence-thumb:hover {
    transform: scale(1.05);
    border-color: #6e22dd;
  }
  
  .evidence-lightbox {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.95);
    align-items: center;
    justify-content: center;
  }
  
  .evidence-lightbox.active {
    display: flex;
  }
  
  .evidence-lightbox img,
  .evidence-lightbox video {
    max-width: 90%;
    max-height: 90%;
    border-radius: 12px;
  }
  
  .lightbox-close {
    position: absolute;
    top: 20px;
    right: 30px;
    font-size: 40px;
    color: white;
    cursor: pointer;
    z-index: 10001;
  }
  
  .empty-state {
    text-align: center;
    padding: 80px 20px;
    color: #666;
    background: linear-gradient(135deg, #1a0033 0%, #0f001f 100%);
    border-radius: 12px;
    border: 2px dashed rgba(110, 34, 221, 0.3);
  }
  
  .empty-icon {
    font-size: 72px;
    margin-bottom: 20px;
    opacity: 0.4;
  }
  
  .empty-text {
    font-size: 18px;
    color: #888;
    font-weight: 500;
  }

  .modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(5px);
    overflow-y: auto;
    padding: 20px;
  }

  .modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .modal-content {
    background: linear-gradient(135deg, #1a1a1a 0%, #252525 100%);
    border: 2px solid #6e22dd;
    border-radius: 20px;
    width: 100%;
    max-width: 850px;
    max-height: 85vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(110, 34, 221, 0.5);
    animation: slideDown 0.3s ease;
  }

  @keyframes slideDown {
    from {
      transform: translateY(-50px);
      opacity: 0;
    }
    to {
      transform: translateY(0);
      opacity: 1;
    }
  }

  .modal-header {
    background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
    padding: 20px 25px;
    border-radius: 18px 18px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .modal-header.warranty {
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
  }

  .modal-title {
    font-size: 22px;
    font-weight: 800;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .close-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    font-size: 24px;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
  }

  .close-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
  }

  .modal-body {
    padding: 25px;
  }

  .modal-section {
    margin-bottom: 25px;
  }

  .modal-section-title {
    font-size: 16px;
    font-weight: 700;
    color: #6e22dd;
    margin-bottom: 15px;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    background: rgba(110, 34, 221, 0.05);
    padding: 18px;
    border-radius: 12px;
    border: 1px solid rgba(110, 34, 221, 0.2);
  }

  .detail-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .detail-label {
    font-size: 11px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
  }

  .detail-value {
    font-size: 14px;
    color: #fff;
    font-weight: 600;
  }

  .items-table {
    width: 100%;
    border-collapse: collapse;
    background: rgba(110, 34, 221, 0.05);
    border-radius: 12px;
    overflow: hidden;
  }

  .items-table thead {
    background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
  }

  .items-table th {
    padding: 12px;
    text-align: left;
    font-weight: 700;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .items-table td {
    padding: 12px;
    border-bottom: 1px solid rgba(110, 34, 221, 0.1);
  }

  .items-table tbody tr:last-child td {
    border-bottom: none;
  }

  .items-table tbody tr:hover {
    background: rgba(110, 34, 221, 0.1);
  }

  .category-tag {
    display: inline-block;
    padding: 4px 10px;
    background: rgba(110, 34, 221, 0.2);
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    color: #b19cd9;
    margin-top: 5px;
    border: 1px solid rgba(110, 34, 221, 0.3);
  }

  .total-amount {
    background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
    padding: 18px 20px;
    border-radius: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    box-shadow: 0 4px 15px rgba(110, 34, 221, 0.3);
  }

  .total-label {
    font-size: 16px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .total-value {
    font-size: 28px;
    font-weight: 800;
  }

  .modal-actions {
    display: flex;
    gap: 12px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid rgba(110, 34, 221, 0.2);
  }

  .btn {
    flex: 1;
    padding: 12px 20px;
    border: none;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.3s;
    letter-spacing: 1px;
  }

  .btn-approve {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
  }

  .btn-approve:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(34, 197, 94, 0.5);
  }

  .btn-reject {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
  }

  .btn-reject:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.5);
  }

  .btn-complete {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
  }

  .btn-complete:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.5);
  }
  
  @media (max-width: 1200px) {
    .appointments-table {
      font-size: 13px;
    }
    
    .stats-container {
      grid-template-columns: repeat(2, 1fr);
    }
  }
  
  @media (max-width: 768px) {
    .stats-container {
      grid-template-columns: 1fr;
    }

    .detail-grid {
      grid-template-columns: 1fr;
    }

    .modal-content {
      margin: 10px;
    }
    
    .modal-actions {
      flex-direction: column;
    }
  }
</style>
</head>
<body>
  <?php include 'header.php'; ?>

  <div class="container">
    <div class="page-header">
      <h1 class="page-title">MY REFERRAL APPOINTMENTS</h1>
      <p class="page-subtitle">Appointments from customers who used your reference code</p>
      <br>
      <div class="info-notice">
        <span class="info-notice-icon">ℹ️</span>
        <div class="info-notice-text">
          You are viewing <strong>ONLY referral appointments</strong> where customers used your reference code. 
          Direct sales (without reference codes) are managed by admin only.
        </div>
      </div>
      
      <div class="stats-container">
        <div class="stat-card">
          <div class="stat-value"><?php echo $total_pending; ?></div>
          <div class="stat-label">Pending</div>
        </div>
        
        <div class="stat-card">
          <div class="stat-value"><?php echo $total_approved; ?></div>
          <div class="stat-label">Approved</div>
        </div>
        
        <div class="stat-card">
          <div class="stat-value"><?php echo $total_completed; ?></div>
          <div class="stat-label">Completed</div>
        </div>
        
        <div class="stat-card warranty">
          <div class="stat-value"><?php echo $total_warranty; ?></div>
          <div class="stat-label">Warranty Claims</div>
        </div>
      </div>
    </div>
    
    <?php if ($total_warranty > 0): ?>
    <div class="section">
      <div class="section-header">
        <h2 class="section-title">Pending Warranty Claims</h2>
        <span class="section-count"><?php echo count($pending_warranty_claims); ?> claims</span>
      </div>
      
      <table class="appointments-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Claim ID</th>
            <th>Invoice No.</th>
            <th>Customer</th>
            <th>Service Type</th>
            <th>Reason</th>
            <th>Submitted</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $num = 1;
          foreach($pending_warranty_claims as $claim): 
          ?>
          <tr onclick="viewWarrantyClaim(<?php echo $claim['warranty_id']; ?>)">
            <td style="text-align: center;"><strong><?php echo $num++; ?></strong></td>
            <td><strong style="color: #fbbf24;">#<?php echo str_pad($claim['warranty_id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
            <td><strong style="color: #6e22dd;"><?php echo htmlspecialchars($claim['invoice_number']); ?></strong></td>
            <td><?php echo htmlspecialchars($claim['first_name'] . ' ' . $claim['last_name']); ?></td>
            <td><span class="service-badge"><?php echo htmlspecialchars($claim['service_type'] ?? 'N/A'); ?></span></td>
            <td>
              <div class="claim-reason-preview"><?php echo htmlspecialchars($claim['claim_reason']); ?></div>
            </td>
            <td><?php echo date('d/m/Y H:i', strtotime($claim['created_at'])); ?></td>
            <td>
              <span class="status-badge status-pending">Pending Review</span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
    
    <div class="section">
      <div class="section-header">
        <h2 class="section-title">Pending Appointments</h2>
        <span class="section-count"><?php echo count($pending_appointments); ?> items</span>
      </div>
      
      <?php if (empty($pending_appointments)): ?>
      <div class="empty-state">
        <div class="empty-text">No pending referral appointments</div>
      </div>
      <?php else: ?>
      <table class="appointments-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Invoice No.</th>
            <th>Customer</th>
            <th>Service</th>
            <th>Date</th>
            <th>Time</th>
            <th>Contact</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $num = 1;
          foreach($pending_appointments as $apt): 
          ?>
          <tr onclick="viewDetails(<?php echo $apt['appointment_id']; ?>, 'pending')">
  <td style="text-align: center;"><strong><?php echo $num++; ?></strong></td>
  <td><strong style="color: #6e22dd;"><?php echo htmlspecialchars($apt['invoice_number']); ?></strong></td>
  <td><?php echo htmlspecialchars($apt['first_name'] . ' ' . $apt['last_name']); ?></td>
  <td><span class="service-badge"><?php echo htmlspecialchars($apt['service_type']); ?></span></td>
  <td><?php echo date('d/m/Y', strtotime($apt['appointment_date'])); ?></td>
  <td><?php echo date('h:i A', strtotime($apt['appointment_time'])); ?></td>
  <td>
    <small><?php echo htmlspecialchars($apt['phone_number']); ?></small><br>
    <small style="color: #888;"><?php echo htmlspecialchars($apt['email']); ?></small>
  </td>
  <td>
    <span class="status-badge status-pending">Pending</span>
  </td>
</tr>

          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
    
    <div class="section">
      <div class="section-header">
        <h2 class="section-title">Pending Pickup / Ready for Collection</h2>
        <span class="section-count"><?php echo count($approved_appointments); ?> items</span>
      </div>
      
      <?php if (empty($approved_appointments)): ?>
      <div class="empty-state">
        <div class="empty-text">No items ready for pickup</div>
      </div>
      <?php else: ?>
      <table class="appointments-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Invoice No.</th>
            <th>Customer</th>
            <th>Service</th>
            <th>Date</th>
            <th>Time</th>
            <th>Contact</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $num = 1;
          foreach($approved_appointments as $apt): 
          ?>
          <tr onclick="viewDetails(<?php echo $apt['appointment_id']; ?>, 'approved')">
            <td style="text-align: center;"><strong><?php echo $num++; ?></strong></td>
            <td><strong style="color: #6e22dd;"><?php echo htmlspecialchars($apt['invoice_number']); ?></strong></td>
            <td><?php echo htmlspecialchars($apt['first_name'] . ' ' . $apt['last_name']); ?></td>
            <td><span class="service-badge"><?php echo htmlspecialchars($apt['service_type']); ?></span></td>
            <td><?php echo date('d/m/Y', strtotime($apt['appointment_date'])); ?></td>
            <td><?php echo date('h:i A', strtotime($apt['appointment_time'])); ?></td>
            <td>
              <small><?php echo htmlspecialchars($apt['phone_number']); ?></small><br>
              <small style="color: #888;"><?php echo htmlspecialchars($apt['email']); ?></small>
            </td>
            <td>
              <span class="status-badge status-approved">Ready Pickup</span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
    
    <div class="section">
      <div class="section-header">
        <h2 class="section-title">Recent Completed Appointments</h2>
        <span class="section-count"><?php echo count($completed_appointments); ?> items</span>
      </div>
      
      <?php if (empty($completed_appointments)): ?>
      <div class="empty-state">
        <div class="empty-text">No completed appointments yet</div>
      </div>
      <?php else: ?>
      <table class="appointments-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Invoice No.</th>
            <th>Customer</th>
            <th>Service</th>
            <th>Date</th>
            <th>Time</th>
            <th>Contact</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $num = 1;
          foreach($completed_appointments as $apt): 
          ?>
          <tr onclick="viewDetails(<?php echo $apt['appointment_id']; ?>, 'completed')">
            <td style="text-align: center;"><strong><?php echo $num++; ?></strong></td>
            <td><strong style="color: #6e22dd;"><?php echo htmlspecialchars($apt['invoice_number']); ?></strong></td>
            <td><?php echo htmlspecialchars($apt['first_name'] . ' ' . $apt['last_name']); ?></td>
            <td><span class="service-badge"><?php echo htmlspecialchars($apt['service_type']); ?></span></td>
            <td><?php echo date('d/m/Y', strtotime($apt['appointment_date'])); ?></td>
            <td><?php echo date('h:i A', strtotime($apt['appointment_time'])); ?></td>
            <td>
              <small><?php echo htmlspecialchars($apt['phone_number']); ?></small><br>
              <small style="color: #888;"><?php echo htmlspecialchars($apt['email']); ?></small>
            </td>
            <td>
              <span class="status-badge status-completed">Completed</span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

  <div id="detailsModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">Appointment Details</h3>
        <button class="close-btn" onclick="closeModal()">&times;</button>
      </div>
      <div class="modal-body" id="modalBody">
        <p style="text-align: center; padding: 40px; color: #888;">Loading...</p>
      </div>
    </div>
  </div>

  <div id="warrantyModal" class="modal">
    <div class="modal-content">
      <div class="modal-header warranty">
        <h3 class="modal-title">Warranty Claim Details</h3>
        <button class="close-btn" onclick="closeWarrantyModal()">&times;</button>
      </div>
      <div class="modal-body" id="warrantyModalBody">
        <p style="text-align: center; padding: 40px; color: #888;">Loading...</p>
      </div>
    </div>
  </div>

  <div id="evidenceLightbox" class="evidence-lightbox" onclick="closeLightbox()">
    <span class="lightbox-close">&times;</span>
    <div id="lightboxContent"></div>
  </div>

  <script>
    function viewDetails(appointmentId, status) {
      const modal = document.getElementById('detailsModal');
      const modalBody = document.getElementById('modalBody');
      
      modal.classList.add('active');
      modalBody.innerHTML = '<p style="text-align: center; padding: 40px; color: #888;">Loading...</p>';
      
      fetch('get_appointment_detail.php?id=' + appointmentId)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            displayDetails(data.appointment, status);
          } else {
            modalBody.innerHTML = '<p style="text-align: center; padding: 40px; color: #ef4444;">Error: ' + data.message + '</p>';
          }
        })
        .catch(error => {
          modalBody.innerHTML = '<p style="text-align: center; padding: 40px; color: #ef4444;">Error loading details</p>';
          console.error('Error:', error);
        });
    }

    function displayDetails(apt, status) {
      const modalBody = document.getElementById('modalBody');
      
      let itemsHTML = '';
      if (apt.items && apt.items.length > 0) {
        itemsHTML = `
          <table class="items-table">
            <thead>
              <tr>
                <th>No.</th>
                <th>Part Code</th>
                <th>Part Name</th>
                <th>Qty</th>
                <th style="text-align: right;">Price</th>
                <th style="text-align: right;">Total</th>
              </tr>
            </thead>
            <tbody>
        `;
        
        apt.items.forEach((item, index) => {
          itemsHTML += `
            <tr>
              <td>${index + 1}</td>
              <td>${item.part_code}</td>
              <td>
                <strong>${item.part_name}</strong>
                <br><span class="category-tag">${item.category}</span>
              </td>
              <td>${item.quantity}</td>
              <td style="text-align: right;">RM ${parseFloat(item.unit_price).toFixed(2)}</td>
              <td style="text-align: right;"><strong>RM ${parseFloat(item.total_price).toFixed(2)}</strong></td>
            </tr>
          `;
        });
        
        itemsHTML += '</tbody></table>';
      } else {
        itemsHTML = '<p style="text-align: center; color: #888;">No items found</p>';
      }

      // Commission info HTML
      let commissionHTML = '';
      if (apt.is_referral && apt.commission_amount) {
        const commissionStatus = apt.commission_status || 'pending';
        const statusBadge = commissionStatus === 'paid' 
          ? '<span style="color: #22c55e; font-weight: 700;">PAID</span>' 
          : '<span style="color: #fbbf24; font-weight: 700;">PENDING</span>';
        
        commissionHTML = `
          <div class="modal-section">
            <h4 class="modal-section-title">Commission Details</h4>
            <div class="detail-grid">
              <div class="detail-item">
                <span class="detail-label">Reference Code</span>
                <span class="detail-value">${apt.reference_code}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Commission Amount (10%)</span>
                <span class="detail-value">RM ${parseFloat(apt.commission_amount).toFixed(2)}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Commission Status</span>
                <span class="detail-value">${statusBadge}</span>
              </div>
            </div>
          </div>
        `;
      }

      let actionsHTML = '';
      if (status === 'pending') {
        actionsHTML = `
          <div class="modal-actions">
            <button class="btn btn-approve" onclick="approveAppointment(${apt.appointment_id})">Approve Appointment</button>
            <button class="btn btn-reject" onclick="rejectAppointment(${apt.appointment_id})">Reject</button>
          </div>
        `;
      } else if (status === 'approved') {
        actionsHTML = `
          <div class="modal-actions">
            <button class="btn btn-complete" onclick="completeAppointment(${apt.appointment_id})">Mark as Done</button>
          </div>
        `;
      }
      
      modalBody.innerHTML = `
        <div class="modal-section">
          <h4 class="modal-section-title">Customer Information</h4>
          <div class="detail-grid">
            <div class="detail-item">
              <span class="detail-label">Full Name</span>
              <span class="detail-value">${apt.customer_full_name}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Email</span>
              <span class="detail-value">${apt.email}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Phone</span>
              <span class="detail-value">${apt.customer_phone}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Invoice Number</span>
              <span class="detail-value">${apt.invoice_number}</span>
            </div>
          </div>
        </div>

        ${commissionHTML}

        <div class="modal-section">
          <h4 class="modal-section-title">Appointment Details</h4>
          <div class="detail-grid">
            <div class="detail-item">
              <span class="detail-label">Service Type</span>
              <span class="detail-value">${apt.service_type}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Date</span>
              <span class="detail-value">${apt.formatted_date}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Time</span>
              <span class="detail-value">${apt.formatted_time}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Status</span>
              <span class="detail-value">${apt.status.toUpperCase()}</span>
            </div>
          </div>
        </div>

        <div class="modal-section">
          <h4 class="modal-section-title">${apt.service_type === 'Build PC' ? 'Components' : 'Services'} List</h4>
          ${itemsHTML}
        </div>

        <div class="total-amount">
          <div class="total-label">Total Amount</div>
          <div class="total-value">RM ${parseFloat(apt.total_amount).toFixed(2)}</div>
        </div>

        ${actionsHTML}
      `;
    }

    function closeModal() {
      document.getElementById('detailsModal').classList.remove('active');
    }

    function approveAppointment(appointmentId) {
      if (!confirm('Are you sure you want to approve this appointment?')) return;
      
      fetch('approve_appointment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ appointment_id: appointmentId })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Appointment approved successfully!');
          location.reload();
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(error => {
        alert('Error approving appointment');
        console.error('Error:', error);
      });
    }

    function rejectAppointment(appointmentId) {
      const reason = prompt('Enter rejection reason:');
      if (!reason) return;
      
      fetch('reject_appointment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ appointment_id: appointmentId, reason: reason })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Appointment rejected successfully!');
          location.reload();
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(error => {
        alert('Error rejecting appointment');
        console.error('Error:', error);
      });
    }

    function completeAppointment(appointmentId) {
      if (!confirm('Mark this appointment as completed? Invoice will be generated for the customer.')) return;
      
      fetch('complete_appointment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ appointment_id: appointmentId })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Appointment completed! Invoice number: ' + data.invoice_number);
          location.reload();
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(error => {
        alert('Error completing appointment');
        console.error('Error:', error);
      });
    }

    function viewWarrantyClaim(warrantyId) {
      const modal = document.getElementById('warrantyModal');
      const modalBody = document.getElementById('warrantyModalBody');
      
      modal.classList.add('active');
      modalBody.innerHTML = '<p style="text-align: center; padding: 40px; color: #888;">Loading...</p>';
      
      fetch('get_warranty_claim_details.php?id=' + warrantyId)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            displayWarrantyDetails(data.claim);
          } else {
            modalBody.innerHTML = '<p style="text-align: center; padding: 40px; color: #ef4444;">Error: ' + data.message + '</p>';
          }
        })
        .catch(error => {
          modalBody.innerHTML = '<p style="text-align: center; padding: 40px; color: #ef4444;">Error loading claim</p>';
          console.error('Error:', error);
        });
    }

    function displayWarrantyDetails(claim) {
      const modalBody = document.getElementById('warrantyModalBody');
      
      let evidenceHTML = '';
      if (claim.files && claim.files.length > 0) {
        evidenceHTML = '<div class="evidence-preview">';
        claim.files.forEach(file => {
          const filePath = '../uploads/warranty_claims/' + file.file_name;
          if (file.file_type === 'image') {
            evidenceHTML += `<img src="${filePath}" class="evidence-thumb" onclick="event.stopPropagation(); openLightbox('${filePath}', 'image')">`;
          } else {
            evidenceHTML += `<video src="${filePath}" class="evidence-thumb" muted onclick="event.stopPropagation(); openLightbox('${filePath}', 'video')"></video>`;
          }
        });
        evidenceHTML += '</div>';
      }
      
      modalBody.innerHTML = `
        <div class="modal-section">
          <h4 class="modal-section-title">Customer Information</h4>
          <div class="detail-grid">
            <div class="detail-item">
              <span class="detail-label">Full Name</span>
              <span class="detail-value">${claim.customer_name}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Email</span>
              <span class="detail-value">${claim.email}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Phone</span>
              <span class="detail-value">${claim.phone}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Claim ID</span>
              <span class="detail-value">#${String(claim.warranty_id).padStart(6, '0')}</span>
            </div>
          </div>
        </div>
        
        <div class="modal-section">
          <h4 class="modal-section-title">Claim Information</h4>
          <div class="detail-grid">
            <div class="detail-item">
              <span class="detail-label">Invoice Number</span>
              <span class="detail-value">${claim.invoice_number}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Service Type</span>
              <span class="detail-value">${claim.service_type || 'N/A'}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Total Amount</span>
              <span class="detail-value">RM ${parseFloat(claim.total_amount || 0).toFixed(2)}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Submitted</span>
              <span class="detail-value">${claim.formatted_date}</span>
            </div>
          </div>
        </div>
        
        <div class="modal-section">
          <h4 class="modal-section-title">Issue Description</h4>
          <div style="background: rgba(110, 34, 221, 0.05); padding: 18px; border-radius: 12px; border: 1px solid rgba(110, 34, 221, 0.2);">
            <p style="line-height: 1.6; color: #fff;">${claim.claim_reason}</p>
          </div>
        </div>
        
        <div class="modal-section">
          <h4 class="modal-section-title">Uploaded Evidence (${claim.files ? claim.files.length : 0} files)</h4>
          ${evidenceHTML || '<p style="text-align: center; color: #888;">No files uploaded</p>'}
        </div>
        
        <div class="modal-actions">
          <button class="btn btn-approve" onclick="approveWarrantyClaim(${claim.warranty_id})">Approve Claim</button>
          <button class="btn btn-reject" onclick="rejectWarrantyClaim(${claim.warranty_id})">Reject Claim</button>
        </div>
      `;
    }

    function closeWarrantyModal() {
      document.getElementById('warrantyModal').classList.remove('active');
    }

    function openLightbox(filePath, fileType) {
      const lightbox = document.getElementById('evidenceLightbox');
      const content = document.getElementById('lightboxContent');
      
      if (fileType === 'image') {
        content.innerHTML = `<img src="${filePath}" style="max-width: 90%; max-height: 90vh; border-radius: 12px;">`;
      } else {
        content.innerHTML = `<video src="${filePath}" controls style="max-width: 90%; max-height: 90vh; border-radius: 12px;"></video>`;
      }
      
      lightbox.classList.add('active');
    }

    function closeLightbox() {
      document.getElementById('evidenceLightbox').classList.remove('active');
    }

    function approveWarrantyClaim(warrantyId) {
      if (!confirm('Approve this warranty claim? Customer will be able to schedule repair appointment.')) return;
      
      fetch('approve_warranty_claim.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ warranty_id: warrantyId })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Warranty claim approved! Customer can now schedule repair.');
          location.reload();
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(error => {
        alert('Error approving claim');
        console.error('Error:', error);
      });
    }

    function rejectWarrantyClaim(warrantyId) {
      const reason = prompt('Enter rejection reason:');
      if (!reason) return;
      
      fetch('reject_warranty_claim.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ warranty_id: warrantyId, reason: reason })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Warranty claim rejected.');
          location.reload();
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(error => {
        alert('Error rejecting claim');
        console.error('Error:', error);
      });
    }

    window.onclick = function(event) {
      const modal = document.getElementById('detailsModal');
      const warrantyModal = document.getElementById('warrantyModal');
      if (event.target === modal) {
        closeModal();
      }
      if (event.target === warrantyModal) {
        closeWarrantyModal();
      }
    }
  </script>
</body>
</html>
<?php include 'footer.php'; ?>
</body>
</html>