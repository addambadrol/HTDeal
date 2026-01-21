<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - About Us</title>
  <!-- <link rel="stylesheet" href="./style.css" /> -->
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
    font-family: 'Poppins', sans-serif;
    background-color: #1a1a1a;
    color: white;
    margin: 0;
  }

  header {
            background-color: #6e22dd;
            padding: 10px 20px;
        }

        .navbar {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            max-width: 1200px !important;
            margin: 0 auto !important;
        }

        .logo img {
            height: 35px !important;
        }

        .nav-links {
            display: flex !important;
            gap: 25px !important;
        }

        .nav-links a {
            color: #fff !important;
            text-decoration: none !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            transition: color 0.3s !important;
        }

        .nav-links a:hover {
            color: #ccc !important;
        }

        .profile-icon img {
            width: 35px !important;
            height: 35px !important;
            cursor: pointer !important;
        }

  /* Animated gradient background */
  body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle at 20% 50%, rgba(110, 34, 221, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(139, 77, 255, 0.1) 0%, transparent 50%);
    animation: gradientShift 15s ease infinite;
    pointer-events: none;
    z-index: 0;
  }

  @keyframes gradientShift {
    0%, 100% { opacity: 0.3; }
    50% { opacity: 0.6; }
  }

  

  /* Hero Section */
  .hero-section {
    text-align: center;
    padding: 120px 30px;
    position: relative;
    z-index: 1;
    background: linear-gradient(180deg, transparent 0%, rgba(110, 34, 221, 0.05) 100%);
  }

  .hero-section h1 {
    font-size: 56px;
    font-weight: 900;
    background: linear-gradient(135deg, #8b4dff 0%, #6e22dd 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 35px;
    text-transform: uppercase;
    letter-spacing: 3px;
    animation: fadeInUp 0.8s ease;
  }

  .hero-section p {
    font-size: 18px;
    line-height: 1.9;
    color: #ccc;
    max-width: 900px;
    margin: 0 auto;
    animation: fadeInUp 1s ease 0.2s backwards;
  }

  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(30px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Content Section */
  .content-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    margin: 60px auto;
    max-width: 1400px;
    border-radius: 20px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(139, 77, 255, 0.2);
    box-shadow: 0 8px 32px rgba(110, 34, 221, 0.2);
    position: relative;
    z-index: 1;
  }

  .content-left {
    position: relative;
    overflow: hidden;
  }

  .content-left img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
  }

  .content-left:hover img {
    transform: scale(1.05);
  }

  .content-left::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(110, 34, 221, 0.3) 0%, transparent 100%);
    opacity: 0;
    transition: opacity 0.6s ease;
  }

  .content-left:hover::after {
    opacity: 1;
  }

  .content-right {
    padding: 80px 70px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    background: linear-gradient(135deg, rgba(110, 34, 221, 0.1) 0%, transparent 100%);
  }

  .content-right h2 {
    font-size: 38px;
    font-weight: 900;
    background: linear-gradient(135deg, #8b4dff 0%, #6e22dd 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 30px;
    text-transform: uppercase;
    letter-spacing: 2px;
  }

  .content-right p {
    font-size: 16px;
    line-height: 1.9;
    color: #d0d0d0;
    margin-bottom: 20px;
  }

  /* Map Section */
  .map-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    margin: 60px auto;
    max-width: 1400px;
    border-radius: 20px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(139, 77, 255, 0.2);
    box-shadow: 0 8px 32px rgba(110, 34, 221, 0.2);
    position: relative;
    z-index: 1;
  }

  .map-left {
    padding: 80px 70px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    background: linear-gradient(135deg, rgba(139, 77, 255, 0.1) 0%, transparent 100%);
  }

  .map-left h2 {
    font-size: 38px;
    font-weight: 900;
    background: linear-gradient(135deg, #8b4dff 0%, #6e22dd 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 30px;
    text-transform: uppercase;
    letter-spacing: 2px;
  }

  .map-left p {
    font-size: 17px;
    line-height: 1.9;
    color: #d0d0d0;
  }

  .map-right {
    position: relative;
    min-height: 450px;
  }

  #map_canvas {
    width: 100%;
    height: 100%;
    min-height: 450px;
    filter: grayscale(0.3) contrast(1.1);
  }

  /* Contact Section */
  .contact-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    margin: 60px auto 80px;
    max-width: 1400px;
    border-radius: 20px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(139, 77, 255, 0.2);
    box-shadow: 0 8px 32px rgba(110, 34, 221, 0.2);
    position: relative;
    z-index: 1;
  }

  .contact-left {
    padding: 80px 70px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    background: linear-gradient(135deg, rgba(110, 34, 221, 0.1) 0%, transparent 100%);
  }

  .contact-left h2 {
    font-size: 38px;
    font-weight: 900;
    background: linear-gradient(135deg, #8b4dff 0%, #6e22dd 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 35px;
    text-transform: uppercase;
    letter-spacing: 2px;
  }

  .contact-left p {
    font-size: 16px;
    line-height: 2.2;
    color: #d0d0d0;
    margin-bottom: 12px;
    transition: transform 0.3s ease;
  }

  .contact-left a {
    color: #8b4dff;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
  }

  .contact-left a:hover {
    color: #fff;
    text-shadow: 0 0 10px rgba(139, 77, 255, 0.5);
  }

  .contact-right {
    position: relative;
    overflow: hidden;
  }

  .contact-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
  }

  .contact-right:hover .contact-image {
    transform: scale(1.05);
  }

  .contact-right::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, transparent 0%, rgba(110, 34, 221, 0.3) 100%);
    opacity: 0;
    transition: opacity 0.6s ease;
  }

  .contact-right:hover::after {
    opacity: 1;
  }

  /* Responsive */
  @media (max-width: 968px) {
    

    .hero-section {
      padding: 80px 25px;
    }

    .hero-section h1 {
      font-size: 38px;
    }

    .hero-section p {
      font-size: 16px;
    }

    .content-section,
    .map-section,
    .contact-section {
      grid-template-columns: 1fr;
      margin: 30px 20px;
    }

    .content-left,
    .map-right,
    .contact-right {
      min-height: 350px;
    }

    .content-right,
    .map-left,
    .contact-left {
      padding: 50px 35px;
    }

    .content-right h2,
    .map-left h2,
    .contact-left h2 {
      font-size: 30px;
    }

    .map-right {
      order: 2;
    }

    .map-left {
      order: 1;
    }
  }
  </style>
  
  <script type="text/javascript">
    function initMap(position) {
      var lat = 3.1945;
      var long = 101.5902;
      
      var center = new google.maps.LatLng(lat, long);
      
      var mapOptions = {
        zoom: 16,
        center: center,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        
      };
      
      var map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
      
      var marker = new google.maps.Marker({
        map: map,
        position: center,
        title: 'HTDeal - Apartment Suria',
        animation: google.maps.Animation.DROP
      });
    }
  </script>
