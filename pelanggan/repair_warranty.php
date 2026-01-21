<?php
session_start();
require_once '../db_config.php';

// Check if user is logged in as customer
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'pelanggan') {
    header("Location: ../landing/loginpelanggan.php");
    exit();
}

$account_id = $_SESSION['account_id'];

// Fetch customer info
try {
    $stmt = $pdo->prepare("SELECT first_name, last_name, email FROM account WHERE account_id = ?");
    $stmt->execute([$account_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - Repair & Warranty</title>
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

  .profile-icon img {
    width: 35px;
    height: 35px;
    cursor: pointer;
    margin-left: 20px;
  }

  .page-container {
    max-width: 900px;
    margin: 60px auto;
    padding: 0 20px;
  }

  .page-heading {
    max-width: 1290px;
    margin: 0 auto 30px;
    padding: 0 20px;
    text-align: center;
  }

  .page-heading h2 {
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

  .page-heading h4 {
    font-size: 16px;
    font-weight: 400;
    color: #aaa;
  }

  /* Search & Filter Bar */
  .filter-bar {
    background: rgba(110, 34, 221, 0.1);
    border: 1px solid rgba(110, 34, 221, 0.3);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: center;
  }

  .search-box {
    flex: 1;
    min-width: 250px;
    position: relative;
  }

  .search-box input {
    width: 100%;
    padding: 12px 45px 12px 15px;
    background: #0a0a0a;
    border: 2px solid #333;
    border-radius: 10px;
    color: #fff;
    font-size: 14px;
    transition: all 0.3s;
  }

  .search-box input:focus {
    outline: none;
    border-color: #6e22dd;
    box-shadow: 0 0 0 3px rgba(110, 34, 221, 0.2);
  }

  .search-box::after {
    content: '';
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 16px;
  }

  .filter-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  .filter-btn {
    padding: 10px 20px;
    background: #0a0a0a;
    border: 2px solid #333;
    border-radius: 10px;
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .filter-btn:hover {
    border-color: #6e22dd;
    background: rgba(110, 34, 221, 0.1);
  }

  .filter-btn.active {
    background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
    border-color: #6e22dd;
    box-shadow: 0 4px 15px rgba(110, 34, 221, 0.4);
  }

  .container {
    border: 2px solid #6e22dd;
    border-radius: 15px;
    padding: 40px;
    background: linear-gradient(145deg, #1a1a1a 0%, #252525 100%);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
  }

  form {
    text-align: left;
    display: flex;
    flex-direction: column;
  }

  label {
    font-weight: 700;
    display: block;
    margin-bottom: 10px;
    font-size: 14px;
    color: #fff;
  }

  label span {
    color: #ff4444;
  }

  .invoice-selector {
    margin-bottom: 25px;
  }

  .invoice-list {
    display: grid;
    gap: 15px;
    max-height: 450px;
    overflow-y: auto;
    padding-right: 10px;
  }

  .invoice-list::-webkit-scrollbar {
    width: 8px;
  }

  .invoice-list::-webkit-scrollbar-track {
    background: #0a0a0a;
    border-radius: 4px;
  }

  .invoice-list::-webkit-scrollbar-thumb {
    background: #6e22dd;
    border-radius: 4px;
  }

  .invoice-item {
    background: #0a0a0a;
    border: 2px solid #333;
    border-radius: 12px;
    padding: 18px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    overflow: hidden;
  }

  .invoice-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 5px;
    background: #333;
    transition: all 0.3s;
  }

  .invoice-item.warranty-valid::before {
    background: linear-gradient(180deg, #22c55e 0%, #16a34a 100%);
  }

  .invoice-item.warranty-expired::before {
    background: linear-gradient(180deg, #ef4444 0%, #dc2626 100%);
  }

  .invoice-item:hover {
    border-color: #6e22dd;
    background: rgba(110, 34, 221, 0.1);
    transform: translateX(5px);
  }

  .invoice-item.selected {
    border-color: #6e22dd;
    background: rgba(110, 34, 221, 0.2);
    box-shadow: 0 0 0 3px rgba(110, 34, 221, 0.2);
    transform: translateX(5px);
  }

  .invoice-info {
    flex: 1;
  }

  .invoice-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
  }

  .invoice-number {
    font-weight: 700;
    font-size: 17px;
    color: #6e22dd;
  }

  .warranty-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
  }

  .warranty-badge.valid {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    color: #fff;
    box-shadow: 0 2px 8px rgba(34, 197, 94, 0.3);
  }

  .warranty-badge.expired {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #fff;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
  }

  .invoice-meta {
    font-size: 12px;
    color: #888;
    margin-bottom: 8px;
  }

  .invoice-meta span {
    margin-right: 15px;
  }

  .warranty-info {
    font-size: 11px;
    color: #aaa;
    padding: 8px 12px;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 6px;
    margin-top: 8px;
    display: inline-block;
  }

  .warranty-info.valid {
    border-left: 3px solid #22c55e;
    color: #22c55e;
  }

  .warranty-info.expired {
    border-left: 3px solid #ef4444;
    color: #ef4444;
  }

  .warranty-info.expiring-soon {
    border-left: 3px solid #fbbf24;
    color: #fbbf24;
  }

  .invoice-actions {
    display: flex;
    gap: 10px;
  }

  .btn-view {
    background: rgba(110, 34, 221, 0.2);
    border: 1px solid #6e22dd;
    color: #6e22dd;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
  }

  .btn-view:hover {
    background: #6e22dd;
    color: #fff;
  }

  /* Empty State */
  .empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #888;
  }

  .empty-state-icon {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.5;
  }

  .empty-state h3 {
    font-size: 20px;
    margin-bottom: 10px;
    color: #aaa;
  }

  .empty-state p {
    font-size: 14px;
  }

  /* Modal */
  .modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(5px);
    overflow-y: auto;
    padding: 20px;
  }

  .modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .modal-content {
    background: linear-gradient(135deg, #1a1a1a 0%, #252525 100%);
    border: 2px solid #6e22dd;
    border-radius: 20px;
    width: 100%;
    max-width: 700px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(110, 34, 221, 0.5);
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
    background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
    padding: 20px 25px;
    border-radius: 18px 18px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .modal-title {
    font-size: 20px;
    font-weight: 800;
    color: #fff;
    text-transform: uppercase;
  }

  .close-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    font-size: 22px;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
  }

  .close-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
  }

  .modal-body {
    padding: 25px;
  }

  .detail-section {
    margin-bottom: 20px;
  }

  .detail-section-title {
    font-size: 14px;
    font-weight: 700;
    color: #6e22dd;
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    background: rgba(110, 34, 221, 0.05);
    padding: 15px;
    border-radius: 10px;
    border: 1px solid rgba(110, 34, 221, 0.2);
  }

  .detail-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
  }

  .detail-label {
    font-size: 11px;
    color: #888;
    text-transform: uppercase;
  }

  .detail-value {
    font-size: 13px;
    color: #fff;
    font-weight: 600;
  }

  .items-table {
    width: 100%;
    border-collapse: collapse;
    background: rgba(110, 34, 221, 0.05);
    border-radius: 10px;
    overflow: hidden;
  }

  .items-table thead {
    background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
  }

  .items-table th {
    padding: 10px;
    text-align: left;
    font-size: 11px;
    text-transform: uppercase;
  }

  .items-table td {
    padding: 10px;
    border-bottom: 1px solid rgba(110, 34, 221, 0.1);
    font-size: 13px;
  }

  .items-table tbody tr:last-child td {
    border-bottom: none;
  }

  textarea {
    width: 100%;
    min-height: 120px;
    border-radius: 12px;
    border: 2px solid #333;
    background: #0a0a0a;
    color: #fff;
    padding: 12px 15px;
    margin-bottom: 25px;
    font-size: 15px;
    resize: vertical;
    box-sizing: border-box;
    font-family: inherit;
    transition: all 0.3s ease;
  }

  textarea:focus {
    outline: none;
    border-color: #6e22dd;
    box-shadow: 0 0 0 3px rgba(110, 34, 221, 0.2);
  }

  textarea::placeholder {
    color: #666;
  }

  .upload-area {
    border: 2px dashed #6e22dd;
    border-radius: 12px;
    padding: 30px;
    text-align: center;
    background: rgba(110, 34, 221, 0.05);
    margin-bottom: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .upload-area:hover {
    background: rgba(110, 34, 221, 0.1);
    border-color: #8b4dff;
  }

  .upload-icon {
    font-size: 48px;
    margin-bottom: 15px;
    color: #6e22dd;
  }

  .upload-text {
    font-size: 14px;
    color: #aaa;
    margin-bottom: 8px;
  }

  .upload-hint {
    font-size: 12px;
    color: #666;
  }

  input[type="file"] {
    display: none;
  }

  .preview-area {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 15px;
    margin-bottom: 25px;
  }

  .preview-item {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid #333;
    aspect-ratio: 1;
  }

  .preview-item img,
  .preview-item video {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .remove-btn {
    position: absolute;
    top: 5px;
    right: 5px;
    background: rgba(239, 68, 68, 0.9);
    border: none;
    width: 25px;
    height: 25px;
    border-radius: 50%;
    color: white;
    cursor: pointer;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
  }

  .remove-btn:hover {
    background: #dc2626;
    transform: scale(1.1);
  }

  button[type="submit"] {
    background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
    border: none;
    padding: 15px 0;
    border-radius: 12px;
    color: white;
    font-weight: 800;
    cursor: pointer;
    font-size: 16px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    transition: all 0.3s ease;
    box-shadow: 0 6px 20px rgba(110, 34, 221, 0.4);
  }

  button[type="submit"]:hover {
    background: linear-gradient(135deg, #8b4dff 0%, #6e22dd 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(110, 34, 221, 0.5);
  }

  button[type="submit"]:active {
    transform: translateY(0);
  }

  button[type="submit"]:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  footer {
    text-align: center;
    padding: 30px 20px;
    background-color: #1a1a1a;
    font-size: 12px;
    color: #777;
    margin-top: auto;
    letter-spacing: 0.6px;
  }

  @media (max-width: 768px) {
    .nav-links {
      display: none;
    }

    .page-heading h2 {
      font-size: 28px;
    }

    .container {
      padding: 25px 20px;
    }

    .detail-grid {
      grid-template-columns: 1fr;
    }

    .filter-bar {
      flex-direction: column;
    }

    .search-box {
      width: 100%;
    }

    .invoice-header {
      flex-direction: column;
      align-items: flex-start;
      gap: 8px;
    }

    .invoice-actions {
      flex-direction: column;
      width: 100%;
    }

    .btn-view {
      width: 100%;
    }

    .preview-area {
      grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    }
  }
</style>
</head>
<body>
  <?php include 'header.php'; ?>

  <div class="page-container">
    <div class="page-heading">
      <h2>REPAIR & WARRANTY</h2>
      <h4>Submit a claim for warranty repair or paid repair service</h4>
    </div>

    <!-- Search & Filter Bar -->
    <div class="filter-bar">
      <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search invoice number..." />
      </div>
      <div class="filter-buttons">
        <button class="filter-btn active" data-filter="all">All</button>
        <button class="filter-btn" data-filter="valid">Under Warranty</button>
        <button class="filter-btn" data-filter="expired">Warranty Expired</button>
      </div>
    </div>

    <div class="container">
      <form id="warrantyForm" onsubmit="handleSubmit(event)">
        <div class="invoice-selector">
          <label>Select Invoice <span>*</span></label>
          <div class="invoice-list" id="invoiceList">
            <p style="text-align: center; color: #888; padding: 20px;">Loading your invoices...</p>
          </div>
        </div>

        <label for="reason">Describe the Issue <span>*</span></label>
        <textarea 
          id="reason" 
          name="reason" 
          placeholder="Describe the issue with your product in detail..."
          required
        ></textarea>

        <label>Upload Photos/Videos <span>*</span></label>
        <div class="upload-area" onclick="document.getElementById('fileInput').click()">
          <div class="upload-icon"></div>
          <div class="upload-text">Click to upload photos or videos</div>
          <div class="upload-hint">Supports: JPG, PNG, MP4, MOV (Max 10MB each)</div>
        </div>
        <input 
          type="file" 
          id="fileInput" 
          accept="image/*,video/*" 
          multiple 
          onchange="handleFiles(event)"
        />

        <div id="previewArea" class="preview-area"></div>

        <button type="submit" id="submitBtn" disabled>Submit Claim</button>
      </form>
    </div>
  </div>

  <!-- Invoice Details Modal -->
  <div id="invoiceModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">Invoice Details</h3>
        <button class="close-btn" onclick="closeModal()">&times;</button>
      </div>
      <div class="modal-body" id="modalBody">
        <p style="text-align: center; padding: 40px; color: #888;">Loading...</p>
      </div>
    </div>
  </div>

  <?php include 'footer.php'; ?>

  <script>
    let selectedInvoice = null;
    let selectedInvoiceWarrantyStatus = false;
    let selectedFiles = [];
    let customerInvoices = [];
    let currentFilter = 'all';

    document.addEventListener('DOMContentLoaded', function() {
      loadCustomerInvoices();
      setupFilterButtons();
      setupSearch();
    });

    function loadCustomerInvoices() {
      fetch('get_customer_invoices.php')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            customerInvoices = data.invoices;
            displayInvoices(data.invoices);
          } else {
            document.getElementById('invoiceList').innerHTML = 
              '<div class="empty-state"><div class="empty-state-icon"></div><h3>No Invoices Found</h3><p>' + 
              (data.message || 'You need to purchase products first.') + '</p></div>';
          }
        })
        .catch(error => {
          console.error('Error:', error);
          document.getElementById('invoiceList').innerHTML = 
            '<div class="empty-state"><div class="empty-state-icon"></div><h3>Error Loading Invoices</h3><p>Please try again later.</p></div>';
        });
    }

    function displayInvoices(invoices) {
      const invoiceList = document.getElementById('invoiceList');
      
      if (invoices.length === 0) {
        invoiceList.innerHTML = '<div class="empty-state"><div class="empty-state-icon"></div><h3>No Invoices Found</h3><p>You need to purchase products from our store first.</p></div>';
        return;
      }

      invoiceList.innerHTML = '';
      
      invoices.forEach(invoice => {
        const invoiceDate = new Date(invoice.date);
        const isWarrantyValid = invoice.warranty_valid;
        const warrantyEndDate = invoice.warranty_end_date ? new Date(invoice.warranty_end_date) : null;
        const daysRemaining = invoice.warranty_days_remaining || 0;
        
        // Warranty badge
        let warrantyBadge = '';
        let warrantyInfoText = '';
        let warrantyInfoClass = '';
        
        if (isWarrantyValid) {
          warrantyBadge = '<span class="warranty-badge valid">UNDER WARRANTY</span>';
          
          if (daysRemaining <= 30) {
            warrantyInfoClass = 'expiring-soon';
            warrantyInfoText = `Warranty expires in ${daysRemaining} days (${warrantyEndDate.toLocaleDateString('en-GB')})`;
          } else {
            warrantyInfoClass = 'valid';
            warrantyInfoText = `Valid until ${warrantyEndDate.toLocaleDateString('en-GB')} (${daysRemaining} days remaining)`;
          }
        } else {
          warrantyBadge = '<span class="warranty-badge expired">WARRANTY EXPIRED</span>';
          warrantyInfoClass = 'expired';
          warrantyInfoText = warrantyEndDate 
            ? `Expired on ${warrantyEndDate.toLocaleDateString('en-GB')}`
            : 'No warranty information';
        }
        
        const invoiceItem = document.createElement('div');
        invoiceItem.className = 'invoice-item ' + (isWarrantyValid ? 'warranty-valid' : 'warranty-expired');
        invoiceItem.dataset.invoiceId = invoice.invoice_number;
        invoiceItem.dataset.warrantyValid = isWarrantyValid;
        invoiceItem.dataset.filter = isWarrantyValid ? 'valid' : 'expired';
        
        invoiceItem.innerHTML = `
          <div class="invoice-info">
            <div class="invoice-header">
              <div class="invoice-number">${invoice.invoice_number}</div>
              ${warrantyBadge}
            </div>
            <div class="invoice-meta">
              <span>${invoiceDate.toLocaleDateString('en-GB')}</span>
              <span>RM ${parseFloat(invoice.total_amount).toFixed(2)}</span>
            </div>
            <div class="warranty-info ${warrantyInfoClass}">
              ${warrantyInfoText}
            </div>
          </div>
          <div class="invoice-actions">
            <button type="button" class="btn-view" onclick="viewInvoiceDetails('${invoice.invoice_number}', event)">
            View
            </button>
          </div>
        `;
        
        invoiceItem.addEventListener('click', function(e) {
          if (!e.target.classList.contains('btn-view')) {
            selectInvoice(invoice.invoice_number, isWarrantyValid);
          }
        });
        
        invoiceList.appendChild(invoiceItem);
      });

      applyFilter(currentFilter);
    }

    function selectInvoice(invoiceNumber, warrantyValid) {
      selectedInvoice = invoiceNumber;
      selectedInvoiceWarrantyStatus = warrantyValid;
      
      document.querySelectorAll('.invoice-item').forEach(item => {
        item.classList.remove('selected');
      });
      
      const selectedItem = document.querySelector(`[data-invoice-id="${invoiceNumber}"]`);
      if (selectedItem) {
        selectedItem.classList.add('selected');
      }
      
      updateSubmitButton();
    }

    function setupFilterButtons() {
      const filterButtons = document.querySelectorAll('.filter-btn');
      filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
          filterButtons.forEach(b => b.classList.remove('active'));
          this.classList.add('active');
          currentFilter = this.dataset.filter;
          applyFilter(currentFilter);
        });
      });
    }

    function applyFilter(filter) {
      const invoiceList = document.getElementById('invoiceList');
      const items = invoiceList.querySelectorAll('.invoice-item');
      
      let visibleCount = 0;
      
      items.forEach(item => {
        if (filter === 'all') {
          item.style.display = 'flex';
          visibleCount++;
        } else {
          if (item.dataset.filter === filter) {
            item.style.display = 'flex';
            visibleCount++;
          } else {
            item.style.display = 'none';
          }
        }
      });

      // Only show empty state if NO items visible AND we actually have items
      if (visibleCount === 0 && items.length > 0) {
        // Create empty state without removing existing items
        let emptyState = invoiceList.querySelector('.empty-state');
        if (!emptyState) {
          emptyState = document.createElement('div');
          emptyState.className = 'empty-state';
          emptyState.innerHTML = '<div class="empty-state-icon"></div><h3>No Results Found</h3><p>No invoices match your filter criteria.</p>';
          invoiceList.appendChild(emptyState);
        }
        emptyState.style.display = 'block';
      } else {
        // Remove empty state if exists
        const emptyState = invoiceList.querySelector('.empty-state');
        if (emptyState) {
          emptyState.style.display = 'none';
        }
      }
    }

    function setupSearch() {
      const searchInput = document.getElementById('searchInput');
      searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const invoiceList = document.getElementById('invoiceList');
        const items = invoiceList.querySelectorAll('.invoice-item');
        
        let visibleCount = 0;
        
        items.forEach(item => {
          const invoiceNumber = item.dataset.invoiceId.toLowerCase();
          const matchesSearch = invoiceNumber.includes(searchTerm);
          const matchesFilter = currentFilter === 'all' || item.dataset.filter === currentFilter;
          
          if (matchesSearch && matchesFilter) {
            item.style.display = 'flex';
            visibleCount++;
          } else {
            item.style.display = 'none';
          }
        });

        // Show/hide empty state
        if (visibleCount === 0 && items.length > 0) {
          let emptyState = invoiceList.querySelector('.empty-state');
          if (!emptyState) {
            emptyState = document.createElement('div');
            emptyState.className = 'empty-state';
            emptyState.innerHTML = '<div class="empty-state-icon"></div><h3>No Results Found</h3><p>Try adjusting your search or filter.</p>';
            invoiceList.appendChild(emptyState);
          }
          emptyState.style.display = 'block';
        } else {
          const emptyState = invoiceList.querySelector('.empty-state');
          if (emptyState) {
            emptyState.style.display = 'none';
          }
        }
      });
    }

    function viewInvoiceDetails(invoiceNumber, event) {
      event.stopPropagation();
      
      const modal = document.getElementById('invoiceModal');
      const modalBody = document.getElementById('modalBody');
      
      modal.classList.add('active');
      modalBody.innerHTML = '<p style="text-align: center; padding: 40px; color: #888;">Loading...</p>';
      
      fetch(`get_invoice_details.php?invoice=${invoiceNumber}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            displayInvoiceDetails(data.invoice);
          } else {
            modalBody.innerHTML = '<p style="text-align: center; padding: 40px; color: #ef4444;">Error loading invoice details</p>';
          }
        })
        .catch(error => {
          console.error('Error:', error);
          modalBody.innerHTML = '<p style="text-align: center; padding: 40px; color: #ef4444;">Error loading invoice details</p>';
        });
    }

    function displayInvoiceDetails(invoice) {
      const modalBody = document.getElementById('modalBody');
      
      let itemsHTML = '';
      if (invoice.items && invoice.items.length > 0) {
        itemsHTML = `
          <table class="items-table">
            <thead>
              <tr>
                <th>Part</th>
                <th>Qty</th>
                <th style="text-align: right;">Price</th>
              </tr>
            </thead>
            <tbody>
        `;
        
        invoice.items.forEach(item => {
          itemsHTML += `
            <tr>
              <td><strong>${item.part_name}</strong><br><small style="color: #888;">${item.category}</small></td>
              <td>${item.quantity}</td>
              <td style="text-align: right;">RM ${parseFloat(item.total_price).toFixed(2)}</td>
            </tr>
          `;
        });
        
        itemsHTML += '</tbody></table>';
      }
      
      modalBody.innerHTML = `
        <div class="detail-section">
          <h4 class="detail-section-title">Invoice Information</h4>
          <div class="detail-grid">
            <div class="detail-item">
              <span class="detail-label">Invoice Number</span>
              <span class="detail-value">${invoice.invoice_number}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Date</span>
              <span class="detail-value">${new Date(invoice.date).toLocaleDateString('en-GB')}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Service Type</span>
              <span class="detail-value">${invoice.service_type}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Total Amount</span>
              <span class="detail-value">RM ${parseFloat(invoice.total_amount).toFixed(2)}</span>
            </div>
          </div>
        </div>
        
        <div class="detail-section">
          <h4 class="detail-section-title">Items Purchased</h4>
          ${itemsHTML}
        </div>
      `;
    }

    function closeModal() {
      document.getElementById('invoiceModal').classList.remove('active');
    }

    function handleFiles(event) {
      const files = Array.from(event.target.files);
      const previewArea = document.getElementById('previewArea');
      
      files.forEach((file, index) => {
        if (file.size > 10 * 1024 * 1024) {
          alert(`File ${file.name} is too large. Maximum size is 10MB.`);
          return;
        }

        const isImage = file.type.startsWith('image/');
        const isVideo = file.type.startsWith('video/');
        
        if (!isImage && !isVideo) {
          alert(`File ${file.name} is not supported. Please upload images or videos only.`);
          return;
        }

        const fileId = Date.now() + index;
        selectedFiles.push({ id: fileId, file: file });

        const reader = new FileReader();
        reader.onload = function(e) {
          const previewItem = document.createElement('div');
          previewItem.className = 'preview-item';
          previewItem.dataset.fileId = fileId;

          if (isImage) {
            previewItem.innerHTML = `
              <img src="${e.target.result}" alt="Preview">
              <button type="button" class="remove-btn" onclick="removeFile(${fileId})">×</button>
            `;
          } else if (isVideo) {
            previewItem.innerHTML = `
              <video src="${e.target.result}" muted></video>
              <button type="button" class="remove-btn" onclick="removeFile(${fileId})">×</button>
            `;
          }

          previewArea.appendChild(previewItem);
          updateSubmitButton();
        };

        reader.readAsDataURL(file);
      });

      event.target.value = '';
    }

    function removeFile(fileId) {
      selectedFiles = selectedFiles.filter(f => f.id !== fileId);
      
      const previewItem = document.querySelector(`[data-file-id="${fileId}"]`);
      if (previewItem) {
        previewItem.remove();
      }
      
      updateSubmitButton();
    }

    function updateSubmitButton() {
      const submitBtn = document.getElementById('submitBtn');
      const reason = document.getElementById('reason').value.trim();
      
      if (selectedInvoice && reason.length > 0 && selectedFiles.length > 0) {
        submitBtn.disabled = false;
      } else {
        submitBtn.disabled = true;
      }
    }

    document.getElementById('reason').addEventListener('input', updateSubmitButton);

    function handleSubmit(event) {
      event.preventDefault();

      const reason = document.getElementById('reason').value;

      if (!selectedInvoice) {
        alert('Please select an invoice.');
        return;
      }

      if (selectedFiles.length === 0) {
        alert('Please upload at least one photo or video of the issue.');
        return;
      }

      const formData = new FormData();
      formData.append('invoice', selectedInvoice);
      formData.append('reason', reason);
      formData.append('warranty_valid', selectedInvoiceWarrantyStatus);
      
      selectedFiles.forEach((item, index) => {
        formData.append(`file_${index}`, item.file);
      });

      const submitBtn = document.getElementById('submitBtn');
      submitBtn.disabled = true;
      submitBtn.textContent = 'Submitting...';

      fetch('submit_warranty.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          window.location.href = 'warranty_success.php?warranty_id=' + data.warranty_id;
        } else {
          alert('Error: ' + data.message);
          submitBtn.disabled = false;
          submitBtn.textContent = 'Submit Claim';
        }
      })
      .catch(error => {
        alert('Error submitting claim. Please try again.');
        console.error('Error:', error);
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Claim';
      });
    }

    window.onclick = function(event) {
      const modal = document.getElementById('invoiceModal');
      if (event.target === modal) {
        closeModal();
      }
    }
  </script>
</body>
</html>