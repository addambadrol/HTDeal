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
$reason = isset($data['reason']) ? trim($data['reason']) : '';

if ($appointment_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid appointment ID']);
    exit();
}

if (empty($reason)) {
    echo json_encode(['success' => false, 'message' => 'Rejection reason is required']);
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
    
    if ($appointment['status'] !== 'pending') {
        throw new Exception('Only pending appointments can be rejected');
    }
    
    // Update appointment status to rejected
    $updateStmt = $pdo->prepare("
        UPDATE appointments 
        SET status = 'rejected', 
            notes = CONCAT(COALESCE(notes, ''), '\nRejection Reason: ', ?),
            updated_at = NOW()
        WHERE appointment_id = ?
    ");
    $updateStmt->execute([$reason, $appointment_id]);
    
    // If this is a warranty claim appointment, update the warranty claim status
    if ($appointment['warranty_id']) {
        $updateWarranty = $pdo->prepare("
            UPDATE warranty_claims 
            SET claim_status = 'rejected',
                admin_response = ?,
                updated_at = NOW()
            WHERE warranty_id = ?
        ");
        $updateWarranty->execute([$reason, $appointment['warranty_id']]);
        
        // Notification for warranty rejection
        $notifStmt = $pdo->prepare("
            INSERT INTO notifications (account_id, notification_type, title, message, link_url, related_id)
            VALUES (?, 'warranty_rejected', ?, ?, ?, ?)
        ");
        $notifStmt->execute([
            $appointment['account_id'],
            'Warranty Claim Rejected ❌',
            'Your warranty claim has been rejected. Reason: ' . $reason . '. Please contact us if you have any questions.',
            'warranty_status.php',
            $appointment['warranty_id']
        ]);
    } else {
        // Notification for regular appointment rejection
        $notifStmt = $pdo->prepare("
            INSERT INTO notifications (account_id, notification_type, title, message, link_url, related_id)
            VALUES (?, 'appointment_rejected', ?, ?, ?, ?)
        ");
        $notifStmt->execute([
            $appointment['account_id'],
            'Appointment Rejected ❌',
            'Your appointment for ' . $appointment['service_type'] . ' on ' . date('d M Y', strtotime($appointment['appointment_date'])) . ' has been rejected. Reason: ' . $reason . '. Please contact us for more information.',
            'appointment_summary.php?id=' . $appointment_id,
            $appointment_id
        ]);
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Appointment rejected successfully'
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