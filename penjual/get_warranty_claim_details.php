<?php
session_start();
require_once '../db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'penjual') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$warranty_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    // Fetch warranty claim details
    $stmt = $pdo->prepare("
        SELECT wc.*,
               acc.first_name,
               acc.last_name,
               acc.email,
               acc.phone_number,
               a.total_amount,
               a.service_type
        FROM warranty_claims wc
        JOIN account acc ON wc.account_id = acc.account_id
        LEFT JOIN appointments a ON wc.invoice_number = a.invoice_number
        WHERE wc.warranty_id = ?
    ");
    $stmt->execute([$warranty_id]);
    $claim = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$claim) {
        echo json_encode(['success' => false, 'message' => 'Claim not found']);
        exit();
    }
    
    // Fetch uploaded files
    $filesStmt = $pdo->prepare("SELECT * FROM warranty_files WHERE warranty_id = ?");
    $filesStmt->execute([$warranty_id]);
    $files = $filesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $claim['files'] = $files;
    $claim['customer_name'] = $claim['first_name'] . ' ' . $claim['last_name'];
    $claim['phone'] = ($claim['country_code'] ?? '') . $claim['phone_number'];
    $claim['formatted_date'] = date('d M Y, h:i A', strtotime($claim['created_at']));
    
    echo json_encode(['success' => true, 'claim' => $claim]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>