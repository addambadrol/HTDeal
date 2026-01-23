<?php
session_start();
require_once '../db_config.php';

// Check if user is logged in
if (!isset($_SESSION['account_id']) || $_SESSION['role'] != 'pelanggan') {
    header("Location: ../landing/loginpelanggan.php");
    exit();
}

// Check if this is a warranty appointment
$warranty_id = isset($_GET['warranty_id']) ? intval($_GET['warranty_id']) : 0;
$warranty_claim = null;

if ($warranty_id > 0) {
    // Fetch warranty claim details
    try {
        $stmt = $pdo->prepare("
            SELECT wc.*, a.invoice_number, a.total_amount
            FROM warranty_claims wc
            LEFT JOIN appointments a ON wc.invoice_number = a.invoice_number
            WHERE wc.warranty_id = ? AND wc.account_id = ?
        ");
        $stmt->execute([$warranty_id, $_SESSION['account_id']]);
        $warranty_claim = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify warranty is approved
        if (!$warranty_claim || $warranty_claim['claim_status'] !== 'approved') {
            header("Location: warranty_status.php");
            exit();
        }
        
        // Check if already scheduled
        if ($warranty_claim['appointment_id']) {
            header("Location: warranty_status.php");
            exit();
        }
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - <?php echo $warranty_claim ? 'Schedule Repair' : 'Appointment'; ?></title>
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

  .page-header {
    max-width: 1290px;
    margin: 50px auto 30px;
    padding: 0 20px;
    text-align: center;
  }

  .page-header h1 {
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

  .page-header p {
    font-size: 16px;
    color: #aaa;
  }

  main {
    max-width: 900px;
    margin: 40px auto 60px auto;
    padding: 0 20px;
  }

  /* Warranty Info Box */
  .warranty-info-box {
    background: rgba(110, 34, 221, 0.1);
    border: 2px solid #6e22dd;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 30px;
  }

  .warranty-info-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid rgba(110, 34, 221, 0.3);
  }

  .warranty-info-title {
    font-size: 18px;
    font-weight: 700;
    color: #6e22dd;
    text-transform: uppercase;
  }

  .warranty-details {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
  }

  .warranty-detail-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
  }

  .warranty-label {
    font-size: 12px;
    color: #888;
    text-transform: uppercase;
  }

  .warranty-value {
    font-size: 15px;
    color: #fff;
    font-weight: 600;
  }

  .warranty-reason {
    grid-column: 1 / -1;
    background: rgba(0, 0, 0, 0.3);
    padding: 15px;
    border-radius: 8px;
    margin-top: 10px;
  }

  .appointment-container {
    background: #1a1a1a;
    border: 2px solid #6e22dd;
    border-radius: 20px;
    padding: 50px;
    box-shadow: 0 10px 40px rgba(110, 34, 221, 0.3);
  }

  .section-title {
    font-size: 18px;
    font-weight: 700;
    color: #6e22dd;
    margin-bottom: 25px;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .section-title::before {
    content: '';
    font-size: 24px;
  }

  .scroll-section {
    margin-bottom: 50px;
  }

  .scroll-row {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
  }

  .scroll-btn {
    background: rgba(110, 34, 221, 0.2);
    border: 2px solid #6e22dd;
    border-radius: 50%;
    width: 45px;
    height: 45px;
    cursor: pointer;
    font-size: 20px;
    color: #6e22dd;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    flex-shrink: 0;
  }

  .scroll-btn:hover {
    background: #6e22dd;
    color: white;
    transform: scale(1.1);
  }

  #dateScroll, #timeScroll {
    display: flex;
    overflow-x: scroll !important;
    -webkit-overflow-scrolling: touch;
    gap: 15px;
    padding: 10px 5px;
    scrollbar-width: none;
    scroll-behavior: auto;
    flex: 1;
  }

  #dateScroll::-webkit-scrollbar,
  #timeScroll::-webkit-scrollbar {
    display: none;
  }

  .date-btn {
    min-width: 100px;
    padding: 15px 10px;
    border-radius: 15px;
    border: 2px solid #333;
    cursor: pointer;
    background: linear-gradient(135deg, #1a1a1a 0%, #252525 100%);
    color: #fff;
    text-align: center;
    transition: all 0.3s ease;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
  }

  .date-btn:hover {
    border-color: #6e22dd;
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(110, 34, 221, 0.4);
  }

  .date-btn.selected {
    background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
    border-color: #6e22dd;
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(110, 34, 221, 0.6);
  }

  .date-day {
    font-size: 11px;
    font-weight: 600;
    opacity: 0.8;
    margin-bottom: 5px;
    letter-spacing: 1px;
  }

  .date-number {
    font-size: 28px;
    font-weight: 800;
    margin-bottom: 5px;
  }

  .date-month {
    font-size: 11px;
    font-weight: 600;
    opacity: 0.8;
    letter-spacing: 1px;
  }

  .time-btn {
    min-width: 90px;
    padding: 18px 15px;
    border-radius: 15px;
    border: 2px solid #333;
    cursor: pointer;
    background: linear-gradient(135deg, #1a1a1a 0%, #252525 100%);
    color: #fff;
    font-weight: 700;
    font-size: 18px;
    text-align: center;
    transition: all 0.3s ease;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
  }

  .time-btn:hover {
    border-color: #6e22dd;
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(110, 34, 221, 0.4);
  }

  .time-btn.selected {
    background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
    border-color: #6e22dd;
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(110, 34, 221, 0.6);
  }

  /* REFERENCE CODE SECTION */
  .reference-code-section {
    margin-bottom: 50px;
  }

  .reference-code-container {
    background: rgba(110, 34, 221, 0.05);
    border: 2px solid rgba(110, 34, 221, 0.3);
    border-radius: 15px;
    padding: 25px;
    margin-top: 15px;
  }

  .reference-code-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
  }

  .reference-code-header span:first-child {
    font-size: 24px;
  }

  .reference-code-header h3 {
    font-size: 14px;
    color: #aaa;
    font-weight: 500;
  }

  .reference-input-group {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
  }

  .reference-input {
    flex: 1;
    background: #1a1a1a;
    border: 2px solid #333;
    border-radius: 10px;
    padding: 15px;
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 2px;
    transition: all 0.3s ease;
  }

  .reference-input:focus {
    outline: none;
    border-color: #6e22dd;
    box-shadow: 0 0 15px rgba(110, 34, 221, 0.3);
  }

  .reference-input::placeholder {
    text-transform: none;
    letter-spacing: normal;
    color: #666;
  }

  .validate-btn {
    background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
    border: none;
    border-radius: 10px;
    padding: 15px 30px;
    color: white;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
  }

  .validate-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(110, 34, 221, 0.4);
  }

  .validate-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  .reference-result {
    padding: 15px;
    border-radius: 10px;
    margin-top: 15px;
    display: none;
  }

  .reference-result.success {
    background: rgba(34, 197, 94, 0.1);
    border: 2px solid #22c55e;
    display: block;
  }

  .reference-result.error {
    background: rgba(239, 68, 68, 0.1);
    border: 2px solid #ef4444;
    display: block;
  }

  .reference-result-content {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .reference-result-icon {
    font-size: 24px;
  }

  .reference-result-text {
    flex: 1;
  }

  .reference-result-text h4 {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 5px;
  }

  .reference-result.success h4 {
    color: #22c55e;
  }

  .reference-result.error h4 {
    color: #ef4444;
  }

  .reference-result-text p {
    font-size: 14px;
    color: #aaa;
  }

  .skip-reference {
    text-align: center;
    margin-top: 15px;
  }

  .skip-reference button {
    background: transparent;
    border: none;
    color: white;
    font-size: 14px;
    cursor: pointer;
    text-decoration: underline;
    transition: color 0.3s ease;
  }

  .skip-reference button:hover {
    color: #6e22dd;
  }

  .selection-info {
    background: rgba(110, 34, 221, 0.1);
    border: 1px solid rgba(110, 34, 221, 0.3);
    border-radius: 15px;
    padding: 20px;
    margin-top: 30px;
    text-align: center;
  }

  .selection-info h3 {
    font-size: 16px;
    color: #6e22dd;
    margin-bottom: 15px;
    font-weight: 700;
  }

  .selection-details {
    display: flex;
    justify-content: center;
    gap: 30px;
    flex-wrap: wrap;
  }

  .selection-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
  }

  .selection-label {
    font-size: 12px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .selection-value {
    font-size: 18px;
    font-weight: 700;
    color: #fff;
  }

  .confirm-btn {
    width: 100%;
    background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
    border: none;
    padding: 18px 0;
    border-radius: 15px;
    color: white;
    font-weight: 800;
    cursor: pointer;
    font-size: 16px;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-top: 40px;
    transition: all 0.3s ease;
    box-shadow: 0 6px 20px rgba(110, 34, 221, 0.4);
  }

  .confirm-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(110, 34, 221, 0.6);
  }

  .confirm-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
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
    .page-header h1 {
      font-size: 28px;
    }

    .appointment-container {
      padding: 30px 20px;
    }

    .warranty-details {
      grid-template-columns: 1fr;
    }

    .reference-input-group {
      flex-direction: column;
    }
  }
  </style>
