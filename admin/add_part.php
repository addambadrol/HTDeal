<?php
// add_part.php
require_once '../db_config.php';

$category = isset($_GET['category']) ? $_GET['category'] : '';

// Function to generate part code
function generatePartCode($pdo, $category) {
    // Category prefix mapping
    $prefixes = [
        'Monitor' => 'MON',
        'Casing' => 'CAS',
        'CPU' => 'CPU',
        'GPU' => 'GPU',
        'Cooler' => 'COO',
        'Ram' => 'RAM',
        'Storage' => 'SSD',
        'Power Supply' => 'PSU',
        'Motherboard' => 'MOB'
    ];
    
    // Starting numbers for specific categories (optional override)
    $startingNumbers = [
        'Monitor' => 9,
    ];
    
    // Get prefix for category
    $prefix = isset($prefixes[$category]) ? $prefixes[$category] : 'XXX';
    
    try {
        // Get last part code for this category
        $stmt = $pdo->prepare("SELECT part_code FROM inventory WHERE category = ? ORDER BY part_code DESC LIMIT 1");
        $stmt->execute([$category]);
        $lastPart = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($lastPart) {
            // Extract number from last part code (e.g., MON-008 -> 8)
            $lastCode = $lastPart['part_code'];
            $parts = explode('-', $lastCode);
            $lastNumber = isset($parts[1]) ? intval($parts[1]) : 0;
            $newNumber = $lastNumber + 1;
        } else {
            // No existing parts, use starting number or default to 1
            $newNumber = isset($startingNumbers[$category]) ? $startingNumbers[$category] : 1;
        }
        
        // Format: PREFIX-XXX (3 digits with leading zeros)
        $newPartCode = $prefix . '-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        
        return $newPartCode;
        
    } catch(PDOException $e) {
        return $prefix . '-001'; // Default if error
    }
}
 
// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'];
    $part_name = $_POST['part_name'];
    $stock = $_POST['stock'];
    $purchase_price = $_POST['purchase_price'];
    $selling_price = $_POST['selling_price'];
    
    // Auto-generate part code
    $part_code = generatePartCode($pdo, $category);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO inventory (part_code, category, part_name, stock, purchase_price, selling_price) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$part_code, $category, $part_name, $stock, $purchase_price, $selling_price]);
        
        $_SESSION['success'] = "Part added successfully with code: " . $part_code;
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

// Preview part code when category selected (for display only)
$previewCode = '';
if (!empty($category)) {
    $previewCode = generatePartCode($pdo, $category);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - Build & Services</title>
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
  
  /* Part Code Preview */
  .part-code-preview {
    background: rgba(110, 34, 221, 0.15);
    border: 2px dashed #6e22dd;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 25px;
    text-align: center;
  }
  
  .part-code-preview .label {
    font-size: 12px;
    color: #aaa;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 1px;
  }
  
  .part-code-preview .code {
    font-size: 24px;
    font-weight: 800;
    color: #8b4dff;
    font-family: 'Courier New', monospace;
    letter-spacing: 2px;
  }
  
  .part-code-preview .code.placeholder {
    color: #555;
    font-style: italic;
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
  <?php include 'header.php'; ?>

  <div class="container">
    <div class="form-card">
      <div class="form-header">
        <h1>Add New Part</h1>
        <p>Fill in the details below to add a new part to inventory</p>
      </div>
      
      <?php if(isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
      <?php endif; ?>
      
      <form method="POST" action="" id="addPartForm">
        <div class="form-group">
          <label>Category <span>*</span></label>
          <select name="category" id="categorySelect" required>
            <option value="">Select Category</option>
            <?php foreach($categories as $key => $value): ?>
              <option value="<?php echo $key; ?>" <?php echo $category === $key ? 'selected' : ''; ?>>
                <?php echo $value; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <!-- Part Code Preview -->
        <div class="part-code-preview">
          <div class="label">Auto-Generated Part Code</div>
          <div class="code <?php echo empty($previewCode) ? 'placeholder' : ''; ?>" id="partCodePreview">
            <?php echo !empty($previewCode) ? $previewCode : 'Select category first'; ?>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label>Stock Quantity <span>*</span></label>
            <input type="number" name="stock" placeholder="0" min="0" required />
            <div class="form-hint">Current stock available</div>
          </div>
          
          <div class="form-group">
            <label>Part Name <span>*</span></label>
            <input type="text" name="part_name" placeholder="Enter part name" required />
            <div class="form-hint">Full descriptive name of the part</div>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label>Purchase Price (RM) <span>*</span></label>
            <input type="number" name="purchase_price" placeholder="0.00" step="0.01" min="0" required />
            <div class="form-hint">Cost price from supplier</div>
          </div>
          
          <div class="form-group">
            <label>Selling Price (RM) <span>*</span></label>
            <input type="number" name="selling_price" placeholder="0.00" step="0.01" min="0" required />
            <div class="form-hint">Selling price to customer</div>
          </div>
        </div>
        
        <div class="btn-group">
          <button type="button" class="btn btn-secondary" onclick="window.location.href='inventory.php'">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Part</button>
        </div>
      </form>
    </div>
  </div>
  
  <script>
    // Update part code preview when category changes
    document.getElementById('categorySelect').addEventListener('change', function() {
      const category = this.value;
      const previewElement = document.getElementById('partCodePreview');
      
      if (category) {
        // Reload page with category parameter to get new preview
        window.location.href = 'add_part.php?category=' + encodeURIComponent(category);
      } else {
        previewElement.textContent = 'Select category first';
        previewElement.classList.add('placeholder');
      }
    });
  </script>
  
  <?php include 'footer.php'; ?>
</body>
</html>