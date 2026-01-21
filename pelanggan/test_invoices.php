<?php
session_start();
require_once '../db_config.php';

echo '<html><head><title>Debug Invoices</title><style>
body { font-family: monospace; background: #1a1a1a; color: #fff; padding: 20px; }
.box { background: #252525; border: 2px solid #6e22dd; padding: 20px; margin: 20px 0; border-radius: 10px; }
.success { color: #22c55e; }
.error { color: #ef4444; }
pre { background: #0a0a0a; padding: 15px; border-radius: 5px; overflow: auto; }
</style></head><body>';

echo '<h1>🔍 Invoice Debug Report</h1>';

// Check session
echo '<div class="box">';
echo '<h2>1. Session Check</h2>';
if (isset($_SESSION['account_id'])) {
    echo '<p class="success">✅ Logged in as account_id: ' . $_SESSION['account_id'] . '</p>';
    echo '<p class="success">✅ Role: ' . $_SESSION['role'] . '</p>';
} else {
    echo '<p class="error">❌ NOT LOGGED IN!</p>';
    echo '<p>You need to login first at: <a href="../landing/loginpelanggan.php">Login Page</a></p>';
    exit();
}
echo '</div>';

$account_id = $_SESSION['account_id'];

// Test 1: Simple appointment query
echo '<div class="box">';
echo '<h2>2. Test Basic Appointments Query</h2>';
try {
    $stmt = $pdo->prepare("
        SELECT invoice_number, status, created_at 
        FROM appointments 
        WHERE account_id = ?
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$account_id]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($appointments) > 0) {
        echo '<p class="success">✅ Found ' . count($appointments) . ' appointments</p>';
        echo '<pre>' . print_r($appointments, true) . '</pre>';
    } else {
        echo '<p class="error">⚠️ No appointments found for account_id: ' . $account_id . '</p>';
    }
} catch (PDOException $e) {
    echo '<p class="error">❌ Error: ' . $e->getMessage() . '</p>';
}
echo '</div>';

// Test 2: Check warranty columns
echo '<div class="box">';
echo '<h2>3. Check Warranty Columns in appointment_items</h2>';
try {
    $stmt = $pdo->query("
        SELECT ai.*, a.invoice_number 
        FROM appointment_items ai
        JOIN appointments a ON ai.appointment_id = a.appointment_id
        WHERE a.account_id = $account_id
        LIMIT 1
    ");
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($item) {
        echo '<p class="success">✅ Sample item found</p>';
        
        if (isset($item['warranty_start_date'])) {
            echo '<p class="success">✅ warranty_start_date exists: ' . ($item['warranty_start_date'] ?? 'NULL') . '</p>';
        } else {
            echo '<p class="error">❌ warranty_start_date column NOT FOUND!</p>';
        }
        
        if (isset($item['warranty_end_date'])) {
            echo '<p class="success">✅ warranty_end_date exists: ' . ($item['warranty_end_date'] ?? 'NULL') . '</p>';
        } else {
            echo '<p class="error">❌ warranty_end_date column NOT FOUND!</p>';
        }
        
        echo '<pre>' . print_r($item, true) . '</pre>';
    } else {
        echo '<p class="error">⚠️ No items found</p>';
    }
} catch (PDOException $e) {
    echo '<p class="error">❌ Error: ' . $e->getMessage() . '</p>';
}
echo '</div>';

// Test 3: Test the EXACT query from get_customer_invoices.php
echo '<div class="box">';
echo '<h2>4. Test EXACT Query from get_customer_invoices.php</h2>';
try {
    $stmt = $pdo->prepare("
        SELECT 
            a.invoice_number,
            a.appointment_date,
            a.appointment_time,
            a.service_type,
            a.total_amount,
            a.status,
            a.created_at,
            (SELECT ai.warranty_start_date 
             FROM appointment_items ai 
             WHERE ai.appointment_id = a.appointment_id 
             LIMIT 1) as warranty_start_date,
            (SELECT ai.warranty_end_date 
             FROM appointment_items ai 
             WHERE ai.appointment_id = a.appointment_id 
             LIMIT 1) as warranty_end_date,
            CASE 
                WHEN (SELECT ai.warranty_end_date 
                      FROM appointment_items ai 
                      WHERE ai.appointment_id = a.appointment_id 
                      LIMIT 1) >= CURDATE() 
                THEN 1 
                ELSE 0 
            END as warranty_active,
            DATEDIFF(
                (SELECT ai.warranty_end_date 
                 FROM appointment_items ai 
                 WHERE ai.appointment_id = a.appointment_id 
                 LIMIT 1),
                CURDATE()
            ) as days_remaining,
            (SELECT COUNT(*) 
             FROM warranty_claims wc 
             WHERE wc.invoice_number = a.invoice_number) as has_claim
        FROM appointments a
        WHERE a.account_id = ? 
        AND a.status IN ('approved', 'completed')
        AND a.invoice_number IS NOT NULL
        ORDER BY a.created_at DESC
    ");
    
    $stmt->execute([$account_id]);
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($invoices) > 0) {
        echo '<p class="success">✅ Query SUCCESS! Found ' . count($invoices) . ' invoices</p>';
        echo '<pre>' . print_r($invoices, true) . '</pre>';
    } else {
        echo '<p class="error">⚠️ Query works but NO RESULTS</p>';
        echo '<p>Possible reasons:</p>';
        echo '<ul>';
        echo '<li>No appointments with status "approved" or "completed"</li>';
        echo '<li>All appointments have NULL invoice_number</li>';
        echo '</ul>';
    }
} catch (PDOException $e) {
    echo '<p class="error">❌ QUERY FAILED!</p>';
    echo '<p class="error">Error: ' . $e->getMessage() . '</p>';
    echo '<p>This is the exact error happening in get_customer_invoices.php</p>';
}
echo '</div>';

// Test 4: Check appointment status
echo '<div class="box">';
echo '<h2>5. Check Appointment Status Distribution</h2>';
try {
    $stmt = $pdo->prepare("
        SELECT status, COUNT(*) as count
        FROM appointments
        WHERE account_id = ?
        GROUP BY status
    ");
    $stmt->execute([$account_id]);
    $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($statuses) > 0) {
        echo '<p class="success">✅ Status breakdown:</p>';
        echo '<pre>' . print_r($statuses, true) . '</pre>';
        echo '<p><strong>Note:</strong> Only "approved" and "completed" status will show in warranty page!</p>';
    }
} catch (PDOException $e) {
    echo '<p class="error">❌ Error: ' . $e->getMessage() . '</p>';
}
echo '</div>';

// Test 5: Check if warranty_claims table exists
echo '<div class="box">';
echo '<h2>6. Check warranty_claims Table</h2>';
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM warranty_claims");
    $count = $stmt->fetchColumn();
    echo '<p class="success">✅ warranty_claims table exists!</p>';
    echo '<p>Total claims: ' . $count . '</p>';
} catch (PDOException $e) {
    echo '<p class="error">❌ warranty_claims table error: ' . $e->getMessage() . '</p>';
}
echo '</div>';

echo '<div class="box">';
echo '<h2>✅ Debug Complete</h2>';
echo '<p>Check the results above to find the issue.</p>';
echo '<p><a href="repair_warranty.php">← Back to Warranty Page</a></p>';
echo '</div>';

echo '</body></html>';
?>