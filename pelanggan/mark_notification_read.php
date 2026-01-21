<?php
session_start();
require_once '../db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['account_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$notification_id = $data['notification_id'] ?? 0;

try {
    $stmt = $pdo->prepare("
        UPDATE notifications 
        SET is_read = TRUE 
        WHERE notification_id = ? AND account_id = ?
    ");
    $stmt->execute([$notification_id, $_SESSION['account_id']]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false]);
}
?>