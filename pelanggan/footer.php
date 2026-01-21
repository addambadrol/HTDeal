<?php
// footer.php - Modern Reusable footer component
?>
<style>
.main-footer {
    background: linear-gradient(180deg, #0a0a0a 0%, #1a0033 100%);
    color: #fff;
    margin-top: auto;
    border-top: 2px solid rgba(110, 34, 221, 0.3);
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 50px 20px 30px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 40px;
}

.footer-section h3 {
    font-size: 18px;
    font-weight: 800;
    color: #6e22dd;
    margin-bottom: 20px;
    text-transform: uppercase;
    letter-spacing: 1px;
    position: relative;
    padding-bottom: 10px;
}

.footer-section h3::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 3px;
    background: linear-gradient(90deg, #6e22dd 0%, transparent 100%);
    border-radius: 2px;
}

.footer-section p {
    color: #aaa;
    line-height: 1.8;
    font-size: 14px;
    margin-bottom: 15px;
}

.footer-section a {
    color: #aaa;
    text-decoration: none;
    display: block;
    padding: 8px 0;
    font-size: 14px;
    transition: all 0.3s ease;
    position: relative;
    padding-left: 15px;
}

.footer-section a::before {
    content: '';
    position: absolute;
    left: 0;
    color: #6e22dd;
    opacity: 0;
    transition: all 0.3s ease;
}

.footer-section a:hover {
    color: #6e22dd;
    padding-left: 20px;
}

.footer-section a:hover::before {
    opacity: 1;
}

.social-links {
    display: flex;
    gap: 15px;
    margin-top: 15px;
    justify-content: center;
}

.social-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    color: #6e22dd;
}

.social-icon:hover {
    color: #8b4dff;
    transform: translateY(-3px);
}

.social-icon svg {
    filter: drop-shadow(0 2px 4px rgba(110, 34, 221, 0.3));
}

.contact-item {
    text-align: center;
    margin-bottom: 15px;
    color: #aaa;
    font-size: 14px;
    line-height: 1.6;
}

.contact-item a {
    color: #aaa;
    text-decoration: none;
    display: block;
    transition: color 0.3s ease;
}

.contact-item a:hover {
    color: #6e22dd;
}

.contact-address {
    text-align: center;
    color: #aaa;
    font-size: 14px;
    line-height: 1.6;
    margin-top: 15px;
}

.footer-bottom {
    background: rgba(0, 0, 0, 0.3);
    padding: 25px 20px;
    text-align: center;
    border-top: 1px solid rgba(110, 34, 221, 0.2);
}

.footer-bottom p {
    color: #888;
    font-size: 13px;
    margin: 0;
    letter-spacing: 0.3px;
    line-height: 1.6;
}

.footer-bottom .copyright-symbol {
    color: #6e22dd;
    font-weight: 700;
}

.footer-bottom a {
    color: #6e22dd;
    text-decoration: none;
    font-weight: 700;
    transition: color 0.3s ease;
}

.footer-bottom a:hover {
    color: #8b4dff;
}

@media (max-width: 768px) {
    .footer-content {
        grid-template-columns: 1fr;
        gap: 35px;
        padding: 40px 20px 25px;
    }
    
    .social-links {
        justify-content: center;
    }
}
</style>

<footer class="main-footer">
    <div class="footer-content">
        <!-- About Us Section -->
        <div class="footer-section">
            <h3>About Us</h3>
            <p>HTDeal is your trusted partner for PC building, repairs, and warranty services in Malaysia.</p>
            <a href="about.php">Learn More About Us</a>
        </div>

        <!-- Our Services Section -->
        <div class="footer-section">
            <h3>Our Services</h3>
            <a href="buildpc.php">Build Custom PC</a>
            <a href="repair_warranty.php">Repair & Warranty</a>
            <a href="other_services.php">Other Services</a>
        </div>

        <!-- Customer Service Section -->
        <div class="footer-section">
            <h3>Customer Service</h3>
            <div class="contact-item">
                <a href="tel:+60192501153">+60 19-250 1153</a>
            
                <a href="mailto:heykalmykal90@gmail.com">heykalmykal90@gmail.com</a>
            
                Blok D M-23 Jalan PJU 10/4A<br>
                Apartment Suria Damansara Damai<br>
                Petaling Jaya, Selangor
            </div>
        </div>

        <!-- Follow Us Section -->
        <div class="footer-section">
            <h3>Follow Us</h3>
            <p>Stay connected with us on social media for updates and promotions!</p>
            <div class="social-links">
                <a href="https://www.facebook.com/share/1GipCvPJsN/" target="_blank" class="social-icon" title="Facebook">
                    <svg width="32" height="32" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </a>
                <a href="#" target="_blank" class="social-icon" title="Instagram">
                    <svg width="32" height="32" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                    </svg>
                </a>
                <a href="#" target="_blank" class="social-icon" title="TikTok">
                    <svg width="32" height="32" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p><span class="copyright-symbol">&copy;</span> 2025 <a href="index.php">HTDeal</a> | Sistem Temu Janji dan Pengurusan Jual Beli Komputer HA-KAL TECH | All Rights Reserved</p>
    </div>
</footer>