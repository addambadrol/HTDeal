<?php
session_start();
require_once '../db_config.php';

header('Content-Type: application/json');

// Check if user is logged in as customer
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'pelanggan') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

$account_id = $_SESSION['account_id'];
$invoice_number = isset($_GET['invoice']) ? $_GET['invoice'] : '';

if (empty($invoice_number)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invoice number required'
    ]);
    exit();
}

try {
    // Fetch appointment details
    $stmt = $pdo->prepare("
        SELECT 
            appointment_id,
            invoice_number,
            appointment_date,
            appointment_time,
            service_type,
            total_amount,
            status,
            created_at
        FROM appointments
        WHERE invoice_number = ? AND account_id = ?
    ");
    
    $stmt->execute([$invoice_number, $account_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$appointment) {
        echo json_encode([
            'success' => false,
            'message' => 'Invoice not found'
        ]);
        exit();
    }
    
    // Fetch appointment items WITH WARRANTY INFO
    $itemsStmt = $pdo->prepare("
        SELECT 
            part_id,
            part_code,
            part_name,
            category,
            quantity,
            unit_price,
            total_price,
            warranty_start_date,
            warranty_end_date,
            warranty_period_months
        FROM appointment_items
        WHERE appointment_id = ?
        ORDER BY category
    ");
    
    $itemsStmt->execute([$appointment['appointment_id']]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'invoice' => [
            'invoice_number' => $appointment['invoice_number'],
            'date' => $appointment['created_at'],
            'appointment_date' => $appointment['appointment_date'],
            'appointment_time' => $appointment['appointment_time'],
            'service_type' => $appointment['service_type'],
            'total_amount' => $appointment['total_amount'],
            'status' => $appointment['status'],
            'items' => $items
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>