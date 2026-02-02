<?php
// edit_part.php
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

// Fetch part details
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

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $part_code = $_POST['part_code'];
    $category = $_POST['category'];
    $part_name = $_POST['part_name'];
    $stock = $_POST['stock'];
    $purchase_price = $_POST['purchase_price'];
    $selling_price = $_POST['selling_price'];
    
    try {
        $stmt = $pdo->prepare("UPDATE inventory SET part_code = ?, category = ?, part_name = ?, stock = ?, purchase_price = ?, selling_price = ? WHERE part_id = ?");
        $stmt->execute([$part_code, $category, $part_name, $stock, $purchase_price, $selling_price, $part_id]);
        
        $_SESSION['success'] = "Part updated successfully!";
        header("Location: inventory.php");
        exit();
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Category options
$categories = [
    'Monitor' => 'Monitor',
    'Casing' => 'Casing',
    'CPU' => 'CPU',
    'GPU' => 'GPU',
    'Cooler' => 'Cooler',
    'Ram' => 'Ram',
    'Storage' => 'Storage',
    'Power Supply' => 'Power Supply',
    'Motherboard' => 'Motherboard'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTDeal - Edit Part</title>
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
            max-width: 700px;
            margin: 50px auto;
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

        /* Form Card */
        .form-card {
            background: linear-gradient(145deg, #1a1a2e 0%, #16162a 100%);
            border: 2px solid #6e22dd;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(110, 34, 221, 0.3);
        }

        .form-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .form-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #8b4dff 0%, #6e22dd 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-header p {
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

        /* Form Elements */
        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #ddd;
            font-size: 14px;
        }

        .form-group label span {
            color: #ff4444;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            background-color: #0a0a0a;
            border: 2px solid #333;
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #6e22dd;
            box-shadow: 0 0 0 3px rgba(110, 34, 221, 0.2);
        }

        .form-group input::placeholder {
            color: #666;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-hint {
            font-size: 12px;
            color: #888;
            margin-top: 5px;
        }

        /* Buttons */
        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 35px;
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

        .btn-primary {
            background: linear-gradient(135deg, #6e22dd 0%, #8b4dff 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(110, 34, 221, 0.4);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5a1bb8 0%, #7a3ee6 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(110, 34, 221, 0.5);
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
            .form-row {
                grid-template-columns: 1fr;
            }

            .form-card {
                padding: 25px;
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

        <div class="form-card">
            <div class="form-header">
                <h1>Edit Part</h1>
                <p>Update the details below to modify the part information</p>
            </div>

            <?php if(isset($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Category <span>*</span></label>
                    <select name="category" required>
                        <option value="">Select Category</option>
                        <?php foreach($categories as $key => $value): ?>
                            <option value="<?= $key ?>" <?= $part['category'] === $key ? 'selected' : '' ?>>
                                <?= $value ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Part Code <span>*</span></label>
                        <input type="text" name="part_code" value="<?= htmlspecialchars($part['part_code']) ?>" placeholder="e.g., MON001" required>
                        <div class="form-hint">Unique identifier for the part</div>
                    </div>

                    <div class="form-group">
                        <label>Stock Quantity <span>*</span></label>
                        <input type="number" name="stock" value="<?= $part['stock'] ?>" placeholder="0" min="0" required>
                        <div class="form-hint">Current stock available</div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Part Name <span>*</span></label>
                    <input type="text" name="part_name" value="<?= htmlspecialchars($part['part_name']) ?>" placeholder="Enter part name" required>
                    <div class="form-hint">Full descriptive name of the part</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Purchase Price (RM) <span>*</span></label>
                        <input type="number" name="purchase_price" value="<?= number_format($part['purchase_price'], 2, '.', '') ?>" placeholder="0.00" step="0.01" min="0" required>
                        <div class="form-hint">Cost price from supplier</div>
                    </div>

                    <div class="form-group">
                        <label>Selling Price (RM) <span>*</span></label>
                        <input type="number" name="selling_price" value="<?= number_format($part['selling_price'], 2, '.', '') ?>" placeholder="0.00" step="0.01" min="0" required>
                        <div class="form-hint">Selling price to customer</div>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='inventory.php'">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Part</button>
                </div>
            </form>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>