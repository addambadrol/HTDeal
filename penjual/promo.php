<?php
// promo.php
require_once '../db_config.php';

// Fetch all promos from inventory table
try {
    $stmt = $pdo->query("
        SELECT * FROM inventory 
        WHERE is_promo = 1 
        ORDER BY promo_start_date DESC
    ");
    $promos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $promos = [];
    $error = "Error fetching promos: " . $e->getMessage();
}

// Handle delete promo (set is_promo = 0)
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("
            UPDATE inventory 
            SET is_promo = 0, 
                promo_price = NULL, 
                promo_start_date = NULL, 
                promo_end_date = NULL 
            WHERE part_id = ?
        ");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['success'] = "Promo removed successfully!";
        header("Location: promo.php");
        exit();
    } catch(PDOException $e) {
        $error = "Error removing promo: " . $e->getMessage();
    }
}

// Handle toggle active status
if (isset($_GET['toggle'])) {
    try {
        $stmt = $pdo->prepare("SELECT is_promo FROM inventory WHERE part_id = ?");
        $stmt->execute([$_GET['toggle']]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $new_status = $current['is_promo'] == 1 ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE inventory SET is_promo = ? WHERE part_id = ?");
        $stmt->execute([$new_status, $_GET['toggle']]);
        
        $_SESSION['success'] = "Promo status updated!";
        header("Location: promo.php");
        exit();
    } catch(PDOException $e) {
        $error = "Error updating status: " . $e->getMessage();
    }
}

// Calculate statistics
$total_promos = count($promos);
$active_promos = 0;
$expired_promos = 0;
$scheduled_promos = 0;
$current_date = date('Y-m-d');

foreach($promos as $promo) {
    if($promo['is_promo'] && $promo['promo_start_date'] <= $current_date && $promo['promo_end_date'] >= $current_date) {
        $active_promos++;
    } elseif($promo['promo_end_date'] < $current_date) {
        $expired_promos++;
    } elseif($promo['promo_start_date'] > $current_date) {
        $scheduled_promos++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - Promo Management</title>
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
    max-width: 1400px;
    margin: 30px auto;
    padding: 0 20px;
  }
  
  .page-header {
    background: linear-gradient(145deg, #1a1a2e 0%, #16162a 100%);
    border: 2px solid #6e22dd;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 8px 32px rgba(110, 34, 221, 0.3);
  }
  
  .page-header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .page-header h1 {
    font-size: 32px;
    font-weight: 700;
    background: linear-gradient(135deg, #8b4dff 0%, #6e22dd 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 8px;
  }
  
  .page-header p {
    color: #aaa;
    font-size: 14px;
  }
  
  .btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-decoration: none;
    display: inline-block;
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
  
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
  }
  
  .stat-card {
    background: linear-gradient(145deg, #1a1a2e 0%, #16162a 100%);
    border: 2px solid #333;
    border-radius: 12px;
    padding: 25px;
    text-align: center;
    transition: all 0.3s ease;
  }
  
  .stat-card:hover {
    border-color: #6e22dd;
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(110, 34, 221, 0.3);
  }
  
  .stat-icon {
    font-size: 36px;
    margin-bottom: 15px;
  }
  
  .stat-value {
    font-size: 36px;
    font-weight: 700;
    color: #6e22dd;
    margin-bottom: 5px;
  }
  
  .stat-label {
    color: #aaa;
    font-size: 14px;
    font-weight: 600;
  }
  
  .promo-table-container {
    background: linear-gradient(145deg, #1a1a2e 0%, #16162a 100%);
    border: 2px solid #6e22dd;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 8px 32px rgba(110, 34, 221, 0.3);
    overflow-x: auto;
  }
  
  .table-header {
    margin-bottom: 20px;
  }
  
  .table-header h2 {
    font-size: 24px;
    color: #fff;
    font-weight: 700;
  }
  
  table {
    width: 100%;
    border-collapse: collapse;
  }
  
  thead {
    background-color: rgba(110, 34, 221, 0.1);
  }
  
  th {
    padding: 15px;
    text-align: left;
    font-weight: 700;
    color: #6e22dd;
    font-size: 13px;
    text-transform: uppercase;
    border-bottom: 2px solid #6e22dd;
  }
  
  td {
    padding: 15px;
    border-bottom: 1px solid #333;
    color: #ddd;
    font-size: 14px;
  }
  
  tr:hover {
    background-color: rgba(110, 34, 221, 0.05);
  }
  
  .badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
  }
  
  .badge-active {
    background-color: rgba(34, 197, 94, 0.2);
    color: #22c55e;
    border: 1px solid #22c55e;
  }
  
  .badge-inactive {
    background-color: rgba(239, 68, 68, 0.2);
    color: #ef4444;
    border: 1px solid #ef4444;
  }
  
  .badge-scheduled {
    background-color: rgba(251, 191, 36, 0.2);
    color: #fbbf24;
    border: 1px solid #fbbf24;
  }
  
  .badge-expired {
    background-color: rgba(156, 163, 175, 0.2);
    color: #9ca3af;
    border: 1px solid #9ca3af;
  }
  
  .action-buttons {
    display: flex;
    gap: 8px;
  }
  
  .btn-sm {
    padding: 6px 12px;
    font-size: 12px;
    border-radius: 6px;
  }
  
  .btn-info {
    background-color: #3b82f6;
    color: white;
  }
  
  .btn-info:hover {
    background-color: #2563eb;
  }
  
  .btn-success {
    background-color: #22c55e;
    color: white;
  }
  
  .btn-success:hover {
    background-color: #16a34a;
  }
  
  .btn-danger {
    background-color: #ef4444;
    color: white;
  }
  
  .btn-danger:hover {
    background-color: #dc2626;
  }
  
  .btn-warning {
    background-color: #f59e0b;
    color: white;
  }
  
  .btn-warning:hover {
    background-color: #d97706;
  }
  
  .alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
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
  
  .empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #666;
  }
  
  .empty-state-icon {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.5;
  }
  
  .empty-state h3 {
    font-size: 20px;
    margin-bottom: 10px;
    color: #888;
  }
  
  .empty-state p {
    font-size: 14px;
    margin-bottom: 20px;
  }
  
  @media (max-width: 768px) {
    .page-header-content {
      flex-direction: column;
      gap: 20px;
      text-align: center;
    }
    
    .stats-grid {
      grid-template-columns: 1fr;
    }
    
    table {
      font-size: 12px;
    }
    
    th, td {
      padding: 10px;
    }
    
    .action-buttons {
      flex-direction: column;
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

  <div class="container">
    <?php if(isset($_SESSION['success'])): ?>
      <div class="alert alert-success">
        <?php 
          echo $_SESSION['success']; 
          unset($_SESSION['success']);
        ?>
      </div>
    <?php endif; ?>
    
    <?php if(isset($error)): ?>
      <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="page-header">
      <div class="page-header-content">
        <div>
          <h1>🏷️ Promo Management</h1>
          <p>Create and manage promotional offers for your inventory items</p>
        </div>
        <a href="add_promo.php" class="btn btn-primary">+ Add New Promo</a>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">🏷️</div>
        <div class="stat-value"><?php echo $total_promos; ?></div>
        <div class="stat-label">Total Promos</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-value"><?php echo $active_promos; ?></div>
        <div class="stat-label">Active Promos</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">⏰</div>
        <div class="stat-value"><?php echo $scheduled_promos; ?></div>
        <div class="stat-label">Scheduled</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div class="stat-value"><?php echo $expired_promos; ?></div>
        <div class="stat-label">Expired</div>
      </div>
    </div>

    <!-- Promo Table -->
    <div class="promo-table-container">
      <div class="table-header">
        <h2>📋 All Promos</h2>
      </div>
      
      <?php if(empty($promos)): ?>
        <div class="empty-state">
          <div class="empty-state-icon">📦</div>
          <h3>No Promos Yet</h3>
          <p>Start by creating your first promotional offer</p>
          <a href="add_promo.php" class="btn btn-primary">Create First Promo</a>
        </div>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Part Code</th>
              <th>Part Name</th>
              <th>Category</th>
              <th>Original Price</th>
              <th>Promo Price</th>
              <th>Discount</th>
              <th>Period</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($promos as $promo): 
              $current_date = date('Y-m-d');
              $is_active = $promo['is_promo'] && $promo['promo_start_date'] <= $current_date && $promo['promo_end_date'] >= $current_date;
              $is_scheduled = $promo['promo_start_date'] > $current_date;
              $is_expired = $promo['promo_end_date'] < $current_date;
              
              // Calculate discount
              $discount_amount = $promo['price'] - $promo['promo_price'];
              $discount_percentage = ($discount_amount / $promo['price']) * 100;
            ?>
            <tr>
              <td><strong><?php echo htmlspecialchars($promo['part_code']); ?></strong></td>
              <td><?php echo htmlspecialchars($promo['part_name']); ?></td>
              <td><?php echo htmlspecialchars($promo['category']); ?></td>
              <td>
                <span style="text-decoration: line-through; color: #888;">
                  RM <?php echo number_format($promo['price'], 2); ?>
                </span>
              </td>
              <td>
                <strong style="color: #22c55e;">
                  RM <?php echo number_format($promo['promo_price'], 2); ?>
                </strong>
              </td>
              <td>
                <span class="badge badge-active">
                  <?php echo number_format($discount_percentage, 0); ?>% OFF
                </span>
                <br>
                <small style="color: #888;">
                  Save RM <?php echo number_format($discount_amount, 2); ?>
                </small>
              </td>
              <td>
                <small>
                  <?php echo date('d M Y', strtotime($promo['promo_start_date'])); ?><br>
                  to<br>
                  <?php echo date('d M Y', strtotime($promo['promo_end_date'])); ?>
                </small>
              </td>
              <td>
                <?php if($is_expired): ?>
                  <span class="badge badge-expired">Expired</span>
                <?php elseif($is_scheduled): ?>
                  <span class="badge badge-scheduled">Scheduled</span>
                <?php elseif($is_active): ?>
                  <span class="badge badge-active">Active</span>
                <?php else: ?>
                  <span class="badge badge-inactive">Inactive</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="action-buttons">
                  <a href="edit_promo.php?id=<?php echo $promo['part_id']; ?>" 
                     class="btn btn-sm btn-info" 
                     title="Edit">
                    ✏️
                  </a>
                  
                  <a href="?toggle=<?php echo $promo['part_id']; ?>" 
                     class="btn btn-sm <?php echo $promo['is_promo'] ? 'btn-warning' : 'btn-success'; ?>" 
                     title="<?php echo $promo['is_promo'] ? 'Deactivate' : 'Activate'; ?>"
                     onclick="return confirm('Change promo status?')">
                    <?php echo $promo['is_promo'] ? '⏸️' : '▶️'; ?>
                  </a>
                  
                  <a href="?delete=<?php echo $promo['part_id']; ?>" 
                     class="btn btn-sm btn-danger" 
                     title="Remove Promo"
                     onclick="return confirm('Are you sure you want to remove this promo?')">
                    🗑️
                  </a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

  <script>
    // Auto-hide success messages after 3 seconds
    setTimeout(function() {
      const alerts = document.querySelectorAll('.alert-success');
      alerts.forEach(function(alert) {
        alert.style.opacity = '0';
        setTimeout(function() {
          alert.remove();
        }, 300);
      });
    }, 3000);
  </script>
</body>
</html>