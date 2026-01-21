<?php
session_start();
require_once '../db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: loginpelanggan.php");
    exit();
}

$token = trim($_POST['token']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Validate passwords match
if ($password !== $confirm_password) {
    $_SESSION['reset_error'] = "Passwords do not match!";
    header("Location: reset_password.php?token=" . urlencode($token));
    exit();
}

// Validate password strength
if (strlen($password) < 8) {
    $_SESSION['reset_error'] = "Password must be at least 8 characters long!";
    header("Location: reset_password.php?token=" . urlencode($token));
    exit();
}

try {
    // Verify token is still valid
    $stmt = $pdo->prepare("
        SELECT pr.account_id, pr.email 
        FROM password_resets pr
        WHERE pr.token = ? AND pr.expires_at > NOW()
    ");
    $stmt->execute([$token]);
    $reset_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reset_data) {
        $_SESSION['reset_error'] = "This reset link has expired or is invalid. Please request a new one.";
        header("Location: forgot_password.php");
        exit();
    }
    
    // Hash new password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Update password in database
    $updateStmt = $pdo->prepare("UPDATE account SET password = ?, updated_at = NOW() WHERE account_id = ?");
    $updateStmt->execute([$hashed_password, $reset_data['account_id']]);
    
    // Delete used token
    $deleteStmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
    $deleteStmt->execute([$token]);
    
    // Set success message
    $_SESSION['login_success'] = "Password has been reset successfully! You can now login with your new password.";
    
    header("Location: loginpelanggan.php");
    exit();
    
} catch (PDOException $e) {
    $_SESSION['reset_error'] = "An error occurred. Please try again.";
    error_log("Password Reset Error: " . $e->getMessage());
    header("Location: reset_password.php?token=" . urlencode($token));
    exit();
}
?>