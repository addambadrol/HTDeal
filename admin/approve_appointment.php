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
    // Check if appointment exists and is pending
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE appointment_id = ? AND status = 'pending'");
    $stmt->execute([$appointment_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$appointment) {
        echo json_encode(['success' => false, 'message' => 'Appointment not found or already processed']);
        exit();
    }
    
    // Update status to approved
    $updateStmt = $pdo->prepare("UPDATE appointments SET status = 'approved', updated_at = NOW() WHERE appointment_id = ?");
    $updateStmt->execute([$appointment_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Appointment approved successfully',
        'invoice_number' => $appointment['invoice_number']
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>