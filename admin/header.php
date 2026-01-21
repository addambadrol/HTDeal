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
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
}

.logo img {
    height: 40px;
    filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.3));
}

.nav-links {
    display: flex;
    gap: 35px;
    margin-left: auto;
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
    display: flex;
    align-items: center;
    gap: 10px;
    margin-left: 20px;
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

        <div style="width: 100%; text-align: center; justify-content: center;" class="nav-links">
            <a href="homepage.php">HOME</a>
            <a href="seller.php">SELLER</a>
            <a href="inventory.php">INVENTORY</a>
            <a href="promo.php">PROMO</a>
            <a href="appointment.php">APPOINTMENTS</a>
            <a href="invoices.php">INVOICES</a>
        </div>

        <div class="profile-icon">
            <a href="profile.php">
                <img src="../picture/profileicon.png" alt="Profile" />
            </a>
        </div>
    </div>
</header>