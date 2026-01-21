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

// Validate POST data
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

$invoice_number = isset($_POST['invoice']) ? trim($_POST['invoice']) : '';
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

if (empty($invoice_number) || empty($reason)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invoice number and reason are required'
    ]);
    exit();
}

// Check if files were uploaded
if (empty($_FILES)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please upload at least one photo or video'
    ]);
    exit();
}

try {
    // Verify invoice belongs to this customer
    $stmt = $pdo->prepare("
        SELECT appointment_id, service_type 
        FROM appointments 
        WHERE invoice_number = ? AND account_id = ? AND status = 'completed'
    ");
    $stmt->execute([$invoice_number, $account_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$appointment) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid invoice number or appointment not completed'
        ]);
        exit();
    }
    
    // Check if warranty claim already exists for this invoice
    $checkStmt = $pdo->prepare("
        SELECT warranty_id 
        FROM warranty_claims 
        WHERE invoice_number = ?
    ");
    $checkStmt->execute([$invoice_number]);
    
    if ($checkStmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'A warranty claim has already been submitted for this invoice'
        ]);
        exit();
    }
    
    // Create uploads directory if it doesn't exist
    $upload_dir = '../uploads/warranty_claims/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Process file uploads
    $uploaded_files = [];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'mp4', 'mov'];
    $max_file_size = 10 * 1024 * 1024; // 10MB
    
    foreach ($_FILES as $key => $file) {
        if ($file['error'] === UPLOAD_ERR_OK) {
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            // Validate file extension
            if (!in_array($file_extension, $allowed_extensions)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid file type. Only JPG, PNG, MP4, and MOV files are allowed'
                ]);
                exit();
            }
            
            // Validate file size
            if ($file['size'] > $max_file_size) {
                echo json_encode([
                    'success' => false,
                    'message' => 'File size exceeds 10MB limit'
                ]);
                exit();
            }
            
            // Generate unique filename
            $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
            $destination = $upload_dir . $unique_filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $uploaded_files[] = $unique_filename;
            }
        }
    }
    
    if (empty($uploaded_files)) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to upload files. Please try again'
        ]);
        exit();
    }
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Insert warranty claim with status 'pending' (not yet approved, no appointment)
    $insertStmt = $pdo->prepare("
        INSERT INTO warranty_claims (
            account_id,
            invoice_number,
            claim_reason,
            claim_status,
            created_at
        ) VALUES (?, ?, ?, 'pending', NOW())
    ");
    
    $insertStmt->execute([
        $account_id,
        $invoice_number,
        $reason
    ]);
    
    $warranty_id = $pdo->lastInsertId();
    
    // Insert file records
    $fileStmt = $pdo->prepare("
        INSERT INTO warranty_files (
            warranty_id,
            file_name,
            file_type,
            uploaded_at
        ) VALUES (?, ?, ?, NOW())
    ");
    
    foreach ($uploaded_files as $filename) {
        $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $file_type = in_array($file_extension, ['mp4', 'mov']) ? 'video' : 'image';
        
        $fileStmt->execute([
            $warranty_id,
            $filename,
            $file_type
        ]);
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Warranty claim submitted successfully',
        'warranty_id' => $warranty_id
    ]);
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Clean up uploaded files on error
    if (!empty($uploaded_files)) {
        foreach ($uploaded_files as $filename) {
            $file_path = $upload_dir . $filename;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>