<?php
session_start();
require_once '../db_config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['account_id']) || $_SESSION['role'] != 'pelanggan') {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Validate required fields
if (!isset($_POST['appointment_date']) || !isset($_POST['appointment_time']) || !isset($_POST['service_type'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

// Get form data
$account_id = $_SESSION['account_id'];
$appointment_date = $_POST['appointment_date'];
$appointment_time = $_POST['appointment_time'];
$service_type = $_POST['service_type'];
$warranty_id = isset($_POST['warranty_id']) ? intval($_POST['warranty_id']) : 0;

// Get reference code data (OPTIONAL)
$reference_code = isset($_POST['reference_code']) ? trim(strtolower($_POST['reference_code'])) : null;
$referrer_id = isset($_POST['referrer_id']) ? intval($_POST['referrer_id']) : null;
$is_referral = ($reference_code && $referrer_id) ? 1 : 0;

try {
    // Start transaction
    $pdo->beginTransaction();
    
    $invoice_number = '';
    $total_price = 0;
    $notes = '';
    
    // Handle Warranty & Repair
    if ($service_type === 'Warranty & Repair' && $warranty_id > 0) {
        // Verify warranty claim
        $stmt = $pdo->prepare("
            SELECT wc.*, a.total_amount, a.invoice_number as original_invoice
            FROM warranty_claims wc
            LEFT JOIN appointments a ON wc.invoice_number = a.invoice_number
            WHERE wc.warranty_id = ? AND wc.account_id = ? AND wc.claim_status = 'approved'
        ");
        $stmt->execute([$warranty_id, $account_id]);
        $warranty_claim = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$warranty_claim) {
            throw new Exception('Invalid warranty claim');
        }
        
        // Check if appointment already exists for this warranty claim
        if ($warranty_claim['appointment_id']) {
            throw new Exception('Appointment already scheduled for this warranty claim');
        }
        
        // Generate NEW invoice for warranty repair
        $invoice_number = 'WRR' . date('Ymd') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        // Check uniqueness
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE invoice_number = ?");
        $checkStmt->execute([$invoice_number]);
        while ($checkStmt->fetchColumn() > 0) {
            $invoice_number = 'WRR' . date('Ymd') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $checkStmt->execute([$invoice_number]);
        }
        
        $total_price = 0; // Warranty repair is usually free
        $notes = "Warranty & Repair - Claim #" . $warranty_id . " - Original Invoice: " . $warranty_claim['invoice_number'] . " - " . $warranty_claim['claim_reason'];
        
    } else {
        // Handle Build PC OR Other Service - Generate NEW invoice number
        $invoice_number = 'INV' . date('Ymd') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        // Check if invoice number exists
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE invoice_number = ?");
        $checkStmt->execute([$invoice_number]);
        while ($checkStmt->fetchColumn() > 0) {
            $invoice_number = 'INV' . date('Ymd') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $checkStmt->execute([$invoice_number]);
        }
        
        $build_items = isset($_POST['build_items']) ? json_decode($_POST['build_items'], true) : [];
        
        if (empty($build_items)) {
            throw new Exception('No items selected');
        }
        
        // Calculate total price
        foreach ($build_items as $item) {
            $total_price += floatval($item['price']) * intval($item['quantity'] ?? 1);
        }
        
        // Set notes based on service type
        if ($service_type === 'Other Service') {
            $notes = "Other Service - " . count($build_items) . " service(s) selected";
        } else {
            $notes = "Build PC - " . count($build_items) . " components selected";
        }
        
        // Add reference code info to notes if present
        if ($reference_code) {
            $notes .= " | Referral Code: " . strtoupper($reference_code);
        }
    }
    
    // Insert appointment with reference code data
    $stmt = $pdo->prepare("
        INSERT INTO appointments 
        (account_id, invoice_number, service_type, appointment_date, appointment_time, 
         status, total_amount, notes, warranty_id, referrer_id, reference_code, is_referral) 
        VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $account_id,
        $invoice_number,
        $service_type,
        $appointment_date,
        $appointment_time,
        $total_price,
        $notes,
        $warranty_id > 0 ? $warranty_id : null,
        $referrer_id,
        $reference_code,
        $is_referral
    ]);
    
    $appointment_id = $pdo->lastInsertId();
    
    // If referral appointment, create commission record
    if ($is_referral && $referrer_id && $total_price > 0) {
        $commission_rate = 10.00; // 10%
        $commission_amount = $total_price * ($commission_rate / 100);
        
        $commissionStmt = $pdo->prepare("
            INSERT INTO referral_commissions 
            (appointment_id, referrer_id, customer_id, invoice_number, 
             appointment_amount, commission_rate, commission_amount, commission_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        
        $commissionStmt->execute([
            $appointment_id,
            $referrer_id,
            $account_id,
            $invoice_number,
            $total_price,
            $commission_rate,
            $commission_amount
        ]);
        
        // Update referrer's total referrals count
        $updateReferrerStmt = $pdo->prepare("
            UPDATE account 
            SET total_referrals = total_referrals + 1 
            WHERE account_id = ?
        ");
        $updateReferrerStmt->execute([$referrer_id]);
        
        // Create notification for referrer (penjual)
        $notifStmt = $pdo->prepare("
            INSERT INTO notifications (account_id, notification_type, title, message, link_url, related_id)
            VALUES (?, 'referral_appointment', ?, ?, ?, ?)
        ");
        $notifStmt->execute([
            $referrer_id,
            'New Referral Appointment!',
            'Customer used your reference code ' . strtoupper($reference_code) . '. Potential commission: RM ' . number_format($commission_amount, 2),
            '../penjual/appointments.php',
            $appointment_id
        ]);
    }
    
    // If warranty appointment, update warranty_claims
    if ($warranty_id > 0) {
        $updateWarranty = $pdo->prepare("
            UPDATE warranty_claims 
            SET appointment_id = ?, 
                claim_status = 'scheduled',
                updated_at = NOW()
            WHERE warranty_id = ?
        ");
        $updateWarranty->execute([$appointment_id, $warranty_id]);
        
        // Create notification for customer
        $notifStmt = $pdo->prepare("
            INSERT INTO notifications (account_id, notification_type, title, message, link_url, related_id)
            VALUES (?, 'appointment_created', ?, ?, ?, ?)
        ");
        $notifStmt->execute([
            $account_id,
            'Repair Appointment Created! ',
            'Your repair appointment has been scheduled for ' . date('d M Y', strtotime($appointment_date)) . ' at ' . $appointment_time . '. Waiting for admin approval.',
            'appointment_summary.php?id=' . $appointment_id,
            $appointment_id
        ]);
    } else {
        // Insert items for Build PC OR Other Service
        $build_items = json_decode($_POST['build_items'], true);
        
        $stmt = $pdo->prepare("
            INSERT INTO appointment_items 
            (appointment_id, part_id, part_code, part_name, category, quantity, unit_price, total_price, 
             warranty_start_date, warranty_end_date, warranty_period_months)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($build_items as $item) {
            $quantity = intval($item['quantity'] ?? 1);
            $unit_price = floatval($item['price']);
            $item_total = $unit_price * $quantity;
            
            $part_code = isset($item['partCode']) ? $item['partCode'] : 'N/A';
            $part_id = isset($item['partId']) && $item['partId'] !== null ? intval($item['partId']) : null;
            
            // For Build PC: try to get part_code from inventory
            if ($service_type === 'Build PC' && $part_code === 'N/A' && $part_id !== null) {
                $partStmt = $pdo->prepare("SELECT part_code FROM inventory WHERE part_id = ?");
                $partStmt->execute([$part_id]);
                $partData = $partStmt->fetch(PDO::FETCH_ASSOC);
                $part_code = $partData['part_code'] ?? 'N/A';
            }
            
            // Calculate warranty dates (only for Build PC)
            $warranty_start = null;
            $warranty_end = null;
            $warranty_period = null;
            
            if ($service_type === 'Build PC') {
                $warranty_start = date('Y-m-d');
                $warranty_end = date('Y-m-d', strtotime('+1 year'));
                $warranty_period = 12;
            }
            
            $stmt->execute([
                $appointment_id,
                $part_id,
                $part_code,
                $item['name'],
                $item['category'],
                $quantity,
                $unit_price,
                $item_total,
                $warranty_start,
                $warranty_end,
                $warranty_period
            ]);
        }
        
        // Create notification for customer
        $notifStmt = $pdo->prepare("
            INSERT INTO notifications (account_id, notification_type, title, message, link_url, related_id)
            VALUES (?, 'appointment_created', ?, ?, ?, ?)
        ");
        
        $notificationMessage = 'Your ' . $service_type . ' appointment has been scheduled for ' . date('d M Y', strtotime($appointment_date)) . ' at ' . $appointment_time . '. Waiting for approval.';
        
        if ($reference_code) {
            $notificationMessage .= ' (Reference code applied: ' . strtoupper($reference_code) . ')';
        }
        
        $notifStmt->execute([
            $account_id,
            'Appointment Created! ',
            $notificationMessage,
            'appointment_summary.php?id=' . $appointment_id,
            $appointment_id
        ]);
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'invoice_number' => $invoice_number,
        'appointment_id' => $appointment_id,
        'total_amount' => $total_price,
        'is_referral' => $is_referral,
        'reference_code' => $reference_code ? strtoupper($reference_code) : null
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>