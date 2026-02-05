<?php
// header.php - GUARANTEED BALANCED MOBILE LAYOUT
?>
<style>
/* ========================================
   HEADER - GUARANTEED MOBILE RESPONSIVE
   ======================================== */

* {
    margin: 0;
    padding: 0;
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

.logo {
    z-index: 1001;
}

.logo img {
    height: 40px;
    display: block;
}

/* Hamburger - HIDDEN on desktop */
.menu-toggle {
    display: none;
    flex-direction: column;
    cursor: pointer;
    padding: 8px;
    background: transparent;
    border: none;
    z-index: 1001;
}

.menu-toggle span {
    width: 25px;
    height: 3px;
    background-color: white;
    margin: 3px 0;
    transition: all 0.3s ease;
    border-radius: 3px;
}

.menu-toggle.active span:nth-child(1) {
    transform: rotate(-45deg) translate(-5px, 6px);
}

.menu-toggle.active span:nth-child(2) {
    opacity: 0;
}

.menu-toggle.active span:nth-child(3) {
    transform: rotate(45deg) translate(-5px, -6px);
}

/* Desktop Nav Links */
.nav-links {
    display: flex;
    gap: 35px;
}

.nav-links a {
    color: #fff;
    text-decoration: none;
    font-weight: 600;
    font-size: 15px;
    transition: color 0.3s ease;
}

.nav-links a:hover {
    color: #ccc;
}

.profile-icon {
    display: flex;
    align-items: center;
    gap: 10px;
}

.profile-icon img {
    width: 40px;
    height: 40px;
    cursor: pointer;
    border-radius: 50%;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

/* ========================================
   SIDEBAR
   ======================================== */

.sidebar {
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

.sidebar.active {
    left: 0;
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.sidebar-logo img {
    height: 35px;
}

.sidebar-close {
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

.sidebar-close:hover {
    transform: rotate(90deg);
}

.sidebar-menu {
    padding: 20px 0;
}

.sidebar-menu a {
    display: block;
    color: white;
    text-decoration: none;
    padding: 15px 25px;
    font-weight: 600;
    font-size: 16px;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.sidebar-menu a:hover {
    background-color: rgba(255, 255, 255, 0.1);
    border-left-color: white;
    padding-left: 30px;
}

.sidebar-footer {
    position: absolute;
    bottom: 0;
    width: 100%;
    padding: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    text-align: center;
    color: rgba(255, 255, 255, 0.7);
    font-size: 12px;
}

.sidebar-overlay {
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

.sidebar-overlay.active {
    opacity: 1;
    visibility: visible;
}

/* ========================================
   MOBILE LAYOUT - CRITICAL SECTION!
   ======================================== */

@media screen and (max-width: 768px) {
    
    /* FORCE GRID LAYOUT */
    .navbar {
        display: grid !important;
        grid-template-columns: auto 1fr auto !important;
        grid-template-rows: 1fr !important;
        align-items: center !important;
        gap: 10px !important;
        justify-content: normal !important;
    }
    
    /* HAMBURGER - LEFT (Column 1) */
    .menu-toggle {
        display: flex !important;
        grid-column: 1 !important;
        grid-row: 1 !important;
        order: 1 !important;
    }
    
    /* LOGO - CENTER (Column 2) */
    .logo {
        grid-column: 2 !important;
        grid-row: 1 !important;
        order: 2 !important;
        text-align: center !important;
        justify-self: center !important;
        margin: 0 auto !important;
    }
    
    .logo img {
        height: 32px !important;
        margin: 0 auto !important;
    }
    
    /* ICONS - RIGHT (Column 3) */
    .profile-icon {
        grid-column: 3 !important;
        grid-row: 1 !important;
        order: 3 !important;
        justify-self: end !important;
        margin-left: 0 !important;
    }
    
    .profile-icon img {
        width: 35px !important;
        height: 35px !important;
    }
    
    /* HIDE DESKTOP NAV - CRITICAL! */
    .nav-links {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        position: absolute !important;
        left: -9999px !important;
    }
    
    .sidebar {
        width: 250px;
        left: -250px;
    }
}

/* SMALLER MOBILE */
@media screen and (max-width: 480px) {
    
    header {
        padding: 8px 10px !important;
    }
    
    .logo img {
        height: 28px !important;
    }
    
    .profile-icon img {
        width: 30px !important;
        height: 30px !important;
    }
    
    .navbar {
        gap: 8px !important;
    }
}

/* VERY SMALL PHONES */
@media screen and (max-width: 360px) {
    
    .logo img {
        height: 24px !important;
    }
    
    .profile-icon img {
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
        <!-- HAMBURGER (Mobile: Left) -->
        <button class="menu-toggle" id="menuToggle" type="button">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- LOGO (Mobile: Center, Desktop: Left) -->
        <div class="logo">
            <a href="homepage.php">
                <img src="../picture/logo.png" alt="HTDeal Logo" />
            </a>
        </div>

        <!-- DESKTOP NAV (Hidden on mobile) -->
        <div class="nav-links">
            <a href="homepage.php">HOME</a>
            <a href="buildservices.php">BUILD & SERVICES</a>
            <a href="review.php">REVIEW</a>
            <a href="about.php">ABOUT</a>
        </div>

        <!-- PROFILE ICONS (Mobile: Right, Desktop: Right) -->
        <div class="profile-icon">
            <?php include 'notification_bell.php'; ?>
            <a href="profile.php">
                <img src="../picture/profileicon.png" alt="Profile" />
            </a>
        </div>
    </div>
</header>

<!-- SIDEBAR (Mobile Navigation) -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <img src="../picture/logo.png" alt="HTDeal Logo" />
        </div>
        <button class="sidebar-close" id="sidebarClose" type="button">&times;</button>
    </div>
    
    <nav class="sidebar-menu">
        <a href="homepage.php">🏠 HOME</a>
        <a href="buildservices.php">🔧 BUILD & SERVICES</a>
        <a href="review.php">⭐ REVIEW</a>
        <a href="about.php">ℹ️ ABOUT</a>
        <a href="profile.php">👤 PROFILE</a>
    </nav>
    
    <div class="sidebar-footer">
        HTDeal © 2025
    </div>
</div>

<!-- OVERLAY -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- JAVASCRIPT -->
<script>
(function() {
    'use strict';
    
    var menuToggle = document.getElementById('menuToggle');
    var sidebar = document.getElementById('sidebar');
    var sidebarClose = document.getElementById('sidebarClose');
    var sidebarOverlay = document.getElementById('sidebarOverlay');
    
    if (!menuToggle || !sidebar || !sidebarClose || !sidebarOverlay) {
        console.error('Sidebar elements not found');
        return;
    }
    
    function openSidebar() {
        sidebar.classList.add('active');
        sidebarOverlay.classList.add('active');
        menuToggle.classList.add('active');
        document.body.style.overflow = 'hidden';
        console.log('✅ Sidebar opened');
    }
    
    function closeSidebar() {
        sidebar.classList.remove('active');
        sidebarOverlay.classList.remove('active');
        menuToggle.classList.remove('active');
        document.body.style.overflow = '';
        console.log('✅ Sidebar closed');
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
    
    console.log('🚀 Mobile sidebar ready');
})();
</script>