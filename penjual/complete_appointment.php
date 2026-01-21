<?php
session_start();
require_once '../db_config.php';

header('Content-Type: application/json');

// Check if user is logged in as seller
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'penjual') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);
$appointment_id = isset($data['appointment_id']) ? intval($data['appointment_id']) : 0;

if ($appointment_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid appointment ID']);
    exit();
}

try {
    $pdo->beginTransaction();
    
    // Get appointment details
    $stmt = $pdo->prepare("
        SELECT * FROM appointments WHERE appointment_id = ?
    ");
    $stmt->execute([$appointment_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$appointment) {
        throw new Exception('Appointment not found');
    }
    
    if ($appointment['status'] !== 'approved') {
        throw new Exception('Appointment must be approved first');
    }
    
    // Update appointment status to completed
    $updateStmt = $pdo->prepare("
        UPDATE appointments 
        SET status = 'completed', updated_at = NOW()
        WHERE appointment_id = ?
    ");
    $updateStmt->execute([$appointment_id]);
    
    // If this is a referral appointment, update commission status to 'paid'
    if ($appointment['is_referral'] && $appointment['referrer_id']) {
        $updateCommission = $pdo->prepare("
            UPDATE referral_commissions 
            SET commission_status = 'paid',
                paid_at = NOW()
            WHERE appointment_id = ?
        ");
        $updateCommission->execute([$appointment_id]);
        
        // Update seller's total commission
        $commission_amount = $appointment['total_amount'] * 0.10;
        $updateSeller = $pdo->prepare("
            UPDATE account 
            SET total_commission = total_commission + ?,
                total_referrals = total_referrals + 1
            WHERE account_id = ?
        ");
        $updateSeller->execute([$commission_amount, $appointment['referrer_id']]);
        
        // Create notification for referrer (seller)
        $notifReferrer = $pdo->prepare("
            INSERT INTO notifications (account_id, notification_type, title, message, link_url, related_id)
            VALUES (?, 'commission_earned', ?, ?, ?, ?)
        ");
        $notifReferrer->execute([
            $appointment['referrer_id'],
            '💰 Commission Earned!',
            'Your referral appointment has been completed. Commission of RM ' . number_format($commission_amount, 2) . ' has been paid.',
            'homepage.php',
            $appointment_id
        ]);
    }
    
    // If this is a warranty claim appointment, update the warranty claim status
    if ($appointment['warranty_id']) {
        $updateWarranty = $pdo->prepare("
            UPDATE warranty_claims 
            SET claim_status = 'completed',
                updated_at = NOW()
            WHERE warranty_id = ?
        ");
        $updateWarranty->execute([$appointment['warranty_id']]);
    }
    
    // Create notification for customer
    $notifStmt = $pdo->prepare("
        INSERT INTO notifications (account_id, notification_type, title, message, link_url, related_id)
        VALUES (?, 'appointment_completed', ?, ?, ?, ?)
    ");
    $notifStmt->execute([
        $appointment['account_id'],
        'Service Completed! ✅',
        'Your ' . $appointment['service_type'] . ' service has been completed. Thank you for choosing HTDeal!',
        'appointment_summary.php?id=' . $appointment_id,
        $appointment_id
    ]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Appointment marked as completed',
        'invoice_number' => $appointment['invoice_number']
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>