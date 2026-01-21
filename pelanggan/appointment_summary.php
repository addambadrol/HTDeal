<?php
session_start();
require_once '../db_config.php';

if (!isset($_SESSION['account_id']) || $_SESSION['role'] != 'pelanggan') {
    header("Location: ../landing/loginpelanggan.php");
    exit();
}

$appointment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($appointment_id === 0) {
    header("Location: homepage.php");
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT a.*, 
               acc.first_name, 
               acc.last_name, 
               acc.email, 
               acc.phone_number
        FROM appointments a
        JOIN account acc ON a.account_id = acc.account_id
        WHERE a.appointment_id = ? AND a.account_id = ?
    ");
    
    $stmt->execute([$appointment_id, $_SESSION['account_id']]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$appointment) {
        header("Location: homepage.php");
        exit();
    }
    
    $itemsStmt = $pdo->prepare("
        SELECT * FROM appointment_items 
        WHERE appointment_id = ?
        ORDER BY category
    ");
    $itemsStmt->execute([$appointment_id]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $formatted_date = date('l, d F Y', strtotime($appointment['appointment_date']));
    $formatted_time = date('h:i A', strtotime($appointment['appointment_time']));
    
    $rejection_reason = '';
    if ($appointment['status'] === 'rejected' && !empty($appointment['notes'])) {
        if (preg_match('/Rejection Reason: (.+?)(?:\n|$)/s', $appointment['notes'], $matches)) {
            $rejection_reason = trim($matches[1]);
        }
    }
    
    // FIXED: Service Type Detection
    $service_type = $appointment['service_type'];
    
    if ($service_type === 'Build PC') {
        $section_title = "Your PC Build Components";
        $section_icon = "";
        $page_subtitle = "Your PC build appointment details";
    } elseif ($service_type === 'Other Service') {
        $section_title = "Your Selected Services";
        $section_icon = "";
        $page_subtitle = "Your service appointment details";
    } elseif ($service_type === 'Warranty & Repair') {
        $section_title = "Repair Service Details";
        $section_icon = "";
        $page_subtitle = "Your warranty repair appointment details";
    } else {
        $section_title = "Appointment Details";
        $section_icon = "";
        $page_subtitle = "Your appointment details";
    }
    
switch ($appointment['status']) {
    case 'pending':
        $page_title = 'Appointment Pending';
        break;

    case 'approved':
        $page_title = 'Appointment Confirmed';
        break;

    case 'completed':
        $page_title = 'Appointment Completed';
        break;

    case 'rejected':
        $page_title = 'Appointment Rejected';
        break;

    default:
        $page_title = 'Appointment Details';
        break;
}
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $appointment['status'] === 'rejected' ? 'Appointment Rejected' : 'Appointment Confirmed'; ?> - HTDeal</title>
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
        
        .page-header {
            text-align: center;
            padding: 60px 20px 40px;
            background: linear-gradient(180deg, rgba(110, 34, 221, 0.1) 0%, transparent 100%);
        }
        
        .page-header.rejected {
            background: linear-gradient(180deg, rgba(239, 68, 68, 0.1) 0%, transparent 100%);
        }
        
        .page-header h1 {
            font-size: 36px;
            font-weight: 800;
            color: #6e22dd;
            margin-bottom: 10px;
        }
        
        .page-header.rejected h1 {
            color: #ef4444;
        }
        
        .page-header p {
            font-size: 16px;
            color: #aaa;
        }
        
        main {
            max-width: 1000px;
            margin: 40px auto 60px auto;
            padding: 0 20px;
        }
        
        .success-icon {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .success-icon-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
            border-radius: 50%;
            font-size: 60px;
            margin-bottom: 20px;
            box-shadow: 0 10px 40px rgba(110, 34, 221, 0.4);
            animation: scaleIn 0.5s ease-out;
        }
        
        .success-icon-circle.rejected {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            box-shadow: 0 10px 40px rgba(239, 68, 68, 0.4);
        }
        
        @keyframes scaleIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .success-title {
            font-size: 28px;
            font-weight: 800;
            color: #6e22dd;
            margin-bottom: 10px;
        }
        
        .success-title.rejected {
            color: #ef4444;
        }
        
        .success-subtitle {
            font-size: 16px;
            color: #aaa;
        }
        
        .rejection-alert {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(220, 38, 38, 0.15) 100%);
            border: 2px solid #ef4444;
            border-left: 6px solid #ef4444;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.2);
            animation: fadeInUp 0.5s ease-out;
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
        
        .rejection-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(239, 68, 68, 0.3);
        }
        
        .rejection-icon {
            font-size: 28px;
        }
        
        .rejection-title {
            font-size: 18px;
            font-weight: 800;
            color: #ef4444;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .rejection-content {
            background: rgba(0, 0, 0, 0.3);
            padding: 18px;
            border-radius: 10px;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        
        .rejection-label {
            font-size: 12px;
            color: #ef4444;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        
        .rejection-reason {
            font-size: 15px;
            color: #fff;
            line-height: 1.6;
            font-weight: 500;
        }
        
        .rejection-footer {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(239, 68, 68, 0.2);
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #aaa;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .info-card {
            background: #1a1a1a;
            border: 2px solid #6e22dd;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(110, 34, 221, 0.2);
        }
        
        .info-card.rejected {
            border-color: #ef4444;
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.2);
        }
        
        .info-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(110, 34, 221, 0.3);
        }
        
        .info-card.rejected .info-card-header {
            border-bottom-color: rgba(239, 68, 68, 0.3);
        }
        
        .info-icon {
            font-size: 28px;
        }
        
        .info-title {
            font-size: 16px;
            font-weight: 700;
            color: #6e22dd;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .info-card.rejected .info-title {
            color: #ef4444;
        }
        
        .info-content {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .info-label {
            font-size: 13px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            text-align: right;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #000;
        }
        
        .status-badge.rejected {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #fff;
        }
        
        .status-badge.approved {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: #fff;
        }
        
        .status-badge.completed {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #fff;
        }
        
        .components-section {
            background: #1a1a1a;
            border: 2px solid #6e22dd;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(110, 34, 221, 0.2);
        }
        
        .section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(110, 34, 221, 0.3);
        }
        
        .section-icon {
            font-size: 28px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #6e22dd;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .components-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .components-table thead {
            background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
        }
        
        .components-table th {
            padding: 15px;
            text-align: left;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .components-table th:last-child {
            text-align: right;
        }
        
        .components-table td {
            padding: 18px 15px;
            border-bottom: 1px solid rgba(110, 34, 221, 0.2);
        }
        
        .components-table td:last-child {
            text-align: right;
            font-weight: 700;
            font-size: 16px;
        }
        
        .components-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .components-table tbody tr:hover {
            background: rgba(110, 34, 221, 0.05);
        }
        
        .category-badge {
            display: inline-block;
            padding: 4px 10px;
            background: rgba(110, 34, 221, 0.2);
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 5px;
            color: #b19cd9;
        }
        
        .item-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            background: #6e22dd;
            border-radius: 50%;
            font-weight: 700;
            font-size: 13px;
        }
        
        .total-section {
            background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(110, 34, 221, 0.4);
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .total-label {
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .total-amount {
            font-size: 32px;
            font-weight: 800;
            color: #fff;
        }
        
        .instructions-box {
            background: rgba(59, 130, 246, 0.1);
            border: 2px solid #3b82f6;
            border-left: 6px solid #3b82f6;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .instructions-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 18px;
            font-weight: 700;
            color: #3b82f6;
            margin-bottom: 15px;
        }
        
        .instructions-list {
            list-style: none;
            padding: 0;
        }
        
        .instructions-list li {
            padding: 10px 0;
            padding-left: 30px;
            position: relative;
            color: #ccc;
            line-height: 1.6;
        }
        
        .instructions-list li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #3b82f6;
            font-weight: bold;
            font-size: 18px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .btn {
            flex: 1;
            padding: 18px 0;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
            color: white;
            box-shadow: 0 6px 20px rgba(110, 34, 221, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(110, 34, 221, 0.6);
        }
        
        .btn-secondary {
            background: #333;
            color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }
        
        .btn-secondary:hover {
            background: #444;
            transform: translateY(-2px);
        }
        
        .contact-info {
            background: rgba(110, 34, 221, 0.05);
            border: 1px solid rgba(110, 34, 221, 0.2);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        
        .contact-title {
            font-size: 16px;
            font-weight: 700;
            color: #6e22dd;
            margin-bottom: 15px;
        }
        
        .contact-details {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #aaa;
            font-size: 14px;
        }
        
        .contact-item span:first-child {
            font-size: 18px;
        }
        
        footer {
            text-align: center;
            padding: 30px 20px;
            background-color: #0a0a0a;
            font-size: 12px;
            color: #666;
            margin-top: auto;
            border-top: 1px solid rgba(110, 34, 221, 0.2);
        }
        
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 28px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .components-table {
                font-size: 13px;
            }
            
            .components-table th,
            .components-table td {
                padding: 10px;
            }
            
            .total-amount {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?> 

    <div class="page-header <?php echo $appointment['status'] === 'rejected' ? 'rejected' : ''; ?>">
        <h1><?php echo $page_title; ?></h1>
        <p><?php echo $page_subtitle; ?></p>
    </div>

    <main>
        <div class="success-icon">
            <div class="success-icon-circle <?php echo $appointment['status'] === 'rejected' ? 'rejected' : ''; ?>">
                <?php echo $appointment['status'] === 'rejected' ? '✕' : '✓'; ?>
            </div>
            <div class="success-title <?php echo $appointment['status'] === 'rejected' ? 'rejected' : ''; ?>">
                <?php echo $appointment['status'] === 'rejected' ? 'Booking Rejected' : 'Booking Confirmed'; ?>
            </div>
            <div class="success-subtitle">Reference: <?php echo htmlspecialchars($appointment['invoice_number']); ?></div>
        </div>

        <?php if ($appointment['status'] === 'rejected' && !empty($rejection_reason)): ?>
        <div class="rejection-alert">
            <div class="rejection-header">
                <span class="rejection-icon">⚠️</span>
                <span class="rejection-title">Appointment Rejection Notice</span>
            </div>
            <div class="rejection-content">
                <div class="rejection-label">Reason for Rejection:</div>
                <div class="rejection-reason"><?php echo htmlspecialchars($rejection_reason); ?></div>
            </div>
            <div class="rejection-footer">
                <span></span>
                <span>If you have any questions or need clarification, please contact us using the information below.</span>
            </div>
        </div>
        <?php endif; ?>

        <div class="info-grid">
            <div class="info-card <?php echo $appointment['status'] === 'rejected' ? 'rejected' : ''; ?>">
                <div class="info-card-header">
                    <span class="info-icon"></span>
                    <span class="info-title">Appointment Date</span>
                </div>
                <div class="info-content">
                    <div class="info-value" style="font-size: 20px; text-align: left;">
                        <?php echo $formatted_date; ?>
                    </div>
                </div>
            </div>

            <div class="info-card <?php echo $appointment['status'] === 'rejected' ? 'rejected' : ''; ?>">
                <div class="info-card-header">
                    <span class="info-icon"></span>
                    <span class="info-title">Appointment Time</span>
                </div>
                <div class="info-content">
                    <div class="info-value" style="font-size: 20px; text-align: left;">
                        <?php echo $formatted_time; ?>
                    </div>
                </div>
            </div>

            <div class="info-card <?php echo $appointment['status'] === 'rejected' ? 'rejected' : ''; ?>">
                <div class="info-card-header">
                    <span class="info-icon"></span>
                    <span class="info-title">Status</span>
                </div>
                <div class="info-content">
                    <span class="status-badge <?php echo $appointment['status']; ?>">
                        <?php echo strtoupper($appointment['status']); ?>
                    </span>
                    <div style="font-size: 12px; color: #888; margin-top: 10px;">
                        <?php 
                        if ($appointment['status'] === 'pending') {
                            echo 'Waiting for confirmation';
                        } elseif ($appointment['status'] === 'approved') {
                            echo 'Approved - Ready for service';
                        } elseif ($appointment['status'] === 'completed') {
                            echo 'Service completed';
                        } elseif ($appointment['status'] === 'rejected') {
                            echo 'Appointment rejected';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="components-section">
            <div class="section-header">
                <span class="section-icon"><?php echo $section_icon; ?></span>
                <span class="section-title"><?php echo $section_title; ?></span>
            </div>

            <table class="components-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">No.</th>
                        <th><?php echo $service_type === 'Other Service' ? 'Service' : 'Component'; ?></th>
                        <th style="width: 80px; text-align: center;">Qty</th>
                        <th style="width: 120px;">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($items as $item): 
                    ?>
                    <tr>
                        <td>
                            <span class="item-number"><?php echo $no++; ?></span>
                        </td>
                        <td>
                            <strong style="font-size: 15px;"><?php echo htmlspecialchars($item['part_name']); ?></strong><br>
                            <span class="category-badge"><?php echo htmlspecialchars($item['category']); ?></span>
                        </td>
                        <td style="text-align: center; font-weight: 600;">
                            <?php echo $item['quantity']; ?>
                        </td>
                        <td>
                            RM <?php echo number_format($item['total_price'], 2); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="total-section">
            <div class="total-row">
                <span class="total-label"><?php echo $service_type === 'Warranty & Repair' ? 'Service Cost' : 'Estimated Total Amount'; ?></span>
                <span class="total-amount">RM <?php echo number_format($appointment['total_amount'], 2); ?></span>
            </div>
        </div>

        <?php if ($appointment['status'] !== 'rejected'): ?>
        <div class="instructions-box">
            <div class="instructions-title">
                <span>ℹ️</span>
                <span>Important Information</span>
            </div>
            <ul class="instructions-list">
                <li>Please arrive at our shop on your scheduled appointment date and time</li>
                <li>Bring a valid ID for verification purposes</li>
                <?php if ($service_type === 'Build PC'): ?>
                <li>Your PC will be assembled and ready for pickup on the appointment date</li>
                <?php elseif ($service_type === 'Other Service'): ?>
                <li>Our technician will perform the selected services on your PC</li>
                <?php elseif ($service_type === 'Warranty & Repair'): ?>
                <li>Bring your original invoice and the product that needs repair</li>
                <?php endif; ?>
                <li>Payment will be collected upon pickup and completion of service</li>
                <li>Invoice will be available in your profile after transaction completion</li>
                <li>If you need to reschedule, please contact us at least 24 hours in advance</li>
            </ul>
        </div>
        <?php endif; ?>

        <div class="action-buttons">
            <?php if ($appointment['status'] === 'rejected'): ?>
            <a href="buildservices.php" class="btn btn-primary">
                Book New Appointment
            </a>
            <?php else: ?>
            <a href="profile.php" class="btn btn-primary">
                View My Appointments
            </a>
            <?php endif; ?>
            <a href="homepage.php" class="btn btn-secondary">
                Back to Home
            </a>
        </div>

        <div class="contact-info">
            <div class="contact-title">Need Help? Contact Us</div>
            <div class="contact-details">
                <div class="contact-item">
                    <span>📍</span>
                    <span>Blok D M-23, Apartment Suria, Damansara Damai</span>
                </div>
                <div class="contact-item">
                    <span>📞</span>
                    <span>+60 19-250 1153</span>
                </div>
                <div class="contact-item">
                    <span>✉️</span>
                    <span>heykalmykal90@gmail.com</span>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>