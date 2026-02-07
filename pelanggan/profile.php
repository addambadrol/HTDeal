<?php
session_start();
require_once '../db_config.php';

// Check if user is logged in
if (!isset($_SESSION['account_id']) || $_SESSION['role'] != 'pelanggan') {
    header("Location: ../landing/loginpelanggan.php");
    exit();
}

$account_id = $_SESSION['account_id'];

try {
    // Fetch user profile
    $stmt = $pdo->prepare("SELECT * FROM account WHERE account_id = ?");
    $stmt->execute([$account_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Fetch pending appointments (belum complete)
    $pendingStmt = $pdo->prepare("
        SELECT * FROM appointments 
        WHERE account_id = ? AND status IN ('pending', 'approved')
        ORDER BY appointment_date DESC, appointment_time DESC
    ");
    $pendingStmt->execute([$account_id]);
    $pendingAppointments = $pendingStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch completed appointments (dah ada invoice)
    $completedStmt = $pdo->prepare("
        SELECT * FROM appointments 
        WHERE account_id = ? AND status = 'completed'
        ORDER BY created_at DESC
    ");
    $completedStmt->execute([$account_id]);
    $completedAppointments = $completedStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch warranty claims
    $warrantyStmt = $pdo->prepare("
        SELECT 
            wc.*,
            a.appointment_date,
            a.appointment_time,
            a.status as appointment_status
        FROM warranty_claims wc
        LEFT JOIN appointments a ON wc.appointment_id = a.appointment_id
        WHERE wc.account_id = ?
        ORDER BY wc.created_at DESC
    ");
    $warrantyStmt->execute([$account_id]);
    $warrantyClaims = $warrantyStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            /* margin-left: auto; */
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
        
        /* Page Header */
        .page-header {
            text-align: center;
            padding: 40px 20px 130px;
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
        
        /* Main Container */
        main {
            max-width: 1200px;
            margin: 40px auto 60px auto;
            padding: 0 20px;
            width: 100%;
        }
        
        /* Profile Card */
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
        
        /* Tabs */
        .tabs {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            border-bottom: 2px solid rgba(110, 34, 221, 0.3);
        }
        
        .tab {
            padding: 15px 30px;
            background: transparent;
            border: none;
            color: #aaa;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .tab:hover {
            color: #fff;
        }
        
        .tab.active {
            color: #6e22dd;
            border-bottom-color: #6e22dd;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Section Title */
        .section-title {
            font-size: 24px;
            font-weight: 800;
            color: #6e22dd;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .section-icon {
            font-size: 28px;
        }
        
        /* Appointment Cards */
        .appointments-grid {
            display: grid;
            gap: 20px;
        }
        
        .appointment-card {
            background: #1a1a1a;
            border: 2px solid #333;
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .appointment-card:hover {
            border-color: #6e22dd;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(110, 34, 221, 0.3);
        }
        
        .appointment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(110, 34, 221, 0.2);
        }
        
        .appointment-ref {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .ref-number {
            font-size: 18px;
            font-weight: 700;
            color: #6e22dd;
        }
        
        .ref-date {
            font-size: 13px;
            color: #888;
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #000;
        }
        
        .status-approved {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
        }
        
        .status-completed {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #fff;
        }
        
        .status-rejected {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #fff;
        }
        
        .status-scheduled {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: #fff;
        }
        
        .appointment-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .detail-box {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .detail-box-label {
            font-size: 11px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .detail-box-value {
            font-size: 15px;
            font-weight: 600;
            color: #fff;
        }
        
        .appointment-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(110, 34, 221, 0.4);
        }
        
        .btn-secondary {
            background: #333;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #444;
        }
        
        /* Admin Response Box */
        .admin-response {
            background: rgba(110, 34, 221, 0.1);
            border-left: 4px solid #6e22dd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .admin-response-title {
            font-size: 12px;
            font-weight: 700;
            color: #6e22dd;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }
        
        .admin-response-text {
            font-size: 14px;
            color: #ddd;
            line-height: 1.5;
        }
        
        /* Warranty Issue Description */
        .warranty-issue {
            background: rgba(0, 0, 0, 0.3);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .warranty-issue-title {
            font-size: 12px;
            font-weight: 700;
            color: #888;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }
        
        .warranty-issue-text {
            font-size: 14px;
            color: #ddd;
            line-height: 1.5;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: rgba(110, 34, 221, 0.05);
            border: 2px dashed rgba(110, 34, 221, 0.3);
            border-radius: 15px;
        }
        
        .empty-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-title {
            font-size: 20px;
            font-weight: 700;
            color: #6e22dd;
            margin-bottom: 10px;
        }
        
        .empty-text {
            font-size: 14px;
            color: #888;
            margin-bottom: 25px;
        }
        
        /* Invoice Card - Similar but different styling */
        .invoice-card {
            background: linear-gradient(135deg, #1a1a1a 0%, #252525 100%);
            border: 2px solid #6e22dd;
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s;
        }
        
        .invoice-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(110, 34, 221, 0.4);
        }
        
        /* Footer */
        footer {
            text-align: center;
            padding: 30px 20px;
            background-color: #0a0a0a;
            font-size: 12px;
            color: #666;
            margin-top: auto;
            border-top: 1px solid rgba(110, 34, 221, 0.2);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .page-header h1 {
                font-size: 28px;
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
            
            .tabs {
                overflow-x: auto;
            }
            
            .tab {
                padding: 12px 20px;
                font-size: 14px;
                white-space: nowrap;
            }
            
            .appointment-details {
                grid-template-columns: 1fr;
            }
            
            .appointment-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="page-header">
        <h1>My Profile</h1>
        <p>Manage your account and view your appointments</p>
    </div>

    <main>
        <!-- Profile Card -->
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-left">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                    </div>
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                        <p><?php echo htmlspecialchars(($user['country_code'] ?? '') . $user['phone_number']); ?></p>
                    </div>
                </div>
                <div class="profile-actions">
                    <a href="edit_profile.php" class="btn-edit">Edit Profile</a>
                    <a href="logout.php" class="btn-logout">Log Out</a>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="switchTab('appointments')">
                My Appointments
            </button>
            <button class="tab" onclick="switchTab('warranties')">
                My Warranties
            </button>
            <button class="tab" onclick="switchTab('invoices')">
                My Invoices
            </button>
        </div>

        <!-- Appointments Tab -->
        <div id="appointments-tab" class="tab-content active">
            <div class="section-title">
                <span>My Appointments</span>
            </div>

            <?php if (empty($pendingAppointments)): ?>
            <div class="empty-state">
                <div class="empty-title">No Pending Appointments</div>
                <div class="empty-text">You don't have any upcoming appointments yet</div>
                <a href="buildpc.php" class="btn btn-primary" style="max-width: 250px; margin: 0 auto;">
                    Book New Appointment
                </a>
            </div>
            <?php else: ?>
            <div class="appointments-grid">
                <?php foreach ($pendingAppointments as $appointment): ?>
                <div class="appointment-card">
                    <div class="appointment-header">
                        <div class="appointment-ref">
                            <span class="ref-number"><?php echo htmlspecialchars($appointment['invoice_number']); ?></span>
                            <span class="ref-date">Booked on <?php echo date('d M Y', strtotime($appointment['created_at'])); ?></span>
                        </div>
                        <span class="status-badge status-<?php echo $appointment['status']; ?>">
                            <?php echo strtoupper($appointment['status']); ?>
                        </span>
                    </div>
                    
                    <div class="appointment-details">
                        <div class="detail-box">
                            <span class="detail-box-label">Service Type</span>
                            <span class="detail-box-value"><?php echo htmlspecialchars($appointment['service_type']); ?></span>
                        </div>
                        <div class="detail-box">
                            <span class="detail-box-label">Date</span>
                            <span class="detail-box-value"><?php echo date('d M Y', strtotime($appointment['appointment_date'])); ?></span>
                        </div>
                        <div class="detail-box">
                            <span class="detail-box-label">Time</span>
                            <span class="detail-box-value"><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></span>
                        </div>
                        <div class="detail-box">
                            <span class="detail-box-label">Total Amount</span>
                            <span class="detail-box-value">RM <?php echo number_format($appointment['total_amount'], 2); ?></span>
                        </div>
                    </div>
                    
                    <div class="appointment-actions">
                        <a href="appointment_summary_profile.php?id=<?php echo $appointment['appointment_id']; ?>" class="btn btn-primary">
                            View Details
                        </a>
                        <?php if ($appointment['status'] == 'pending'): ?>
                        <button onclick="cancelAppointment(<?php echo $appointment['appointment_id']; ?>)" class="btn btn-secondary">
                            Cancel
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Warranties Tab -->
        <div id="warranties-tab" class="tab-content">
            <div class="section-title">
                <span>My Warranty Claims</span>
            </div>

            <?php if (empty($warrantyClaims)): ?>
            <div class="empty-state">
                <div class="empty-title">No Warranty Claims Yet</div>
                <div class="empty-text">You haven't submitted any warranty claims</div>
                <a href="repair_warranty.php" class="btn btn-primary" style="max-width: 250px; margin: 0 auto;">
                    Submit Warranty Claim
                </a>
            </div>
            <?php else: ?>
            <div class="appointments-grid">
                <?php foreach ($warrantyClaims as $claim): ?>
                <div class="appointment-card">
                    <div class="appointment-header">
                        <div class="appointment-ref">
                            <span class="ref-number">Claim #<?php echo str_pad($claim['warranty_id'], 6, '0', STR_PAD_LEFT); ?></span>
                            <span class="ref-date">Submitted on <?php echo date('d M Y, h:i A', strtotime($claim['created_at'])); ?></span>
                        </div>
                        <span class="status-badge status-<?php echo $claim['claim_status']; ?>">
                            <?php echo strtoupper($claim['claim_status']); ?>
                        </span>
                    </div>
                    
                    <div class="appointment-details">
                        <div class="detail-box">
                            <span class="detail-box-label">Invoice Number</span>
                            <span class="detail-box-value"><?php echo htmlspecialchars($claim['invoice_number']); ?></span>
                        </div>
                        <?php if ($claim['appointment_date']): ?>
                        <div class="detail-box">
                            <span class="detail-box-label">Appointment Date</span>
                            <span class="detail-box-value"><?php echo date('d M Y', strtotime($claim['appointment_date'])); ?></span>
                        </div>
                        <div class="detail-box">
                            <span class="detail-box-label">Appointment Time</span>
                            <span class="detail-box-value"><?php echo date('h:i A', strtotime($claim['appointment_time'])); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Issue Description -->
                    <div class="warranty-issue">
                        <div class="warranty-issue-title">Issue Description</div>
                        <div class="warranty-issue-text">
                            <?php 
                            $issue = htmlspecialchars($claim['claim_reason']);
                            echo strlen($issue) > 150 ? substr($issue, 0, 150) . '...' : $issue;
                            ?>
                        </div>
                    </div>
                    
                    <!-- Admin Response (if any) -->
                    <?php if (!empty($claim['admin_response'])): ?>
                    <div class="admin-response">
                        <div class="admin-response-title">Admin Response</div>
                        <div class="admin-response-text"><?php echo htmlspecialchars($claim['admin_response']); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Action Buttons -->
                    <div class="appointment-actions">
                        <?php if ($claim['claim_status'] === 'approved' && !$claim['appointment_id']): ?>
                            <a href="appointment.php?warranty_id=<?php echo $claim['warranty_id']; ?>" class="btn btn-primary">
                                Schedule Appointment
                            </a>
                        <?php elseif ($claim['claim_status'] === 'scheduled' || $claim['claim_status'] === 'completed'): ?>
                            <a href="appointment_summary_profile.php?id=<?php echo $claim['appointment_id']; ?>" class="btn btn-primary">
                                View Appointment
                            </a>
                        <?php elseif ($claim['claim_status'] === 'rejected'): ?>
                            <a href="repair_warranty.php" class="btn btn-secondary">
                                Submit New Claim
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled style="opacity: 0.5; cursor: not-allowed;">
                                Pending Review
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Invoices Tab -->
        <div id="invoices-tab" class="tab-content">
            <div class="section-title">
                <span>My Invoices</span>
            </div>

            <?php if (empty($completedAppointments)): ?>
            <div class="empty-state">
                <div class="empty-title">No Invoices Yet</div>
                <div class="empty-text">Invoices will appear here after your appointment is completed</div>
            </div>
            <?php else: ?>
            <div class="appointments-grid">
                <?php foreach ($completedAppointments as $invoice): ?>
                <div class="invoice-card">
                    <div class="appointment-header">
                        <div class="appointment-ref">
                            <span class="ref-number">Invoice: <?php echo htmlspecialchars($invoice['invoice_number']); ?></span>
                            <span class="ref-date">Completed on <?php echo date('d M Y', strtotime($invoice['updated_at'] ?? $invoice['created_at'])); ?></span>
                        </div>
                        <span class="status-badge status-completed">
                            COMPLETED
                        </span>
                    </div>
                    
                    <div class="appointment-details">
                        <div class="detail-box">
                            <span class="detail-box-label">Service Type</span>
                            <span class="detail-box-value"><?php echo htmlspecialchars($invoice['service_type']); ?></span>
                        </div>
                        <div class="detail-box">
                            <span class="detail-box-label">Appointment Date</span>
                            <span class="detail-box-value"><?php echo date('d M Y', strtotime($invoice['appointment_date'])); ?></span>
                        </div>
                        <div class="detail-box">
                            <span class="detail-box-label">Invoice Date</span>
                            <span class="detail-box-value"><?php echo date('d M Y', strtotime($invoice['created_at'])); ?></span>
                        </div>
                        <div class="detail-box">
                            <span class="detail-box-label">Total Paid</span>
                            <span class="detail-box-value" style="color: #10b981; font-weight: 800;">RM <?php echo number_format($invoice['total_amount'], 2); ?></span>
                        </div>
                    </div>
                    
                    <div class="appointment-actions">
                        <a href="invoice_detail.php?invoice=<?php echo $invoice['invoice_number']; ?>" class="btn btn-primary">
                            View Invoice
                        </a>
                        <a href="generate_pdf.php?invoice=<?php echo $invoice['invoice_number']; ?>" class="btn btn-secondary">
                            Download PDF
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script>
        function switchTab(tabName) {
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Add active class to clicked tab
            event.target.classList.add('active');
            
            // Show selected tab content
            document.getElementById(tabName + '-tab').classList.add('active');
        }

        function cancelAppointment(appointmentId) {
            if (confirm('Are you sure you want to cancel this appointment?')) {
                fetch('cancel_appointment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'appointment_id=' + appointmentId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Appointment cancelled successfully');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error cancelling appointment');
                    console.error('Error:', error);
                });
            }
        }
    </script>
</body>
</html>