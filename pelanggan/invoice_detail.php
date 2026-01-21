<?php
session_start();
require_once '../db_config.php';

// Check if user logged in
if (!isset($_SESSION['account_id']) || $_SESSION['role'] != 'pelanggan') {
    header("Location: ../login/loginpelanggan.php");
    exit();
}

// Get invoice number from URL
$invoice_number = isset($_GET['invoice']) ? $_GET['invoice'] : '';

if (empty($invoice_number)) {
    header("Location: homepage.php");
    exit();
}

try {
    // Fetch appointment details with customer info AND referrer info
    $stmt = $pdo->prepare("
        SELECT a.*, 
               acc.first_name, 
               acc.last_name, 
               acc.email, 
               acc.phone_number,
               ref_acc.first_name as referrer_first_name,
               ref_acc.last_name as referrer_last_name
        FROM appointments a
        JOIN account acc ON a.account_id = acc.account_id
        LEFT JOIN account ref_acc ON a.referrer_id = ref_acc.account_id
        WHERE a.invoice_number = ? AND a.account_id = ?
    ");
    
    $stmt->execute([$invoice_number, $_SESSION['account_id']]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$appointment) {
        header("Location: homepage.php");
        exit();
    }
    
    // Fetch appointment items (build components) with warranty info
    $itemsStmt = $pdo->prepare("
        SELECT ai.*, 
               i.is_promo, 
               i.promo_price, 
               i.promo_start_date, 
               i.promo_end_date
        FROM appointment_items ai
        LEFT JOIN inventory i ON ai.part_id = i.part_id
        WHERE ai.appointment_id = ?
        ORDER BY ai.category
    ");
    $itemsStmt->execute([$appointment['appointment_id']]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Prepare customer data
    $customer = [
        'first_name' => $appointment['first_name'],
        'last_name' => $appointment['last_name'],
        'email' => $appointment['email'],
        'phone_number' => $appointment['phone_number']
    ];
    
    // Check if this is a referral appointment
    $is_referral = $appointment['is_referral'] == 1;
    $referrer_name = '';
    if ($is_referral && $appointment['referrer_first_name']) {
        $referrer_name = $appointment['referrer_first_name'] . ' ' . $appointment['referrer_last_name'];
    }
    
    // Format dates
    $formatted_date = date('d/m/Y', strtotime($appointment['appointment_date']));
    $formatted_time = date('h:i A', strtotime($appointment['appointment_time']));
    $invoice_date = date('d-m-Y', strtotime($appointment['created_at']));
    
    // CALCULATE WARRANTY STATUS
    $warranty_start = null;
    $warranty_end = null;
    
    if (!empty($items) && isset($items[0]['warranty_start_date']) && isset($items[0]['warranty_end_date'])) {
        $warranty_start = $items[0]['warranty_start_date'];
        $warranty_end = $items[0]['warranty_end_date'];
    } else {
        $warranty_start = date('Y-m-d', strtotime($appointment['created_at']));
        $warranty_end = date('Y-m-d', strtotime($appointment['created_at'] . ' +1 year'));
    }
    
    $today = date('Y-m-d');
    $warranty_active = ($today <= $warranty_end);
    
    $date1 = new DateTime($today);
    $date2 = new DateTime($warranty_end);
    $days_remaining = $warranty_active ? $date1->diff($date2)->days : 0;
    
    $warranty_start_formatted = date('d F Y', strtotime($warranty_start));
    $warranty_end_formatted = date('d F Y', strtotime($warranty_end));
    
    // Check if warranty claim exists
    $claimStmt = $pdo->prepare("
        SELECT claim_status, created_at 
        FROM warranty_claims 
        WHERE invoice_number = ?
    ");
    $claimStmt->execute([$invoice_number]);
    $existing_claim = $claimStmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?php echo $invoice_number; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #121212;
            color: #fff;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 12px 24px;
            background: #333;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .back-button:hover {
            background: #444;
            transform: translateX(-5px);
        }
        
        .invoice-container {
            background: #1a1a1a;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
            border: 1px solid #333;
        }
        
        .invoice-header {
            text-align: center;
            padding-bottom: 30px;
            border-bottom: 3px solid #6e22dd;
            margin-bottom: 30px;
        }
        
        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #6e22dd;
            margin-bottom: 10px;
        }
        
        .company-info {
            font-size: 13px;
            color: #999;
            line-height: 1.6;
        }
        
        .invoice-title {
            font-size: 32px;
            font-weight: bold;
            text-align: right;
            color: #6e22dd;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* REFERRAL INFO BOX */
        .referral-info-box {
            background: rgba(34, 197, 94, 0.1);
            border: 2px solid #22c55e;
            border-left: 6px solid #22c55e;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .referral-info-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
        }

        .referral-info-title {
            font-size: 16px;
            font-weight: 700;
            color: #22c55e;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .referral-info-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            background: rgba(0, 0, 0, 0.3);
            padding: 15px;
            border-radius: 8px;
        }

        .referral-info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .referral-info-label {
            font-size: 11px;
            color: #888;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .referral-info-value {
            font-size: 16px;
            color: #fff;
            font-weight: 700;
        }

        .referral-code-display {
            color: #22c55e !important;
            letter-spacing: 2px;
            font-size: 18px !important;
        }

        .referral-footer {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid rgba(34, 197, 94, 0.2);
            font-size: 12px;
            color: #aaa;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        
        .invoice-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .detail-section {
            background: rgba(110, 34, 221, 0.05);
            padding: 20px;
            border-radius: 10px;
            border: 1px solid rgba(110, 34, 221, 0.2);
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #6e22dd;
            margin-bottom: 15px;
            text-transform: uppercase;
        }
        
        .info-row {
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
        }
        
        .info-label {
            color: #999;
            font-size: 14px;
        }
        
        .info-value {
            color: #fff;
            font-weight: 500;
        }
        
        .items-table {
            width: 100%;
            background: rgba(110, 34, 221, 0.05);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .items-table thead {
            background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
        }
        
        .items-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
        }
        
        .items-table td {
            padding: 15px;
            border-top: 1px solid rgba(110, 34, 221, 0.1);
        }
        
        .items-table tbody tr:hover {
            background: rgba(110, 34, 221, 0.1);
        }
        
        .category-tag {
            display: inline-block;
            padding: 4px 10px;
            background: rgba(110, 34, 221, 0.2);
            border-radius: 5px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .warranty-info {
            font-size: 11px;
            color: #999;
            margin-top: 5px;
        }
        
        .warranty-expiry {
            font-size: 11px;
            color: #22c55e;
            margin-top: 5px;
            font-weight: 600;
        }

        .warranty-expiry.expired {
            color: #ef4444;
        }

        .warranty-expiry.expiring {
            color: #fbbf24;
        }
        
        .totals-section {
            background: rgba(110, 34, 221, 0.1);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(110, 34, 221, 0.2);
        }
        
        .total-row:last-child {
            border-bottom: none;
            border-top: 2px solid #6e22dd;
            margin-top: 10px;
            padding-top: 15px;
            font-size: 18px;
            font-weight: bold;
            color: #6e22dd;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fbbf24;
            color: #000;
        }
        
        .status-approved {
            background: #22c55e;
            color: #fff;
        }
        
        .status-completed {
            background: #3b82f6;
            color: #fff;
        }
        
        .status-rejected {
            background: #ef4444;
            color: #fff;
        }
        
        .appointment-info {
            background: rgba(59, 130, 246, 0.1);
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #3b82f6;
            margin-bottom: 30px;
        }
        
        .appointment-info h3 {
            color: #3b82f6;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .appointment-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .warranty-section {
            background: rgba(110, 34, 221, 0.1);
            padding: 25px;
            border-radius: 10px;
            border-left: 4px solid #6e22dd;
            margin-bottom: 30px;
        }

        .warranty-section h3 {
            color: #6e22dd;
            margin-bottom: 20px;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .warranty-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .warranty-item {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .warranty-item .label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            font-weight: 600;
        }

        .warranty-item .value {
            font-size: 15px;
            color: #fff;
            font-weight: 600;
        }

        .warranty-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }

        .warranty-badge.active {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: white;
        }

        .warranty-badge.expiring {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #000;
        }

        .warranty-badge.expired {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .claim-status {
            font-size: 13px;
            color: #ccc;
        }

        .claim-status.claimed {
            color: #fbbf24;
            font-weight: bold;
        }

        .warranty-warning {
            background: rgba(239, 68, 68, 0.1);
            border: 2px solid #ef4444;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: start;
            gap: 15px;
        }

        .warranty-warning.expiring {
            background: rgba(251, 191, 36, 0.1);
            border-color: #fbbf24;
        }

        .warranty-warning .icon {
            font-size: 30px;
        }

        .warranty-warning .content h4 {
            color: #ef4444;
            margin-bottom: 8px;
            font-size: 16px;
        }

        .warranty-warning.expiring .content h4 {
            color: #fbbf24;
        }

        .warranty-warning .content p {
            color: #ccc;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .notes-section {
            background: rgba(110, 34, 221, 0.05);
            padding: 25px;
            border-radius: 10px;
            border-left: 4px solid #6e22dd;
            margin-bottom: 30px;
        }
        
        .notes-title {
            font-weight: bold;
            margin-bottom: 15px;
            color: #6e22dd;
            font-size: 16px;
        }
        
        .notes-list {
            margin-left: 20px;
            line-height: 2;
            color: #ccc;
        }
        
        .notes-list li {
            margin-bottom: 8px;
        }
        
        .payment-section {
            background: rgba(110, 34, 221, 0.1);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .payment-section strong {
            color: #6e22dd;
            display: block;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(110, 34, 221, 0.4);
        }

        .btn-warning {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #000;
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #333;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #444;
        }

        .btn-disabled {
            background: #333;
            color: #666;
            cursor: not-allowed;
            opacity: 0.5;
        }

        .btn-disabled:hover {
            transform: none;
        }

        .btn-thirdly {
            padding: 12px 357px;
            background: #333;
            color: white;
        }
        
        .btn-thirdly:hover {
            background: #444;
        }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #333;
            color: #666;
            font-size: 13px;
        }
        
        @media print {
            body {
                background: white;
                color: black;
            }
            
            .back-button,
            .action-buttons,
            .warranty-warning,
            .referral-info-box {
                display: none;
            }
            
            .invoice-container {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
        
        @media (max-width: 768px) {
            .invoice-details,
            .appointment-details,
            .warranty-grid,
            .referral-info-content {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="invoice-container">
            <div class="invoice-header">
                <div class="company-name">Ha-Ikal Tech Enterprise</div>
                <div class="company-info">
                    Blok D M-23 Jalan PJU 10/4A Apartment Suria Damansara Damai<br>
                    Petaling Jaya, Selangor<br>
                    6019-2501153 | heykalmykal90@gmail.com<br>
                    SSM: LA0025383-W
                </div>
            </div>
            
            <div class="invoice-title">Invoice</div>

            <?php if ($is_referral && $referrer_name): ?>
            <!-- REFERRAL INFO BOX -->
            <div class="referral-info-box">
                <div class="referral-info-header">
                    <span class="referral-info-title">Referral Code Applied</span>
                </div>
                <div class="referral-info-content">
                    <div class="referral-info-item">
                        <span class="referral-info-label">Reference Code Used</span>
                        <span class="referral-info-value referral-code-display"><?php echo strtoupper($appointment['reference_code']); ?></span>
                    </div>
                    <div class="referral-info-item">
                        <span class="referral-info-label">Referred By</span>
                        <span class="referral-info-value"><?php echo htmlspecialchars($referrer_name); ?></span>
                    </div>
                </div>
                <div class="referral-footer">
                    <span>✓</span>
                    <span>This purchase will help <?php echo htmlspecialchars(explode(' ', $referrer_name)[0]); ?> earn commission</span>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="invoice-details">
                <div class="detail-section">
                    <div class="section-title">Invoice Details</div>
                    <div class="info-row">
                        <span class="info-label">Invoice Number</span>
                        <span class="info-value"><?php echo $appointment['invoice_number']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Invoice Date</span>
                        <span class="info-value"><?php echo $invoice_date; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status</span>
                        <span class="status-badge status-<?php echo $appointment['status']; ?>">
                            <?php echo ucfirst($appointment['status']); ?>
                        </span>
                    </div>
                </div>
                
                <div class="detail-section">
                    <div class="section-title">Bill To</div>
                    <div class="info-row">
                        <span class="info-label">Name</span>
                        <span class="info-value"><?php echo $customer['first_name'] . ' ' . $customer['last_name']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?php echo $customer['email']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone</span>
                        <span class="info-value"><?php echo $customer['phone_number']; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="appointment-info">
                <h3>Appointment Details</h3>
                <div class="appointment-details">
                    <div class="info-row">
                        <span class="info-label">Service Type</span>
                        <span class="info-value"><?php echo $appointment['service_type']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Appointment Date</span>
                        <span class="info-value"><?php echo $formatted_date; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Appointment Time</span>
                        <span class="info-value"><?php echo $formatted_time; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status</span>
                        <span class="status-badge status-<?php echo $appointment['status']; ?>">
                            <?php echo ucfirst($appointment['status']); ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="warranty-section">
                <h3>Warranty Information</h3>
                <div class="warranty-grid">
                    <div class="warranty-item">
                        <span class="label">Warranty Start Date</span>
                        <span class="value"><?php echo $warranty_start_formatted; ?></span>
                    </div>
                    <div class="warranty-item">
                        <span class="label">Warranty End Date</span>
                        <span class="value"><?php echo $warranty_end_formatted; ?></span>
                    </div>
                    <div class="warranty-item">
                        <span class="label">Warranty Status</span>
                        <?php if ($warranty_active): ?>
                            <?php 
                            $badge_class = ($days_remaining <= 30) ? 'expiring' : 'active';
                            ?>
                            <span class="warranty-badge <?php echo $badge_class; ?>">
                                ✓ Active (<?php echo $days_remaining; ?> days left)
                            </span>
                        <?php else: ?>
                            <span class="warranty-badge expired">
                                ✗ Expired
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="warranty-item">
                        <span class="label">Claim Status</span>
                        <?php if ($existing_claim): ?>
                            <span class="claim-status claimed">
                                Claim submitted - Status: <?php echo ucfirst($existing_claim['claim_status']); ?>
                            </span>
                        <?php else: ?>
                            <span class="claim-status">No claims submitted</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($warranty_active && $days_remaining <= 30): ?>
            <div class="warranty-warning expiring">
                <div class="content">
                    <h4>Warranty Expiring Soon!</h4>
                    <p>Your warranty will expire in <strong><?php echo $days_remaining; ?> days</strong>. If you have any issues with your products, please submit a warranty claim before <?php echo $warranty_end_formatted; ?>.</p>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!$warranty_active): ?>
            <div class="warranty-warning">
                <div class="content">
                    <h4>Warranty Expired</h4>
                    <p>The warranty period for this purchase ended on <strong><?php echo $warranty_end_formatted; ?></strong>. Warranty claims are no longer accepted for this invoice.</p>
                </div>
            </div>
            <?php endif; ?>
            
            <table class="items-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Part ID</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th style="text-align: right;">Unit Price</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (empty($items)): 
                    ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: #999;">
                            No items found
                        </td>
                    </tr>
                    <?php 
                    else:
                        $no = 1;
                        $subtotal = 0;
                        foreach ($items as $item): 
                            $subtotal += $item['total_price'];
                            
                            $expiry_class = '';
                            if (!$warranty_active) {
                                $expiry_class = 'expired';
                            } elseif ($days_remaining <= 30) {
                                $expiry_class = 'expiring';
                            }
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($item['part_code']); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($item['part_name']); ?></strong><br>
                            <span class="category-tag"><?php echo htmlspecialchars($item['category']); ?></span>
                            <div class="warranty-info">
                                <?php 
                                $category = strtoupper($item['category']);
                                if (strpos($category, 'PROCESSOR') !== false || strpos($category, 'RAM') !== false) {
                                    echo '# LIFETIME LIMITED WARRANTY';
                                } elseif (strpos($category, 'GRAPHICS') !== false) {
                                    echo '# 2 YEARS WARRANTY';
                                } elseif (strpos($category, 'STORAGE') !== false || strpos($category, 'DRIVE') !== false) {
                                    echo '# 2 YEARS WARRANTY';
                                } elseif (strpos($category, 'MOTHERBOARD') !== false) {
                                    echo '# 1 YEAR WARRANTY';
                                } elseif (strpos($category, 'POWER SUPPLY') !== false) {
                                    echo '# 2 YEARS WARRANTY';
                                } elseif (strpos($category, 'COOLER') !== false) {
                                    echo '# 1 YEAR WARRANTY';
                                } else {
                                    echo '# WARRANTY APPLIES';
                                }
                                ?>
                            </div>
                            <div class="warranty-expiry <?php echo $expiry_class; ?>">
                                <?php if ($warranty_active): ?>
                                    Valid until: <strong><?php echo $warranty_end_formatted; ?></strong>
                                <?php else: ?>
                                    Expired on: <strong><?php echo $warranty_end_formatted; ?></strong>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td style="text-align: right;">RM <?php echo number_format($item['unit_price'], 2); ?></td>
                        <td style="text-align: right;"><strong>RM <?php echo number_format($item['total_price'], 2); ?></strong></td>
                    </tr>
                    <?php 
                        endforeach;
                    endif;
                    ?>
                </tbody>
            </table>
            
            <div class="totals-section">
                <div class="total-row">
                    <span>Subtotal</span>
                    <span>RM <?php echo number_format($appointment['total_amount'] ?? 0, 2); ?></span>
                </div>
                <div class="total-row">
                    <span>GRAND TOTAL</span>
                    <span>RM <?php echo number_format($appointment['total_amount'] ?? 0, 2); ?></span>
                </div>
            </div>
            
            <div class="notes-section">
                <div class="notes-title">Please Note:</div>
                <ul class="notes-list">
                    <li>The customer requires to return the product within the warranty period</li>
                    <li>Customer need to bear the warranty & returning fee</li>
                    <li>Goods sold are not refundable</li>
                    <li>The warranty does not cover damage resulting from misuse - physical damage - burn mark - crack Installation - modified bios</li>
                    <li>Deposit or booking fee is not refundable</li>
                    <li>Used item cover only 30 days warranty</li>
                </ul>
            </div>
            
            <div class="payment-section">
                <strong>Payment Details:</strong>
                Ha-Ikal Tech Enterprise MAYBANK: 5627 5973 6405<br>
                <em style="color: #999;">Payment to be made after service completion</em>
            </div>
            
            <div class="action-buttons">
                <a href="generate_pdf.php?invoice=<?php echo $invoice_number; ?>" class="btn btn-primary">
                    Download PDF
                </a>
                
                <?php if ($warranty_active && !$existing_claim): ?>
                <a href="repair_warranty.php?invoice=<?php echo $invoice_number; ?>" class="btn btn-warning">
                    Claim Warranty
                </a>
                <?php elseif ($existing_claim): ?>
                <button class="btn btn-disabled" disabled>
                    Claim Submitted
                </button>
                <?php else: ?>
                <button class="btn btn-disabled" disabled>
                    Warranty Expired
                </button>
                <?php endif; ?>
                
                <button onclick="window.print()" class="btn btn-secondary">
                    Print Invoice
                </button>


            </div>
            <br>

            <a href="profile.php" class="btn btn-thirdly">
                    Back to Profile
                </a>
            
            <div class="footer">
                <p>&copy; <?php echo date('Y'); ?> Ha-Ikal Tech Enterprise. All rights reserved.</p>
                <p style="margin-top: 5px;">SISTEM TEMU JANJI DAN PENGURUSAN JUAL BELI KOMPUTER HA-IKAL TECH</p>
            </div>
        </div>
    </div>
</body>
</html>