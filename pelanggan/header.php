<?php
// header.php - Reusable header component for customer pages with MOBILE RESPONSIVE
?>
<style>
/* Header Styles - MOBILE RESPONSIVE */
header {
    background-color: #6e22dd;
    padding: 10px 15px;
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
    flex-wrap: wrap;
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    position: relative;
}

.logo {
    order: 1;
    z-index: 1001;
}

.logo img {
    height: 40px;
    filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.3));
}

/* Mobile Menu Toggle Button */
.menu-toggle {
    display: none;
    flex-direction: column;
    cursor: pointer;
    padding: 5px;
    order: 2;
    z-index: 1001;
    background: transparent;
    border: none;
    outline: none;
}

.menu-toggle span {
    width: 25px;
    height: 3px;
    background-color: white;
    margin: 3px 0;
    transition: all 0.3s ease;
    border-radius: 3px;
}

/* Animate hamburger to X */
.menu-toggle.active span:nth-child(1) {
    transform: rotate(-45deg) translate(-5px, 6px);
}

.menu-toggle.active span:nth-child(2) {
    opacity: 0;
}

.menu-toggle.active span:nth-child(3) {
    transform: rotate(45deg) translate(-5px, -6px);
}

.nav-links {
    display: flex;
    gap: 35px;
    margin-left: auto;
    order: 3;
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
    order: 4;
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

/* ========================================
   RESPONSIVE - MOBILE & TABLET
   ======================================== */

/* Tablet and Mobile - 768px and below */
@media (max-width: 768px) {
    
    header {
        padding: 10px 15px;
    }
    
    .navbar {
        justify-content: space-between;
    }
    
    /* Show mobile menu toggle */
    .menu-toggle {
        display: flex;
    }
    
    /* Hide navigation by default on mobile */
    .nav-links {
        display: none;
        flex-direction: column;
        width: 100%;
        gap: 0;
        margin-left: 0;
        background-color: #5a1fb8;
        border-radius: 8px;
        padding: 15px 0;
        margin-top: 15px;
        order: 5;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }
    
    /* Show navigation when active */
    .nav-links.active {
        display: flex !important;
    }
    
    .nav-links a {
        font-size: 16px;
        padding: 12px 20px;
        width: 100%;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .nav-links a:last-child {
        border-bottom: none;
    }
    
    .nav-links a::after {
        display: none; /* Remove underline animation on mobile */
    }
    
    .nav-links a:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
    
    .profile-icon {
        margin-left: 10px;
        order: 3;
    }
    
    .profile-icon img {
        width: 35px;
        height: 35px;
        margin-left: 10px;
    }
}

/* Mobile - 480px and below */
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
        margin-left: 5px;
    }
    
    .nav-links a {
        font-size: 14px;
        padding: 10px 15px;
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

        <!-- Mobile Menu Toggle Button -->
        <button class="menu-toggle" id="menuToggle" type="button" onclick="toggleMobileMenu()">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <div class="nav-links" id="navLinks">
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

<!-- JavaScript for Mobile Menu Toggle - SIMPLIFIED & GUARANTEED TO WORK -->
<script>
// Global function - runs immediately, no waiting for DOM
function toggleMobileMenu() {
    var menuToggle = document.getElementById('menuToggle');
    var navLinks = document.getElementById('navLinks');
    
    if (menuToggle && navLinks) {
        // Toggle classes
        if (menuToggle.classList.contains('active')) {
            menuToggle.classList.remove('active');
            navLinks.classList.remove('active');
            console.log('Menu CLOSED');
        } else {
            menuToggle.classList.add('active');
            navLinks.classList.add('active');
            console.log('Menu OPENED');
        }
    }
}

// Initialize when ready
(function() {
    function initMenu() {
        var menuToggle = document.getElementById('menuToggle');
        var navLinks = document.getElementById('navLinks');
        
        if (!menuToggle || !navLinks) {
            console.log('Waiting for elements...');
            setTimeout(initMenu, 100);
            return;
        }
        
        console.log('Menu initialized!');
        
        // Close on link click
        var links = navLinks.getElementsByTagName('a');
        for (var i = 0; i < links.length; i++) {
            links[i].onclick = function() {
                menuToggle.classList.remove('active');
                navLinks.classList.remove('active');
            };
        }
        
        // Close on outside click
        document.onclick = function(e) {
            var target = e.target;
            var clickedToggle = menuToggle.contains(target);
            var clickedMenu = navLinks.contains(target);
            
            if (!clickedToggle && !clickedMenu && navLinks.classList.contains('active')) {
                menuToggle.classList.remove('active');
                navLinks.classList.remove('active');
            }
        };
        
        // Close on ESC
        document.onkeydown = function(e) {
            if (e.key === 'Escape' && navLinks.classList.contains('active')) {
                menuToggle.classList.remove('active');
                navLinks.classList.remove('active');
            }
        };
    }
    
    // Try to init immediately
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMenu);
    } else {
        initMenu();
    }
})();
</script>