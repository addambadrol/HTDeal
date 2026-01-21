<?php
session_start();
require_once '../db_config.php';

// Check if user is logged in
// if (!isset($_SESSION['account_id']) || $_SESSION['role'] != 'pelanggan') {
//     header("Location: ../landing/loginpelanggan.php");
//     exit();
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - Other Services</title>
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

  main {
    max-width: 1000px;
    margin: 40px auto 60px auto;
    padding: 0 20px;
  }

  .services-container {
    display: grid;
    gap: 25px;
    margin-bottom: 40px;
  }

  .category-section {
    background: #1a1a1a;
    border: 2px solid #333;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    width: 1000px;
  }

  .category-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid rgba(110, 34, 221, 0.3);
  }

  .category-icon {
    font-size: 32px;
  }

  .category-title {
    font-size: 24px;
    font-weight: 800;
    color: #6e22dd;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .service-options {
    display: grid;
    gap: 15px;
  }

  .service-option {
    background: #0a0a0a;
    border: 2px solid #333;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
  }

  .service-option::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 5px;
    background: #333;
    transition: all 0.3s;
  }

  .service-option:hover {
    border-color: #6e22dd;
    transform: translateX(5px);
    box-shadow: 0 4px 15px rgba(110, 34, 221, 0.3);
  }

  .service-option:hover::before {
    background: linear-gradient(180deg, #6e22dd 0%, #5a1bb8 100%);
  }

  .service-option.selected {
    border-color: #6e22dd;
    background: linear-gradient(135deg, rgba(110, 34, 221, 0.15) 0%, rgba(90, 27, 184, 0.15) 100%);
    box-shadow: 0 4px 15px rgba(110, 34, 221, 0.4);
  }

  .service-option.selected::before {
    background: linear-gradient(180deg, #6e22dd 0%, #5a1bb8 100%);
    width: 8px;
  }

  .service-details {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .service-name {
    font-size: 16px;
    font-weight: 700;
    color: #fff;
  }

  .service-desc {
    font-size: 12px;
    color: #888;
    margin-top: 5px;
  }

  .service-price {
    font-size: 20px;
    font-weight: 800;
    color: #6e22dd;
    white-space: nowrap;
    margin-left: 20px;
  }

  .summary-box {
    background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 8px 25px rgba(110, 34, 221, 0.4);
    position: sticky;
    top: 20px;
    width: 1000px;
  }

  .summary-title {
    font-size: 20px;
    font-weight: 800;
    margin-bottom: 20px;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .summary-items {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 20px;
    max-height: 300px;
    overflow-y: auto;
    padding-right: 10px;
  }

  .summary-items::-webkit-scrollbar {
    width: 6px;
  }

  .summary-items::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
  }

  .summary-items::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 10px;
  }

  .summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
  }

  .summary-item-name {
    font-size: 14px;
    font-weight: 600;
  }

  .summary-item-category {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.7);
    margin-top: 3px;
  }

  .summary-item-price {
    font-size: 16px;
    font-weight: 800;
  }

  .summary-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 0;
    border-top: 2px solid rgba(255, 255, 255, 0.2);
    margin-bottom: 20px;
  }

  .summary-total-label {
    font-size: 18px;
    font-weight: 700;
    text-transform: uppercase;
  }

  .summary-total-amount {
    font-size: 32px;
    font-weight: 800;
  }

  .continue-btn {
    width: 100%;
    padding: 18px 0;
    background: #fff;
    color: #6e22dd;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 2px;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 4px 15px rgba(255, 255, 255, 0.2);
  }

  .continue-btn:hover:not(:disabled) {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(255, 255, 255, 0.3);
  }

  .continue-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  .empty-state {
    text-align: center;
    padding: 30px;
    color: rgba(255, 255, 255, 0.6);
    font-size: 14px;
  }

  footer {
    text-align: center;
    padding: 30px 20px;
    background-color: #0a0a0a;
    font-size: 12px;
    color: #666;
    margin-top: auto;
    border-top: 1px solid rgba(110, 34, 221, 0.2);
  }

  @media (max-width: 768px) {
    .service-details {
      flex-direction: column;
      align-items: flex-start;
      gap: 8px;
    }

    .service-price {
      margin-left: 0;
    }

    .summary-box {
      position: static;
    }
  }
  </style>
