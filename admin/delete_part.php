<?php
// delete_part.php
session_start();
require_once '../db_config.php';

// Check if user is logged in as admin
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../landing/loginadmin.php");
    exit();
}

// Get part ID from URL
$part_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$part_id) {
    $_SESSION['error'] = "Invalid part ID";
    header("Location: inventory.php");
    exit();
}

// Fetch part details for confirmation
try {
    $stmt = $pdo->prepare("SELECT * FROM inventory WHERE part_id = ?");
    $stmt->execute([$part_id]);
    $part = $stmt->fetch();
    
    if (!$part) {
        $_SESSION['error'] = "Part not found";
        header("Location: inventory.php");
        exit();
    }
} catch(PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: inventory.php");
    exit();
}

// Process deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_delete'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM inventory WHERE part_id = ?");
            $stmt->execute([$part_id]);
            
            $_SESSION['success'] = "Part deleted successfully!";
            header("Location: inventory.php");
            exit();
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        // Cancel button clicked
        header("Location: inventory.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTDeal - Delete Part</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #121212;
            color: #fff;
            min-height: 100vh;
        }

        /* Header Styles */
        header {
            background-color: #6e22dd;
            padding: 10px 20px;
        }

        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo img {
            height: 35px;
        }

        .nav-links {
            display: flex;
            gap: 25px;
        }

        .nav-links a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #ccc;
        }

        .profile-icon img {
            width: 35px;
            height: 35px;
            cursor: pointer;
        }

        /* Main Container */
        .container {
            max-width: 600px;
            margin: 80px auto;
            padding: 0 20px;
        }

        /* Back Link */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #8b4dff;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 25px;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: #6e22dd;
            transform: translateX(-5px);
        }

        .back-link::before {
            content: "←";
            font-size: 20px;
        }

        /* Delete Card */
        .delete-card {
            background: linear-gradient(145deg, #1a1a2e 0%, #16162a 100%);
            border: 2px solid #ff4444;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(255, 68, 68, 0.3);
        }

        .delete-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .warning-icon {
            font-size: 64px;
            margin-bottom: 15px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        .delete-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #ff4444;
            margin-bottom: 8px;
        }

        .delete-header p {
            color: #aaa;
            font-size: 14px;
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
        }

        .alert-error {
            background-color: rgba(255, 68, 68, 0.1);
            border: 1px solid #ff4444;
            color: #ff6666;
        }

        /* Part Details */
        .part-details {
            background-color: #0a0a0a;
            border: 2px solid #333;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .part-details h3 {
            color: #ddd;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #222;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #888;
            font-weight: 600;
        }

        .detail-value {
            color: #fff;
            font-weight: 500;
        }

        /* Warning Message */
        .warning-message {
            background-color: rgba(255, 68, 68, 0.1);
            border: 1px solid #ff4444;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 30px;
            text-align: center;
        }

        .warning-message p {
            color: #ff6666;
            font-weight: 600;
            font-size: 14px;
            margin: 0;
        }

        /* Buttons */
        .btn-group {
            display: flex;
            gap: 15px;
        }

        .btn {
            flex: 1;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.4);
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.5);
        }

        .btn-secondary {
            background-color: #333;
            color: #fff;
        }

        .btn-secondary:hover {
            background-color: #444;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .delete-card {
                padding: 25px;
            }

            .detail-row {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include 'header.php'; ?>

    <!-- Main Content -->
    <div class="container">
        <a href="inventory.php" class="back-link">Back to Inventory</a>

        <div class="delete-card">
            <div class="delete-header">
                <div class="warning-icon">⚠️</div>
                <h1>Delete Part</h1>
                <p>Are you sure you want to delete this part?</p>
            </div>

            <?php if(isset($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="part-details">
                <h3>Part Information</h3>
                <div class="detail-row">
                    <span class="detail-label">Part Code:</span>
                    <span class="detail-value"><?= htmlspecialchars($part['part_code']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Category:</span>
                    <span class="detail-value"><?= htmlspecialchars($part['category']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Part Name:</span>
                    <span class="detail-value"><?= htmlspecialchars($part['part_name']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Stock:</span>
                    <span class="detail-value"><?= $part['stock'] ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Purchase Price:</span>
                    <span class="detail-value">RM <?= number_format($part['purchase_price'], 2) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Selling Price:</span>
                    <span class="detail-value">RM <?= number_format($part['selling_price'], 2) ?></span>
                </div>
            </div>

            <div class="warning-message">
                <p>⚠️ This action cannot be undone. The part will be permanently removed from inventory.</p>
            </div>

            <form method="POST" action="">
                <div class="btn-group">
                    <button type="submit" name="cancel" class="btn btn-secondary">Cancel</button>
                    <button type="submit" name="confirm_delete" class="btn btn-danger">Yes, Delete Part</button>
                </div>
            </form>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>