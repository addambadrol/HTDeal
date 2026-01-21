<?php
session_start();
require_once '../db_config.php';
require_once '../email_config.php';

// PHPMailer path - adjust sesuai struktur folder
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: forgot_password.php");
    exit();
}

$email = trim($_POST['email']);

try {
    // Check if email exists for pelanggan
    $stmt = $pdo->prepare("SELECT account_id, first_name, last_name, email FROM account WHERE email = ? AND role = 'pelanggan' AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $_SESSION['reset_message'] = "If this email exists in our system, you will receive a reset link shortly.";
        $_SESSION['reset_message_type'] = "success";
        header("Location: forgot_password.php");
        exit();
    }
    
    // Generate unique token
    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Delete old tokens for this user
    $deleteStmt = $pdo->prepare("DELETE FROM password_resets WHERE account_id = ?");
    $deleteStmt->execute([$user['account_id']]);
    
    // Insert new token
    $insertStmt = $pdo->prepare("INSERT INTO password_resets (account_id, email, token, expires_at) VALUES (?, ?, ?, ?)");
    $insertStmt->execute([$user['account_id'], $email, $token, $expires_at]);
    
    // Prepare reset link
    $reset_link = SITE_URL . "/landing/reset_password.php?token=" . $token;
    
    // Send email using PHPMailer
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email, $user['first_name'] . ' ' . $user['last_name']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Reset Your HTDeal Password';
        $mail->Body    = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    background-color: #f4f4f4; 
                    margin: 0;
                    padding: 0;
                }
                .email-wrapper {
                    background-color: #f4f4f4;
                    padding: 40px 20px;
                }
                .container { 
                    background: #ffffff; 
                    max-width: 600px; 
                    margin: 0 auto; 
                    padding: 40px; 
                    border-radius: 10px; 
                    box-shadow: 0 4px 20px rgba(0,0,0,0.1); 
                }
                h2 { 
                    color: #6610f2; 
                    margin-top: 0;
                    font-size: 28px;
                }
                p {
                    color: #333;
                    line-height: 1.6;
                    font-size: 16px;
                }
                .button-container {
                    text-align: center;
                    margin: 30px 0;
                }
                .btn { 
                    display: inline-block; 
                    background-color: #6610f2 !important; 
                    color: #ffffff !important; 
                    padding: 15px 40px; 
                    text-decoration: none; 
                    border-radius: 8px; 
                    font-weight: bold; 
                    font-size: 16px;
                }
                .btn:hover {
                    background-color: #520dc1 !important;
                }
                .link-text {
                    font-size: 14px;
                    color: #666;
                    word-break: break-all;
                }
                .link-url {
                    color: #6610f2;
                    text-decoration: none;
                }
                .footer { 
                    margin-top: 40px; 
                    padding-top: 20px; 
                    border-top: 2px solid #eeeeee; 
                    font-size: 14px; 
                    color: #888; 
                }
                .footer strong {
                    color: #6610f2;
                }
                .warning {
                    background-color: #fff3cd;
                    border-left: 4px solid #ffc107;
                    padding: 15px;
                    margin: 20px 0;
                    font-size: 14px;
                    color: #856404;
                }
            </style>
        </head>
        <body>
            <div class='email-wrapper'>
                <div class='container'>
                    <h2>Reset Your Password</h2>
                    <p>Hello <strong>{$user['first_name']}</strong>,</p>
                    <p>We received a request to reset your password for your HTDeal account.</p>
                    <p>Click the button below to create a new password:</p>
                    
                    <div class='button-container'>
                        <a href='{$reset_link}' class='btn' style='background-color: #6610f2; color: #ffffff;'>Reset Password</a>
                    </div>
                    
                    <p class='link-text'><small>Or copy and paste this link into your browser:</small><br>
                    <a href='{$reset_link}' class='link-url'>{$reset_link}</a></p>
                    
                    <div class='warning'>
                        <strong>This link will expire in 1 hour.</strong>
                    </div>
                    
                    <p>If you didn't request this password reset, please ignore this email. Your password will remain unchanged.</p>
                    
                    <div class='footer'>
                        <p>Best regards,<br><strong>HTDeal Team</strong></p>
                        <p style='font-size: 12px; color: #999;'>This is an automated email. Please do not reply to this message.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->AltBody = "Hello {$user['first_name']},\n\nWe received a request to reset your password.\n\nClick this link to reset your password:\n{$reset_link}\n\nThis link will expire in 1 hour.\n\nIf you didn't request this, please ignore this email.\n\nBest regards,\nHTDeal Team";
        
        $mail->send();
        
        $_SESSION['reset_message'] = "Password reset link has been sent to your email. Please check your inbox.";
        $_SESSION['reset_message_type'] = "success";
        
    } catch (Exception $e) {
        $_SESSION['reset_message'] = "Failed to send email. Please try again later.";
        $_SESSION['reset_message_type'] = "error";
        error_log("Mailer Error: " . $mail->ErrorInfo);
    }
    
} catch (PDOException $e) {
    $_SESSION['reset_message'] = "An error occurred. Please try again.";
    $_SESSION['reset_message_type'] = "error";
    error_log("Database Error: " . $e->getMessage());
}

header("Location: forgot_password.php");
exit();
?>