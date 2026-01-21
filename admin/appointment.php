<?php
session_start();
require_once '../db_config.php';

// Check if user is logged in as admin
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/loginadmin.php");
    exit();
}

try {
    // Fetch ALL pending appointments with referral info
    $pendingStmt = $pdo->query("
        SELECT a.*,
               acc.first_name,
               acc.last_name,
               acc.email,
               acc.phone_number,
               a.is_referral,
               a.reference_code,
               a.referrer_id,
               seller.first_name as seller_first_name,
               seller.last_name as seller_last_name,
               seller.reference_code as seller_ref_code
        FROM appointments a
        JOIN account acc ON a.account_id = acc.account_id
        LEFT JOIN account seller ON a.referrer_id = seller.account_id
        WHERE a.status = 'pending'
        ORDER BY a.is_referral DESC, a.referrer_id ASC, a.appointment_date ASC, a.appointment_time ASC
    ");
    $all_pending = $pendingStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group pending appointments by seller
    $pending_by_seller = [];
    $pending_direct = [];
    
    foreach ($all_pending as $apt) {
        if ($apt['is_referral'] && $apt['referrer_id']) {
            $seller_id = $apt['referrer_id'];
            if (!isset($pending_by_seller[$seller_id])) {
                $pending_by_seller[$seller_id] = [
                    'seller_name' => $apt['seller_first_name'] . ' ' . $apt['seller_last_name'],
                    'seller_ref_code' => $apt['seller_ref_code'],
                    'appointments' => []
                ];
            }
            $pending_by_seller[$seller_id]['appointments'][] = $apt;
        } else {
            $pending_direct[] = $apt;
        }
    }
    
    // Fetch approved appointments with same grouping
    $approvedStmt = $pdo->query("
        SELECT a.*,
               acc.first_name,
               acc.last_name,
               acc.email,
               acc.phone_number,
               a.is_referral,
               a.reference_code,
               a.referrer_id,
               seller.first_name as seller_first_name,
               seller.last_name as seller_last_name,
               seller.reference_code as seller_ref_code
        FROM appointments a
        JOIN account acc ON a.account_id = acc.account_id
        LEFT JOIN account seller ON a.referrer_id = seller.account_id
        WHERE a.status = 'approved'
        ORDER BY a.is_referral DESC, a.referrer_id ASC, a.appointment_date ASC, a.appointment_time ASC
    ");
    $all_approved = $approvedStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $approved_by_seller = [];
    $approved_direct = [];
    
    foreach ($all_approved as $apt) {
        if ($apt['is_referral'] && $apt['referrer_id']) {
            $seller_id = $apt['referrer_id'];
            if (!isset($approved_by_seller[$seller_id])) {
                $approved_by_seller[$seller_id] = [
                    'seller_name' => $apt['seller_first_name'] . ' ' . $apt['seller_last_name'],
                    'seller_ref_code' => $apt['seller_ref_code'],
                    'appointments' => []
                ];
            }
            $approved_by_seller[$seller_id]['appointments'][] = $apt;
        } else {
            $approved_direct[] = $apt;
        }
    }
    
    // Fetch completed appointments (recent 20)
    $completedStmt = $pdo->query("
        SELECT a.*,
               acc.first_name,
               acc.last_name,
               acc.email,
               acc.phone_number,
               a.is_referral,
               a.reference_code
        FROM appointments a
        JOIN account acc ON a.account_id = acc.account_id
        WHERE a.status = 'completed'
        ORDER BY a.updated_at DESC
        LIMIT 20
    ");
    $completed_appointments = $completedStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Count stats
    $total_pending = count($all_pending);
    $total_approved = count($all_approved);
    $total_completed = count($completed_appointments);
    
    $rejectedStmt = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'rejected'");
    $total_rejected = $rejectedStmt->fetchColumn();
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - Appointments Management</title>
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
    width: 1200px;
    margin: 30px auto 60px;
    padding: 0 20px;
  }
  
  .page-header {
    text-align: center;
    padding: 40px 20px 60px;
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
  
  .stat-label {
    font-size: 13px;
    color: #aaa;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
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

  /* Seller Group */
  .seller-group {
    margin-bottom: 35px;
    background: linear-gradient(135deg, #1a1a2e 0%, #16162a 100%);
    border: 2px solid rgba(110, 34, 221, 0.3);
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(110, 34, 221, 0.1);
  }

  .seller-group-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid rgba(110, 34, 221, 0.2);
  }

  .seller-info {
    display: flex;
    align-items: center;
    gap: 15px;
  }

  .seller-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6e22dd 0%, #8b4dff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 800;
    box-shadow: 0 4px 15px rgba(110, 34, 221, 0.4);
  }

  .seller-details h3 {
    font-size: 18px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 5px;
  }

  .seller-ref-code {
    display: inline-block;
    background: rgba(110, 34, 221, 0.2);
    color: #8b4dff;
    padding: 4px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    border: 1px solid rgba(110, 34, 221, 0.3);
  }

  .seller-count {
    background: rgba(34, 197, 94, 0.2);
    color: #22c55e;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    border: 1px solid rgba(34, 197, 94, 0.3);
  }

  .direct-sale-badge {
    background: rgba(156, 163, 175, 0.2);
    color: #9ca3af;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
    border: 1px solid rgba(156, 163, 175, 0.3);
    display: inline-block;
    margin-left: 8px;
  }

  .referral-badge {
    background: rgba(34, 197, 94, 0.2);
    color: #22c55e;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
    border: 1px solid rgba(34, 197, 94, 0.3);
    display: inline-block;
    margin-left: 8px;
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

  /* Modal Styles - Same as before */
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

    .seller-group-header {
      flex-direction: column;
      align-items: flex-start;
      gap: 15px;
    }
  }
</style>
</head>
<body>
  <?php include 'header.php'; ?>

  <div class="container">
    <div class="page-header">
      <h1 class="page-title">APPOINTMENTS MANAGEMENT</h1>
      
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
        
        <div class="stat-card">
          <div class="stat-value"><?php echo $total_rejected; ?></div>
          <div class="stat-label">Rejected</div>
        </div>
      </div>
    </div>
    
    <!-- PENDING APPOINTMENTS -->
    <div class="section">
      <div class="section-header">
        <h2 class="section-title">Pending Appointments</h2>
        <span class="section-count"><?php echo $total_pending; ?> items</span>
      </div>
      
      <?php if (empty($all_pending)): ?>
      <div class="empty-state">
        <div class="empty-text">No pending appointments</div>
      </div>
      <?php else: ?>
        
        <!-- Referral Appointments by Seller -->
        <?php foreach ($pending_by_seller as $seller_id => $seller_data): ?>
        <div class="seller-group">
          <div class="seller-group-header">
            <div class="seller-info">
              <div class="seller-avatar">
                <?php echo strtoupper(substr($seller_data['seller_name'], 0, 1)); ?>
              </div>
              <div class="seller-details">
                <h3><?php echo htmlspecialchars($seller_data['seller_name']); ?></h3>
                <span class="seller-ref-code"><?php echo htmlspecialchars($seller_data['seller_ref_code']); ?></span>
              </div>
            </div>
            <span class="seller-count"><?php echo count($seller_data['appointments']); ?> appointments</span>
          </div>
          
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
              foreach($seller_data['appointments'] as $apt): 
              ?>
              <tr onclick="viewDetails(<?php echo $apt['appointment_id']; ?>, 'pending')">
                <td style="text-align: center;"><strong><?php echo $num++; ?></strong></td>
                <td>
                  <strong style="color: #6e22dd;"><?php echo htmlspecialchars($apt['invoice_number']); ?></strong>
                  <span class="referral-badge">Referral
                    </span>
                </td>
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
        </div>
        <?php endforeach; ?>
        
        <!-- Direct Sales (No Referral) -->
        <?php if (!empty($pending_direct)): ?>
        <div class="seller-group">
          <div class="seller-group-header">
            <div class="seller-info">
              
              <div class="seller-details">
                <h3>Direct Sales</h3>
                <span class="seller-ref-code" style="background: rgba(156, 163, 175, 0.2); color: #9ca3af; border-color: rgba(156, 163, 175, 0.3);">No Referral Code</span>
              </div>
            </div>
            <span class="seller-count" style="background: rgba(156, 163, 175, 0.2); color: #9ca3af; border-color: rgba(156, 163, 175, 0.3);"><?php echo count($pending_direct); ?> appointments</span>
          </div>
          
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
              foreach($pending_direct as $apt): 
              ?>
              <tr onclick="viewDetails(<?php echo $apt['appointment_id']; ?>, 'pending')">
                <td style="text-align: center;"><strong><?php echo $num++; ?></strong></td>
                <td>
                  <strong style="color: #6e22dd;"><?php echo htmlspecialchars($apt['invoice_number']); ?></strong>
                  <span class="direct-sale-badge">Direct</span>
                </td>
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
        </div>
        <?php endif; ?>
        
      <?php endif; ?>
    </div>
    
    <!-- APPROVED APPOINTMENTS (Ready for Pickup) -->
    <div class="section">
      <div class="section-header">
        <h2 class="section-title">Pending Pickup / Ready for Collection</h2>
        <span class="section-count"><?php echo $total_approved; ?> items</span>
      </div>
      
      <?php if (empty($all_approved)): ?>
      <div class="empty-state">
        <div class="empty-text">No items ready for pickup</div>
      </div>
      <?php else: ?>
        
        <!-- Referral Appointments by Seller -->
        <?php foreach ($approved_by_seller as $seller_id => $seller_data): ?>
        <div class="seller-group">
          <div class="seller-group-header">
            <div class="seller-info">
              <div class="seller-avatar">
                <?php echo strtoupper(substr($seller_data['seller_name'], 0, 1)); ?>
              </div>
              <div class="seller-details">
                <h3><?php echo htmlspecialchars($seller_data['seller_name']); ?></h3>
                <span class="seller-ref-code"><?php echo htmlspecialchars($seller_data['seller_ref_code']); ?></span>
              </div>
            </div>
            <span class="seller-count"><?php echo count($seller_data['appointments']); ?> appointments</span>
          </div>
          
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
              foreach($seller_data['appointments'] as $apt): 
              ?>
              <tr onclick="viewDetails(<?php echo $apt['appointment_id']; ?>, 'approved')">
                <td style="text-align: center;"><strong><?php echo $num++; ?></strong></td>
                <td>
                  <strong style="color: #6e22dd;"><?php echo htmlspecialchars($apt['invoice_number']); ?></strong>
                  <span class="referral-badge">Referral</span>
                </td>
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
        </div>
        <?php endforeach; ?>
        
        <!-- Direct Sales (No Referral) -->
        <?php if (!empty($approved_direct)): ?>
        <div class="seller-group">
          <div class="seller-group-header">
            <div class="seller-info">
              <div class="seller-details">
                <h3>Direct Sales</h3>
                <span class="seller-ref-code" style="background: rgba(156, 163, 175, 0.2); color: #9ca3af; border-color: rgba(156, 163, 175, 0.3);">No Referral Code</span>
              </div>
            </div>
            <span class="seller-count" style="background: rgba(156, 163, 175, 0.2); color: #9ca3af; border-color: rgba(156, 163, 175, 0.3);"><?php echo count($approved_direct); ?> appointments</span>
          </div>
          
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
              foreach($approved_direct as $apt): 
              ?>
              <tr onclick="viewDetails(<?php echo $apt['appointment_id']; ?>, 'approved')">
                <td style="text-align: center;"><strong><?php echo $num++; ?></strong></td>
                <td>
                  <strong style="color: #6e22dd;"><?php echo htmlspecialchars($apt['invoice_number']); ?></strong>
                  <span class="direct-sale-badge">Direct</span>
                </td>
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
        </div>
        <?php endif; ?>
        
      <?php endif; ?>
    </div>
    
    <!-- COMPLETED APPOINTMENTS -->
    <div class="section">
      <div class="section-header">
        <h2 class="section-title">Recent Completed Appointments</h2>
        <span class="section-count"><?php echo $total_completed; ?> items</span>
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
            <td>
              <strong style="color: #6e22dd;"><?php echo htmlspecialchars($apt['invoice_number']); ?></strong>
              <?php if ($apt['is_referral']): ?>
                <span class="referral-badge">Referral</span>
              <?php else: ?>
                <span class="direct-sale-badge">Direct</span>
              <?php endif; ?>
            </td>
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

  <!-- Modal -->
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

      // Referral Information Section
      let sellerHTML = '';
      if (apt.is_referral && apt.seller_full_name) {
        sellerHTML = `
          <div class="modal-section">
            <h4 class="modal-section-title">Referral Information</h4>
            <div class="detail-grid">
              <div class="detail-item">
                <span class="detail-label">Referred By</span>
                <span class="detail-value">${apt.seller_full_name}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Reference Code</span>
                <span class="detail-value">${apt.reference_code ? apt.reference_code.toUpperCase() : 'N/A'}</span>
              </div>
              ${apt.commission_amount ? `
              <div class="detail-item">
                <span class="detail-label">Commission Amount</span>
                <span class="detail-value" style="color: #22c55e; font-weight: 800;">RM ${parseFloat(apt.commission_amount).toFixed(2)}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Commission Status</span>
                <span class="detail-value">
                  <span style="padding: 4px 10px; background: ${apt.commission_status === 'paid' ? 'rgba(34, 197, 94, 0.2)' : 'rgba(251, 191, 36, 0.2)'}; color: ${apt.commission_status === 'paid' ? '#22c55e' : '#fbbf24'}; border-radius: 8px; font-size: 11px; font-weight: 700; border: 1px solid ${apt.commission_status === 'paid' ? 'rgba(34, 197, 94, 0.3)' : 'rgba(251, 191, 36, 0.3)'};">
                    ${apt.commission_status ? apt.commission_status.toUpperCase() : 'N/A'}
                  </span>
                </span>
              </div>
              ` : ''}
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

        ${sellerHTML}

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
          <h4 class="modal-section-title">Components List</h4>
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
          alert('Appointment completed! Invoice generated: ' + data.invoice_number);
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

    window.onclick = function(event) {
      const modal = document.getElementById('detailsModal');
      if (event.target === modal) {
        closeModal();
      }
    }
  </script>
  <?php include 'footer.php'; ?>
</body>
</html>