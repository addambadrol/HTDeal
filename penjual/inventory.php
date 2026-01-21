<?php
// inventory.php
session_start();
require_once '../db_config.php';

// Check if user is logged in as seller
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../login/loginpenjual.php");
    exit();
}

// Handle success/error messages
$success_message = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success']);
unset($_SESSION['error']);

// Fetch all inventory data grouped by category
$categories = [
    'MONITOR' => '💻 MONITOR',
    'GAMING BOOSTER PACK' => '🎮 GAMING BOOSTER PACK',
    'CHASSIS' => '🧰 CHASSIS OPTIONS',
    'MOTHERBOARD' => '🖥️ MOTHERBOARD',
    'PROCESSOR' => '🧩 PROCESSOR',
    'GRAPHICS CARD' => '🎮 GRAPHICS CARD',
    'COOLER' => '❄️ COOLER',
    'THERMAL COOLING' => '🌡️ THERMAL COOLING',
    'RAM' => '💾 RAM',
    'SSD' => '💽 SOLID STATE DRIVE',
    'SATA DRIVE' => '💿 SATA DRIVE',
    'POWER SUPPLY' => '🔌 POWER SUPPLY',
    'WIRELESS ADAPTER' => '📡 WIRELESS NETWORK ADAPTER',
    'OPERATING SYSTEM' => '🖥️ OPERATING SYSTEM',
    'ARGB LED' => '💡 ARGB CUSTOM BUILD LED OPTION',
    'ACCESSORIES' => '🎧 CUSTOM BUILD ACCESSORIES OPTION',
    'PERIPHERALS' => '🖱️ PERIPHERALS',
    'KEYBOARD' => '⌨️ KEYBOARD',
    'WEBCAM' => '📷 WEBCAM'
];

// Function to get parts by category
function getPartsByCategory($pdo, $category) {
    $stmt = $pdo->prepare("SELECT * FROM inventory WHERE category = ? ORDER BY part_name");
    $stmt->execute([$category]);
    return $stmt->fetchAll();
}

