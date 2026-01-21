<?php
// homepage.php (Admin Dashboard)
session_start();
require_once '../db_config.php';

// Check if user is logged in as seller
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../landing/loginadmin.php");
    exit();
}

// Get admin name
$admin_name = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - Admin Dashboard</title>
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

  /* Navbar - Same as original */
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
    text-align: center;
    padding: 50px 20px 120px;
    background: linear-gradient(180deg, rgba(110, 34, 221, 0.2) 0%, transparent 100%);
    position: relative;
    overflow: hidden;
  }

  .page-header h1 {
    font-size: 48px;
    font-weight: 900;
    background: linear-gradient(135deg, #8b4dff, #6e22dd);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 15px;
    letter-spacing: 1px;
    text-transform: uppercase;
  }

  .page-header p {
    font-size: 18px;
    color: #bbb;
    font-weight: 400;
  }

  /* Container */
  .container {
    display: flex;
    gap: 30px;
    justify-content: center;
    flex-wrap: wrap;
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
  }

  /* Card - Enhanced from original */
  .card {
    background: linear-gradient(135deg, #1a1a1a 0%, #252525 100%);
    border: 2px solid #6e22dd;
    border-radius: 20px;
    padding: 30px 20px;
    width: 280px;
    text-align: center;
    text-decoration: none;
    color: #fff;
    display: block;
    transition: all 0.4s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
  }

  .card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(110, 34, 221, 0.1) 0%, transparent 100%);
    opacity: 0;
    transition: opacity 0.4s ease;
  }

  .card:hover::before {
    opacity: 1;
  }

  .card:hover {
    border-color: #8b4dff;
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(110, 34, 221, 0.5);
  }

  .card > * {
    position: relative;
  }

  .card-icon {
    width: 90px;
    height: 90px;
    object-fit: contain;
    margin: 0 auto 20px;
    filter: drop-shadow(0 4px 10px rgba(110, 34, 221, 0.4));
    transition: transform 0.4s ease;
  }

  .card:hover .card-icon {
    transform: scale(1.1);
  }

  .title {
    font-weight: 800;
    font-size: 18px;
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .desc {
    font-weight: 400;
    font-size: 14px;
    line-height: 1.5;
    color: #bbb;
  }

  /* Footer */
  footer {
    text-align: center;
    padding: 30px 20px;
    background-color: #121212;
    font-size: 12px;
    color: #666;
    margin-top: auto;
    border-top: 1px solid rgba(110, 34, 221, 0.2);
  }

  /* Responsive */
  @media (max-width: 768px) {
    .nav-links {
      display: none;
    }

    .page-header h1 {
      font-size: 32px;
    }

    .container {
      padding: 30px 20px;
    }

    .card {
      width: 100%;
      max-width: 350px;
    }
  }
  </style>
</head>
<body>
  <?php include 'header.php'; ?>

  <div class="page-header">
    <h1>Welcome Back, <?php echo htmlspecialchars($admin_name); ?></h1>
    <p>Manage and oversee all business operations</p>
  </div>

  <div class="container">
    <a href="seller.php" class="card">
      <img src="../picture/seller.png" alt="Seller List" class="card-icon" />
      <div class="title">Seller List</div>
      <div class="desc">Manage seller accounts and permissions</div>
    </a>

    <a href="inventory.php" class="card">
      <img src="../picture/inventory.png" alt="Inventory" class="card-icon" />
      <div class="title">Inventory</div>
      <div class="desc">Manage inventory of products</div>
    </a>

    <a href="promo.php" class="card">
      <img src="../picture/promo.png" alt="Promo" class="card-icon" />
      <div class="title">Promo</div>
      <div class="desc">Create promotional campaigns</div>
    </a>

    <a href="appointment.php" class="card">
      <img src="../picture/appointment.png" alt="Appointments" class="card-icon" />
      <div class="title">Appointments</div>
      <div class="desc">View and manage all appointments</div>
    </a>

    <a href="invoice.php" class="card">
      <img src="../picture/invoice.png" alt="Invoices" class="card-icon" />
      <div class="title">Invoices</div>
      <div class="desc">Monitor all transaction invoices</div>
    </a>
  </div>

  <?php include 'footer.php'; ?>
</body>
</html>