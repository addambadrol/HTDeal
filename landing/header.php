<?php
// customer_header.php - Reusable header component for customer pages
?>
 

<style>
/* Header Styles */
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
</style>

<header>
    <div class="navbar">
      <div class="logo">
        <a href="homepage.php">
          <img src="../picture/logo.png" alt="Logo" />
        </a>
      </div>

      <div class="nav-links">
        <a href="homepage.php">HOME</a>
        <a href="buildservices.php">BUILD & SERVICES</a>
        <a href="review.php">REVIEW</a>
        <a href="about.php">ABOUT</a>
      </div>

      <div class="profile-icon">
        <a href="profile.php">
          <img src="../picture/profileicon.png" alt="Profile" />
        </a>
      </div>
    </div>
</header>