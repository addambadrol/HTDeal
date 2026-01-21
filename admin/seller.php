<?php
// seller.php (Admin - Manage Sellers with Referral Info & Modal)
session_start();
require_once '../db_config.php';

// Check if user is logged in as admin
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../landing/loginadmin.php");
    exit();
}

// Handle success/error messages
$success_message = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success']);
unset($_SESSION['error']);

// Fetch all sellers with referral stats
try {
    $stmt = $pdo->query("
        SELECT 
            a.account_id, 
            a.first_name, 
            a.last_name, 
            a.email, 
            a.phone_number,
            a.status, 
            a.created_at,
            a.reference_code,
            a.total_referrals,
            a.total_commission
        FROM account a
        WHERE a.role = 'penjual' 
        ORDER BY a.created_at DESC
    ");
    $sellers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $sellers = [];
    $error_message = "Error fetching sellers: " . $e->getMessage();
}

// Handle form submission - Add new seller
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_seller') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone_number = trim($_POST['phone_number']);
    $full_phone = $country_code . $phone_number;
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if(empty($first_name) || empty($last_name) || empty($phone_number) || empty($email) || empty($password)) {
        $error_message = "All fields are required!";
    } elseif($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } elseif(strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters!";
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT account_id FROM account WHERE email = ?");
            $stmt->execute([$email]);
            if($stmt->fetch()) {
                $error_message = "Email already exists!";
            } else {
                // Generate unique reference code
                $reference_code = 'htd' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
                
                // Check if reference code exists
                $checkCode = $pdo->prepare("SELECT account_id FROM account WHERE reference_code = ?");
                $checkCode->execute([$reference_code]);
                while($checkCode->fetch()) {
                    $reference_code = 'htd' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
                    $checkCode->execute([$reference_code]);
                }
                
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new seller with reference code
                $stmt = $pdo->prepare("
                    INSERT INTO account 
                    (first_name, last_name, email, password, phone_number, role, status, reference_code, total_referrals, total_commission) 
                    VALUES (?, ?, ?, ?, ?, 'penjual', 'active', ?, 0, 0.00)
                ");
                $stmt->execute([$first_name, $last_name, $email, $hashed_password, $full_phone, $reference_code]);
                
                $_SESSION['success'] = "Seller added successfully with reference code: " . strtoupper($reference_code);
                header("Location: seller.php");
                exit();
            }
        } catch(PDOException $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    }
}

// Handle delete seller
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM account WHERE account_id = ? AND role = 'penjual'");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['success'] = "Seller deleted successfully!";
        header("Location: seller.php");
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error deleting seller: " . $e->getMessage();
        header("Location: seller.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - Manage Sellers</title>
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
    width: 1200px;
    margin: 30px auto;
    padding: 0 20px;
  }
  
  .page-title {
    font-size: 48px;
    font-weight: 800;
    background: linear-gradient(135deg, #8b4dff 0%, #6e22dd 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 30px;
    text-align: center;
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
  
  .section {
    background: linear-gradient(145deg, #1a1a2e 0%, #16162a 100%);
    border: 2px solid #6e22dd;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 8px 32px rgba(110, 34, 221, 0.3);
  }
  
  .section-title {
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 20px;
    color: #fff;
  }
  
  .seller-table {
    width: 100%;
    border-collapse: collapse;
    background: #0a0a0a;
    border-radius: 8px;
    overflow: hidden;
  }
  
  .seller-table thead {
    background: #6e22dd;
  }
  
  .seller-table th {
    padding: 12px 15px;
    text-align: left;
    font-weight: 700;
    font-size: 12px;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  
  .seller-table tbody tr {
    border-bottom: 1px solid #333;
    transition: background 0.2s ease;
    cursor: pointer;
  }
  
  .seller-table tbody tr:hover {
    background: rgba(110, 34, 221, 0.1);
  }
  
  .seller-table td {
    padding: 12px 15px;
    font-size: 14px;
    color: #ddd;
  }
  
  .seller-table th:nth-child(1),
  .seller-table td:nth-child(1) {
    width: 4%;
    text-align: center;
  }

  .reference-code {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(34, 197, 94, 0.1);
    color: #22c55e;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 1px;
    border: 1px solid rgba(34, 197, 94, 0.3);
    font-family: 'Courier New', monospace;
  }

  .reference-code-icon {
    font-size: 16px;
  }

  .stat-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
  }

  .stat-referrals {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
    border: 1px solid rgba(59, 130, 246, 0.3);
  }

  .stat-commission {
    background: rgba(251, 191, 36, 0.1);
    color: #fbbf24;
    border: 1px solid rgba(251, 191, 36, 0.3);
  }
  
  .status-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
  }
  
  .status-active {
    background-color: rgba(34, 197, 94, 0.2);
    color: #22c55e;
    border: 1px solid #22c55e;
  }
  
  .status-inactive {
    background-color: rgba(239, 68, 68, 0.2);
    color: #ef4444;
    border: 1px solid #ef4444;
  }
  
  .form-group {
    margin-bottom: 20px;
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
  
  .phone-input-group {
    display: flex;
    gap: 10px;
  }
  
  .phone-input-group select {
    width: 120px;
    flex-shrink: 0;
  }
  
  .phone-input-group input {
    flex: 1;
  }
  
  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
  }
  
  .btn-submit {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #6e22dd 0%, #8b4dff 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    margin-top: 10px;
  }
  
  .btn-submit:hover {
    background: linear-gradient(135deg, #5a1bb8 0%, #7a3ee6 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(110, 34, 221, 0.5);
  }
  
  .empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #666;
  }
  
  .empty-icon {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.5;
  }
  
  .empty-text {
    font-size: 16px;
    color: #888;
  }

  .info-notice {
    background: rgba(59, 130, 246, 0.1);
    border: 2px solid #3b82f6;
    border-left: 6px solid #3b82f6;
    border-radius: 12px;
    padding: 15px 18px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13px;
    color: #ccc;
  }

  .info-notice-icon {
    font-size: 20px;
  }

  /* MODAL STYLES */
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
    max-width: 500px;
    color: #fff;
    box-shadow: 0 10px 40px rgba(110, 34, 221, 0.5);
    animation: slideDown 0.3s ease;
  }

  @keyframes slideDown {
    from {
      transform: translateY(-50px);
      opacity: 0;
    }
    to {
      transform: translateY(0);
      opacity: 1;
    }
  }
  
  .modal-header {
    font-size: 12px;
    color: #aaa;
    margin-bottom: 5px;
    text-transform: uppercase;
    font-weight: 600;
  }
  
  .modal-seller-name {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 20px;
    color: #fff;
  }
  
  .modal-info {
    margin-bottom: 12px;
    font-size: 15px;
    display: flex;
    justify-content: space-between;
    padding: 10px 12px;
    background: rgba(110, 34, 221, 0.1);
    border-radius: 8px;
    border: 1px solid rgba(110, 34, 221, 0.2);
  }
  
  .modal-info-label {
    color: #aaa;
    font-weight: 600;
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
    .form-row {
      grid-template-columns: 1fr;
    }
    
    .phone-input-group {
      flex-direction: column;
    }
    
    .phone-input-group select {
      width: 100%;
    }

    .seller-table {
      font-size: 12px;
    }

    .seller-table th,
    .seller-table td {
      padding: 8px;
    }
  }
</style>
</head>
<body>
  <?php include 'header.php'; ?>

  <div class="container">
    <h1 class="page-title">MANAGE SELLERS & REFERRALS</h1>
    
    <?php if($success_message): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    
    <?php if($error_message): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
    
    <div class="section">
      <h2 class="section-title">Seller List & Performance</h2>

      <div class="info-notice">
        <span class="info-notice-icon">ℹ️</span>
        <span>Click on any seller row to view full details and manage their account.</span>
      </div>
      
      <?php if(empty($sellers)): ?>
        <div class="empty-state">
          <div class="empty-icon">🔭</div>
          <p class="empty-text">No sellers registered yet</p>
        </div>
      <?php else: ?>
        <table class="seller-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Seller Name</th>
              <th>E-Mail</th>
              <th>Phone</th>
              <th>Reference Code</th>
              <th>Referrals</th>
              <th>Commission</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($sellers as $index => $seller): ?>
            <tr
              data-seller-id="<?php echo $seller['account_id']; ?>" 
              data-seller-name="<?php echo htmlspecialchars($seller['first_name'] . ' ' . $seller['last_name']); ?>"
              data-email="<?php echo htmlspecialchars($seller['email']); ?>"
              data-phone="<?php echo htmlspecialchars($seller['phone_number']); ?>"
              data-reference-code="<?php echo htmlspecialchars($seller['reference_code'] ?? ''); ?>"
              data-referrals="<?php echo $seller['total_referrals'] ?? 0; ?>"
              data-commission="<?php echo number_format($seller['total_commission'] ?? 0, 2); ?>"
              data-status="<?php echo $seller['status']; ?>"
              data-created="<?php echo date('d M Y', strtotime($seller['created_at'])); ?>">
              <td style="text-align: center;"><strong><?php echo $index + 1; ?></strong></td>
              <td><?php echo htmlspecialchars($seller['first_name'] . ' ' . $seller['last_name']); ?></td>
              <td><small><?php echo htmlspecialchars($seller['email']); ?></small></td>
              <td><small><?php echo htmlspecialchars($seller['phone_number']); ?></small></td>
              <td>
                <?php if($seller['reference_code']): ?>
                  <span class="reference-code">
                    <span class="reference-code-icon"></span>
                    <?php echo strtoupper($seller['reference_code']); ?>
                  </span>
                <?php else: ?>
                  <span style="color: #888; font-size: 12px;">Not assigned</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="stat-badge stat-referrals">
                  <?php echo $seller['total_referrals'] ?? 0; ?>
                </span>
              </td>
              <td>
                <span class="stat-badge stat-commission">
                  RM <?php echo number_format($seller['total_commission'] ?? 0, 2); ?>
                </span>
              </td>
              <td>
                <span class="status-badge status-<?php echo $seller['status']; ?>">
                  <?php echo ucfirst($seller['status']); ?>
                </span>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
    
    <div class="section">
      <h2 class="section-title">Add New Seller</h2>
      
      <form method="POST" action="">
        <input type="hidden" name="action" value="add_seller">
        
        <div class="form-row">
          <div class="form-group">
            <label>First Name <span>*</span></label>
            <input type="text" name="first_name" placeholder="Enter first name" required>
          </div>
          
          <div class="form-group">
            <label>Last Name <span>*</span></label>
            <input type="text" name="last_name" placeholder="Enter last name" required>
          </div>
        </div>
        
        <div class="form-group">
          <label>Number Phone <span>*</span></label>
          <div class="phone-input-group">
            
            <input type="tel" name="phone_number" placeholder="0123456789" required>
          </div>
        </div>
        
        <div class="form-group">
          <label>E-Mail <span>*</span></label>
          <input type="email" name="email" placeholder="seller@example.com" required>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label>Password <span>*</span></label>
            <input type="password" name="password" placeholder="Minimum 6 characters" required>
          </div>
          
          <div class="form-group">
            <label>Re-Confirm Password <span>*</span></label>
            <input type="password" name="confirm_password" placeholder="Re-enter password" required>
          </div>
        </div>

        <div class="info-notice">
          <span class="info-notice-icon">🎁</span>
          <span>A unique reference code (e.g., <strong>HTD001</strong>) will be automatically generated for this seller.</span>
        </div>
        
        <button type="submit" class="btn-submit">Add Seller</button>
      </form>
    </div>
  </div>

  <!-- MODAL -->
  <div id="sellerModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">Seller Details</div>
      <div class="modal-seller-name" id="modalSellerName"></div>
      
      <div class="modal-info">
        <span class="modal-info-label">Email:</span>
        <span class="modal-info-value" id="modalEmail"></span>
      </div>
      
      <div class="modal-info">
        <span class="modal-info-label">Phone:</span>
        <span class="modal-info-value" id="modalPhone"></span>
      </div>
      
      <div class="modal-info">
        <span class="modal-info-label">Reference Code:</span>
        <span class="modal-info-value" style="color: #22c55e; font-family: 'Courier New', monospace;" id="modalReferenceCode"></span>
      </div>
      
      <div class="modal-info">
        <span class="modal-info-label">Total Referrals:</span>
        <span class="modal-info-value" style="color: #3b82f6;" id="modalReferrals"></span>
      </div>
      
      <div class="modal-info">
        <span class="modal-info-label">Total Commission:</span>
        <span class="modal-info-value" style="color: #fbbf24;" id="modalCommission"></span>
      </div>
      
      <div class="modal-info">
        <span class="modal-info-label">Status:</span>
        <span class="modal-info-value" id="modalStatus"></span>
      </div>
      
      <div class="modal-info">
        <span class="modal-info-label">Joined Date:</span>
        <span class="modal-info-value" id="modalCreated"></span>
      </div>
      
      <div class="modal-buttons">
        <button class="modal-btn delete" onclick="deleteSeller()">Delete Account</button>
        <button class="modal-btn edit" onclick="editSeller()">Edit Seller</button>
      </div>
    </div>
  </div>

  <script>
  const modal = document.getElementById("sellerModal");
  let currentSellerId = null;

  function showModal(row) {
    const sellerId = row.getAttribute('data-seller-id');
    const sellerName = row.getAttribute('data-seller-name');
    const email = row.getAttribute('data-email');
    const phone = row.getAttribute('data-phone');
    const refCode = row.getAttribute('data-reference-code');
    const referrals = row.getAttribute('data-referrals');
    const commission = row.getAttribute('data-commission');
    const status = row.getAttribute('data-status');
    const created = row.getAttribute('data-created');
    
    document.getElementById("modalSellerName").textContent = sellerName;
    document.getElementById("modalEmail").textContent = email;
    document.getElementById("modalPhone").textContent = phone;
    document.getElementById("modalReferenceCode").textContent = refCode ? refCode.toUpperCase() : 'Not assigned';
    document.getElementById("modalReferrals").textContent = referrals + ' customers';
    document.getElementById("modalCommission").textContent = 'RM ' + commission;
    document.getElementById("modalStatus").textContent = status.charAt(0).toUpperCase() + status.slice(1);
    document.getElementById("modalCreated").textContent = created;
    
    currentSellerId = sellerId;
    modal.classList.add("show");
  }

  function closeModal() {
    modal.classList.remove("show");
    currentSellerId = null;
  }

  function deleteSeller() {
    if (
      currentSellerId &&
      confirm(
        "Are you sure you want to DELETE this seller account?\n\n" +
        "This action cannot be undone and will remove:\n" +
        "• All seller data\n" +
        "• Reference code\n" +
        "• Commission records\n\n" +
        "Proceed with deletion?"
      )
    ) {
      window.location.href = 'seller.php?delete=' + currentSellerId;
    }
  }

  function editSeller() {
    if (currentSellerId) {
      window.location.href = 'edit_seller.php?id=' + currentSellerId;
    }
  }

  // Close modal when clicking outside
  modal.addEventListener("click", function(e) {
    if (e.target === modal) {
      closeModal();
    }
  });

  // Add click event to all seller rows
  document.querySelectorAll(".seller-table tbody tr").forEach(row => {
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

  <?php include 'footer.php'; ?>
</body>
</html>