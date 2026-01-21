<?php
session_start();
require_once '../db_config.php';

header('Content-Type: application/json');

// Check if user is logged in as seller
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'penjual') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$appointment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($appointment_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid appointment ID']);
    exit();
}

try {
    // Get appointment details with referral info
    $stmt = $pdo->prepare("
    SELECT 
        a.*,
        acc.first_name,
        acc.last_name,
        acc.email,
        acc.phone_number,
        CONCAT(acc.first_name, ' ', acc.last_name) as customer_full_name,
        COALESCE(acc.phone_number, 'N/A') as customer_phone,
        rc.commission_amount,
        rc.commission_status
    FROM appointments a
    JOIN account acc ON a.account_id = acc.account_id
    LEFT JOIN referral_commissions rc ON a.appointment_id = rc.appointment_id
    WHERE a.appointment_id = ?
");
    $stmt->execute([$appointment_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$appointment) {
        echo json_encode(['success' => false, 'message' => 'Appointment not found']);
        exit();
    }
    
    // Get appointment items
    $itemsStmt = $pdo->prepare("
        SELECT * FROM appointment_items WHERE appointment_id = ?
    ");
    $itemsStmt->execute([$appointment_id]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format dates
    $appointment['formatted_date'] = date('d M Y (l)', strtotime($appointment['appointment_date']));
    $appointment['formatted_time'] = date('h:i A', strtotime($appointment['appointment_time']));
    $appointment['items'] = $items;
    
    // Calculate commission if not exists (for display purposes)
    if (!$appointment['commission_amount'] && $appointment['is_referral']) {
        $appointment['commission_amount'] = $appointment['total_amount'] * 0.10;
        $appointment['commission_status'] = 'pending';
    }
    
    echo json_encode([
        'success' => true,
        'appointment' => $appointment
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>