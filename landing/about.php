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
    background: #0a0a0f;
    color: #fff;
    overflow-x: hidden;
  }

  /* Animated gradient background */
  body::before {
    content: '';
    position: fixed;
    inset: 0;
    background: 
      radial-gradient(circle at 20% 20%, rgba(110,34,221,.2), transparent 50%),
      radial-gradient(circle at 80% 80%, rgba(139,77,255,.2), transparent 50%),
      radial-gradient(circle at 40% 60%, rgba(157,123,255,.1), transparent 40%);
    animation: bgShift 15s ease infinite;
    z-index: -1;
  }

  @keyframes bgShift {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
  }

  /* Floating particles */
  .particles {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: -1;
    pointer-events: none;
  }

  .particle {
    position: absolute;
    background: rgba(157,123,255,.3);
    border-radius: 50%;
    animation: float 20s infinite;
  }

  @keyframes float {
    0%, 100% { transform: translateY(0) translateX(0); opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { transform: translateY(-100vh) translateX(50px); opacity: 0; }
  }

  /* HERO SECTION */
  .hero-section {
    min-height: 85vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 0 25px;
    position: relative;
    overflow: hidden;
  }

  .hero-content {
    text-align: center;
    max-width: 1000px;
    animation: fadeInUp 1s ease;
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

  .hero-section h1 {
    font-size: 72px;
    font-weight: 900;
    letter-spacing: 5px;
    background: linear-gradient(135deg, #9d7bff 0%, #6e22dd 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 35px;
    position: relative;
    display: inline-block;
  }

  .hero-section h1::after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 150px;
    height: 4px;
    background: linear-gradient(90deg, transparent, #9d7bff, transparent);
    border-radius: 2px;
  }

  .hero-section p {
    font-size: 20px;
    line-height: 2;
    color: #d8d8d8;
    max-width: 850px;
    margin: 40px auto 0;
    animation: fadeInUp 1s ease 0.3s backwards;
  }

  /* CONTENT CARDS */
  .cards-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 100px 25px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 50px;
  }

  .card {
    background: rgba(255,255,255,0.03);
    border-radius: 30px;
    border: 1px solid rgba(157,123,255,0.2);
    overflow: hidden;
    position: relative;
    transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
    box-shadow: 0 20px 60px rgba(0,0,0,.4);
  }

  .card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 100%;
    background: linear-gradient(135deg, rgba(157,123,255,0.1), transparent);
    opacity: 0;
    transition: opacity 0.5s;
  }

  .card:hover {
    transform: translateY(-10px);
    border-color: rgba(157,123,255,0.5);
    box-shadow: 0 30px 80px rgba(110,34,221,.4);
  }

  .card:hover::before {
    opacity: 1;
  }

  .card-image {
    width: 100%;
    height: 350px;
    overflow: hidden;
    position: relative;
  }

  .card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s cubic-bezier(0.23, 1, 0.32, 1);
  }

  .card:hover .card-image img {
    transform: scale(1.1);
  }

  .card-content {
    padding: 50px 45px;
  }

  .card-content h2 {
    font-size: 36px;
    font-weight: 800;
    margin-bottom: 25px;
    background: linear-gradient(135deg, #9d7bff, #6e22dd);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  .card-content p {
    font-size: 17px;
    line-height: 2;
    color: #d0d0d0;
    margin-bottom: 18px;
  }

  .card-content p:last-child {
    margin-bottom: 0;
  }

  /* MAP SECTION */
  .map-wrapper {
    max-width: 1400px;
    margin: 120px auto;
    padding: 0 25px;
  }

  .map-container {
    background: rgba(255,255,255,0.03);
    border-radius: 30px;
    border: 1px solid rgba(157,123,255,0.2);
    overflow: hidden;
    box-shadow: 0 30px 80px rgba(0,0,0,.4);
    display: grid;
    grid-template-columns: 1fr 1.2fr;
  }

  .map-info {
    padding: 80px 60px;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  .map-info h2 {
    font-size: 42px;
    font-weight: 800;
    margin-bottom: 30px;
    background: linear-gradient(135deg, #9d7bff, #6e22dd);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  .map-info p {
    font-size: 19px;
    line-height: 2;
    color: #d8d8d8;
  }

  .map-display {
    min-height: 500px;
    position: relative;
  }

  #map_canvas {
    width: 100%;
    height: 100%;
    filter: grayscale(0.3) contrast(1.15) brightness(0.9);
  }

  /* CONTACT SECTION */
  .contact-wrapper {
    max-width: 1400px;
    margin: 120px auto 150px;
    padding: 0 25px;
  }

  .contact-container {
    background: rgba(255,255,255,0.03);
    border-radius: 30px;
    border: 1px solid rgba(157,123,255,0.2);
    overflow: hidden;
    box-shadow: 0 30px 80px rgba(0,0,0,.4);
    display: grid;
    grid-template-columns: 1.3fr 1fr;
  }

  .contact-info {
    padding: 80px 70px;
  }

  .contact-info h2 {
    font-size: 42px;
    font-weight: 800;
    margin-bottom: 50px;
    background: linear-gradient(135deg, #9d7bff, #6e22dd);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  .contact-item {
    margin-bottom: 30px;
    padding-left: 15px;
    border-left: 3px solid rgba(157,123,255,0.3);
    transition: all 0.3s;
  }

  .contact-item:hover {
    border-left-color: #9d7bff;
    padding-left: 25px;
  }

  .contact-item p {
    font-size: 18px;
    line-height: 2;
    color: #d8d8d8;
  }

  .contact-item a {
    color: #9d7bff;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
    position: relative;
  }

  .contact-item a::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: #9d7bff;
    transition: width 0.3s;
  }

  .contact-item a:hover {
    color: #b89aff;
  }

  .contact-item a:hover::after {
    width: 100%;
  }

  .contact-image {
    position: relative;
    overflow: hidden;
  }

  .contact-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  /* RESPONSIVE */
  @media (max-width: 1024px) {
    .cards-container {
      grid-template-columns: 1fr;
      gap: 40px;
    }

    .map-container,
    .contact-container {
      grid-template-columns: 1fr;
    }

    .hero-section h1 {
      font-size: 52px;
    }
  }

  @media (max-width: 768px) {
    .hero-section h1 {
      font-size: 40px;
      letter-spacing: 3px;
    }

    .hero-section p {
      font-size: 17px;
    }

    .card-content,
    .map-info,
    .contact-info {
      padding: 50px 35px;
    }

    .card-content h2,
    .map-info h2,
    .contact-info h2 {
      font-size: 32px;
    }

    .cards-container {
      grid-template-columns: 1fr;
      padding: 60px 25px;
    }
  }
  </style>

  <script>
    function initMap() {
      const center = { lat: 3.1945, lng: 101.5902 };
      const map = new google.maps.Map(document.getElementById("map_canvas"), {
        zoom: 16,
        center,
        styles: [
          {
            "featureType": "all",
            "elementType": "geometry",
            "stylers": [{"color": "#1a1a2e"}]
          },
          {
            "featureType": "all",
            "elementType": "labels.text.fill",
            "stylers": [{"color": "#9d7bff"}]
          },
          {
            "featureType": "all",
            "elementType": "labels.text.stroke",
            "stylers": [{"visibility": "off"}]
          },
          {
            "featureType": "road",
            "elementType": "geometry",
            "stylers": [{"color": "#2a2a3e"}]
          },
          {
            "featureType": "water",
            "elementType": "geometry",
            "stylers": [{"color": "#16162a"}]
          }
        ]
      });
      new google.maps.Marker({ 
        position: center, 
        map: map,
        animation: google.maps.Animation.DROP
      });
    }

    // Add floating particles
    window.addEventListener('load', () => {
      const particlesContainer = document.createElement('div');
      particlesContainer.className = 'particles';
      document.body.appendChild(particlesContainer);

      for (let i = 0; i < 15; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.left = Math.random() * 100 + '%';
        particle.style.width = particle.style.height = (Math.random() * 4 + 2) + 'px';
        particle.style.animationDelay = Math.random() * 20 + 's';
        particle.style.animationDuration = (Math.random() * 10 + 15) + 's';
        particlesContainer.appendChild(particle);
      }
    });
  </script>
</head>

<body onload="initMap()">
<?php include 'header.php'; ?> 
<section class="hero-section">
  <div class="hero-content">
    <h1>About Us</h1>
    <p>
      HTDeal is an appointment-based platform for buying and selling computer equipment.
      We help buyers and sellers meet safely through verified booking and two-way reviews.
    </p>
  </div>
</section>

<div class="cards-container">
  <div class="card">
    <div class="card-image">
      <img src="../picture/aboutpic1.jpeg" alt="Build & Services">
    </div>
    <div class="card-content">
      <h2>Build & Services</h2>
      <p>Custom PC builds tailored for gaming, work and productivity.</p>
      <p>Professional repair & warranty support to keep your system optimal.</p>
    </div>
  </div>
</div>

<div class="map-wrapper">
  <div class="map-container">
    <div class="map-info">
      <h2>Our Base</h2>
      <p>Apartment Suria, Jalan PJU 10/4A, Petaling Jaya, Selangor</p>
    </div>
    <div class="map-display">
      <div id="map_canvas"></div>
    </div>
  </div>
</div>

<div class="contact-wrapper">
  <div class="contact-container">
    <div class="contact-info">
      <h2>Contact Us</h2>
      <div class="contact-item">
        <p>Email: <a href="mailto:htdeal@gmail.com">htdeal@gmail.com</a></p>
      </div>
      <div class="contact-item">
        <p>WhatsApp: <a href="https://wa.me/60192501153">+019-250 1153</a></p>
      </div>
      <div class="contact-item">
        <p>Instagram: <a href="https://instagram.com/htdeal.my">@htdeal.my</a></p>
      </div>
      <div class="contact-item">
        <p>Operation Hours: Monday – Friday (9AM – 5PM)</p>
      </div>
    </div>
    <div class="contact-image">
      <img src="../picture/aboutpic2.jpeg" alt="Contact">
    </div>
  </div>
</div>

</body>
</html>