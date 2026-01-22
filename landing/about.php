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
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 4px 20px rgba(110, 34, 221, 0.4);
    transition: all 0.3s ease;
}

.navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;  /* Untuk spread logo & profile */
    width: 100%;
}

.logo img {
    flex: 0 0 auto;
}

.nav-links {
    flex: 1;  /* Nav links ambil space tengah */
    display: flex;
    gap: 35px;
    justify-content: center;
}

.nav-links a {
    color: #fff;
    text-decoration: none;
    font-weight: 600;
    font-size: 15px;
    transition: all 0.3s ease;
    position: relative;
    padding: 5px 0;
}

.nav-links a:hover::after {
    width: 100%;
}

.nav-links a::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: #fff;
    transition: width 0.3s ease;
}

.nav-links a:hover {
    color: #ccc;
}

.profile-icon {
    flex: 0 0 auto;
}

.profile-icon img {
    width: 40px;
    height: 40px;
    cursor: pointer;
    margin-left: 25px;
    border-radius: 50%;
    border: 2px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
}

.profile-icon img:hover {
    border-color: #fff;
    transform: scale(1.1);
}

/* Responsive */
@media (max-width: 768px) {
    .nav-links {
        display: none;
    }
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
    text-align: center !important;
    padding: 120px 30px !important;
    position: relative !important;
    z-index: 1 !important;
    background: linear-gradient(180deg, transparent 0%, rgba(110, 34, 221, 0.05) 100%) !important;
  }

  .hero-section h1 {
    font-size: 56px !important;
    font-weight: 900 !important;
    background: linear-gradient(135deg, #8b4dff 0%, #6e22dd 100%) !important;
    -webkit-background-clip: text !important;
    -webkit-text-fill-color: transparent !important;
    background-clip: text !important;
    margin-bottom: 35px !important;
    text-transform: uppercase !important;
    letter-spacing: 3px !important;
    animation: fadeInUp 0.8s ease !important;
  }

  .hero-section p {
    font-size: 18px !important;
    line-height: 1.9 !important;
    color: #ccc !important;
    max-width: 900px !important;
    margin: 0 auto !important;
    animation: fadeInUp 1s ease 0.2s backwards !important;
  }

  @keyframes fadeInUp {
    from {
      opacity: 0 !important;
      transform: translateY(30px) !important;
    }
    to {
      opacity: 1 !important;
      transform: translateY(0) !important;
    }
  }

  /* Content Section */
  .content-section {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    margin: 60px auto !important;
    max-width: 1400px !important;
    border-radius: 20px !important;
    overflow: hidden !important;
    background: rgba(255, 255, 255, 0.02) !important;
    border: 1px solid rgba(139, 77, 255, 0.2) !important;
    box-shadow: 0 8px 32px rgba(110, 34, 221, 0.2) !important;
    position: relative !important;
    z-index: 1 !important;
  }

  .content-left {
    position: relative !important;
    overflow: hidden !important;
  }

  .content-left img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    transition: transform 0.6s ease !important;
  }

  .content-left:hover img {
    transform: scale(1.05) !important;
  }

  .content-left::after {
    content: '' !important;
    position: absolute !important;
    inset: 0 !important;
    background: linear-gradient(135deg, rgba(110, 34, 221, 0.3) 0%, transparent 100%) !important;
    opacity: 0 !important;
    transition: opacity 0.6s ease !important;
  }

  .content-left:hover::after {
    opacity: 1 !important;
  }

  .content-right {
    padding: 80px 70px !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;
    background: linear-gradient(135deg, rgba(110, 34, 221, 0.1) 0%, transparent 100%) !important;
  }

  .content-right h2 {
    font-size: 38px !important;
    font-weight: 900 !important;
    background: linear-gradient(135deg, #8b4dff 0%, #6e22dd 100%) !important;
    -webkit-background-clip: text !important;
    -webkit-text-fill-color: transparent !important;
    background-clip: text !important;
    margin-bottom: 30px !important;
    text-transform: uppercase !important;
    letter-spacing: 2px !important;
  }

  .content-right p {
    font-size: 16px !important;
    line-height: 1.9 !important;
    color: #d0d0d0 !important;
    margin-bottom: 20px !important;
  }

  /* Map Section */
  .map-section {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    margin: 60px auto !important;
    max-width: 1400px !important;
    border-radius: 20px !important;
    overflow: hidden !important;
    background: rgba(255, 255, 255, 0.02) !important;
    border: 1px solid rgba(139, 77, 255, 0.2) !important;
    box-shadow: 0 8px 32px rgba(110, 34, 221, 0.2) !important;
    position: relative !important;
    z-index: 1 !important;
  }

  .map-left {
    padding: 80px 70px !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;
    background: linear-gradient(135deg, rgba(139, 77, 255, 0.1) 0%, transparent 100%) !important;
  }

  .map-left h2 {
    font-size: 38px !important;
    font-weight: 900 !important;
    background: linear-gradient(135deg, #8b4dff 0%, #6e22dd 100%) !important;
    -webkit-background-clip: text !important;
    -webkit-text-fill-color: transparent !important;
    background-clip: text !important;
    margin-bottom: 30px !important;
    text-transform: uppercase !important;
    letter-spacing: 2px !important;
  }

  .map-left p {
    font-size: 17px !important;
    line-height: 1.9 !important;
    color: #d0d0d0 !important;
  }

  .map-right {
    position: relative !important;
    min-height: 450px !important;
  }

  #map_canvas {
    width: 100% !important;
    height: 100% !important;
    min-height: 450px !important;
    filter: grayscale(0.3) contrast(1.1) !important;
  }

  /* Contact Section */
  .contact-section {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    margin: 60px auto 80px !important;
    max-width: 1400px !important;
    border-radius: 20px !important;
    overflow: hidden !important;
    background: rgba(255, 255, 255, 0.02) !important;
    border: 1px solid rgba(139, 77, 255, 0.2) !important;
    box-shadow: 0 8px 32px rgba(110, 34, 221, 0.2) !important;
    position: relative !important;
    z-index: 1 !important;
  }

  .contact-left {
    padding: 80px 70px !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;
    background: linear-gradient(135deg, rgba(110, 34, 221, 0.1) 0%, transparent 100%) !important;
  }

  .contact-left h2 {
    font-size: 38px !important;
    font-weight: 900 !important;
    background: linear-gradient(135deg, #8b4dff 0%, #6e22dd 100%) !important;
    -webkit-background-clip: text !important;
    -webkit-text-fill-color: transparent !important;
    background-clip: text !important;
    margin-bottom: 35px !important;
    text-transform: uppercase !important;
    letter-spacing: 2px !important;
  }

  .contact-left p {
    font-size: 16px !important;
    line-height: 2.2 !important;
    color: #d0d0d0 !important;
    margin-bottom: 12px !important;
    transition: transform 0.3s ease !important;
  }

  .contact-left a {
    color: #8b4dff !important;
    text-decoration: none !important;
    font-weight: 600 !important;
    transition: all 0.3s ease !important;
    position: relative !important;
  }

  .contact-left a:hover {
    color: #fff !important;
    text-shadow: 0 0 10px rgba(139, 77, 255, 0.5) !important;
  }

  .contact-right {
    position: relative !important;
    overflow: hidden !important;
  }

  .contact-image {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    transition: transform 0.6s ease !important;
  }

  .contact-right:hover .contact-image {
    transform: scale(1.05) !important;
  }

  .contact-right::after {
    content: '' !important;
    position: absolute !important;
    inset: 0 !important;
    background: linear-gradient(135deg, transparent 0%, rgba(110, 34, 221, 0.3) 100%) !important;
    opacity: 0 !important;
    transition: opacity 0.6s ease !important;
  }

  .contact-right:hover::after {
    opacity: 1 !important;
  }

  /* Responsive */
  @media (max-width: 968px) {
    

    .hero-section {
      padding: 80px 25px !important;
    }

    .hero-section h1 {
      font-size: 38px !important;
    }

    .hero-section p {
      font-size: 16px !important;
    }

    .content-section,
    .map-section,
    .contact-section {
      grid-template-columns: 1fr !important;
      margin: 30px 20px !important;
    }

    .content-left,
    .map-right,
    .contact-right {
      min-height: 350px !important;
    }

    .content-right,
    .map-left,
    .contact-left {
      padding: 50px 35px !important;
    }

    .content-right h2,
    .map-left h2,
    .contact-left h2 {
      font-size: 30px !important;
    }

    .map-right {
      order: 2 !important;
    }

    .map-left {
      order: 1 !important;
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