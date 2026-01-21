<?php
session_start();
require_once '../db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'penjual') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$warranty_id = $data['warranty_id'] ?? 0;

try {
    // Update warranty claim status
    $stmt = $pdo->prepare("
        UPDATE warranty_claims 
        SET claim_status = 'approved',
            updated_at = NOW()
        WHERE warranty_id = ?
    ");
    $stmt->execute([$warranty_id]);
    
    // Create notification for customer
    $claimStmt = $pdo->prepare("SELECT account_id, invoice_number FROM warranty_claims WHERE warranty_id = ?");
    $claimStmt->execute([$warranty_id]);
    $claim = $claimStmt->fetch();
    
    if ($claim) {
        $notifStmt = $pdo->prepare("
            INSERT INTO notifications (account_id, notification_type, title, message, link_url, related_id)
            VALUES (?, 'warranty_approved', ?, ?, ?, ?)
        ");
        $notifStmt->execute([
            $claim['account_id'],
            'Warranty Claim Approved!',
            'Your warranty claim for invoice ' . $claim['invoice_number'] . ' has been approved. You can now schedule a repair appointment.',
            'warranty_status.php',
            $warranty_id
        ]);
    }
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>