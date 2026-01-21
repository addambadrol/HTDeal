<?php
// add_promo.php
session_start();
require_once '../db_config.php';

// Check if user is logged in as admin
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../landing/loginadmin.php");
    exit();
}

// Fetch all inventory items that don't have active promos
try {
    $stmt = $pdo->query("
        SELECT * 
        FROM inventory 
        WHERE is_promo = 0 OR is_promo IS NULL
        ORDER BY category, part_name
    ");
    $inventory_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching inventory: " . $e->getMessage();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $part_id = $_POST['part_id'];
    $discount_type = $_POST['discount_type'];
    $discount_value = $_POST['discount_value'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    // Validation
    if($end_date < $start_date) {
        $error = "End date cannot be earlier than start date!";
    } else {
        try {
            // Get part details
            $stmt = $pdo->prepare("SELECT selling_price FROM inventory WHERE part_id = ?");
            $stmt->execute([$part_id]);
            $part = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Calculate promo price
            if($discount_type == 'percentage') {
                $promo_price = $part['selling_price'] - ($part['selling_price'] * $discount_value / 100);
            } else {
                $promo_price = $part['selling_price'] - $discount_value;
            }
            
            // Update inventory table with promo details
            $stmt = $pdo->prepare("
                UPDATE inventory 
                SET is_promo = 1, 
                    promo_price = ?, 
                    promo_start_date = ?, 
                    promo_end_date = ?
                WHERE part_id = ?
            ");
            $stmt->execute([$promo_price, $start_date, $end_date, $part_id]);
            
            $_SESSION['success'] = "Promo added successfully!";
            header("Location: promo.php");
            exit();
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - Add Promo</title>
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
    max-width: 900px;
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
  
  .part-preview {
    background-color: rgba(110, 34, 221, 0.1);
    border: 1px solid #6e22dd;
    border-radius: 8px;
    padding: 15px;
    margin-top: 10px;
    display: none;
  }
  
  .part-preview.show {
    display: block;
  }
  
  .part-preview-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 14px;
  }
  
  .part-preview-label {
    color: #aaa;
  }
  
  .part-preview-value {
    color: #fff;
    font-weight: 600;
  }
  
  .price-preview {
    background-color: rgba(139, 77, 255, 0.15);
    border: 1px solid #8b4dff;
    border-radius: 8px;
    padding: 15px;
    margin-top: 15px;
    display: none;
  }
  
  .price-preview.show {
    display: block;
  }
  
  .price-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
  }
  
  .price-label {
    color: #aaa;
    font-size: 14px;
  }
  
  .price-value {
    font-size: 18px;
    font-weight: 700;
  }
  
  .original-price {
    color: #888;
    text-decoration: line-through;
  }
  
  .promo-price {
    color: #22c55e;
  }
  
  .savings {
    color: #fbbf24;
    font-size: 14px;
    text-align: right;
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
    background-color: rgba(239, 68, 68, 0.1);
    border: 1px solid #ef4444;
    color: #ef4444;
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
  <?php include 'header.php'; ?>

  <div class="container">
    <div class="form-card">
      <div class="form-header">
        <h1>Add New Promo</h1>
        <p>Create promotional offers for your inventory items</p>
      </div>
      
      <?php if(isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
      <?php endif; ?>
      
      <form method="POST" action="" id="promoForm">
        <div class="form-group">
          <label>Select Product <span>*</span></label>
          <select name="part_id" id="partSelect" required>
            <option value="">Choose a product</option>
            <?php 
            $current_category = '';
            foreach($inventory_items as $item): 
              if($current_category != $item['category']) {
                if($current_category != '') echo '</optgroup>';
                echo '<optgroup label="' . htmlspecialchars($item['category']) . '">';
                $current_category = $item['category'];
              }
            ?>
              <option value="<?php echo $item['part_id']; ?>" 
                      data-code="<?php echo htmlspecialchars($item['part_code']); ?>"
                      data-name="<?php echo htmlspecialchars($item['part_name']); ?>"
                      data-category="<?php echo htmlspecialchars($item['category']); ?>"
                      data-price="<?php echo $item['selling_price']; ?>"
                      data-stock="<?php echo $item['stock']; ?>">
                <?php echo htmlspecialchars($item['part_code']) . ' - ' . htmlspecialchars($item['part_name']) . ' (Stock: ' . $item['stock'] . ')'; ?>
              </option>
            <?php endforeach; ?>
            <?php if($current_category != '') echo '</optgroup>'; ?>
          </select>
          <div class="form-hint">Select the product you want to add promo</div>
        </div>
        
        <div class="part-preview" id="partPreview">
          <div class="part-preview-row">
            <span class="part-preview-label">Part Code:</span>
            <span class="part-preview-value" id="previewCode">-</span>
          </div>
          <div class="part-preview-row">
            <span class="part-preview-label">Part Name:</span>
            <span class="part-preview-value" id="previewName">-</span>
          </div>
          <div class="part-preview-row">
            <span class="part-preview-label">Category:</span>
            <span class="part-preview-value" id="previewCategory">-</span>
          </div>
          <div class="part-preview-row">
            <span class="part-preview-label">Original Price:</span>
            <span class="part-preview-value" id="previewPrice">RM 0.00</span>
          </div>
          <div class="part-preview-row">
            <span class="part-preview-label">Stock Available:</span>
            <span class="part-preview-value" id="previewStock">0</span>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label>Discount Type <span>*</span></label>
            <select name="discount_type" id="discountType" required>
              <option value="">Select Type</option>
              <option value="percentage">Percentage (%)</option>
              <option value="fixed">Fixed Amount (RM)</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>Discount Value <span>*</span></label>
            <input type="number" name="discount_value" id="discountValue" placeholder="0" step="0.01" min="0" required />
            <div class="form-hint" id="discountHint">Enter discount amount</div>
          </div>
        </div>
        
        <div class="price-preview" id="pricePreview">
          <div class="price-row">
            <span class="price-label">Original Price:</span>
            <span class="price-value original-price" id="calcOriginalPrice">RM 0.00</span>
          </div>
          <div class="price-row">
            <span class="price-label">Promo Price:</span>
            <span class="price-value promo-price" id="calcPromoPrice">RM 0.00</span>
          </div>
          <div class="savings" id="calcSavings">You save: RM 0.00</div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label>Start Date <span>*</span></label>
            <input type="date" name="start_date" id="startDate" required />
            <div class="form-hint">When the promo begins</div>
          </div>
          
          <div class="form-group">
            <label>End Date <span>*</span></label>
            <input type="date" name="end_date" id="endDate" required />
            <div class="form-hint">When the promo ends</div>
          </div>
        </div>
        
        <div class="btn-group">
          <button type="button" class="btn btn-secondary" onclick="window.location.href='promo.php'">Cancel</button>
          <button type="submit" class="btn btn-primary">Create Promo</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('startDate').min = today;
    document.getElementById('endDate').min = today;
    
    document.getElementById('startDate').addEventListener('change', function() {
      document.getElementById('endDate').min = this.value;
    });
    
    const partSelect = document.getElementById('partSelect');
    const partPreview = document.getElementById('partPreview');
    
    partSelect.addEventListener('change', function() {
      const selectedOption = this.options[this.selectedIndex];
      
      if(this.value) {
        document.getElementById('previewCode').textContent = selectedOption.dataset.code;
        document.getElementById('previewName').textContent = selectedOption.dataset.name;
        document.getElementById('previewCategory').textContent = selectedOption.dataset.category;
        document.getElementById('previewPrice').textContent = 'RM ' + parseFloat(selectedOption.dataset.price).toFixed(2);
        document.getElementById('previewStock').textContent = selectedOption.dataset.stock;
        partPreview.classList.add('show');
        calculatePromoPrice();
      } else {
        partPreview.classList.remove('show');
        document.getElementById('pricePreview').classList.remove('show');
      }
    });
    
    const discountType = document.getElementById('discountType');
    const discountHint = document.getElementById('discountHint');
    
    discountType.addEventListener('change', function() {
      if(this.value === 'percentage') {
        discountHint.textContent = 'Enter percentage (e.g., 10 for 10%)';
      } else if(this.value === 'fixed') {
        discountHint.textContent = 'Enter fixed amount in RM';
      }
      calculatePromoPrice();
    });
    
    const discountValue = document.getElementById('discountValue');
    
    discountValue.addEventListener('input', calculatePromoPrice);
    
    function calculatePromoPrice() {
      const selectedOption = partSelect.options[partSelect.selectedIndex];
      const originalPrice = parseFloat(selectedOption.dataset.price) || 0;
      const type = discountType.value;
      const discount = parseFloat(discountValue.value) || 0;
      
      if(partSelect.value && type && discount > 0) {
        let promoPrice = 0;
        let savings = 0;
        
        if(type === 'percentage') {
          if(discount > 100) {
            alert('Percentage cannot exceed 100%');
            discountValue.value = 100;
            return;
          }
          savings = originalPrice * (discount / 100);
          promoPrice = originalPrice - savings;
        } else {
          if(discount >= originalPrice) {
            alert('Fixed discount cannot be equal or greater than original price');
            discountValue.value = (originalPrice - 0.01).toFixed(2);
            return;
          }
          savings = discount;
          promoPrice = originalPrice - savings;
        }
        
        document.getElementById('calcOriginalPrice').textContent = 'RM ' + originalPrice.toFixed(2);
        document.getElementById('calcPromoPrice').textContent = 'RM ' + promoPrice.toFixed(2);
        document.getElementById('calcSavings').textContent = 'You save: RM ' + savings.toFixed(2);
        document.getElementById('pricePreview').classList.add('show');
      } else {
        document.getElementById('pricePreview').classList.remove('show');
      }
    }
    
    document.getElementById('promoForm').addEventListener('submit', function(e) {
      const startDate = document.getElementById('startDate').value;
      const endDate = document.getElementById('endDate').value;
      
      if(endDate < startDate) {
        e.preventDefault();
        alert('End date cannot be earlier than start date!');
      }
    });
  </script>
  <?php include 'footer.php'; ?>
</body>
</html>