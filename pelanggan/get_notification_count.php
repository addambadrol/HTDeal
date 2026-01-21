<?php
session_start();
require_once '../db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['account_id'])) {
    echo json_encode(['count' => 0]);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM notifications
        WHERE account_id = ? AND is_read = FALSE
    ");
    $stmt->execute([$_SESSION['account_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode(['count' => $result['count']]);
} catch (PDOException $e) {
    echo json_encode(['count' => 0]);
}
?>