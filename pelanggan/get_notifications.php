<?php
session_start();
require_once '../db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['account_id'])) {
    echo json_encode(['notifications' => []]);
    exit();
}

$account_id = $_SESSION['account_id'];

try {
    $stmt = $pdo->prepare("
        SELECT * FROM notifications
        WHERE account_id = ?
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$account_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'notifications' => []
    ]);
}
?>