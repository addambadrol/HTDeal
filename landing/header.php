<?php
// header.php - Mobile responsive header untuk landing page (non-logged in users)
?>
<style>
/* ========================================
   SCOPED HEADER STYLES ONLY - LANDING PAGE
   ======================================== */

/* Only reset header elements, not whole page */
header,
header * {
    box-sizing: border-box;
}

header {
    background-color: #6e22dd;
    padding: 10px 15px;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 4px 20px rgba(110, 34, 221, 0.4);
    width: 100%;
    margin: 0;
}

/* DESKTOP LAYOUT */
.navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    max-width: 1200px;
    margin: 0 auto;
    width: 100%;
}

.navbar .logo {
    z-index: 1001;
}

.navbar .logo img {
    height: 40px;
    display: block;
}

/* Hamburger - HIDDEN on desktop */
.navbar .menu-toggle {
    display: none;
    flex-direction: column;
    cursor: pointer;
    padding: 8px;
    background: transparent;
    border: none;
    z-index: 1001;
}

.navbar .menu-toggle span {
    width: 25px;
    height: 3px;
    background-color: white;
    margin: 3px 0;
    transition: all 0.3s ease;
    border-radius: 3px;
}

.navbar .menu-toggle.active span:nth-child(1) {
    transform: rotate(-45deg) translate(-5px, 6px);
}

.navbar .menu-toggle.active span:nth-child(2) {
    opacity: 0;
}

.navbar .menu-toggle.active span:nth-child(3) {
    transform: rotate(45deg) translate(-5px, -6px);
}

/* Desktop Nav Links */
.navbar .nav-links {
    display: flex;
    gap: 35px;
}

.navbar .nav-links a {
    color: #fff;
    text-decoration: none;
    font-weight: 600;
    font-size: 15px;
    transition: color 0.3s ease;
}

.navbar .nav-links a:hover {
    color: #ccc;
}

.navbar .profile-icon {
    display: flex;
    align-items: center;
    gap: 10px;
}

