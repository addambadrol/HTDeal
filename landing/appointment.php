<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - Appointment</title>
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

  /* Page Header */
  .page-header {
    text-align: center;
    padding: 60px 20px 40px;
    background: linear-gradient(180deg, rgba(110, 34, 221, 0.1) 0%, transparent 100%);
  }

  .page-header h1 {
    font-size: 36px;
    font-weight: 800;
    color: #6e22dd;
    margin-bottom: 10px;
  }

  .page-header p {
    font-size: 16px;
    color: #aaa;
  }

  /* Main Container */
  main {
    max-width: 900px;
    margin: 40px auto 60px auto;
    padding: 0 20px;
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
    content: '📅';
    font-size: 24px;
  }

  /* Scroll Row */
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

  .scroll-btn:active {
    transform: scale(0.95);
  }

  /* Date/Time Scroll */
  #dateScroll, #timeScroll {
    display: flex;
    overflow-x: auto;
    gap: 15px;
    padding: 10px 5px;
    scrollbar-width: none;
    scroll-behavior: smooth;
    flex: 1;
  }

  #dateScroll::-webkit-scrollbar,
  #timeScroll::-webkit-scrollbar {
    display: none;
  }

  /* Date Button */
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

  /* Time Button */
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

  /* Confirm Button */
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

  .confirm-btn:active {
    transform: translateY(0);
  }

  .confirm-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  /* Selection Info */
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

  /* Footer */
  footer {
    text-align: center;
    padding: 30px 20px;
    background-color: #0a0a0a;
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
      font-size: 28px;
    }

    .appointment-container {
      padding: 30px 20px;
    }

    .scroll-btn {
      width: 40px;
      height: 40px;
      font-size: 18px;
    }

    .date-btn {
      min-width: 85px;
    }

    .time-btn {
      min-width: 75px;
      font-size: 16px;
    }
  }
  </style>
</head>
<body>
  <?php include 'header.php'; ?>

  <div class="page-header">
    <h1>📅 Schedule Your Appointment</h1>
    <p>Choose your preferred date and time for the service</p>
  </div>

  <main>
    <div class="appointment-container">
      <!-- Date Section -->
      <div class="scroll-section">
        <div class="section-title">Select Date</div>
        <div class="scroll-row">
          <button class="scroll-btn" onclick="scrollLeft('dateScroll')">◄</button>
          <div id="dateScroll"></div>
          <button class="scroll-btn" onclick="scrollRight('dateScroll')">►</button>
        </div>
      </div>

      <!-- Time Section -->
      <div class="scroll-section">
        <div class="section-title">Select Time</div>
        <div class="scroll-row">
          <button class="scroll-btn" onclick="scrollLeft('timeScroll')">◄</button>
          <div id="timeScroll"></div>
          <button class="scroll-btn" onclick="scrollRight('timeScroll')">►</button>
        </div>
      </div>

      <!-- Selection Summary -->
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
        </div>
      </div>

      <button class="confirm-btn" id="confirmBtn" onclick="confirmAppointment()">
        Confirm Appointment
      </button>
    </div>
  </main>

  <footer>
    <p>&copy; 2025 HTDeal - SISTEM TEMU JANJI DAN PENGURUSAN JUAL BELI KOMPUTER HA-KAL TECH</p>
  </footer>

  <script>
  // Generate dates from today to 30 days ahead
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
    "8:00 AM", "9:00 AM", "10:00 AM", "11:00 AM", 
    "12:00 PM", "1:00 PM", "2:00 PM", "3:00 PM", 
    "4:00 PM", "5:00 PM"
  ];

  let selectedDate = null;
  let selectedTime = null;

  function updateSelectionDisplay() {
    const infoBox = document.getElementById('selectionInfo');
    const dateDisplay = document.getElementById('selectedDateDisplay');
    const timeDisplay = document.getElementById('selectedTimeDisplay');
    const confirmBtn = document.getElementById('confirmBtn');

    if (selectedDate !== null && selectedTime !== null) {
      infoBox.style.display = 'block';
      const date = dates[selectedDate];
      dateDisplay.textContent = `${date.day}, ${date.date} ${date.month} ${date.year}`;
      timeDisplay.textContent = times[selectedTime];
      confirmBtn.disabled = false;
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
      btn.textContent = t;
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
    container.scrollBy({ left: -300, behavior: "smooth" });
  }

  function scrollRight(id) {
    const container = document.getElementById(id);
    container.scrollBy({ left: 300, behavior: "smooth" });
  }

  function confirmAppointment() {
    if (selectedDate !== null && selectedTime !== null) {
        window.location.href = 'plslogin.php';
    }
}

function showSuccessModal(invoiceNumber, date, time, totalAmount) {
    const modalHTML = `
        <div id="successModal" style="
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        ">
            <div style="
                background: linear-gradient(135deg, #1a1a1a 0%, #252525 100%);
                border: 2px solid #6e22dd;
                border-radius: 20px;
                padding: 40px;
                max-width: 500px;
                text-align: center;
                box-shadow: 0 10px 40px rgba(110, 34, 221, 0.5);
            ">
                <div style="font-size: 60px; margin-bottom: 20px;">✅</div>
                <h2 style="color: #6e22dd; margin-bottom: 20px;">Appointment Confirmed!</h2>
                
                <div style="background: rgba(110, 34, 221, 0.1); padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                    <p style="margin: 10px 0;"><strong>Invoice Number:</strong><br>${invoiceNumber}</p>
                    <p style="margin: 10px 0;"><strong>Date:</strong><br>${date.day}, ${date.date} ${date.month} ${date.year}</p>
                    <p style="margin: 10px 0;"><strong>Time:</strong><br>${time}</p>
                    <p style="margin: 10px 0;"><strong>Total Amount:</strong><br>RM ${parseFloat(totalAmount).toFixed(2)}</p>
                </div>
                
                <div style="display: flex; gap: 15px;">
                    <a href="invoice_detail.php?invoice=${invoiceNumber}" style="
                        flex: 1;
                        background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
                        color: white;
                        padding: 15px;
                        border-radius: 10px;
                        text-decoration: none;
                        font-weight: bold;
                    ">View Invoice</a>
                    
                    <a href="homepage.php" style="
                        flex: 1;
                        background: #333;
                        color: white;
                        padding: 15px;
                        border-radius: 10px;
                        text-decoration: none;
                        font-weight: bold;
                    ">Back to Home</a>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

  populateDates();
  populateTimes();
  updateSelectionDisplay();
  </script>
</body>
</html>