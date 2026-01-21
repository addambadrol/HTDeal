<?php
session_start();
require_once '../db_config.php';

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'pelanggan') {
    header("Location: ../landing/loginpelanggan.php");
    exit();
}

$account_id = $_SESSION['account_id'];

// Fetch all warranty claims
try {
    $stmt = $pdo->prepare("
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
    $stmt->execute([$account_id]);
    $claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTDeal - My Warranty Claims</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: #121212;
            color: #fff;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .page-header h1 {
            font-size: 36px;
            color: #6e22dd;
            margin-bottom: 10px;
        }
        
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background: #333;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .back-btn:hover {
            background: #444;
        }
        
        .claim-card {
            background: #1a1a1a;
            border: 2px solid #6e22dd;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .claim-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(110, 34, 221, 0.3);
        }
        
        .claim-id {
            font-size: 20px;
            font-weight: bold;
            color: #6e22dd;
        }
        
        .status-badge {
            padding: 8px 16px;
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
        
        .status-rejected {
            background: #ef4444;
            color: #fff;
        }
        
        .status-scheduled {
            background: #3b82f6;
            color: #fff;
        }
        
        .status-completed {
            background: #10b981;
            color: #fff;
        }
        
        .claim-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .info-label {
            font-size: 12px;
            color: #888;
            text-transform: uppercase;
        }
        
        .info-value {
            font-size: 15px;
            color: #fff;
            font-weight: 600;
        }
        
        .admin-response {
            background: rgba(110, 34, 221, 0.1);
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #6e22dd;
            margin-bottom: 15px;
        }
        
        .admin-response-title {
            font-weight: bold;
            color: #6e22dd;
            margin-bottom: 8px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: bold;
            text-align: center;
            transition: all 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(110, 34, 221, 0.5);
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
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }
        
        .empty-state h2 {
            font-size: 24px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="warranty_success.php" class="back-btn">← Back to Home</a>
        
        <div class="page-header">
            <h1>My Warranty Claims</h1>
            <p>Track the status of your warranty claims</p>
        </div>
        
        <?php if (empty($claims)): ?>
        <div class="empty-state">
            <h2>No Warranty Claims Yet</h2>
            <p>You haven't submitted any warranty claims.</p>
            <br>
            <a href="repair_warranty.php" class="btn">Submit a Claim</a>
        </div>
        <?php else: ?>
            <?php foreach ($claims as $claim): ?>
            <div class="claim-card">
                <div class="claim-header">
                    <div class="claim-id">Claim #<?php echo $claim['warranty_id']; ?></div>
                    <div class="status-badge status-<?php echo $claim['claim_status']; ?>">
                        <?php echo ucfirst($claim['claim_status']); ?>
                    </div>
                </div>
                
                <div class="claim-info">
                    <div class="info-item">
                        <span class="info-label">Invoice Number</span>
                        <span class="info-value"><?php echo $claim['invoice_number']; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Submitted On</span>
                        <span class="info-value"><?php echo date('d M Y, h:i A', strtotime($claim['created_at'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Issue Description</span>
                        <span class="info-value"><?php echo htmlspecialchars(substr($claim['claim_reason'], 0, 100)) . (strlen($claim['claim_reason']) > 100 ? '...' : ''); ?></span>
                    </div>
                    <?php if ($claim['appointment_date']): ?>
                    <div class="info-item">
                        <span class="info-label">Appointment</span>
                        <span class="info-value">
                            <?php echo date('d M Y', strtotime($claim['appointment_date'])); ?>, 
                            <?php echo date('h:i A', strtotime($claim['appointment_time'])); ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($claim['admin_response']): ?>
                <div class="admin-response">
                    <div class="admin-response-title">Admin Response:</div>
                    <div><?php echo htmlspecialchars($claim['admin_response']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($claim['claim_status'] === 'approved' && !$claim['appointment_id']): ?>
                <a href="appointment.php?warranty_id=<?php echo $claim['warranty_id']; ?>" class="btn">
                    Schedule Appointment
                </a>
                <?php elseif ($claim['claim_status'] === 'scheduled'): ?>
                <button class="btn btn-disabled" disabled>
                    Appointment Scheduled
                </button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>