</head>
<body>
  <?php include 'header.php'; ?>

  <div class="page-header">
    <h1> <?php echo $warranty_claim ? 'Schedule Repair Appointment' : 'Schedule Your Appointment'; ?></h1>
    <p><?php echo $warranty_claim ? 'Choose your preferred date and time for repair' : 'Choose your preferred date and time for the service'; ?></p>
  </div>

  <main>
    <?php if ($warranty_claim): ?>
    <!-- Warranty Information Box -->
    <div class="warranty-info-box">
      <div class="warranty-info-header">
        <span style="font-size: 24px;"></span>
        <span class="warranty-info-title">Warranty Claim Details</span>
      </div>
      <div class="warranty-details">
        <div class="warranty-detail-item">
          <span class="warranty-label">Claim ID</span>
          <span class="warranty-value">#<?php echo $warranty_claim['warranty_id']; ?></span>
        </div>
        <div class="warranty-detail-item">
          <span class="warranty-label">Invoice Number</span>
          <span class="warranty-value"><?php echo $warranty_claim['invoice_number']; ?></span>
        </div>
        <div class="warranty-detail-item">
          <span class="warranty-label">Status</span>
          <span class="warranty-value" style="color: #22c55e;">✓ Approved</span>
        </div>
        <div class="warranty-detail-item">
          <span class="warranty-label">Submitted</span>
          <span class="warranty-value"><?php echo date('d M Y', strtotime($warranty_claim['created_at'])); ?></span>
        </div>
        <div class="warranty-reason">
          <span class="warranty-label">Issue Description</span>
          <p class="warranty-value" style="margin-top: 10px;"><?php echo htmlspecialchars($warranty_claim['claim_reason']); ?></p>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <div class="appointment-container">
      <!-- DATE SELECTION -->
      <div class="scroll-section">
        <div class="section-title">Select Date</div>
        <div class="scroll-row">
          <button class="scroll-btn" onclick="scrollLeft('dateScroll')">◀</button>
          <div id="dateScroll"></div>
          <button class="scroll-btn" onclick="scrollRight('dateScroll')">▶</button>
        </div>
      </div>

      <!-- TIME SELECTION -->
      <div class="scroll-section">
        <div class="section-title">Select Time</div>
        <div class="scroll-row">
          <button class="scroll-btn" onclick="scrollLeft('timeScroll')">◀</button>
          <div id="timeScroll"></div>
          <button class="scroll-btn" onclick="scrollRight('timeScroll')">▶</button>
        </div>
      </div>

      <!-- REFERENCE CODE SECTION (Only show if NOT warranty) -->
      <?php if (!$warranty_claim): ?>
      <div class="reference-code-section">
        <div class="section-title">Reference Code (Optional)</div>
        <div class="reference-code-container">
          <div class="reference-code-header">
            <span></span>
            <h3>Have a reference code from a seller? Enter it here to support them!</h3>
          </div>
          
          <div class="reference-input-group">
            <input 
              type="text" 
              id="referenceCodeInput" 
              class="reference-input" 
              placeholder="e.g., htd001"
              maxlength="10"
            />
            <button class="validate-btn" onclick="validateReferenceCode()">
              Validate Code
            </button>
          </div>

          <div id="referenceResult" class="reference-result"></div>

          <div class="skip-reference">
              <button id="skipBtn" onclick="toggleSkipReference()">Skip - Continue without reference code</button>
          </div>

        </div>
      </div>
      <?php endif; ?>

      <!-- SELECTION SUMMARY -->
      <div class="selection-info" id="selectionInfo" style="display: none;">
        <h3>Your Selection</h3>
        <div class="selection-details">
          <div class="selection-item">
            <span class="selection-label">Date</span>
            <span class="selection-value" id="selectedDateDisplay">-</span>
          </div>
          <div class="selection-item">
            <span class="selection-label">Time</span>
            <span class="selection-value" id="selectedTimeDisplay">-</span>
          </div>
          <?php if (!$warranty_claim): ?>
          <div class="selection-item" id="referenceDisplay" style="display: none;">
            <span class="selection-label">Reference Code</span>
            <span class="selection-value" id="selectedReferenceDisplay">-</span>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <button class="confirm-btn" id="confirmBtn" onclick="confirmAppointment()">
        Confirm Appointment
      </button>
    </div>
  </main>

  <?php include 'footer.php'; ?>

  <script>
  const WARRANTY_ID = <?php echo $warranty_id; ?>;
  const IS_WARRANTY = <?php echo $warranty_claim ? 'true' : 'false'; ?>;

  let selectedDate = null;
  let selectedTime = null;
  let validatedReferenceCode = null;
  let referrerId = null;
  let isReferenceSkipped = false;

  function generateDates() {
    const dates = [];
    const today = new Date();
    const days = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
    const months = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
    
    for (let i = 0; i < 30; i++) {
      const date = new Date(today);
      date.setDate(today.getDate() + i);
      
      dates.push({
        day: days[date.getDay()],
        date: date.getDate().toString(),
        month: months[date.getMonth()],
        year: date.getFullYear(),
        fullDate: date.toISOString().split('T')[0]
      });
    }
    
    return dates;
  }

  const dates = generateDates();
  const times = [
  { label: "8:00 AM", value: "08:00:00" },
  { label: "9:00 AM", value: "09:00:00" },
  { label: "10:00 AM", value: "10:00:00" },
  { label: "11:00 AM", value: "11:00:00" },
  { label: "12:00 PM", value: "12:00:00" },
  { label: "1:00 PM", value: "13:00:00" },
  { label: "2:00 PM", value: "14:00:00" },
  { label: "3:00 PM", value: "15:00:00" },
  { label: "4:00 PM", value: "16:00:00" },
  { label: "5:00 PM", value: "17:00:00" }
];


  function validateReferenceCode() {
    const input = document.getElementById('referenceCodeInput');
    const code = input.value.trim().toLowerCase();
    const resultDiv = document.getElementById('referenceResult');
    const validateBtn = document.querySelector('.validate-btn');
    
    if (!code) {
      showReferenceResult('error', '❌ Error', 'Please enter a reference code');
      return;
    }
    
    validateBtn.disabled = true;
    validateBtn.textContent = 'Validating...';
    
    fetch('validate_reference_code.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'reference_code=' + encodeURIComponent(code)
    })
    .then(response => response.json())
    .then(data => {
      validateBtn.disabled = false;
      validateBtn.textContent = 'Validate Code';
      
      if (data.success) {
  validatedReferenceCode = code;
  referrerId = data.referrer_id;
  isReferenceSkipped = false;

  document.getElementById('skipBtn').textContent = 'Skip - Continue without reference code';

  showReferenceResult('success', '✓ Valid Code!', 'Referrer: ' + data.referrer_name);
  input.disabled = true;
  validateBtn.style.display = 'none';
  updateSelectionDisplay();
}
 else {
        validatedReferenceCode = null;
        referrerId = null;
        showReferenceResult('error', '❌ Invalid Code', data.message);
      }
    })
    .catch(error => {
      validateBtn.disabled = false;
      validateBtn.textContent = 'Validate Code';
      showReferenceResult('error', '❌ Error', 'Failed to validate code. Please try again.');
      console.error('Error:', error);
    });
  }

  function showReferenceResult(type, title, message) {
    const resultDiv = document.getElementById('referenceResult');
    const icon = type === 'success' ? '✓' : '❌';
    
    resultDiv.className = 'reference-result ' + type;
    resultDiv.innerHTML = `
      <div class="reference-result-content">
        <span class="reference-result-icon">${icon}</span>
        <div class="reference-result-text">
          <h4>${title}</h4>
          <p>${message}</p>
        </div>
      </div>
    `;
  }

  // let isReferenceSkipped = false;

