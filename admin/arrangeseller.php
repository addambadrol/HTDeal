<?php
// arrangeseller.php
require_once '../db_config.php';

// Check if user is logged in as seller
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../landing/loginadmin.php");
    exit();
}

// Dummy data for now (nanti akan guna database)
$sellers = [
    [
        'num' => 1,
        'seller_id' => 'S001',
        'seller_name' => 'Haikal Halim',
        'email' => 'haikalhalim@gmail.com',
        'phone' => '+60 19 572 1971'
    ],
    [
        'num' => 2,
        'seller_id' => 'S002',
        'seller_name' => 'Irfan Zakaria',
        'email' => 'irfanzakaria@gmail.com',
        'phone' => '+60 18 471 7658'
    ]
];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nanti akan process add seller ke database
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Add seller logic here
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - Manage Seller</title>
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
    margin: 50px 30px;
    padding: 0;
  }
  
  .page-title {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 40px;
    text-align: left;
  }
  
  .section {
    border: 2px solid #6e22dd;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 40px;
  }
  
  .section-title {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 20px;
    color: #fff;
  }
  
  .seller-table {
    width: 100%;
    border-collapse: collapse;
    background: #1a0033;
    border-radius: 8px;
    overflow: hidden;
  }
  
  .seller-table thead {
    background: #6e22dd;
  }
  
  .seller-table th {
    padding: 12px 15px;
    text-align: center;
    font-weight: 700;
    font-size: 14px;
    color: #fff;
  }
  
  .seller-table tbody tr {
    background: #1a0033;
    border-bottom: 1px solid #5b00a7;
    transition: background 0.3s ease;
    cursor: pointer;
  }
  
  .seller-table tbody tr:hover {
    background: #8700c6;
  }
  
  .seller-table td {
    padding: 12px 15px;
    font-size: 14px;
    color: #fff;
    text-align: center;
  }
  
  .seller-table th:nth-child(1),
  .seller-table td:nth-child(1) {
    width: 10%;
  }
  
  .seller-table th:nth-child(2),
  .seller-table td:nth-child(2) {
    width: 15%;
  }
  
  .seller-table th:nth-child(3),
  .seller-table td:nth-child(3) {
    width: 25%;
  }
  
  .seller-table th:nth-child(4),
  .seller-table td:nth-child(4) {
    width: 30%;
  }
  
  .seller-table th:nth-child(5),
  .seller-table td:nth-child(5) {
    width: 20%;
  }
  
  .form-group {
    margin-bottom: 20px;
  }
  
  .form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #fff;
    font-size: 14px;
  }
  
  .form-group input,
  .form-group select {
    width: 100%;
    padding: 12px 15px;
    background-color: #fff;
    border: none;
    border-radius: 25px;
    color: #000;
    font-size: 14px;
  }
  
  .form-group input:focus,
  .form-group select:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(110, 34, 221, 0.3);
  }
  
  .phone-input-group {
    display: flex;
    gap: 10px;
  }
  
  .country-code {
    width: 80px;
    padding: 12px 10px;
    background-color: #fff;
    border: none;
    border-radius: 25px;
    color: #000;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .country-code img {
    width: 24px;
    height: 16px;
    margin-right: 5px;
  }
  
  .phone-input {
    flex: 1;
  }
  
  .btn-submit {
    display: block;
    width: 200px;
    margin: 30px auto 0;
    padding: 14px;
    background: linear-gradient(135deg, #6e22dd 0%, #8b4dff 100%);
    color: white;
    border: none;
    border-radius: 25px;
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  
  .btn-submit:hover {
    background: linear-gradient(135deg, #5a1bb8 0%, #7a3ee6 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(110, 34, 221, 0.5);
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
    background-color: rgba(0, 0, 0, 0.7);
    justify-content: center;
    align-items: center;
  }
  
  .modal.show {
    display: flex;
  }
  
  .modal-content {
    background-color: #1a1a1a;
    border: 2px solid #6e22dd;
    border-radius: 15px;
    padding: 30px;
    width: 90%;
    max-width: 400px;
    color: #fff;
  }
  
  .modal-label {
    font-size: 14px;
    color: #aaa;
    margin-bottom: 5px;
    font-weight: 600;
  }
  
  .modal-value {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 20px;
    color: #fff;
  }
  
  .modal-buttons {
    display: flex;
    gap: 15px;
    margin-top: 25px;
  }
  
  .modal-btn {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
  }
  
  .modal-btn.delete {
    background-color: #8b0000;
    color: white;
  }
  
  .modal-btn.delete:hover {
    background-color: #a00000;
  }
  
  .modal-btn.edit {
    background-color: #6e22dd;
    color: white;
  }
  
  .modal-btn.edit:hover {
    background-color: #8642ff;
  }
  
  @media (max-width: 1200px) {
    .seller-table {
      font-size: 12px;
    }
  }
</style>
</head>
<body>
  <?php include 'header.php'; ?>

  <div class="container">
    <h1 class="page-title">MANAGE SELLER</h1>
    
    <!-- Seller List Section -->
    <div class="section">
      <h2 class="section-title">Seller List</h2>
      <table class="seller-table">
        <thead>
          <tr>
            <th>Num.</th>
            <th>Seller ID</th>
            <th>Seller Name</th>
            <th>E-Mail</th>
            <th>Number Phone</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($sellers as $seller): ?>
          <tr onclick="viewSeller('<?php echo $seller['seller_id']; ?>')">
            <td><?php echo $seller['num']; ?></td>
            <td><?php echo $seller['seller_id']; ?></td>
            <td><?php echo $seller['seller_name']; ?></td>
            <td><?php echo $seller['email']; ?></td>
            <td><?php echo $seller['phone']; ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    
    <!-- Add New Seller Section -->
    <div class="section">
      <h2 class="section-title">Add New Seller</h2>
      <form method="POST" action="">
        <div class="form-group">
          <label>First Name</label>
          <input type="text" name="first_name" required />
        </div>
        
        <div class="form-group">
          <label>Last Name</label>
          <input type="text" name="last_name" required />
        </div>
        
        <div class="form-group">
          <label>Number Phone</label>
          <div class="phone-input-group">
            <select class="country-code">
              <option value="+60">🇲🇾 +60</option>
            </select>
            <input type="tel" name="phone" class="phone-input" required />
          </div>
        </div>
        
        <div class="form-group">
          <label>E-Mail</label>
          <input type="email" name="email" required />
        </div>
        
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" required />
        </div>
        
        <div class="form-group">
          <label>Re-Confirm Password</label>
          <input type="password" name="confirm_password" required />
        </div>
        
        <button type="submit" class="btn-submit">Add Seller</button>
      </form>
    </div>
  </div>

  <!-- Modal -->
  <div id="sellerModal" class="modal">
    <div class="modal-content">
      <div class="modal-label">Seller Name :</div>
      <div class="modal-value" id="modalSellerName"></div>
      <div class="modal-label">E-Mail :</div>
      <div class="modal-value" id="modalEmail"></div>
      <div class="modal-buttons">
        <button class="modal-btn delete" onclick="deleteSeller()">DELETE</button>
        <button class="modal-btn edit" onclick="editSeller()">EDIT PROFILE</button>
      </div>
    </div>
  </div>

  <script>
    const modal = document.getElementById("sellerModal");
    let currentSellerId = null;

    function viewSeller(sellerId) {
      // Find seller data
      const sellers = <?php echo json_encode($sellers); ?>;
      const seller = sellers.find(s => s.seller_id === sellerId);
      
      if(seller) {
        document.getElementById("modalSellerName").textContent = seller.seller_name;
        document.getElementById("modalEmail").textContent = seller.email;
        currentSellerId = sellerId;
        modal.classList.add("show");
      }
    }
    
    function closeModal() {
      modal.classList.remove("show");
      currentSellerId = null;
    }
    
    function deleteSeller() {
      if(currentSellerId && confirm("Are you sure you want to delete this seller?")) {
        // Nanti akan redirect ke delete_seller.php?id=xxx
        alert('Delete seller: ' + currentSellerId + ' (Feature coming soon)');
        closeModal();
      }
    }
    
    function editSeller() {
  if(currentSellerId) {
    // Redirect ke edit_seller.php dengan seller ID
    window.location.href = 'edit_seller.php?id=' + currentSellerId;
  }
}
    
    modal.addEventListener("click", function(e) {
      if (e.target === modal) {
        closeModal();
      }
    });
  </script>
</body>
</html>