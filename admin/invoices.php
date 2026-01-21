<?php
// invoices.php (Admin)
session_start();
require_once '../db_config.php';

// Check if user is logged in as admin
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/loginadmin.php");
    exit();
}

try {
    // Fetch all completed appointments with referral info
    $stmt = $pdo->query("
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
               seller.reference_code as seller_ref_code,
               rc.commission_amount,
               rc.commission_status
        FROM appointments a
        JOIN account acc ON a.account_id = acc.account_id
        LEFT JOIN account seller ON a.referrer_id = seller.account_id
        LEFT JOIN referral_commissions rc ON a.appointment_id = rc.appointment_id
        WHERE a.status = 'completed'
        ORDER BY a.is_referral DESC, a.referrer_id ASC, a.updated_at DESC
    ");
    $all_invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group invoices by seller for referrals
    $invoices_by_seller = [];
    $invoices_direct = [];
    
    foreach ($all_invoices as $invoice) {
        if ($invoice['is_referral'] && $invoice['referrer_id']) {
            $seller_id = $invoice['referrer_id'];
            if (!isset($invoices_by_seller[$seller_id])) {
                $invoices_by_seller[$seller_id] = [
                    'seller_name' => $invoice['seller_first_name'] . ' ' . $invoice['seller_last_name'],
                    'seller_ref_code' => $invoice['seller_ref_code'],
                    'invoices' => []
                ];
            }
            $invoices_by_seller[$seller_id]['invoices'][] = $invoice;
        } else {
            $invoices_direct[] = $invoice;
        }
    }
    
    // Calculate stats
    $total_invoices = count($all_invoices);
    $total_referral = array_sum(array_map(function($s) { return count($s['invoices']); }, $invoices_by_seller));
    $total_direct = count($invoices_direct);
    
    $total_referral_amount = 0;
    $total_direct_amount = 0;
    $total_commission = 0;
    
    foreach ($invoices_by_seller as $seller_data) {
        foreach ($seller_data['invoices'] as $inv) {
            $total_referral_amount += $inv['total_amount'];
            $total_commission += $inv['commission_amount'] ?? 0;
        }
    }
    
    foreach ($invoices_direct as $inv) {
        $total_direct_amount += $inv['total_amount'];
    }
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - Invoices Management</title>
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
    margin: 40px auto 60px;
    padding: 0 30px;
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
  
  .page-subtitle {
    font-size: 18px;
    color: #bbb;
    font-weight: 400;
  }
  
  /* Stats Grid */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
  }
  
  .stats-card {
    background: linear-gradient(145deg, #1a1a2e 0%, #16162a 100%);
    border: 2px solid #6e22dd;
    border-radius: 15px;
    padding: 25px 30px;
    box-shadow: 0 4px 15px rgba(110, 34, 221, 0.2);
    transition: all 0.3s ease;
  }
  
  .stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(110, 34, 221, 0.4);
  }
  
  .stats-header {
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
    text-align: center;
  }
  
  .stats-icon {
    font-size: 36px;
  }
  
  .stats-label {
    font-size: 12px;
    color: #aaa;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
  }
  
  .stats-value {
    font-size: 28px;
    font-weight: 800;
    color: #6e22dd;
    margin-bottom: 8px;
    text-align: center;
  }
  
  .stats-subtext {
    font-size: 13px;
    color: #888;
    text-align: center;
  }
  
  .section {
    margin-bottom: 50px;
  }
  
  .section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
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
  
  .invoices-table {
    width: 100%;
    border-collapse: collapse;
    background: #1a0033;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  }
  
  .invoices-table thead {
    background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
  }
  
  .invoices-table th {
    padding: 16px;
    text-align: left;
    font-weight: 700;
    font-size: 13px;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  
  .invoices-table tbody tr {
    border-bottom: 1px solid #5b00a7;
    transition: all 0.2s ease;
    cursor: pointer;
  }
  
  .invoices-table tbody tr:hover {
    background: rgba(110, 34, 221, 0.1);
  }
  
  .invoices-table td {
    padding: 16px;
    font-size: 14px;
    color: #fff;
  }
  
  .invoices-table th:nth-child(1),
  .invoices-table td:nth-child(1) {
    width: 5%;
    text-align: center;
  }
  
  .invoice-badge {
    background: rgba(110, 34, 221, 0.2);
    color: #8b4dff;
    padding: 5px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 700;
    border: 1px solid rgba(110, 34, 221, 0.3);
    display: inline-block;
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

  .commission-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 700;
  }

  .commission-paid {
    background: rgba(34, 197, 94, 0.2);
    color: #22c55e;
    border: 1px solid rgba(34, 197, 94, 0.3);
  }

  .commission-pending {
    background: rgba(251, 191, 36, 0.2);
    color: #fbbf24;
    border: 1px solid rgba(251, 191, 36, 0.3);
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
  
  @media (max-width: 1200px) {
    .invoices-table {
      font-size: 13px;
    }

    .stats-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }
  
  @media (max-width: 768px) {
    .container {
      padding: 0 20px;
    }
    
    .page-title {
      font-size: 28px;
    }

    .stats-grid {
      grid-template-columns: 1fr;
    }
    
    .section-header {
      flex-direction: column;
      gap: 15px;
      align-items: flex-start;
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
    <!-- Page Header -->
    <div class="page-header">
      <h1 class="page-title">INVOICES MANAGEMENT</h1>
      <p class="page-subtitle">View and manage all completed transaction invoices with referral tracking</p>
    </div>
    
    <!-- Stats Grid -->
    <div class="stats-grid">
      <div class="stats-card">
        <div class="stats-header">
          <div class="stats-label">Total Invoices</div>
        </div>
        <div class="stats-value"><?php echo $total_invoices; ?></div>
        <div class="stats-subtext">All completed transactions</div>
      </div>

      <div class="stats-card">
        <div class="stats-header">
          <div class="stats-label">Referral Sales</div>
        </div>
        <div class="stats-value"><?php echo $total_referral; ?></div>
        <div class="stats-subtext">RM <?php echo number_format($total_referral_amount, 2); ?></div>
      </div>

      <div class="stats-card">
        <div class="stats-header">
          <div class="stats-label">Direct Sales</div>
        </div>
        <div class="stats-value"><?php echo $total_direct; ?></div>
        <div class="stats-subtext">RM <?php echo number_format($total_direct_amount, 2); ?></div>
      </div>

      <div class="stats-card">
        <div class="stats-header">
          <div class="stats-label">Total Commission</div>
        </div>
        <div class="stats-value">RM <?php echo number_format($total_commission, 2); ?></div>
        <div class="stats-subtext">Paid to sellers</div>
      </div>
    </div>
    
    <!-- REFERRAL INVOICES BY SELLER -->
    <?php if (!empty($invoices_by_seller)): ?>
    <div class="section">
      <div class="section-header">
        <h2 class="section-title">Referral Invoices by Seller</h2>
        <span class="section-count"><?php echo $total_referral; ?> invoices</span>
      </div>
      
      <?php foreach ($invoices_by_seller as $seller_id => $seller_data): ?>
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
          <span class="seller-count"><?php echo count($seller_data['invoices']); ?> invoices</span>
        </div>
        
        <table class="invoices-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Invoice Number</th>
              <th>Customer Name</th>
              <th>Amount</th>
              <th>Commission</th>
              <th>Status</th>
              <th>Date Issued</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $num = 1;
            foreach($seller_data['invoices'] as $invoice): 
              $commission_status = $invoice['commission_status'] ?? 'pending';
              $status_class = $commission_status === 'paid' ? 'commission-paid' : 'commission-pending';
              $status_text = $commission_status === 'paid' ? 'Paid' : 'Pending';
            ?>
            <tr onclick="window.location.href='invoice_detail.php?invoice=<?php echo urlencode($invoice['invoice_number']); ?>'">
              <td style="text-align: center;"><strong><?php echo $num++; ?></strong></td>
              <td>
                <span class="invoice-badge"><?php echo htmlspecialchars($invoice['invoice_number']); ?></span>
                <span class="referral-badge">Referral</span>
              </td>
              <td><?php echo htmlspecialchars($invoice['first_name'] . ' ' . $invoice['last_name']); ?></td>
              <td><strong>RM <?php echo number_format($invoice['total_amount'], 2); ?></strong></td>
              <td><strong style="color: #22c55e;">RM <?php echo number_format($invoice['commission_amount'] ?? 0, 2); ?></strong></td>
              <td><span class="commission-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
              <td><?php echo date('d/m/Y - h:i A', strtotime($invoice['updated_at'])); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- DIRECT SALES INVOICES -->
<?php if (!empty($invoices_direct)): ?>
<div class="section">
  <div class="section-header">
    <h2 class="section-title">Direct Sales Invoices</h2>
    <span class="section-count"><?php echo count($invoices_direct); ?> invoices</span>
  </div>
  
  <div class="seller-group">
    <div class="seller-group-header">
      <div class="seller-info">
        <div class="seller-details">
          <h3>Direct Sales (Company)</h3>
          <span class="seller-ref-code" style="background: rgba(156, 163, 175, 0.2); color: #9ca3af; border-color: rgba(156, 163, 175, 0.3);">No Referral Code</span>
        </div>
      </div>
      <span class="seller-count" style="background: rgba(156, 163, 175, 0.2); color: #9ca3af; border-color: rgba(156, 163, 175, 0.3);"><?php echo count($invoices_direct); ?> invoices</span>
    </div>
    
    <table class="invoices-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Invoice Number</th>
          <th>Customer Name</th>
          <th>Amount</th>
          <th>Date Issued</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $num = 1;
        foreach($invoices_direct as $invoice): 
        ?>
        <tr onclick="window.location.href='invoice_detail.php?invoice=<?php echo urlencode($invoice['invoice_number']); ?>'">
          <td style="text-align: center;"><strong><?php echo $num++; ?></strong></td>
          <td>
            <span class="invoice-badge"><?php echo htmlspecialchars($invoice['invoice_number']); ?></span>
            <!-- <span class="direct-sale-badge">Direct</span> -->
          </td>
          <td><?php echo htmlspecialchars($invoice['first_name'] . ' ' . $invoice['last_name']); ?></td>
          <td><strong>RM <?php echo number_format($invoice['total_amount'], 2); ?></strong></td>
          <td><?php echo date('d/m/Y - h:i A', strtotime($invoice['updated_at'])); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

    <?php if (empty($all_invoices)): ?>
    <div class="empty-state">
      <div class="empty-icon"></div>
      <div class="empty-text">No invoices found</div>
    </div>
    <?php endif; ?>
  </div>
  <?php include 'footer.php'; ?>
</body>
</html>