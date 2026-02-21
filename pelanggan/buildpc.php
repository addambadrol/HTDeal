<?php
require_once '../db_config.php';

// Check if user is logged in and is a customer
// if (!isset($_SESSION['account_id']) || $_SESSION['role'] != 'pelanggan') {
//     header("Location: ../landing/loginpelanggan.php");
//     exit();
// }

// Fetch all inventory items grouped by category
try {
    $stmt = $pdo->query("SELECT * FROM inventory ORDER BY category, part_name");
    $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group items by category
    $categorized = [];
    foreach ($inventory as $item) {
        $category = $item['category'];
        if (!isset($categorized[$category])) {
            $categorized[$category] = [];
        }
        $categorized[$category][] = $item;
    }
} catch(PDOException $e) {
    die("Error fetching inventory: " . $e->getMessage());
}

// Category emoji mapping and default categories
$allCategories = [
    'Monitor' => '',
    'Casing' => '',
    'CPU' => '',
    'GPU' => '',
    'Cooler' => '',
    'RAM' => '',
    'Storage' => '',
    'Power Supply' => '',
    'Motherboard' => ''
];

// Merge database categories with all categories
foreach ($allCategories as $cat => $emoji) {
    if (!isset($categorized[$cat])) {
        $categorized[$cat] = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>HTDeal - PC Build</title>
<link rel="stylesheet" href="./style.css" />
<style>
  /* Reset */
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
  
  /* Navbar */
  /* header {
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

  .profile-icon img {
    width: 35px;
    height: 35px;
    cursor: pointer;
    margin-left: 20px;
  } */
  
  /* Page Header */
  /* Page Header */
.page-header {
  max-width: 1290px;
  margin: 50px auto 30px;
  padding: 0 20px;
  text-align: center;
}

.page-title {
  font-size: 48px;
  font-weight: 900;
  background: linear-gradient(135deg, #8b4dff 0%, #6e22dd 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 15px;
  letter-spacing: 1px;
  text-transform: uppercase;
  filter: drop-shadow(0 0 20px rgba(139, 77, 255, 0.3));
}

.page-subtitle {
  font-size: 16px;
  color: #bbb;
  margin-bottom: 20px;
  font-weight: 400;
}

/* Responsive */
@media (max-width: 768px) {
  .page-header {
    margin: 40px auto 20px;
  }
  
  .page-title {
    font-size: 32px;
  }
  
  .page-subtitle {
    font-size: 14px;
  }
}
  
  /* Main Container */
  .container {
    display: flex;
    gap: 20px;
    justify-content: center;
    padding: 0 20px;
    margin-bottom: 40px;
    align-items: flex-start;
  }
  
  /* Configurator */
  .configurator {
  background: #121212;
  border-radius: 8px;
  width: 100%;
  max-width: 1290px;
  min-width: 800px;
  padding: 10px 15px;
  box-sizing: border-box;

  min-height: 1600px; /* adjust ikut tinggi penuh bila semua open */
}
  
  /* Category Details */
  details {
    background: #6e22dd;
    border-radius: 6px;
    margin-bottom: 8px;
    padding: 5px 10px;
    cursor: pointer;
    transition: all 0.3s ease;
  }
  
  details:hover {
    background: #8642ff;
    box-shadow: 0 4px 12px rgba(110, 34, 221, 0.4);
  }
  
  summary {
    list-style: none;
  font-weight: 700;
  font-size: 14px;
  user-select: none;
  display: flex;
  justify-content: flex-start; /* Align to the left */
  align-items: center;
  text-align: left; /* Make sure text is left-aligned */
  }
  
  summary::-webkit-details-marker {
    display: none;
  }
  
  summary::before {
    content: "▶";
    display: inline-block;
    margin-right: 8px;
    transform-origin: center;
    transition: transform 0.3s ease;
  }
  
  details[open] summary::before {
    transform: rotate(90deg);
  }
  
  .category-count {
    background: rgba(255, 255, 255, 0.2);
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
  }
  
  /* Table */
  .options {
    margin-left: 20px;
    margin-top: 5px;
  }
  
  .options table {
    width: 100%;
    border-collapse: collapse;
    background: #1a0033;
    border-radius: 5px;
    overflow: hidden;
  }
  
  .options table thead {
    background: #2d0052;
  }
  
  .options table th {
    padding: 10px 8px;
    text-align: left;
    font-weight: 700;
    font-size: 12px;
    border-bottom: 2px solid #5b00a7;
    text-transform: uppercase;
  }
  
  .options table th:nth-child(1) { width: 60px; text-align: center; }
  .options table th:nth-child(2) { width: 100px; }
  .options table th:nth-child(3) { width: auto; }
  .options table th:nth-child(4) { width: 80px; text-align: center; }
  .options table th:nth-child(5) { width: 120px; text-align: right; }
  
  .options table td {
    padding: 10px 8px;
    font-size: 13px;
    border-bottom: 1px solid #5b00a7;
  }
  
  .options table td:nth-child(1) { text-align: center; }
  .options table td:nth-child(4) { text-align: center; }
  .options table td:nth-child(5) { 
    text-align: right; 
    font-weight: 600;
  }
  
  .options table tbody tr {
    cursor: pointer;
    transition: all 0.2s ease;
  }
  
  .options table tbody tr:hover {
    background: rgba(110, 34, 221, 0.1);
  }
  
  .options table tbody tr.selected {
    background: rgba(110, 34, 221, 0.3) !important;
    font-weight: 600;
  }
  
  .options table tbody tr.out-of-stock {
    opacity: 0.4;
    cursor: not-allowed;
  }
  
  .options table tbody tr.out-of-stock:hover {
    background: transparent !important;
  }
  
  .empty-message {
    padding: 15px;
    text-align: center;
    color: #aaa;
    font-style: italic;
  }
  
  .stock-info {
    font-size: 11px;
    color: #ef4444;
    font-weight: 700;
    padding: 3px 8px;
    background-color: rgba(239, 68, 68, 0.2);
    border-radius: 12px;
    display: inline-block;
  }
  
  input[type="radio"] {
    display: none;
  }
  
  .row-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    background: #6e22dd;
    border-radius: 50%;
    font-weight: 700;
    font-size: 11px;
  }
  
  /* Sidebar */
  .sidebar {
    background: linear-gradient(145deg, #1a1a2e 0%, #16162a 100%);
    border: 2px solid #6e22dd;
    border-radius: 12px;
    width: 320px;
    padding: 25px;
    box-sizing: border-box;
    text-align: center;
    position: sticky;
    top: 20px;
    box-shadow: 0 8px 20px rgba(110, 34, 221, 0.4);
  }
  
  .total-label {
    text-transform: uppercase;
    font-weight: 700;
    font-size: 12px;
    margin-bottom: 15px;
    letter-spacing: 1px;
    color: #aaa;
  }
  
  .total-price {
    font-size: 36px;
    font-weight: 800;
    margin-bottom: 30px;
    color: #6e22dd;
    text-shadow: 0 2px 4px rgba(110, 34, 221, 0.3);
  }
  
  button {
    width: 100%;
    border: none;
    background: linear-gradient(135deg, #6e22dd 0%, #8b4dff 100%);
    color: white;
    padding: 14px 0;
    font-size: 14px;
    font-weight: 700;
    border-radius: 8px;
    cursor: pointer;
    margin-bottom: 10px;
    transition: all 0.3s;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 6px rgba(110, 34, 221, 0.3);
  }
  
  button:hover {
    background: linear-gradient(135deg, #5a1bb8 0%, #7a3ee6 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(110, 34, 221, 0.4);
  }
  
  button:active {
    transform: translateY(0);
  }
  
  button.reset {
    background: rgba(110, 34, 221, 0.2);
    color: #fff;
    margin-top: 10px;
    font-weight: 600;
    border: 2px solid rgba(110, 34, 221, 0.5);
  }
  
  button.reset:hover {
    background: rgba(110, 34, 221, 0.3);
    border-color: #6e22dd;
  }

  /* Disabled Button State */
button:disabled {
  background: #333 !important;
  color: #666 !important;
  cursor: not-allowed !important;
  opacity: 0.5;
  transform: none !important;
  box-shadow: none !important;
}

button:disabled:hover {
  background: #333 !important;
  transform: none !important;
  box-shadow: none !important;
}
  
  /* Footer */
  footer {
    text-align: center;
    padding: 30px 20px;
    background-color: #0a0a0a;
    font-size: 12px;
    color: #666;
    margin-top: 60px;
    border-top: 1px solid rgba(110, 34, 221, 0.2);
  }
  
  /* Responsive */
  @media (max-width: 1200px) {
    .container {
      flex-direction: column;
    }
    
    .sidebar {
      width: 100%;
      max-width: 1290px;
      position: relative;
      top: 0;
      margin: 0 auto;
    }
    
    .configurator {
      max-width: 1290px;
    }
  }
  
  @media (max-width: 768px) {
    .nav-links {
      display: none;
    }
    
    .page-title {
      font-size: 24px;
    }
    
    .page-subtitle {
      font-size: 12px;
    }
    
    .options table {
      font-size: 11px;
    }
    
    .options table th,
    .options table td {
      padding: 6px 4px;
    }
  }

  .promo-badge {
  background: #ef4444;
  color: #fff;
  font-size: 10px;
  font-weight: 800;
  padding: 3px 8px;
  border-radius: 12px;
  margin-left: 8px;
  animation: pulsePromo 1.5s infinite;
}

@keyframes pulsePromo {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.old-price {
  text-decoration: line-through;
  color: #999;
  font-size: 11px;
  margin-right: 6px;
}

</style>
</head>
<body>
  <?php include 'header.php'; ?>

  <div class="page-header">
    <h1 class="page-title">BUILD YOUR DREAM PC</h1>
    <p class="page-subtitle">Select components from each category to create your perfect custom build</p>
  </div>

  <div class="container">
    <div class="configurator">
      <?php 
      $firstCategory = true;
      foreach ($allCategories as $category => $emoji): 
          $items = isset($categorized[$category]) ? $categorized[$category] : [];
          $groupName = strtolower(str_replace(' ', '-', $category));
          $itemCount = count($items);
      ?>
      <details <?php echo $firstCategory ? 'open' : ''; ?>>
        <summary>
          <span><?php echo htmlspecialchars($category); ?></span>
        </summary>
        <div class="options" data-group="<?php echo htmlspecialchars($groupName); ?>">
          <table>
            <thead>
              <tr>
                <th>Select</th>
                <th>Part ID</th>
                <th>Part Name</th>
                <th>Stock</th>
                <th>Price</th>
              </tr>
            </thead>
            <tbody>
              <?php 
if (empty($items)): 
?>
<tr class="out-of-stock">
  <td colspan="5" class="empty-message">No items available in this category</td>
  <input type="radio" name="<?php echo htmlspecialchars($groupName); ?>" value="none" data-price="0" disabled />
</tr>
<?php 
else:
    $rowIndex = 1;
    foreach ($items as $item): 
        $isOutOfStock = $item['stock'] <= 0;
        $rowClass = $isOutOfStock ? 'out-of-stock' : '';
                      
                      // Determine which price to display
                      $displayPrice = $item['selling_price'];
                      if (isset($item['is_promo']) && $item['is_promo'] == 1 && isset($item['promo_price']) && $item['promo_price'] > 0) {
                          $displayPrice = $item['promo_price'];
                      }
              ?>
              <tr class="<?php echo $rowClass; ?>" 
                  data-part-id="<?php echo $item['part_id']; ?>" 
                  data-row-num="<?php echo $rowIndex; ?>" 
                  data-group="<?php echo htmlspecialchars($groupName); ?>">
                <td>
                  <input 
  type="radio" 
  name="<?php echo htmlspecialchars($groupName); ?>" 
  value="<?php echo htmlspecialchars($item['part_name']); ?>" 
  data-price="<?php echo $displayPrice; ?>"
  data-part-id="<?php echo $item['part_id']; ?>"
  data-part-code="<?php echo htmlspecialchars($item['part_code']); ?>"
  <?php echo $isOutOfStock ? 'disabled' : ''; ?>
/>
<span class="row-number"></span>
                </td>
                <td><?php echo htmlspecialchars($item['part_code']); ?></td>
                <td>
  <?php echo htmlspecialchars($item['part_name']); ?>

  <?php if (!empty($item['is_promo']) && $item['is_promo'] == 1): ?>
    <span class="promo-badge">PROMO</span>
  <?php endif; ?>
</td>
                <td><?php echo $item['stock'] == 0 ? '<span class="stock-info">Out of Stock</span>' : $item['stock']; ?></td>
                <td>
  <?php if (!empty($item['is_promo']) && $item['is_promo'] == 1 && $item['promo_price'] > 0): ?>
    <span class="old-price">
      RM <?php echo number_format($item['selling_price'], 2); ?>
    </span>
    <span>
      RM<?php echo number_format($item['promo_price'], 2); ?>
    </span>
  <?php else: ?>
    RM <?php echo number_format($item['selling_price'], 2); ?>
  <?php endif; ?>
</td>
              </tr>
              <?php 
                  $rowIndex++;
                  endforeach;
              endif;
              ?>
            </tbody>
          </table>
        </div>
      </details>
      <?php 
      $firstCategory = false;
      endforeach; 
      ?>
    </div>

    <div class="sidebar">
      <div class="total-label">Total Price</div>
      <div class="total-price" id="totalPrice">RM0.00</div>
      <button id="nextBtn" disabled>Continue to Checkout</button>
      <button id="resetBtn" class="reset">Reset Selection</button>
    </div>
  </div>

  <?php include 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const totalCategories = 9; // Monitor, Casing, CPU, GPU, Cooler, RAM, Storage, Power Supply, Motherboard
  const nextBtn = document.getElementById('nextBtn');
  
  // Update row numbers display
  document.querySelectorAll('.options table').forEach(table => {
    table.querySelectorAll('tbody tr').forEach((row, index) => {
      const rowNumberSpan = row.querySelector('.row-number');
      if (rowNumberSpan) {
        rowNumberSpan.textContent = index + 1;
      }
    });
  });

  // Get all category details elements in order
  const categoryDetails = Array.from(document.querySelectorAll('details'));
  
  // Function to check if all categories have selections
  function validateAllSelections() {
    let selectedCount = 0;
    document.querySelectorAll('.options').forEach(optionsDiv => {
      const groupName = optionsDiv.getAttribute('data-group');
      const radio = document.querySelector(`input[name="${groupName}"]:checked`);
      if (radio && !radio.disabled && radio.value !== 'none') {
        selectedCount++;
      }
    });
    
    // Enable/disable button based on selection count
    if (selectedCount === totalCategories) {
      nextBtn.disabled = false;
    } else {
      nextBtn.disabled = true;
    }
    
    return selectedCount === totalCategories;
  }

  // Function to calculate total
  function calculateTotal() {
    let total = 0;
    document.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
      if (!radio.disabled && radio.value !== 'none') {
        total += parseFloat(radio.dataset.price) || 0;
      }
    });
    return total;
  }

  // Function to update total UI
  function updateTotalUI() {
    document.getElementById("totalPrice").textContent = "RM" + calculateTotal().toFixed(2);
  }

  // Function to get next category
  function getNextCategory(currentDetails) {
    const currentIndex = categoryDetails.indexOf(currentDetails);
    if (currentIndex >= 0 && currentIndex < categoryDetails.length - 1) {
      return categoryDetails[currentIndex + 1];
    }
    return null;
  }

  // Row click handler
  document.querySelectorAll('.options table tbody tr').forEach(row => {
    row.addEventListener('click', function(e) {
      if (this.classList.contains('out-of-stock')) return;
      
      const radio = this.querySelector('input[type="radio"]');
      if (!radio || radio.disabled) return;
      
      const rowNum = this.getAttribute('data-row-num');
      const table = this.closest('table');
      const currentDetails = this.closest('details');
      
      // Clear previous selections in this table
      table.querySelectorAll('tbody tr').forEach(tr => {
        tr.classList.remove('selected');
        const numberSpan = tr.querySelector('.row-number');
        if (numberSpan) numberSpan.textContent = tr.getAttribute('data-row-num') || '';
      });
      
      // Select current row
      this.classList.add('selected');
      radio.checked = true;
      const numberSpan = this.querySelector('.row-number');
      if (numberSpan) numberSpan.textContent = '✓';
      
      // Update total and validate
      updateTotalUI();
      validateAllSelections();
      
      // Auto-collapse current and open next category
      setTimeout(() => {
        currentDetails.open = false;
        const nextCategory = getNextCategory(currentDetails);
        if (nextCategory) {
          nextCategory.open = true;
          // Scroll to next category smoothly
          nextCategory.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
      }, 300);
    });
  });

  // Reset button handler
  document.getElementById("resetBtn").addEventListener("click", () => {
    // Clear all selections
    document.querySelectorAll('.options table').forEach(table => {
      table.querySelectorAll('tbody tr').forEach(tr => {
        tr.classList.remove('selected');
        const numberSpan = tr.querySelector('.row-number');
        if (numberSpan) {
          numberSpan.textContent = tr.getAttribute('data-row-num') || '';
        }
        const trRadio = tr.querySelector('input[type="radio"]');
        if (trRadio) trRadio.checked = false;
      });
    });
    
    // Close all categories except first
    categoryDetails.forEach((details, index) => {
      details.open = (index === 0);
    });
    
    // Update UI
    updateTotalUI();
    validateAllSelections();
  });

  // Next button handler
  document.getElementById("nextBtn").addEventListener("click", () => {
    if (!validateAllSelections()) {
      alert('Please select components from all categories');
      return;
    }
    
    let selectedItems = [];
    document.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
      if (!radio.disabled && radio.value !== 'none') {
        selectedItems.push({
          category: radio.name,
          name: radio.value,
          price: radio.dataset.price,
          partId: radio.dataset.partId,
          partCode: radio.dataset.partCode,
          quantity: 1
        });
      }
    });
    
    sessionStorage.setItem('selectedBuild', JSON.stringify(selectedItems));
    sessionStorage.setItem('totalPrice', calculateTotal());
    
    window.location.href = 'appointment.php';
  });

  // Initial validation
  validateAllSelections();
  updateTotalUI();
});
</script>
</body>
</html>