function toggleSkipReference() {
  const input = document.getElementById('referenceCodeInput');
  const resultDiv = document.getElementById('referenceResult');
  const validateBtn = document.querySelector('.validate-btn');
  const skipBtn = document.getElementById('skipBtn');

  // ====== JIKA BELUM SKIP → SKIP ======
  if (!isReferenceSkipped) {
    validatedReferenceCode = null;
    referrerId = null;
    isReferenceSkipped = true;

    input.value = '';
    input.disabled = true;
    validateBtn.disabled = true;

    showReferenceResult('success', '✓ Skipped', 'Continuing without reference code');

    skipBtn.textContent = 'Undo Skip (Use reference code)';

  } 
  // ====== JIKA DAH SKIP → UNDO ======
  else {
    isReferenceSkipped = false;

    validatedReferenceCode = null;
    referrerId = null;

    input.disabled = false;
    validateBtn.disabled = false;

    resultDiv.style.display = 'none';
    resultDiv.innerHTML = '';
    resultDiv.className = 'reference-result';

    skipBtn.textContent = 'Skip - Continue without reference code';
  }

  updateSelectionDisplay();
}


  function updateSelectionDisplay() {
    const infoBox = document.getElementById('selectionInfo');
    const dateDisplay = document.getElementById('selectedDateDisplay');
    const timeDisplay = document.getElementById('selectedTimeDisplay');
    const confirmBtn = document.getElementById('confirmBtn');

    if (!IS_WARRANTY) {
      const referenceDisplay = document.getElementById('referenceDisplay');
      const selectedReferenceDisplay = document.getElementById('selectedReferenceDisplay');
      
      if (validatedReferenceCode) {
        referenceDisplay.style.display = 'flex';
        selectedReferenceDisplay.textContent = validatedReferenceCode.toUpperCase();
      } else {
        referenceDisplay.style.display = 'none';
      }
    }

    if (selectedDate !== null && selectedTime !== null) {
      infoBox.style.display = 'block';
      const date = dates[selectedDate];
      dateDisplay.textContent = `${date.day}, ${date.date} ${date.month} ${date.year}`;
      timeDisplay.textContent = times[selectedTime].label;
      
      // Enable confirm button if:
      // - Warranty appointment (no reference code needed), OR
      // - Regular appointment AND (reference validated OR skipped)
      if (IS_WARRANTY || validatedReferenceCode !== null || isReferenceSkipped) {
        confirmBtn.disabled = false;
      } else {
        confirmBtn.disabled = true;
      }
    } else {
      infoBox.style.display = 'none';
      confirmBtn.disabled = true;
    }
  }

  function populateDates() {
    const container = document.getElementById("dateScroll");
    container.innerHTML = "";
    dates.forEach((d, i) => {
      const btn = document.createElement("button");
      btn.className = "date-btn";
      if (selectedDate === i) btn.classList.add("selected");
      
      let label = '';
      if (i === 0) label = '<div style="font-size:10px; color:#ffeb3b; font-weight:800; margin-bottom:3px;">TODAY</div>';
      else if (i === 1) label = '<div style="font-size:10px; color:#ffeb3b; font-weight:800; margin-bottom:3px;">TOMORROW</div>';
      
      btn.innerHTML = `
        ${label}
        <div class="date-day">${d.day}</div>
        <div class="date-number">${d.date}</div>
        <div class="date-month">${d.month}</div>
      `;
      btn.onclick = () => {
        selectedDate = i;
        populateDates();
        updateSelectionDisplay();
      };
      container.appendChild(btn);
    });
  }

  function populateTimes() {
    const container = document.getElementById("timeScroll");
    container.innerHTML = "";
    times.forEach((t, i) => {
      const btn = document.createElement("button");
      btn.className = "time-btn";
      if (selectedTime === i) btn.classList.add("selected");
      btn.textContent = t.label;
      btn.onclick = () => {
        selectedTime = i;
        populateTimes();
        updateSelectionDisplay();
      };
      container.appendChild(btn);
    });
  }

  function scrollLeft(id) {
  const container = document.getElementById(id);
  if (!container) return;
  
  container.scrollBy({
    left: -300,
    behavior: 'auto'  // Gunakan 'auto' bukan 'smooth'
  });
}

