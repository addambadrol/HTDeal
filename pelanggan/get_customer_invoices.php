<?php
session_start();
require_once '../db_config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'pelanggan') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

$account_id = $_SESSION['account_id'];

try {
    // Fetch ALL completed appointments (not just PC Build)
    $stmt = $pdo->prepare("
        SELECT 
            a.appointment_id,
            a.invoice_number,
            a.appointment_date as date,
            a.total_amount,
            a.service_type,
            a.created_at,
            MIN(ai.warranty_start_date) as warranty_start_date,
            MAX(ai.warranty_end_date) as warranty_end_date,
            MAX(ai.warranty_period_months) as warranty_period_months
        FROM appointments a
        LEFT JOIN appointment_items ai ON a.appointment_id = ai.appointment_id
        WHERE a.account_id = ? 
        AND a.status = 'completed'
        GROUP BY a.appointment_id
        ORDER BY a.created_at DESC
    ");
    
    $stmt->execute([$account_id]);
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate warranty status for each invoice
    $today = new DateTime();
    foreach ($invoices as &$invoice) {
        // Check if warranty dates exist
        if ($invoice['warranty_end_date']) {
            $warrantyEndDate = new DateTime($invoice['warranty_end_date']);
            $invoice['warranty_valid'] = $warrantyEndDate > $today;
            $invoice['warranty_expired'] = $warrantyEndDate <= $today;
            
            // Calculate days remaining
            if ($invoice['warranty_valid']) {
                $interval = $today->diff($warrantyEndDate);
                $invoice['warranty_days_remaining'] = $interval->days;
            } else {
                $invoice['warranty_days_remaining'] = 0;
            }
        } else {
            // If no warranty dates set, calculate from appointment date
            // Default: 1 year warranty from purchase
            $appointmentDate = new DateTime($invoice['created_at']);
            $warrantyEndDate = clone $appointmentDate;
            $warrantyEndDate->modify('+1 year');
            
            $invoice['warranty_start_date'] = $appointmentDate->format('Y-m-d');
            $invoice['warranty_end_date'] = $warrantyEndDate->format('Y-m-d');
            $invoice['warranty_valid'] = $warrantyEndDate > $today;
            $invoice['warranty_expired'] = $warrantyEndDate <= $today;
            
            if ($invoice['warranty_valid']) {
                $interval = $today->diff($warrantyEndDate);
                $invoice['warranty_days_remaining'] = $interval->days;
            } else {
                $invoice['warranty_days_remaining'] = 0;
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'invoices' => $invoices
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>