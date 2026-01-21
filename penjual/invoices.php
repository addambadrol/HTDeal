<?php
// invoices.php (Penjual)
session_start();
require_once '../db_config.php';

// Check if user is logged in as penjual
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../login/loginpenjual.php");
    exit();
}

$current_seller_id = $_SESSION['account_id'];

try {
    // Fetch MY REFERRAL invoices (completed appointments with referrer_id)
    $myInvoicesStmt = $pdo->prepare("
        SELECT a.*, 
               acc.first_name, 
               acc.last_name, 
               acc.email, 
               acc.phone_number,
               rc.commission_amount,
               rc.commission_status
        FROM appointments a
        JOIN account acc ON a.account_id = acc.account_id
        LEFT JOIN referral_commissions rc ON a.appointment_id = rc.appointment_id
        WHERE a.status = 'completed'
        AND a.is_referral = 1
        AND a.referrer_id = ?
        ORDER BY a.updated_at DESC
    ");
    $myInvoicesStmt->execute([$current_seller_id]);
    $my_invoices = $myInvoicesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch ALL invoices (for reference)
    $allInvoicesStmt = $pdo->query("
        SELECT a.*, 
               acc.first_name, 
               acc.last_name, 
               acc.email, 
               acc.phone_number,
               a.is_referral
        FROM appointments a
        JOIN account acc ON a.account_id = acc.account_id
        WHERE a.status = 'completed'
        ORDER BY a.updated_at DESC
    ");
    $all_invoices = $allInvoicesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate my commission stats
    $myCommissionStmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN commission_status = 'paid' THEN commission_amount ELSE 0 END) as total_paid,
            SUM(CASE WHEN commission_status = 'pending' THEN commission_amount ELSE 0 END) as total_pending,
            COUNT(*) as total_referrals
        FROM referral_commissions
        WHERE referrer_id = ?
    ");
    $myCommissionStmt->execute([$current_seller_id]);
    $commission_stats = $myCommissionStmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - Invoices</title>
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
    margin: 40px auto 60px;
    padding: 0 30px;
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
  
  /* Stats Grid */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
  }
  
  .stats-card {
    background: linear-gradient(145deg, #1a1a2e 0%, #16162a 100%);
    border: 2px solid #6e22dd;
    border-radius: 15px;
    padding: 25px 30px;
    box-shadow: 0 4px 15px rgba(110, 34, 221, 0.2);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: all 0.3s ease;
  }
  
  .stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(110, 34, 221, 0.4);
  }
  
  
  
  .stats-content h3 {
    font-size: 32px;
    font-weight: 800;
    color: #6e22dd;
    margin-bottom: 5px;
    text-align: center;
  }
  
  .stats-content p {
    font-size: 12px;
    color: #aaa;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
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

  .info-badge {
    background: rgba(34, 197, 94, 0.1);
    border: 2px solid #22c55e;
    border-left: 5px solid #22c55e;
    border-radius: 10px;
    padding: 15px 20px;
    margin-bottom: 25px;
    font-size: 13px;
    color: #ccc;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .info-badge-icon {
    font-size: 24px;
  }

  .info-badge strong {
    color: #22c55e;
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
    padding: 6px 14px;
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

  .direct-badge {
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

    .stats-card {
      flex-direction: column;
      text-align: center;
    }
    
    .section-header {
      flex-direction: column;
      gap: 15px;
      align-items: flex-start;
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
    <!-- Page Header -->
    <div class="page-header">
      <h1 class="page-title">INVOICES MANAGEMENT</h1>
      <p class="page-subtitle">View and manage all completed transaction invoices</p>
    </div>
    
    <!-- Stats Grid -->
    <div class="stats-grid">
      <div class="stats-card">
        <div class="stats-icon"></div>
        <div class="stats-content">
          <h3><?php echo count($my_invoices); ?></h3>
          <p>My Referral Invoices</p>
        </div>
      </div>

      <div class="stats-card">
        <div class="stats-icon"></div>
        <div class="stats-content">
          <h3>RM <?php echo number_format($commission_stats['total_paid'] ?? 0, 2); ?></h3>
          <p>Total Earned</p>
        </div>
      </div>

      <div class="stats-card">
        <div class="stats-icon"></div>
        <div class="stats-content">
          <h3>RM <?php echo number_format($commission_stats['total_pending'] ?? 0, 2); ?></h3>
          <p>Pending Commission</p>
        </div>
      </div>
    </div>
    
    <!-- MY REFERRAL INVOICES Section -->
    <div class="section">
      <div class="section-header">
        <h2 class="section-title">My Referral Invoices</h2>
        <span class="section-count"><?php echo count($my_invoices); ?> invoices</span>
      </div>

      <div class="info-badge">
        <span class="info-badge-icon">💡</span>
        <span>These are invoices from customers who used <strong>your reference code</strong>. You earn commission from these sales!</span>
      </div>
      
      <?php if (empty($my_invoices)): ?>
      <div class="empty-state">
        <div class="empty-icon"></div>
        <div class="empty-text">No referral invoices yet. Share your reference code to start earning!</div>
      </div>
      <?php else: ?>
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
          foreach($my_invoices as $invoice): 
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
      <?php endif; ?>
    </div>

    <!-- ALL INVOICES Section -->
    <div class="section">
      <div class="section-header">
        <h2 class="section-title">All Company Invoices</h2>
        <span class="section-count"><?php echo count($all_invoices); ?> invoices</span>
      </div>

      <div class="info-badge">
        <span class="info-badge-icon">ℹ️</span>
        <span>Complete list of all company invoices including <strong>direct sales</strong> and <strong>referral sales</strong>.</span>
      </div>
      
      <?php if (empty($all_invoices)): ?>
      <div class="empty-state">
        <div class="empty-icon"></div>
        <div class="empty-text">No invoices found</div>
      </div>
      <?php else: ?>
      <table class="invoices-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Invoice Number</th>
            <th>Customer Name</th>
            <th>Amount</th>
            <th>Type</th>
            <th>Date Issued</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $num = 1;
          foreach($all_invoices as $invoice): 
            $is_referral = $invoice['is_referral'] ?? false;
          ?>
          <tr onclick="window.location.href='invoice_detail.php?invoice=<?php echo urlencode($invoice['invoice_number']); ?>'">
            <td style="text-align: center;"><strong><?php echo $num++; ?></strong></td>
            <td>
              <span class="invoice-badge"><?php echo htmlspecialchars($invoice['invoice_number']); ?></span>
              <?php if ($is_referral): ?>
                <span class="referral-badge">Referral</span>
              <?php else: ?>
                <span class="direct-badge">Direct</span>
              <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($invoice['first_name'] . ' ' . $invoice['last_name']); ?></td>
            <td><strong>RM <?php echo number_format($invoice['total_amount'], 2); ?></strong></td>
            <td><?php echo $is_referral ? '<span style="color: #22c55e;">Referral Sale</span>' : '<span style="color: #9ca3af;">Direct Sale</span>'; ?></td>
            <td><?php echo date('d/m/Y - h:i A', strtotime($invoice['updated_at'])); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</body>
<?php include 'footer.php'; ?>
</html>