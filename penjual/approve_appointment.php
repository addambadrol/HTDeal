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
        SELECT a.*, acc.email, acc.first_name
        FROM appointments a
        JOIN account acc ON a.account_id = acc.account_id
        WHERE a.appointment_id = ?
    ");
    $stmt->execute([$appointment_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$appointment) {
        throw new Exception('Appointment not found');
    }
    
    if ($appointment['status'] !== 'pending') {
        throw new Exception('Appointment is not pending');
    }
    
    // Update appointment status to approved
    $updateStmt = $pdo->prepare("
        UPDATE appointments 
        SET status = 'approved', updated_at = NOW()
        WHERE appointment_id = ?
    ");
    $updateStmt->execute([$appointment_id]);
    
    // Check if this is NOT a warranty repair (warranty repairs don't reduce stock)
    if ($appointment['service_type'] !== 'Warranty Repair') {
        // Get appointment items
        $itemsStmt = $pdo->prepare("
            SELECT part_id, quantity 
            FROM appointment_items 
            WHERE appointment_id = ? AND part_id IS NOT NULL
        ");
        $itemsStmt->execute([$appointment_id]);
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Update inventory stock for each item
        foreach ($items as $item) {
            // Check current stock
            $checkStmt = $pdo->prepare("
                SELECT stock FROM inventory WHERE part_id = ?
            ");
            $checkStmt->execute([$item['part_id']]);
            $inventoryItem = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($inventoryItem) {
                $currentStock = $inventoryItem['stock'];
                $newStock = $currentStock - $item['quantity'];
                
                if ($newStock < 0) {
                    throw new Exception('Insufficient stock for part ID: ' . $item['part_id']);
                }
                
                // Update inventory stock
                $updateInventory = $pdo->prepare("
                    UPDATE inventory 
                    SET stock = ?,
                        updated_at = NOW()
                    WHERE part_id = ?
                ");
                $updateInventory->execute([$newStock, $item['part_id']]);
            }
        }
    }
    
    // Create notification for customer
    $notifStmt = $pdo->prepare("
        INSERT INTO notifications (account_id, notification_type, title, message, link_url, related_id)
        VALUES (?, 'appointment_approved', ?, ?, ?, ?)
    ");
    $notifStmt->execute([
        $appointment['account_id'],
        'Appointment Approved! 🎉',
        'Your appointment for ' . $appointment['service_type'] . ' has been approved. Please collect your items on ' . 
        date('d M Y', strtotime($appointment['appointment_date'])) . ' at ' . 
        date('h:i A', strtotime($appointment['appointment_time'])),
        'appointment_summary.php?id=' . $appointment_id,
        $appointment_id
    ]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Appointment approved successfully'
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