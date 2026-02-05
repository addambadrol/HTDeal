<?php
// header.php - Reusable header component with SIDEBAR NAVIGATION for mobile
?>
<style>
/* Header Styles - WITH SIDEBAR */
header {
    background-color: #6e22dd;
    padding: 10px 15px;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 4px 20px rgba(110, 34, 221, 0.4);
}

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
    filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.3));
}

/* Hamburger Menu Button */
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

/* X Animation when active */
.menu-toggle.active span:nth-child(1) {
    transform: rotate(-45deg) translate(-5px, 6px);
}

.menu-toggle.active span:nth-child(2) {
    opacity: 0;
}

.menu-toggle.active span:nth-child(3) {
    transform: rotate(45deg) translate(-5px, -6px);
}

/* Desktop Navigation */
.nav-links {
    display: flex;
    gap: 35px;
    align-items: center;
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
}

.profile-icon img {
    width: 40px;
    height: 40px;
    cursor: pointer;
    border-radius: 50%;
    border: 2px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
}

.profile-icon img:hover {
    border-color: #fff;
    transform: scale(1.1);
}

/* SIDEBAR - Hidden by default */
.sidebar {
    position: fixed;
    top: 0;
    left: -300px; /* Hidden off-screen */
    width: 280px;
    height: 100vh;
    background: linear-gradient(180deg, #6e22dd 0%, #5a1fb8 100%);
    box-shadow: 2px 0 20px rgba(0, 0, 0, 0.5);
    z-index: 2000;
    transition: left 0.3s ease;
    overflow-y: auto;
}

/* Sidebar Active State */
.sidebar.active {
    left: 0; /* Slide in */
}

/* Sidebar Header */
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

/* Sidebar Menu */
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

.sidebar-menu a:active {
    background-color: rgba(255, 255, 255, 0.2);
}

/* Sidebar Footer */
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

/* Overlay - Dark background when sidebar open */
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
   RESPONSIVE - MOBILE & TABLET
   ======================================== */

@media (max-width: 768px) {
    
    /* Show hamburger menu */
    .menu-toggle {
        display: flex;
    }
    
    /* Hide desktop navigation */
    .nav-links {
        display: none;
    }
    
    .profile-icon img {
        width: 35px;
        height: 35px;
    }
}

@media (max-width: 480px) {
    
    header {
        padding: 8px 10px;
    }
    
    .logo img {
        height: 32px;
    }
    
    .profile-icon img {
        width: 30px;
        height: 30px;
    }
    
    .sidebar {
        width: 250px;
        left: -250px;
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

        <!-- Hamburger Menu Button (Mobile Only) -->
        <button class="menu-toggle" id="menuToggle">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Desktop Navigation -->
        <div class="nav-links">
            <a href="homepage.php">HOME</a>
            <a href="buildservices.php">BUILD & SERVICES</a>
            <a href="review.php">REVIEW</a>
            <a href="about.php">ABOUT</a>
        </div>

        <div class="profile-icon">
            <?php include 'notification_bell.php'; ?>
            <a href="profile.php">
                <img src="../picture/profileicon.png" alt="Profile" />
            </a>
        </div>
    </div>
</header>

<!-- Sidebar Navigation (Mobile Only) -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <img src="../picture/logo.png" alt="Logo" />
        </div>
        <button class="sidebar-close" id="sidebarClose">&times;</button>
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

<!-- Overlay (Dark background) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- JavaScript for Sidebar -->
<script>
(function() {
    // Get elements
    var menuToggle = document.getElementById('menuToggle');
    var sidebar = document.getElementById('sidebar');
    var sidebarClose = document.getElementById('sidebarClose');
    var sidebarOverlay = document.getElementById('sidebarOverlay');
    
    // Open sidebar
    function openSidebar() {
        if (sidebar && sidebarOverlay && menuToggle) {
            sidebar.classList.add('active');
            sidebarOverlay.classList.add('active');
            menuToggle.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent scrolling
            console.log('Sidebar OPENED');
        }
    }
    
    // Close sidebar
    function closeSidebar() {
        if (sidebar && sidebarOverlay && menuToggle) {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            menuToggle.classList.remove('active');
            document.body.style.overflow = ''; // Restore scrolling
            console.log('Sidebar CLOSED');
        }
    }
    
    // Toggle sidebar
    function toggleSidebar() {
        if (sidebar.classList.contains('active')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    }
    
    // Event listeners
    if (menuToggle) {
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleSidebar();
        });
    }
    
    if (sidebarClose) {
        sidebarClose.addEventListener('click', function(e) {
            e.stopPropagation();
            closeSidebar();
        });
    }
    
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }
    
    // Close on link click
    var sidebarLinks = document.querySelectorAll('.sidebar-menu a');
    sidebarLinks.forEach(function(link) {
        link.addEventListener('click', closeSidebar);
    });
    
    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('active')) {
            closeSidebar();
        }
    });
    
    console.log('Sidebar initialized');
})();
</script>