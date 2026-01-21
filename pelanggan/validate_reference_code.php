<?php
session_start();
require_once '../db_config.php';

header('Content-Type: application/json');

// Enable error logging for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['account_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login first',
        'debug' => 'Not logged in'
    ]);
    exit();
}

// Get reference code from POST
$referenceCode = isset($_POST['reference_code']) ? trim($_POST['reference_code']) : '';

// Validate input
if (empty($referenceCode)) {
    echo json_encode([
        'success' => false,
        'message' => 'Reference code is required',
        'debug' => 'Empty code'
    ]);
    exit();
}

// Store original for debugging
$originalCode = $referenceCode;
$referenceCode = strtolower($referenceCode);

try {
    // First, let's check if ANY sellers exist with reference codes
    $checkStmt = $pdo->prepare("
        SELECT COUNT(*) as total,
               GROUP_CONCAT(LOWER(reference_code)) as codes
        FROM account 
        WHERE role = 'penjual' 
        AND reference_code IS NOT NULL 
        AND reference_code != ''
    ");
    $checkStmt->execute();
    $checkResult = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    // Query to find seller by reference code (case-insensitive)
    $stmt = $pdo->prepare("
        SELECT account_id, first_name, last_name, reference_code, role
        FROM account 
        WHERE LOWER(reference_code) = ? 
        AND role = 'penjual'
        AND status = 'active'
    ");
    
    $stmt->execute([$referenceCode]);
    $seller = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($seller) {
        // Reference code found
        echo json_encode([
            'success' => true,
            'referrer_id' => $seller['account_id'],
            'referrer_name' => $seller['first_name'] . ' ' . $seller['last_name'],
            'reference_code' => strtoupper($seller['reference_code'])
        ]);
    } else {
        // Reference code not found - provide helpful debug info
        echo json_encode([
            'success' => false,
            'message' => 'Reference code not found. Please check and try again.',
            'debug' => [
                'searched_code' => $referenceCode,
                'original_code' => $originalCode,
                'total_sellers_with_codes' => $checkResult['total'],
                'available_codes' => $checkResult['codes']
            ]
        ]);
    }
    
} catch (PDOException $e) {
    // Database error
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again later.',
        'debug' => [
            'error' => $e->getMessage(),
            'code_searched' => $referenceCode
        ]
    ]);
    
    // Log error
    error_log("Reference code validation error: " . $e->getMessage());
}
?>