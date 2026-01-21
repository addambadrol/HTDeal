<?php
session_start();
require_once '../db_config.php';

// Check if user is logged in as admin
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../landing/loginpenjual.php");
    exit();
}

// Get invoice number from GET
$invoice_number = isset($_GET['invoice']) ? $_GET['invoice'] : '';

if (empty($invoice_number)) {
    header("Location: invoices.php");
    exit();
}

try {
    // Fetch invoice with customer details
    $stmt = $pdo->prepare("
        SELECT a.*, 
               acc.first_name, 
               acc.last_name, 
               acc.email, 
               acc.phone_number
        FROM appointments a
        JOIN account acc ON a.account_id = acc.account_id
        WHERE a.invoice_number = ? AND a.status = 'completed'
    ");
    
    $stmt->execute([$invoice_number]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$appointment) {
        header("Location: invoices.php");
        exit();
    }
    
    // Fetch appointment items (components)
    $itemsStmt = $pdo->prepare("
        SELECT * FROM appointment_items 
        WHERE appointment_id = ?
        ORDER BY category
    ");
    $itemsStmt->execute([$appointment['appointment_id']]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Prepare customer data
    $customer = [
        'first_name' => $appointment['first_name'],
        'last_name' => $appointment['last_name'],
        'email' => $appointment['email'],
        'phone_number' => ($appointment['country_code'] ?? '') . $appointment['phone_number']
    ];
    
    $formatted_date = date('d/m/Y', strtotime($appointment['appointment_date']));
    $formatted_time = date('h:i A', strtotime($appointment['appointment_time']));
    $invoice_date = date('d/m/Y', strtotime($appointment['created_at']));
    
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
        
        .status-completed {
            background: #3b82f6;
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
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(110, 34, 221, 0.4);
        }
        
        .btn-secondary {
            background: #333;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #444;
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
            .action-buttons {
                display: none;
            }
            
            .invoice-container {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
        
        @media (max-width: 768px) {
            .invoice-details,
            .appointment-details {
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
            <!-- Header -->
            <div class="invoice-header">
                <div class="company-name">Ha-Ikal Tech Enterprise</div>
                <div class="company-info">
                    Blok D M-23 Jalan PJU 10/4A Apartment Suria Damansara Damai<br>
                    Petaling Jaya, Selangor<br>
                    6019-2501153 | heykalmykal90@gmail.com<br>
                    SSM: LA0025383-W
                </div>
            </div>
            
            <!-- Invoice Title -->
            <div class="invoice-title">Invoice</div>
            
            <!-- Invoice & Customer Details -->
            <div class="invoice-details">
                <div class="detail-section">
                    <div class="section-title">Invoice Details</div>
                    <div class="info-row">
                        <span class="info-label">Invoice Number</span>
                        <span class="info-value"><?php echo htmlspecialchars($appointment['invoice_number']); ?></span>
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
                        <span class="info-value"><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?php echo htmlspecialchars($customer['email']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone</span>
                        <span class="info-value"><?php echo htmlspecialchars($customer['phone_number']); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Appointment Info -->
            <div class="appointment-info">
                <h3>Appointment Details</h3>
                <div class="appointment-details">
                    <div class="info-row">
                        <span class="info-label">Service Type</span>
                        <span class="info-value"><?php echo htmlspecialchars($appointment['service_type']); ?></span>
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
            
            <!-- Build Items Table -->
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
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($item['part_code']); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($item['part_name']); ?></strong><br>
                            <span class="category-tag"><?php echo htmlspecialchars($item['category']); ?></span>
                            <div class="warranty-info">
                                <?php 
                                // Display warranty info based on category
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
            
            <!-- Totals -->
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
            
            <!-- Notes Section -->
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
            
            <!-- Bank Details -->
            <div class="payment-section">
                <strong>Payment Details:</strong>
                Ha-Ikal Tech Enterprise MAYBANK: 5627 5973 6405<br>
                <em style="color: #999;">Payment to be made after service completion</em>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="generate_pdf.php?invoice=<?php echo urlencode($invoice_number); ?>" class="btn btn-primary">
                    Download PDF
                </a>
                <button onclick="window.print()" class="btn btn-secondary">
                    Print Invoice
                </button>
            </div>
            <br>

            <a href="invoices.php" class="btn btn-thirdly">
                    Back to Invoice
                </a>
            
            <!-- Footer -->
            <div class="footer">
                <p>&copy; <?php echo date('Y'); ?> Ha-Ikal Tech Enterprise. All rights reserved.</p>
                <p style="margin-top: 5px;">SISTEM TEMU JANJI DAN PENGURUSAN JUAL BELI KOMPUTER HA-IKAL TECH</p>
            </div>
        </div>
    </div>
</body>
</html>