// Calculate total stats
try {
    $total_parts = $pdo->query("SELECT COUNT(*) FROM inventory")->fetchColumn();
    $total_stock = $pdo->query("SELECT SUM(stock) FROM inventory")->fetchColumn();
    $low_stock = $pdo->query("SELECT COUNT(*) FROM inventory WHERE stock < 5 AND stock > 0")->fetchColumn();
    $out_of_stock = $pdo->query("SELECT COUNT(*) FROM inventory WHERE stock = 0")->fetchColumn();
} catch(PDOException $e) {
    $total_parts = 0;
    $total_stock = 0;
    $low_stock = 0;
    $out_of_stock = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - Inventory Management</title>
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
  
  /* Navbar */
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
  
  /* Page Header */
  .page-header {
    max-width: 1290px;
    margin: 30px auto 20px;
    padding: 0 20px;
  }
  
  .page-title {
    font-size: 32px;
    font-weight: 800;
    background: linear-gradient(135deg, #8b4dff 0%, #6e22dd 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 20px;
  }
  
  /* Stats Cards */
  .stats-container {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin-bottom: 20px;
  }
  
  .stat-card {
    background: linear-gradient(145deg, #1a1a2e 0%, #16162a 100%);
    border: 2px solid #6e22dd;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
  }
  
  .stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(110, 34, 221, 0.4);
  }
  
  .stat-icon {
    font-size: 30px;
    margin-bottom: 10px;
  }
  
  .stat-value {
    font-size: 28px;
    font-weight: 700;
    color: #6e22dd;
    margin-bottom: 5px;
  }
  
  .stat-label {
    font-size: 12px;
    color: #aaa;
    font-weight: 600;
    text-transform: uppercase;
  }
  
  /* Alert Messages */
  .alert {
    max-width: 1290px;
    margin: 0 auto 20px;
    padding: 15px 20px;
    border-radius: 8px;
    font-weight: 500;
  }
  
  .alert-success {
    background-color: rgba(34, 197, 94, 0.1);
    border: 1px solid #22c55e;
    color: #22c55e;
  }
  
  .alert-error {
    background-color: rgba(239, 68, 68, 0.1);
    border: 1px solid #ef4444;
    color: #ef4444;
  }
  
  /* Container */
  .container {
    display: flex;
    gap: 20px;
    justify-content: center;
    padding: 0 20px;
    margin-bottom: 40px;
  }
  
  .configurator {
    background: #121212;
    border-radius: 8px;
    width: 100%;
    max-width: 1290px;
    padding: 10px 15px;
    box-sizing: border-box;
  }
  
  /* Add New Button */
  .add-new-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #6e22dd 0%, #8b4dff 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(110, 34, 221, 0.3);
    text-decoration: none;
  }
  
  .add-new-btn:hover {
    background: linear-gradient(135deg, #5a1bb8 0%, #7a3ee6 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(110, 34, 221, 0.4);
  }
  
  .add-new-btn:active {
    transform: translateY(0);
  }
  
  .add-new-btn::before {
    content: "+";
    font-size: 20px;
    font-weight: 700;
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
    justify-content: space-between;
    align-items: center;
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
  
  .options table th:nth-child(1) { width: 50px; }
  .options table th:nth-child(2) { width: 100px; }
  .options table th:nth-child(3) { width: auto; }
  .options table th:nth-child(4) { width: 80px; }
  .options table th:nth-child(5) { width: 100px; }
  .options table th:nth-child(6) { width: 80px; }
  
  .options table td {
    padding: 10px 8px;
    font-size: 13px;
    border-bottom: 1px solid #5b00a7;
  }
  
  .options table tbody tr {
    cursor: pointer;
    transition: all 0.2s ease;
  }
  
  .options table tbody tr:hover {
    background: #8700c6;
    transform: scale(1.01);
  }
  
  .stock-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
  }
  
  .stock-high {
    background: rgba(34, 197, 94, 0.2);
    color: #22c55e;
  }
  
  .stock-low {
    background: rgba(251, 191, 36, 0.2);
    color: #fbbf24;
  }
  
  .stock-out {
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
  }
  
  .promo-badge {
    background: rgba(255, 215, 0, 0.2);
    color: #ffd700;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    display: inline-block;
  }
  
  .empty-message {
    padding: 15px;
    text-align: center;
    color: #aaa;
    font-style: italic;
  }
  
  /* Modal */
  .modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    justify-content: center;
    align-items: center;
  }
  
  .modal.show {
    display: flex;
  }
  
  .modal-content {
    background: linear-gradient(145deg, #1a1a2e 0%, #16162a 100%);
    border: 2px solid #6e22dd;
    border-radius: 15px;
    padding: 30px;
    width: 90%;
    max-width: 450px;
    color: #fff;
    box-shadow: 0 10px 40px rgba(110, 34, 221, 0.5);
  }
  
  .modal-header {
    font-size: 12px;
    color: #aaa;
    margin-bottom: 5px;
    text-transform: uppercase;
    font-weight: 600;
  }
  
  .modal-part-name {
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 20px;
    color: #fff;
  }
  
  .modal-info {
    margin-bottom: 12px;
    font-size: 16px;
    display: flex;
    justify-content: space-between;
    padding: 8px;
    background: rgba(110, 34, 221, 0.1);
    border-radius: 6px;
  }
  
  .modal-info-label {
    color: #aaa;
  }
  
  .modal-info-value {
    font-weight: 700;
    color: #fff;
  }
  
  .modal-buttons {
    display: flex;
    gap: 15px;
    margin-top: 25px;
  }
  
  .modal-btn {
    flex: 1;
    padding: 14px;
    border: none;
    border-radius: 8px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
  }
  
  .modal-btn.delete {
    background-color: #ef4444;
    color: white;
  }
  
  .modal-btn.delete:hover {
    background-color: #dc2626;
    transform: translateY(-2px);
  }
  
  .modal-btn.edit {
    background: linear-gradient(135deg, #6e22dd 0%, #8b4dff 100%);
    color: white;
  }
  
  .modal-btn.edit:hover {
    background: linear-gradient(135deg, #5a1bb8 0%, #7a3ee6 100%);
    transform: translateY(-2px);
  }
  
  @media (max-width: 768px) {
    .stats-container {
      grid-template-columns: repeat(2, 1fr);
    }
    
    .options table {
      font-size: 11px;
    }
    
    .options table th,
    .options table td {
      padding: 6px 4px;
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
        <a href="inventory.php">INVENTORY</a>
        <a href="appointment.php">APPOINTMENT</a>
        <a href="invoice.php">INVOICE</a>
        <a href="review.php">REVIEW</a>
      </div>
      <div class="profile-icon">
        <a href="profile.php">
          <img src="../picture/profileicon.png" alt="Profile" />
        </a>
      </div>
    </div>
  </header>

  <!-- Alerts -->
  <?php if($success_message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
  <?php endif; ?>
  
  <?php if($error_message): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
  <?php endif; ?>

  <!-- Page Header -->
  <div class="page-header">
    <h1 class="page-title">📦 INVENTORY MANAGEMENT</h1>
    
    <!-- Stats Cards -->
    <div class="stats-container">
      <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-value"><?php echo number_format($total_parts); ?></div>
        <div class="stat-label">Total Parts</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">📊</div>
        <div class="stat-value"><?php echo number_format($total_stock); ?></div>
        <div class="stat-label">Total Stock</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">⚠️</div>
        <div class="stat-value"><?php echo number_format($low_stock); ?></div>
        <div class="stat-label">Low Stock</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">❌</div>
        <div class="stat-value"><?php echo number_format($out_of_stock); ?></div>
        <div class="stat-label">Out of Stock</div>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="configurator">
      <div style="display: flex; justify-content: flex-end; margin-bottom: 15px;">
        <a href="add_part.php" class="add-new-btn">Add New Part</a>
      </div>
      
      <?php 
      $isFirst = true;
      foreach($categories as $categoryKey => $categoryDisplay): 
        $parts = getPartsByCategory($pdo, $categoryKey);
        $partCount = count($parts);
      ?>
      <details <?php echo $isFirst ? 'open' : ''; ?>>
        <summary>
          <span><?php echo $categoryDisplay; ?></span>
          <span class="category-count"><?php echo $partCount; ?> items</span>
        </summary>
        <div class="options">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Part ID</th>
                <th>Part Name</th>
                <th>Stock</th>
                <th>Price (RM)</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if(empty($parts)): ?>
                <tr>
                  <td colspan="6" class="empty-message">No parts available in this category</td>
                </tr>
              <?php else: ?>
                <?php foreach($parts as $index => $part): ?>
                <tr data-part-id="<?php echo $part['part_id']; ?>" 
                    data-part-code="<?php echo htmlspecialchars($part['part_code']); ?>"
                    data-part-name="<?php echo htmlspecialchars($part['part_name']); ?>"
                    data-stock="<?php echo $part['stock']; ?>"
                    data-price="<?php echo number_format($part['price'], 2); ?>"
                    data-category="<?php echo htmlspecialchars($part['category']); ?>"
                    data-is-promo="<?php echo $part['is_promo'] ?? 0; ?>"
                    data-promo-price="<?php echo isset($part['promo_price']) ? number_format($part['promo_price'], 2) : ''; ?>">
                  <td><?php echo $index + 1; ?></td>
                  <td><?php echo htmlspecialchars($part['part_code']); ?></td>
                  <td><?php echo htmlspecialchars($part['part_name']); ?></td>
                  <td>
                    <?php 
                    $stock = $part['stock'];
                    if($stock == 0) {
                      echo '<span class="stock-badge stock-out">Out</span>';
                    } elseif($stock < 5) {
                      echo '<span class="stock-badge stock-low">' . $stock . '</span>';
                    } else {
                      echo '<span class="stock-badge stock-high">' . $stock . '</span>';
                    }
                    ?>
                  </td>
                  <td><?php echo number_format($part['price'], 2); ?></td>
                  <td>
                    <?php if(isset($part['is_promo']) && $part['is_promo'] == 1): ?>
                      <span class="promo-badge">🏷️ PROMO</span>
                    <?php else: ?>
                      <span style="color: #666;">-</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </details>
      <?php 
      $isFirst = false;
      endforeach; 
      ?>
    </div>
  </div>

  <!-- Modal -->
  <div id="partModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">Part Details</div>
      <div class="modal-part-name" id="modalPartName"></div>
      
      <div class="modal-info">
        <span class="modal-info-label">Part Code:</span>
        <span class="modal-info-value" id="modalPartCode"></span>
      </div>
      
      <div class="modal-info">
        <span class="modal-info-label">Category:</span>
        <span class="modal-info-value" id="modalCategory"></span>
      </div>
      
      <div class="modal-info">
        <span class="modal-info-label">Stock:</span>
        <span class="modal-info-value" id="modalStock"></span>
      </div>
      
      <div class="modal-info">
        <span class="modal-info-label">Price:</span>
        <span class="modal-info-value" id="modalPrice"></span>
      </div>
      
      <div class="modal-info" id="modalPromoInfo" style="display: none;">
        <span class="modal-info-label">Promo Price:</span>
        <span class="modal-info-value" style="color: #22c55e;" id="modalPromoPrice"></span>
      </div>
      
      <div class="modal-buttons">
        <button class="modal-btn delete" onclick="deletePart()">🗑️ Delete</button>
        <button class="modal-btn edit" onclick="editPart()">✏️ Edit</button>
      </div>
    </div>
  </div>

  <script>
    const modal = document.getElementById("partModal");
    let currentPartId = null;

    function showModal(row) {
      const partName = row.getAttribute('data-part-name');
      const partCode = row.getAttribute('data-part-code');
      const category = row.getAttribute('data-category');
      const stock = row.getAttribute('data-stock');
      const price = row.getAttribute('data-price');
      const partId = row.getAttribute('data-part-id');
      const isPromo = row.getAttribute('data-is-promo');
      const promoPrice = row.getAttribute('data-promo-price');
      
      document.getElementById("modalPartName").textContent = partName;
      document.getElementById("modalPartCode").textContent = partCode;
      document.getElementById("modalCategory").textContent = category;
      document.getElementById("modalStock").textContent = stock;
      document.getElementById("modalPrice").textContent = "RM " + price;
      
      // Show promo info if applicable
      const promoInfo = document.getElementById("modalPromoInfo");
      if(isPromo == 1 && promoPrice) {
        document.getElementById("modalPromoPrice").textContent = "RM " + promoPrice;
        promoInfo.style.display = "flex";
      } else {
        promoInfo.style.display = "none";
      }
      
      currentPartId = partId;
      modal.classList.add("show");
    }

    function closeModal() {
      modal.classList.remove("show");
      currentPartId = null;
    }

    function deletePart() {
      if(currentPartId && confirm("Are you sure you want to delete this part?")) {
        window.location.href = 'delete_part.php?id=' + currentPartId;
      }
    }

    function editPart() {
      if(currentPartId) {
        window.location.href = 'edit_part.php?id=' + currentPartId;
      }
    }

    modal.addEventListener("click", function(e) {
      if (e.target === modal) {
        closeModal();
      }
    });

    document.querySelectorAll(".options tbody tr").forEach(row => {
      // Skip empty message rows
      if(row.querySelector('.empty-message')) return;
      
      row.addEventListener("click", function() {
        showModal(this);
      });
    });
    
    // Auto-hide alerts after 3 seconds
    setTimeout(function() {
      const alerts = document.querySelectorAll('.alert');
      alerts.forEach(function(alert) {
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.3s ease';
        setTimeout(function() {
          alert.remove();
        }, 300);
      });
    }, 3000);
  </script>
</body>
</html>