<?php
session_start();
require_once '../db_config.php';

header('Content-Type: application/json');

// Check if user is logged in as seller
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get appointment ID from POST
$data = json_decode(file_get_contents('php://input'), true);
$appointment_id = isset($data['appointment_id']) ? intval($data['appointment_id']) : 0;

if ($appointment_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid appointment ID']);
    exit();
}

try {
    // Check if appointment exists and is approved
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE appointment_id = ? AND status = 'approved'");
    $stmt->execute([$appointment_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$appointment) {
        echo json_encode(['success' => false, 'message' => 'Appointment not found or not approved yet']);
        exit();
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Update status to completed
    $updateStmt = $pdo->prepare("UPDATE appointments SET status = 'completed', updated_at = NOW() WHERE appointment_id = ?");
    $updateStmt->execute([$appointment_id]);
    
    // Fetch appointment items to update stock
    $itemsStmt = $pdo->prepare("SELECT part_id, quantity FROM appointment_items WHERE appointment_id = ?");
    $itemsStmt->execute([$appointment_id]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Update inventory stock (reduce stock)
    foreach ($items as $item) {
        $stockStmt = $pdo->prepare("UPDATE inventory SET stock = stock - ? WHERE part_id = ? AND stock >= ?");
        $stockStmt->execute([$item['quantity'], $item['part_id'], $item['quantity']]);
        
        // Check if stock was updated (if not enough stock, rollback)
        if ($stockStmt->rowCount() === 0) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Insufficient stock for some items']);
            exit();
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Appointment completed and invoice generated successfully',
        'invoice_number' => $appointment['invoice_number']
    ]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>