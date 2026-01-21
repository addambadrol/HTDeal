<?php
// edit_part.php
require_once '../db_config.php';

// Get part ID from URL
$part_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$part_id) {
    header("Location: inventory.php");
    exit();
}

// Fetch part details
try {
    $stmt = $pdo->prepare("SELECT * FROM inventory WHERE part_id = ?");
    $stmt->execute([$part_id]);
    $part = $stmt->fetch();
    
    if (!$part) {
        header("Location: inventory.php");
        exit();
    }
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $part_code = $_POST['part_code'];
    $category = $_POST['category'];
    $part_name = $_POST['part_name'];
    $stock = $_POST['stock'];
    $price = $_POST['price'];
    
    try {
        $stmt = $pdo->prepare("UPDATE inventory SET part_code = ?, category = ?, part_name = ?, stock = ?, price = ? WHERE part_id = ?");
        $stmt->execute([$part_code, $category, $part_name, $stock, $price, $part_id]);
        
        $_SESSION['success'] = "Part updated successfully!";
        header("Location: inventory.php");
        exit();
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Category options
$categories = [
    'MONITOR' => 'Monitor',
    'GAMING BOOSTER PACK' => 'Gaming Booster Pack',
    'CHASSIS' => 'Chassis',
    'MOTHERBOARD' => 'Motherboard',
    'PROCESSOR' => 'Processor',
    'GRAPHICS CARD' => 'Graphics Card',
    'COOLER' => 'Cooler',
    'THERMAL COOLING' => 'Thermal Cooling',
    'RAM' => 'RAM',
    'SSD' => 'SSD',
    'SATA DRIVE' => 'SATA Drive',
    'POWER SUPPLY' => 'Power Supply',
    'WIRELESS ADAPTER' => 'Wireless Adapter',
    'OPERATING SYSTEM' => 'Operating System',
    'ARGB LED' => 'ARGB LED',
    'ACCESSORIES' => 'Accessories',
    'PERIPHERALS' => 'Peripherals',
    'KEYBOARD' => 'Keyboard',
    'WEBCAM' => 'Webcam'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - Edit Part</title>
  <link rel="stylesheet" href="./style.css" />
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
    display: flex;
    flex-direction: column;
  }
  header {
    background-color: #6e22dd;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .navbar {
    display: flex;
    align-items: center;
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
  }
  .logo img {
    height: 35px;
  }
  .nav-links {
    display: flex;
    gap: 25px;
    margin-left: auto;
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
  .profile-icon {
    margin-left: 20px;
  }
  .profile-icon img {
    width: 35px;
    height: 35px;
    cursor: pointer;
  }
  
  .container {
    max-width: 700px;
    margin: 50px auto;
    padding: 0 20px;
  }
  
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
    color: #fff;
    margin-bottom: 8px;
    background: linear-gradient(135deg, #8b4dff 0%, #6e22dd 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }
  
  .form-header p {
    color: #aaa;
    font-size: 14px;
  }
  
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
  
  .form-hint {
    font-size: 12px;
    color: #888;
    margin-top: 5px;
  }
  
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
  <header>
    <div class="navbar">
      <div class="logo">
        <a href="index.php">
          <img src="../picture/logo.png" alt="Logo" class="logo-image" />
        </a>
      </div>
      <div class="nav-links">
        <a href="homepage.php">HOME</a>
        <a href="appointment.php">APPOINTMENT</a>
        <a href="invoices.php">INVOICE</a>
        <a href="review.php">REVIEW</a>
      </div>
      <div class="profile-icon">
        <a href="profile.php">
          <img src="../picture/profileicon.png" alt="Profile" />
        </a>
      </div>
    </div>
  </header>

  <div class="container">
    <a href="inventory.php" class="back-link">Back to Inventory</a>
    
    <div class="form-card">
      <div class="form-header">
        <h1>Edit Part</h1>
        <p>Update the details below to modify the part information</p>
      </div>
      
      <?php if(isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
      <?php endif; ?>
      
      <form method="POST" action="">
        <div class="form-group">
          <label>Category <span>*</span></label>
          <select name="category" required>
            <option value="">Select Category</option>
            <?php foreach($categories as $key => $value): ?>
              <option value="<?php echo $key; ?>" <?php echo $part['category'] === $key ? 'selected' : ''; ?>>
                <?php echo $value; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label>Part Code <span>*</span></label>
            <input type="text" name="part_code" value="<?php echo htmlspecialchars($part['part_code']); ?>" placeholder="e.g., MON001" required />
            <div class="form-hint">Unique identifier for the part</div>
          </div>
          
          <div class="form-group">
            <label>Stock Quantity <span>*</span></label>
            <input type="number" name="stock" value="<?php echo $part['stock']; ?>" placeholder="0" min="0" required />
            <div class="form-hint">Current stock available</div>
          </div>
        </div>
        
        <div class="form-group">
          <label>Part Name <span>*</span></label>
          <input type="text" name="part_name" value="<?php echo htmlspecialchars($part['part_name']); ?>" placeholder="Enter part name" required />
          <div class="form-hint">Full descriptive name of the part</div>
        </div>
        
        <div class="form-group">
          <label>Price (RM) <span>*</span></label>
          <input type="number" name="price" value="<?php echo $part['price']; ?>" placeholder="0.00" step="0.01" min="0" required />
          <div class="form-hint">Price in Malaysian Ringgit</div>
        </div>
        
        <div class="btn-group">
          <button type="button" class="btn btn-secondary" onclick="window.location.href='inventory.php'">Cancel</button>
          <button type="submit" class="btn btn-primary">Update Part</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>