</head>
<body>
  <?php include 'header.php'; ?>

  <main>
    <div class="services-container" id="servicesContainer"></div>

    <div class="summary-box">
      <div class="summary-title">Selected Services</div>
      <div class="summary-items" id="summaryItems">
        <div class="empty-state">No services selected yet</div>
      </div>
      <div class="summary-total">
        <span class="summary-total-label">Total:</span>
        <span class="summary-total-amount" id="totalAmount">RM 0.00</span>
      </div>
      <button class="continue-btn" id="continueBtn" onclick="continueToAppointment()" disabled>
        Continue to Appointment
      </button>
    </div>
  </main>

  <?php include 'footer.php'; ?>

  <script>
  const serviceCategories = [
    {
      id: 'cleaning',
      name: 'PC Cleaning Services',
      icon: '',
      options: [
        { id: 'minor-cleaning', name: 'Minor Cleaning', price: 50, desc: 'Basic dust removal and surface cleaning' },
        { id: 'major-cleaning', name: 'Major Cleaning', price: 80, desc: 'Deep cleaning including fans and components' },
        { id: 'full-cleaning', name: 'Full Cleaning', price: 120, desc: 'Complete disassembly and thorough cleaning' }
      ]
    },
    {
      id: 'thermal-paste',
      name: 'Thermal Paste Replacement',
      icon: '',
      options: [
        { id: 'thermal-low', name: 'Low Quality Thermal Paste', price: 30, desc: 'Standard thermal compound' },
        { id: 'thermal-normal', name: 'Normal Quality Thermal Paste', price: 50, desc: 'Good quality thermal compound' },
        { id: 'thermal-highest', name: 'Highest Quality Thermal Paste', price: 80, desc: 'Premium thermal compound for best cooling' }
      ]
    },
    {
      id: 'change-part',
      name: 'Component Installation',
      icon: '',
      options: [
        { id: 'install-ram', name: 'RAM Installation', price: 30, desc: 'Install and configure RAM modules' },
        { id: 'install-gpu', name: 'Graphic Card Installation', price: 50, desc: 'Install GPU with driver setup' },
        { id: 'install-cpu', name: 'Processor Installation', price: 60, desc: 'Install CPU with thermal paste application' },
        { id: 'install-psu', name: 'Power Supply Installation', price: 40, desc: 'Install and cable management' },
        { id: 'install-storage', name: 'Storage Installation', price: 35, desc: 'Install SSD/HDD with OS migration option' },
        { id: 'install-casing', name: 'Casing Transfer', price: 100, desc: 'Transfer all components to new casing' },
        { id: 'install-mobo', name: 'Motherboard Installation', price: 80, desc: 'Install motherboard with all connections' }
      ]
    }
  ];

  let selectedServices = {};

  function populateServices() {
    const container = document.getElementById('servicesContainer');
    container.innerHTML = '';

    serviceCategories.forEach(category => {
      const section = document.createElement('div');
      section.className = 'category-section';
      
      let optionsHTML = '';
      category.options.forEach(option => {
        optionsHTML += `
          <div class="service-option" id="option-${option.id}" onclick="toggleService('${category.id}', '${option.id}')">
            <div class="service-details">
              <div>
                <div class="service-name">${option.name}</div>
                <div class="service-desc">${option.desc}</div>
              </div>
              <div class="service-price">RM ${option.price.toFixed(2)}</div>
            </div>
          </div>
        `;
      });

      section.innerHTML = `
        <div class="category-header">
          <span class="category-icon">${category.icon}</span>
          <span class="category-title">${category.name}</span>
        </div>
        <div class="service-options">
          ${optionsHTML}
        </div>
      `;

      container.appendChild(section);
    });
  }

  function toggleService(categoryId, optionId) {
    const category = serviceCategories.find(c => c.id === categoryId);
    const option = category.options.find(o => o.id === optionId);

    if (selectedServices[categoryId] && selectedServices[categoryId].option.id === optionId) {
      delete selectedServices[categoryId];
      category.options.forEach(opt => {
        document.getElementById(`option-${opt.id}`).classList.remove('selected');
      });
    } else {
      selectedServices[categoryId] = {
        categoryName: category.name,
        option: option
      };

      category.options.forEach(opt => {
        const optionElement = document.getElementById(`option-${opt.id}`);
        if (opt.id === optionId) {
          optionElement.classList.add('selected');
        } else {
          optionElement.classList.remove('selected');
        }
      });
    }

    updateSummary();
  }

  function updateSummary() {
    const summaryItems = document.getElementById('summaryItems');
    const totalAmount = document.getElementById('totalAmount');
    const continueBtn = document.getElementById('continueBtn');

    const selectedCount = Object.keys(selectedServices).length;

    if (selectedCount === 0) {
      summaryItems.innerHTML = '<div class="empty-state">No services selected yet</div>';
      totalAmount.textContent = 'RM 0.00';
      continueBtn.disabled = true;
    } else {
      let itemsHTML = '';
      let total = 0;

      Object.values(selectedServices).forEach(service => {
        itemsHTML += `
          <div class="summary-item">
            <div>
              <div class="summary-item-name">${service.option.name}</div>
              <div class="summary-item-category">${service.categoryName}</div>
            </div>
            <span class="summary-item-price">RM ${service.option.price.toFixed(2)}</span>
          </div>
        `;
        total += service.option.price;
      });

      summaryItems.innerHTML = itemsHTML;
      totalAmount.textContent = `RM ${total.toFixed(2)}`;
      continueBtn.disabled = false;
    }
  }

  function continueToAppointment() {
    if (Object.keys(selectedServices).length === 0) {
      alert('Please select at least one service');
      return;
    }

    const formattedServices = Object.values(selectedServices).map(service => ({
      partId: null,
      partCode: service.option.id.toUpperCase(),
      name: service.option.name,
      category: service.categoryName,
      price: service.option.price,
      quantity: 1
    }));

    const total = Object.values(selectedServices).reduce((sum, s) => sum + s.option.price, 0);

    sessionStorage.setItem('selectedBuild', JSON.stringify(formattedServices));
    sessionStorage.setItem('serviceType', 'Other Service');
    sessionStorage.setItem('totalPrice', total);

    window.location.href = 'profile.php';
  }

  populateServices();
  </script>
</body>
</html>