function scrollRight(id) {
  const container = document.getElementById(id);
  if (!container) return;
  
  container.scrollBy({
    left: 300,
    behavior: 'auto'
  });
}

  function confirmAppointment() {
  if (selectedDate !== null && selectedTime !== null) {

    const confirmBtn = document.getElementById('confirmBtn');
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Processing...';

    const formData = new FormData();

    const date = dates[selectedDate];
    const time = times[selectedTime].value;

    formData.append('appointment_date', date.fullDate);
    formData.append('appointment_time', time);

    if (IS_WARRANTY) {
        // Warranty & Repair appointment
        formData.append('service_type', 'Warranty & Repair');
        formData.append('warranty_id', WARRANTY_ID);
    } else {
        // Regular Build PC or Other Service appointment
        const buildItems = JSON.parse(sessionStorage.getItem('selectedBuild') || '[]');
        
        if (buildItems.length === 0) {
            alert('No items selected. Please go back and select components.');
            window.location.href = 'buildpc.php';
            return;
        }
        
        const serviceType = sessionStorage.getItem('serviceType') || 'Build PC';
        formData.append('service_type', serviceType);
        formData.append('build_items', JSON.stringify(buildItems));
        
        // Add reference code data if validated
        if (validatedReferenceCode && referrerId) {
            formData.append('reference_code', validatedReferenceCode);
            formData.append('referrer_id', referrerId);
        }
    }
    
    fetch('confirm_appointment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (!IS_WARRANTY) {
                sessionStorage.removeItem('selectedBuild');
                sessionStorage.removeItem('totalPrice');
                sessionStorage.removeItem('serviceType');
            }
            window.location.href = 'appointment_summary.php?id=' + data.appointment_id;
        } else {
            alert('Error: ' + data.message);
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Confirm Appointment';
        }
    })
    .catch(error => {
        alert('Error creating appointment. Please try again.');
        console.error('Error:', error);
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Confirm Appointment';
    });
  }
}


  populateDates();
  populateTimes();
  updateSelectionDisplay();
  </script>
</body>
</html>