.navbar .profile-icon img {
    width: 40px;
    height: 40px;
    cursor: pointer;
    border-radius: 50%;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

/* ========================================
   SIDEBAR - Won't affect page content
   ======================================== */

.mobile-sidebar {
    position: fixed;
    top: 0;
    left: -300px;
    width: 280px;
    height: 100vh;
    background: linear-gradient(180deg, #6e22dd 0%, #5a1fb8 100%);
    box-shadow: 2px 0 20px rgba(0, 0, 0, 0.5);
    z-index: 2000;
    transition: left 0.3s ease;
    overflow-y: auto;
}

.mobile-sidebar.active {
    left: 0;
}

.mobile-sidebar .sidebar-header {
    padding: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.mobile-sidebar .sidebar-logo img {
    height: 35px;
}

.mobile-sidebar .sidebar-close {
    background: transparent;
    border: none;
    color: white;
    font-size: 30px;
    cursor: pointer;
    padding: 0;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s ease;
}

.mobile-sidebar .sidebar-close:hover {
    transform: rotate(90deg);
}

.mobile-sidebar .sidebar-menu {
    padding: 20px 0;
}

.mobile-sidebar .sidebar-menu a {
    display: block;
    color: white;
    text-decoration: none;
    padding: 15px 25px;
    font-weight: 600;
    font-size: 16px;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.mobile-sidebar .sidebar-menu a:hover {
    background-color: rgba(255, 255, 255, 0.1);
    border-left-color: white;
    padding-left: 30px;
}

.mobile-sidebar .sidebar-footer {
    position: absolute;
    bottom: 0;
    width: 100%;
    padding: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    text-align: center;
    color: rgba(255, 255, 255, 0.7);
    font-size: 12px;
}

.mobile-sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    z-index: 1500;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.mobile-sidebar-overlay.active {
    opacity: 1;
    visibility: visible;
}

/* ========================================
   MOBILE LAYOUT - SCOPED TO NAVBAR ONLY
   ======================================== */

@media screen and (max-width: 768px) {
    
    /* ONLY affect navbar, not page content */
    .navbar {
        display: grid !important;
        grid-template-columns: auto 1fr auto !important;
        grid-template-rows: 1fr !important;
        align-items: center !important;
        gap: 10px !important;
        justify-content: normal !important;
    }
    
    /* HAMBURGER - LEFT */
    .navbar .menu-toggle {
        display: flex !important;
        grid-column: 1 !important;
        grid-row: 1 !important;
    }
    
    /* LOGO - CENTER */
    .navbar .logo {
        grid-column: 2 !important;
        grid-row: 1 !important;
        text-align: center !important;
        justify-self: center !important;
        margin: 0 auto !important;
    }
    
    .navbar .logo img {
        height: 32px !important;
        margin: 0 auto !important;
    }
    
    /* ICONS - RIGHT */
    .navbar .profile-icon {
        grid-column: 3 !important;
        grid-row: 1 !important;
        justify-self: end !important;
        margin-left: 0 !important;
    }
    
    .navbar .profile-icon img {
        width: 35px !important;
        height: 35px !important;
    }
    
    /* HIDE DESKTOP NAV */
    .navbar .nav-links {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
    }
    
    .mobile-sidebar {
        width: 250px;
        left: -250px;
    }
}

@media screen and (max-width: 480px) {
    
    header {
        padding: 8px 10px !important;
    }
    
    .navbar .logo img {
        height: 28px !important;
    }
    
    .navbar .profile-icon img {
        width: 30px !important;
        height: 30px !important;
    }
    
    .navbar {
        gap: 8px !important;
    }
}

@media screen and (max-width: 360px) {
    
    .navbar .logo img {
        height: 24px !important;
    }
    
    .navbar .profile-icon img {
        width: 28px !important;
        height: 28px !important;
    }
    
    .navbar {
        gap: 5px !important;
    }
}
</style>

<header>
    <div class="navbar">
        <!-- HAMBURGER (Mobile) -->
        <button class="menu-toggle" id="menuToggle" type="button">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- LOGO -->
        <div class="logo">
            <a href="homepage.php">
                <img src="../picture/logo.png" alt="HTDeal Logo" />
            </a>
        </div>

        <!-- DESKTOP NAV -->
        <div class="nav-links">
            <a href="homepage.php">HOME</a>
            <a href="buildservices.php">BUILD & SERVICES</a>
            <a href="review.php">REVIEW</a>
            <a href="about.php">ABOUT</a>
        </div>

        <!-- PROFILE ICON (leads to login) -->
        <div class="profile-icon">
            <a href="profile.php">
                <img src="../picture/profileicon.png" alt="Profile" />
            </a>
        </div>
    </div>
</header>

<!-- SIDEBAR (Mobile Navigation) -->
<div class="mobile-sidebar" id="mobileSidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <img src="../picture/logo.png" alt="HTDeal Logo" />
        </div>
        <button class="sidebar-close" id="sidebarClose" type="button">&times;</button>
    </div>
    
    <nav class="sidebar-menu">
        <a href="homepage.php">HOME</a>
        <a href="buildservices.php">BUILD & SERVICES</a>
        <a href="review.php">REVIEW</a>
        <a href="about.php">ABOUT</a>
        <a href="profile.php">LOGIN / SIGN UP</a>
    </nav>
    
    <div class="sidebar-footer">
        HTDeal © 2025
    </div>
</div>

<!-- OVERLAY -->
<div class="mobile-sidebar-overlay" id="sidebarOverlay"></div>

<!-- JAVASCRIPT -->
<script>
(function() {
    'use strict';
    
    var menuToggle = document.getElementById('menuToggle');
    var sidebar = document.getElementById('mobileSidebar');
    var sidebarClose = document.getElementById('sidebarClose');
    var sidebarOverlay = document.getElementById('sidebarOverlay');
    
    if (!menuToggle || !sidebar || !sidebarClose || !sidebarOverlay) {
        return;
    }
    
    function openSidebar() {
        sidebar.classList.add('active');
        sidebarOverlay.classList.add('active');
        menuToggle.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function closeSidebar() {
        sidebar.classList.remove('active');
        sidebarOverlay.classList.remove('active');
        menuToggle.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    function toggleSidebar() {
        if (sidebar.classList.contains('active')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    }
    
    menuToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        toggleSidebar();
    });
    
    sidebarClose.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        closeSidebar();
    });
    
    sidebarOverlay.addEventListener('click', function(e) {
        closeSidebar();
    });
    
    var links = sidebar.querySelectorAll('.sidebar-menu a');
    for (var i = 0; i < links.length; i++) {
        links[i].addEventListener('click', function() {
            closeSidebar();
        });
    }
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('active')) {
            closeSidebar();
        }
    });
})();
</script>