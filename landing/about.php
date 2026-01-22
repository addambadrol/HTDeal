<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - About Us</title>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBUGI9VjTTo0-5c8hUZMzGcrQfSoN41Yu4&sensor=false"></script>

  <style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }

  body {
    display: flex;
    flex-direction: column;
    background-color: #1a1a1a;
    color: white;
  }

  /* ================= NAVBAR FIX ================= */
  header {
    width: 100%;
    background-color: #6e22dd;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 4px 20px rgba(110, 34, 221, 0.4);
  }

  .header-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 10px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .nav-links {
    display: flex;
    gap: 35px;
  }

  .nav-links a {
    color: #fff;
    text-decoration: none;
    font-weight: 600;
    font-size: 15px;
  }

  .profile-icon img {
    width: 40px;
    height: 40px;
    cursor: pointer;
  }

  /* ================================================= */

  body::before {
    content: '';
    position: fixed;
    inset: 0;
    background:
      radial-gradient(circle at 20% 50%, rgba(110, 34, 221, 0.1), transparent 50%),
      radial-gradient(circle at 80% 80%, rgba(139, 77, 255, 0.1), transparent 50%);
    animation: gradientShift 15s ease infinite;
    pointer-events: none;
    z-index: 0;
  }

  @keyframes gradientShift {
    0%, 100% { opacity: 0.3; }
    50% { opacity: 0.6; }
  }

  /* Hero */
  .hero-section {
    text-align: center;
    padding: 120px 30px;
    position: relative;
    z-index: 1;
  }

  .hero-section h1 {
    font-size: 56px;
    font-weight: 900;
    background: linear-gradient(135deg, #8b4dff, #6e22dd);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 35px;
  }

  .hero-section p {
    font-size: 18px;
    line-height: 1.9;
    color: #ccc;
    max-width: 900px;
    margin: auto;
  }

  /* Sections shared width */
  .content-section,
  .map-section,
  .contact-section {
    max-width: 1400px;
    margin: 60px auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    border-radius: 20px;
    overflow: hidden;
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(139,77,255,0.2);
    box-shadow: 0 8px 32px rgba(110,34,221,0.2);
    z-index: 1;
  }

  .content-left img,
  .contact-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .content-right,
  .map-left,
  .contact-left {
    padding: 80px 70px;
  }

  .map-right,
  .content-left,
  .contact-right {
    min-height: 450px;
  }

  #map_canvas {
    width: 100%;
    height: 100%;
  }

  /* Responsive */
  @media (max-width: 968px) {
    .content-section,
    .map-section,
    .contact-section {
      grid-template-columns: 1fr;
      margin: 30px 20px;
    }

    .header-container {
      padding: 10px 15px;
    }

    .hero-section h1 {
      font-size: 38px;
    }
  }
  </style>

  <script>
    function initMap() {
      const center = { lat: 3.1945, lng: 101.5902 };
      const map = new google.maps.Map(document.getElementById("map_canvas"), {
        zoom: 16,
        center: center,
      });
      new google.maps.Marker({
        position: center,
        map: map,
        title: "HTDeal - Apartment Suria"
      });
    }
  </script>
</head>

<body onload="initMap()">

<?php include 'header.php'; ?>

<section class="hero-section">
  <h1>About Us</h1>
  <p>
    HTDeal is an appointment-based platform for buying and selling computer equipment.
  </p>
</section>

<section class="content-section">
  <div class="content-left">
    <img src="../picture/aboutpic1.jpeg">
  </div>
  <div class="content-right">
    <h2>Build & Services</h2>
    <p>Custom PC building & repair services.</p>
  </div>
</section>

<section class="map-section">
  <div class="map-left">
    <h2>Our Base</h2>
    <p>Apartment Suria, Petaling Jaya</p>
  </div>
  <div class="map-right">
    <div id="map_canvas"></div>
  </div>
</section>

<section class="contact-section">
  <div class="contact-left">
    <h2>Contact Us</h2>
    <p>Email: htdeal@gmail.com</p>
    <p>WhatsApp: +019-250 1153</p>
  </div>
  <div class="contact-right">
    <img src="../picture/aboutpic2.jpeg" class="contact-image">
  </div>
</section>

<?php include 'footer.php'; ?>

</body>
</html>
