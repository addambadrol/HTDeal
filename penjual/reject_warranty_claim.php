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
$reason = $data['reason'] ?? 'No reason provided';

try {
    // Update warranty claim with rejection
    $stmt = $pdo->prepare("
        UPDATE warranty_claims 
        SET claim_status = 'rejected',
            rejection_reason = ?,
            updated_at = NOW()
        WHERE warranty_id = ?
    ");
    $stmt->execute([$reason, $warranty_id]);
    
    // Notify customer
    $claimStmt = $pdo->prepare("SELECT account_id, invoice_number FROM warranty_claims WHERE warranty_id = ?");
    $claimStmt->execute([$warranty_id]);
    $claim = $claimStmt->fetch();
    
    if ($claim) {
        $notifStmt = $pdo->prepare("
            INSERT INTO notifications (account_id, notification_type, title, message, link_url, related_id)
            VALUES (?, 'warranty_rejected', ?, ?, ?, ?)
        ");
        $notifStmt->execute([
            $claim['account_id'],
            'Warranty Claim Rejected',
            'Your warranty claim has been rejected. Reason: ' . $reason,
            'warranty_status.php',
            $warranty_id
        ]);
    }
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
```

## 🎯 **Summary - Complete Warranty Flow:**
```
CUSTOMER SIDE:
1. repair_warranty.php → Submit claim (upload evidence)
2. submit_warranty.php → Create warranty_claims record
3. warranty_status.php → View claim status

PENJUAL SIDE:
4. appointment.php → View pending warranty claims
5. get_warranty_claim_details.php → View claim details + photos
6. approve_warranty_claim.php → Approve claim
   OR reject_warranty_claim.php → Reject claim

CUSTOMER AFTER APPROVAL:
7. warranty_status.php → Click "Schedule Repair"
8. appointment.php?warranty_id=X → Pick date/time
9. confirm_appointment.php → Create appointment
10. appointment_summary.php → Confirmation