</head>
<body onload="initMap({coords: {latitude: 3.1945, longitude: 101.5902}})">
  <?php include 'header.php'; ?>

  <section class="hero-section">
    <h1>About Us</h1>
    <p>
      HTDeal is an appointment-based platform for buying and selling computer equipment. We help buyers and sellers meet safely and conveniently through a booking system and two-way reviews. Our mission: to simplify transactions and build trust.
    </p>
  </section>

  <section class="content-section">
    <div class="content-left">
      <img src="../picture/aboutpic1.jpeg" alt="HTDeal Store" />
    </div>
    <div class="content-right">
      <h2>Build & Services</h2>
      <p>
        HTDeal provides custom PC building services tailored to your needs. Whether for gaming, professional work, or everyday use, our team is ready to help.
      </p>
      <p>
        We also offer warranty claim and repair services to ensure your equipment is always in top condition.
      </p>
    </div>
  </section>

  <section class="map-section">
    <div class="map-left">
      <h2>Our Base</h2>
      <p>
        Jalan PJU 10/4a, Apartment Suria, 47830 Petaling Jaya, Selangor
      </p>
    </div>
    <div class="map-right">
      <div id="map_canvas"></div>
    </div>
  </section>

  <section class="contact-section">
    <div class="contact-left">
      <h2>Contact Us</h2>
      <p>Emel: <a href="mailto:htdeal@gmail.com">htdeal@gmail.com</a></p>
      <p>WhatsApp: <a href="https://wa.me/60192501153">+019-250 1153</a></p>
      <p>Instagram: <a href="https://instagram.com/htdeal.my" target="_blank">@htdeal.my</a></p>
      <p>Waktu operasi: Isnin – Jumaat, 9.00 pagi – 5.00 petang</p>
    </div>
    <div class="contact-right">
      <img src="../picture/aboutpic2.jpeg" alt="Contact HTDeal" class="contact-image" />
    </div>
  </section>

  <?php include 'footer.php'; ?>
</body>
</html>