<?php
session_start();
require_once '../db_config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Check if user logged in as penjual
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'penjual') {
    die("Unauthorized access");
}

// Get invoice number
$invoice_number = isset($_GET['invoice']) ? $_GET['invoice'] : '';

if (empty($invoice_number)) {
    die("Invoice number required");
}

try {
    // Fetch appointment & customer data
    $stmt = $pdo->prepare("
        SELECT a.*, 
               acc.first_name, 
               acc.last_name, 
               acc.email, 
               acc.phone_number,
               acc.country_code
        FROM appointments a
        JOIN account acc ON a.account_id = acc.account_id
        WHERE a.invoice_number = ? AND a.status = 'completed'
    ");
    
    $stmt->execute([$invoice_number]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$appointment) {
        die("Invoice not found or not completed yet");
    }
    
    // Fetch appointment items
    $itemsStmt = $pdo->prepare("
        SELECT * FROM appointment_items 
        WHERE appointment_id = ?
        ORDER BY category
    ");
    $itemsStmt->execute([$appointment['appointment_id']]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Prepare customer data
    $customer = [
        'first_name' => $appointment['first_name'],
        'last_name' => $appointment['last_name'],
        'email' => $appointment['email'],
        'phone_number' => ($appointment['country_code'] ?? '') . $appointment['phone_number'],
        'address' => $appointment['address'] ?? 'N/A'
    ];
    
    // Load invoice template
    ob_start();
    include '../templates/invoice_template.php';
    $html = ob_get_clean();
    
    // Configure DomPDF
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', false);
    $options->set('defaultFont', 'Arial');
    
    // Create PDF
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    
    // Output PDF
    $filename = 'Invoice_' . $invoice_number . '.pdf';
    $dompdf->stream($filename, ['Attachment' => true]);
    
} catch (Exception $e) {
    die("Error generating PDF: " . $e->getMessage());
}
?>