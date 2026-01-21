<?php
session_start();
require_once '../db_config.php';

header('Content-Type: application/json');

// Check if user is logged in as admin
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get appointment ID from GET
$appointment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($appointment_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid appointment ID']);
    exit();
}

try {
    // Fetch appointment with customer details AND seller/referrer details
    $stmt = $pdo->prepare("
        SELECT a.*, 
               acc.first_name, 
               acc.last_name, 
               acc.email, 
               acc.phone_number,
               a.is_referral,
               a.reference_code,
               a.referrer_id,
               seller.first_name as seller_first_name,
               seller.last_name as seller_last_name,
               seller.reference_code as seller_ref_code,
               rc.commission_amount,
               rc.commission_status
        FROM appointments a
        JOIN account acc ON a.account_id = acc.account_id
        LEFT JOIN account seller ON a.referrer_id = seller.account_id
        LEFT JOIN referral_commissions rc ON a.appointment_id = rc.appointment_id
        WHERE a.appointment_id = ?
    ");
    
    $stmt->execute([$appointment_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$appointment) {
        echo json_encode(['success' => false, 'message' => 'Appointment not found']);
        exit();
    }
    
    // Fetch appointment items (components)
    $itemsStmt = $pdo->prepare("
        SELECT * FROM appointment_items 
        WHERE appointment_id = ?
        ORDER BY category
    ");
    $itemsStmt->execute([$appointment_id]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Prepare customer data
    $appointment['customer_full_name'] = $appointment['first_name'] . ' ' . $appointment['last_name'];
    $appointment['customer_phone'] = $appointment['phone_number'];
    $appointment['items'] = $items;
    
    // Prepare seller/referrer data
    if ($appointment['is_referral'] && $appointment['referrer_id']) {
        $appointment['seller_full_name'] = $appointment['seller_first_name'] . ' ' . $appointment['seller_last_name'];
    } else {
        $appointment['seller_full_name'] = null;
    }
    
    // Format dates
    $appointment['formatted_date'] = date('l, d F Y', strtotime($appointment['appointment_date']));
    $appointment['formatted_time'] = date('h:i A', strtotime($appointment['appointment_time']));
    
    echo json_encode([
        'success' => true,
        'appointment' => $appointment
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>