<?php
session_start();
require_once '../db_config.php';

// Check if user is logged in as seller
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../landing/loginadmin.php");
    exit();
}
 
// Get fresh data from database
try {
    $stmt = $pdo->prepare("SELECT * FROM account WHERE account_id = ?");
    $stmt->execute([$_SESSION['account_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        session_destroy();
        header("Location: ../landing/loginadmin.php");
        exit();
    }
} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Dummy data for invoices (nanti akan guna database)
// $invoices = [
//     ['num' => 1, 'invoice_number' => 'B20250729-004'],
//     ['num' => 2, 'invoice_number' => 'W20250510-002'],
//     ['num' => 3, 'invoice_number' => 'R20250801-005']
// ];

// Dummy data for appointments (nanti akan guna database)
// $appointments = [
//     ['num' => 1, 'invoice_number' => 'B20250729-004', 'date' => '29/7/2025', 'time' => '10.00 A.M', 'status' => 'Approved'],
//     ['num' => 2, 'invoice_number' => 'W20250810-002', 'date' => '10/8/2025', 'time' => '02.00 P.M', 'status' => 'Pending Approval'],
//     ['num' => 3, 'invoice_number' => 'R20250530-005', 'date' => '30/5/2025', 'time' => '11.00 A.M', 'status' => 'Completed'],
//     ['num' => 4, 'invoice_number' => 'R20250530-004', 'date' => '30/5/2025', 'time' => '10.00 A.M', 'status' => 'Rejected']
// ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - My Profile</title>
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

  .profile-icon img {
    width: 35px;
    height: 35px;
    cursor: pointer;
    margin-left: 20px;
  }

  /* Profile Card */
        .profile-card {
            width: 1150px;   /* ubah ikut selera: 900 / 1000 / 1100 */
            margin: 0 auto 40px; /* auto = center kiri kanan */


            background: linear-gradient(135deg, #1a1a1a 0%, #252525 100%);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            border: 2px solid #333;
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 30px;
        }
        
        .profile-left {
            display: flex;
            align-items: center;
            gap: 30px;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: #333;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            border: 3px solid #444;
        }
        
        .profile-info h2 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 10px;
            color: #fff;
        }
        
        .profile-info p {
            font-size: 14px;
            color: #aaa;
            margin: 5px 0;
        }
        
        .profile-actions {
            display: flex;
            gap: 15px;
        }
        
        .btn-edit {
            padding: 12px 30px;
            background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(110, 34, 221, 0.4);
        }
        
        .btn-logout {
            padding: 12px 30px;
            background: #dc2626;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-logout:hover {
            background: #b91c1c;
            transform: translateY(-2px);
        }

  /* Section */
  .section {
    margin-bottom: 50px;
  }

  .section-title {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 20px;
    color: #fff;
  }

  /* Invoices Table */
  .invoices-table {
    width: 100%;
    border-collapse: collapse;
    background: #1a0033;
    border-radius: 8px;
    overflow: hidden;
  }

  .invoices-table thead {
    background: #6e22dd;
  }

  .invoices-table th {
    padding: 12px 15px;
    text-align: center;
    font-weight: 700;
    font-size: 14px;
    color: #fff;
  }

  .invoices-table tbody tr {
    border-bottom: 1px solid #5b00a7;
    transition: background 0.3s ease;
    cursor: pointer;
  }

  .invoices-table tbody tr:hover {
    background: #8700c6;
  }

  .invoices-table td {
    padding: 12px 15px;
    font-size: 14px;
    color: #fff;
    text-align: center;
  }

  .invoices-table th:nth-child(1),
  .invoices-table td:nth-child(1) {
    width: 20%;
  }

  .invoices-table th:nth-child(2),
  .invoices-table td:nth-child(2) {
    width: 80%;
  }

  /* Appointments Table */
  .appointments-table {
    width: 100%;
    border-collapse: collapse;
    background: #1a0033;
    border-radius: 8px;
    overflow: hidden;
  }

  .appointments-table thead {
    background: #6e22dd;
  }

  .appointments-table th {
    padding: 12px 15px;
    text-align: center;
    font-weight: 700;
    font-size: 14px;
    color: #fff;
  }

  .appointments-table tbody tr {
    border-bottom: 1px solid #5b00a7;
    transition: background 0.3s ease;
    cursor: pointer;
  }

  .appointments-table tbody tr:hover {
    background: #8700c6;
  }

  .appointments-table td {
    padding: 12px 15px;
    font-size: 14px;
    color: #fff;
    text-align: center;
  }

  .appointments-table th:nth-child(1),
  .appointments-table td:nth-child(1) {
    width: 10%;
  }

  .appointments-table th:nth-child(2),
  .appointments-table td:nth-child(2) {
    width: 30%;
  }

  .appointments-table th:nth-child(3),
  .appointments-table td:nth-child(3) {
    width: 20%;
  }

  .appointments-table th:nth-child(4),
  .appointments-table td:nth-child(4) {
    width: 20%;
  }

  .appointments-table th:nth-child(5),
  .appointments-table td:nth-child(5) {
    width: 20%;
  }

  .status-badge {
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
  }

  .status-approved {
    background-color: #28a745;
    color: white;
  }

  .status-pending {
    background-color: #ffc107;
    color: #000;
  }

  .status-completed {
    background-color: #17a2b8;
    color: white;
  }

  .status-rejected {
    background-color: #dc3545;
    color: white;
  }

  /* Footer */
  footer {
    text-align: center;
    padding: 15px 20px;
    background-color: #1a1a1a;
    font-size: 12px;
    color: #777;
    margin-top: auto;
    letter-spacing: 0.6px;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .nav-links {
      display: none;
    }

    .profile-header {
      flex-direction: column;
      text-align: center;
    }

    .profile-buttons {
      width: 100%;
      flex-direction: column;
    }

    .btn-edit, .btn-logout {
      width: 100%;
    }

    .appointments-table {
      overflow-x: auto;
    }
  }
</style>
</head>
<body>
  <?php include 'header.php'; ?>
<br><br>
  <div class="profile-card">
            <div class="profile-header">
                <div class="profile-left">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                    </div>
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                        <p><?php echo htmlspecialchars(($user['country_code'] ?? '') . $user['phone_number']); ?></p>
                    </div>
                </div>
                <div class="profile-actions">
                    <a href="edit_profile.php" class="btn-edit">Edit Profile</a>
                    <a href="logout.php" class="btn-logout">Log Out</a>
                </div>
            </div>
        </div>

    <!-- My Invoices -->
<!--     <div class="section">
      <h2 class="section-title">My Invoices</h2>
      <table class="invoices-table">
        <thead>
          <tr>
            <th>Num.</th>
            <th>Invoice Number</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($invoices as $invoice): ?>
          <tr onclick="viewInvoice('<?php echo $invoice['invoice_number']; ?>')">
            <td><?php echo $invoice['num']; ?></td>
            <td><?php echo $invoice['invoice_number']; ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div> -->

    <!-- My Appointments -->
    <!-- <div class="section">
      <h2 class="section-title">My Appointments</h2>
      <table class="appointments-table">
        <thead>
          <tr>
            <th>Num.</th>
            <th>Invoice Number</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($appointments as $appointment): ?>
          <tr onclick="viewAppointment('<?php echo $appointment['invoice_number']; ?>')">
            <td><?php echo $appointment['num']; ?></td>
            <td><?php echo $appointment['invoice_number']; ?></td>
            <td><?php echo $appointment['date']; ?></td>
            <td><?php echo $appointment['time']; ?></td>
            <td>
              <?php
              $status = strtolower(str_replace(' ', '-', $appointment['status']));
              $statusClass = 'status-' . $status;
              if ($status == 'pending-approval') $statusClass = 'status-pending';
              ?>
              <span class="status-badge <?php echo $statusClass; ?>">
                <?php echo $appointment['status']; ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div> -->
  </div>

  <script>
    function viewInvoice(invoiceNumber) {
      // Nanti akan redirect ke view_invoice.php?invoice=xxx
      alert('View invoice: ' + invoiceNumber + ' (Feature coming soon)');
    }

    function viewAppointment(invoiceNumber) {
      // Nanti akan redirect ke view_appointment.php?invoice=xxx
      alert('View appointment: ' + invoiceNumber + ' (Feature coming soon)');
    }
  </script>
  <?php include 'footer.php'; ?>
</body>
</html>