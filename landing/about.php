<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - About Us</title>
  <link rel="stylesheet" href="./style.css" />
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBUGI9VjTTo0-5c8hUZMzGcrQfSoN41Yu4&sensor=false"></script>

  <style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
  }

  body {
    background: #0f0f14;
    color: #fff;
    overflow-x: hidden;
  }

  body::before {
    content: '';
    position: fixed;
    inset: 0;
    background:
      radial-gradient(circle at 15% 30%, rgba(110,34,221,.15), transparent 40%),
      radial-gradient(circle at 85% 80%, rgba(139,77,255,.15), transparent 40%);
    z-index: -1;
  }

  /* ================= HERO ================= */
  .hero-section {
    padding: 140px 25px 120px !important;
    text-align: center;
  }

  .hero-section h1 {
    font-size: 60px;
    font-weight: 900;
    letter-spacing: 4px;
    background: linear-gradient(135deg, #9d7bff, #6e22dd);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 30px;
  }

  .hero-section p {
    max-width: 850px;
    margin: auto;
    font-size: 18px;
    line-height: 1.9;
    color: #cfcfcf;
  }

  /* ================= SECTION BASE ================= */
  section:not(.hero-section) {
    max-width: 1300px !important;
    margin: 80px auto;
    background: rgba(255,255,255,0.04);
    border-radius: 24px;
    border: 1px solid rgba(255,255,255,0.08);
    box-shadow: 0 30px 80px rgba(0,0,0,.45);
    overflow: hidden;
  }

  /* ================= CONTENT ================= */
  .content-section {
    display: grid !important;
    grid-template-columns: 1fr 1fr;
  }

  .content-left img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .content-right {
    padding: 90px 80px;
  }

  .content-right h2 {
    font-size: 40px;
    font-weight: 800;
    margin-bottom: 30px;
    background: linear-gradient(135deg, #9d7bff, #6e22dd);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  .content-right p {
    font-size: 17px;
    line-height: 2;
    color: #d6d6d6;
    margin-bottom: 20px;
  }

  /* ================= MAP ================= */
  .map-section {
    display: grid !important;
    grid-template-columns: 1fr 1fr;
  }

  .map-left {
    padding: 90px 80px;
  }

  .map-left h2 {
    font-size: 40px;
    margin-bottom: 25px;
    background: linear-gradient(135deg, #9d7bff, #6e22dd);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  .map-left p {
    font-size: 18px;
    line-height: 1.9;
    color: #d0d0d0;
  }

  .map-right {
    min-height: 450px;
  }

  #map_canvas {
    width: 100%;
    height: 100%;
    filter: grayscale(.25) contrast(1.1);
  }

  /* ================= CONTACT ================= */
  .contact-section {
    display: grid !important;
    grid-template-columns: 1fr 1fr;
  }

  .contact-left {
    padding: 90px 80px;
  }

  .contact-left h2 {
    font-size: 40px;
    margin-bottom: 35px;
    background: linear-gradient(135deg, #9d7bff, #6e22dd);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  .contact-left p {
    font-size: 17px;
    line-height: 2.3;
    color: #d0d0d0;
  }

  .contact-left a {
    color: #9d7bff;
    font-weight: 600;
    text-decoration: none;
  }

  .contact-right img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  /* ================= RESPONSIVE ================= */
  @media (max-width: 968px) {
    .content-section,
    .map-section,
    .contact-section {
      grid-template-columns: 1fr;
    }

    .content-right,
    .map-left,
    .contact-left {
      padding: 60px 35px;
    }

    .hero-section h1 {
      font-size: 40px;
    }
  }
  </style>

  <script>
    function initMap() {
      const center = { lat: 3.1945, lng: 101.5902 };
      new google.maps.Map(document.getElementById("map_canvas"), {
        zoom: 16,
        center
      });
      new google.maps.Marker({ position: center, map: map_canvas });
    }
  </script>
</head>

<body onload="initMap()">
<?php include 'header.php'; ?>

<section class="hero-section">
  <h1>About Us</h1>
  <p>
    HTDeal is an appointment-based platform for buying and selling computer equipment.
    We help buyers and sellers meet safely through verified booking and two-way reviews.
  </p>
</section>

<section class="content-section">
  <div class="content-left">
    <img src="../picture/aboutpic1.jpeg">
  </div>
  <div class="content-right">
    <h2>Build & Services</h2>
    <p>Custom PC builds tailored for gaming, work and productivity.</p>
    <p>Professional repair & warranty support to keep your system optimal.</p>
  </div>
</section>

<section class="map-section">
  <div class="map-left">
    <h2>Our Base</h2>
    <p>Apartment Suria, Jalan PJU 10/4A, Petaling Jaya, Selangor</p>
  </div>
  <div class="map-right">
    <div id="map_canvas"></div>
  </div>
</section>

<section class="contact-section">
  <div class="contact-left">
    <h2>Contact Us</h2>
    <p>Email: <a href="mailto:htdeal@gmail.com">htdeal@gmail.com</a></p>
    <p>WhatsApp: <a href="https://wa.me/60192501153">+019-250 1153</a></p>
    <p>Instagram: <a href="https://instagram.com/htdeal.my">@htdeal.my</a></p>
    <p>Operation Hours: Monday – Friday (9AM – 5PM)</p>
  </div>
  <div class="contact-right">
    <img src="../picture/aboutpic2.jpeg">
  </div>
</section>

<?php include 'footer.php'; ?>
</body>
</html>
