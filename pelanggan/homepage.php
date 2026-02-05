<?php
require_once '../db_config.php';

// Fetch active promo items - Get the one with highest discount percentage
try {
    $stmt = $pdo->query("
        SELECT *, 
        ROUND(((selling_price - promo_price) / selling_price) * 100) as discount_percentage
        FROM inventory 
        WHERE is_promo = 1 
        AND promo_price IS NOT NULL 
        AND promo_start_date <= CURDATE()
        AND promo_end_date >= CURDATE()
        ORDER BY discount_percentage DESC 
        LIMIT 1
    ");
    $promoItem = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $promoItem = null;
}

// Calculate discount percentage
$discountPercent = 0;
if ($promoItem && $promoItem['selling_price'] > 0) {
    $discountPercent = $promoItem['discount_percentage'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTDeal - Homepage</title>
    <link rel="stylesheet" href="./style.css" />
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

        /* Hero Banner - Dragon Theme */
        .hero-banner {
            position: relative;
            width: 100%;
            height: 100vh;
            min-height: 700px;
            background: linear-gradient(135deg, #1a0033 0%, #3d0a91 50%, #6e22dd 100%);
            overflow: hidden;
        }

        .hero-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 30% 50%, rgba(110, 34, 221, 0.3) 0%, transparent 50%),
                        radial-gradient(circle at 70% 80%, rgba(139, 77, 255, 0.2) 0%, transparent 50%);
            opacity: 0.6;
            z-index: 1;
            animation: pulse 8s ease-in-out infinite;
        }

        .hero-banner::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 200%;
            height: 200%;
            background-image: 
                linear-gradient(90deg, transparent 90%, rgba(110, 34, 221, 0.1) 100%),
                linear-gradient(0deg, transparent 90%, rgba(110, 34, 221, 0.1) 100%);
            background-size: 50px 50px;
            animation: gridMove 20s linear infinite;
            z-index: 0;
        }

        .hero-content-wrapper {
            position: relative;
            height: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }

        .hero-text {
            max-width: 700px;
            text-align: center;
            animation: slideInUp 1s ease-out;
        }

        .hero-title {
            font-size: 72px;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 30px;
            text-shadow: 0 0 30px rgba(110, 34, 221, 0.8);
            text-transform: uppercase;
            letter-spacing: -2px;
        }

        .hero-title .highlight {
            background: linear-gradient(135deg, #8b4dff 0%, #6e22dd 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: block;
            font-size: 85px;
            filter: drop-shadow(0 0 20px rgba(139, 77, 255, 0.8));
        }

        .hero-description {
            font-size: 20px;
            line-height: 1.6;
            margin-bottom: 40px;
            color: #d0d0d0;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary {
            display: inline-block;
            background: linear-gradient(135deg, #6e22dd 0%, #8b4dff 100%);
            color: #fff;
            padding: 18px 50px;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 800;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(110, 34, 221, 0.5);
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 40px rgba(110, 34, 221, 0.8);
        }

        .btn-secondary {
            display: inline-block;
            background: rgba(110, 34, 221, 0.2);
            backdrop-filter: blur(10px);
            color: #fff;
            padding: 18px 50px;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 800;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: all 0.3s ease;
            border: 2px solid rgba(139, 77, 255, 0.5);
        }

        .btn-secondary:hover {
            background: rgba(110, 34, 221, 0.4);
            border-color: #8b4dff;
            transform: translateY(-5px);
        }

        /* Scroll Indicator */
        .scroll-indicator {
            position: absolute;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            animation: bounce 2s infinite;
            z-index: 10;
            cursor: pointer;
        }

        .scroll-indicator span {
            display: block;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
            color: #8b4dff;
        }

        .scroll-arrow {
            width: 30px;
            height: 50px;
            border: 3px solid #8b4dff;
            border-radius: 25px;
            margin: 0 auto;
            position: relative;
        }

        .scroll-arrow::before {
            content: '';
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            width: 6px;
            height: 6px;
            background: #8b4dff;
            border-radius: 50%;
            animation: scroll 2s infinite;
            box-shadow: 0 0 10px rgba(139, 77, 255, 0.8);
        }

        /* Promo Section */
        .promo-section {
            padding: 80px 40px;
            background: #000;
            position: relative;
        }

        .promo-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .promo-banner {
            background: linear-gradient(135deg, #1a0033 0%, #3d0a91 100%);
            border-radius: 30px;
            padding: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 2px solid #6e22dd;
            box-shadow: 0 20px 60px rgba(110, 34, 221, 0.4);
            position: relative;
            overflow: hidden;
        }

        .promo-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(110, 34, 221, 0.3) 0%, transparent 70%);
            border-radius: 50%;
        }

        .promo-content {
            flex: 1;
            position: relative;
            z-index: 2;
        }

        .promo-tag {
            display: inline-block;
            background: #ff4444;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .promo-title {
            font-size: 42px;
            font-weight: 900;
            margin-bottom: 15px;
            color: #fff;
        }

        .promo-category {
            font-size: 18px;
            color: #8b4dff;
            margin-bottom: 25px;
            font-weight: 600;
        }

        .promo-prices {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .promo-old-price {
            font-size: 24px;
            color: #999;
            text-decoration: line-through;
        }

        .promo-new-price {
            font-size: 48px;
            font-weight: 900;
            color: #fff;
        }

        .promo-discount {
            background: #ff4444;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 16px;
            font-weight: 700;
        }

        .no-promo {
            text-align: center;
            padding: 80px 40px;
        }

        .no-promo h3 {
            font-size: 32px;
            color: #8b4dff;
            margin-bottom: 15px;
        }

        .no-promo p {
            font-size: 18px;
            color: #999;
        }

        /* Features Section */
        .features-section {
            padding: 100px 40px;
            background: linear-gradient(180deg, #000 0%, #0a0014 100%);
            position: relative;
        }

        .features-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 80px;
        }

        .section-title {
            font-size: 48px;
            font-weight: 900;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #8b4dff, #6e22dd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Brands Section */
        .brands-section {
            margin-bottom: 80px;
            position: relative;
        }

        .brand-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            margin-bottom: 60px;
        }

        .brand-card {
            background-color: #1a1a1a;
            padding: 35px 25px;
            border-radius: 15px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-card:hover {
            transform: translateY(-10px);
            border-color: #6e22dd;
            box-shadow: 0 10px 30px rgba(110, 34, 221, 0.3);
        }

        .brand-card img {
            width: 100px;
            height: auto;
            filter: grayscale(100%);
            transition: filter 0.3s ease;
        }

        .brand-card:hover img {
            filter: grayscale(0%);
        }

        /* Services Section */
        .services-section {
            text-align: center;
            position: relative;
            padding-bottom: 80px;
        }

        .services-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            margin-top: 60px;
        }

        .service-card {
            background: linear-gradient(135deg, #1a1a1a 0%, #252525 100%);
            border: 2px solid #6e22dd;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            text-decoration: none;
            color: #fff;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(110, 34, 221, 0.1) 0%, transparent 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: 0;
        }

        .service-card:hover::before {
            opacity: 1;
        }

        .service-card:hover {
            transform: translateY(-15px);
            border-color: #8b4dff;
            box-shadow: 0 15px 40px rgba(110, 34, 221, 0.5);
        }

        .service-card > * {
            position: relative;
            z-index: 1;
        }

        .service-icon {
            font-size: 70px;
            margin-bottom: 25px;
            filter: drop-shadow(0 4px 10px rgba(110, 34, 221, 0.4));
            transition: transform 0.4s ease;
        }

        .service-card:hover .service-icon {
            transform: scale(1.15);
        }

        .service-title {
            font-weight: 800;
            font-size: 22px;
            margin-bottom: 15px;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .service-description {
            font-weight: 400;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 30px;
            color: #bbb;
        }

        .service-btn {
            display: inline-block;
            background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
            color: white;
            padding: 14px 40px;
            border: none;
            border-radius: 30px;
            text-align: center;
            text-decoration: none;
            font-weight: 800;
            cursor: pointer;
            font-family: inherit;
            font-size: 15px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            box-shadow: 0 6px 20px rgba(110, 34, 221, 0.4);
        }

        .service-btn:hover {
            background: linear-gradient(135deg, #8b4dff 0%, #6e22dd 100%);
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(110, 34, 221, 0.6);
        }

        /* Animations */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 0.4;
            }
            50% {
                opacity: 0.8;
            }
        }

        @keyframes gridMove {
            0% {
                transform: translate(0, 0);
            }
            100% {
                transform: translate(50px, 50px);
            }
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateX(-50%) translateY(0);
            }
            50% {
                transform: translateX(-50%) translateY(-20px);
            }
        }

        @keyframes scroll {
            0% {
                opacity: 0;
                top: 10px;
            }
            50% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                top: 30px;
            }
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .hero-title {
                font-size: 56px;
            }

            .hero-title .highlight {
                font-size: 64px;
            }

            .section-title {
                font-size: 38px;
            }
        }

        @media (max-width: 768px) {
            .hero-banner {
                min-height: 600px;
            }

            .hero-content-wrapper {
                padding: 40px 30px;
            }

            .hero-title {
                font-size: 40px;
            }

            .hero-title .highlight {
                font-size: 48px;
            }

            .hero-description {
                font-size: 16px;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .btn-primary,
            .btn-secondary {
                width: 100%;
                text-align: center;
            }

            .promo-banner {
                padding: 40px 30px;
                flex-direction: column;
                text-align: center;
            }

            .promo-title {
                font-size: 28px;
            }

            .promo-new-price {
                font-size: 36px;
            }

            .section-title {
                font-size: 32px;
            }

            .brand-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .services-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- Hero Banner -->
    <section class="hero-banner">
        <div class="hero-content-wrapper">
            <div class="hero-text">
                <h1 class="hero-title">
                    BUILD MACHINE
                    <span class="highlight">WITH TASTE</span>
                </h1>
                <p class="hero-description">
                    Choose your parts. Set your power. Custom PC builds, repairs, warranty claims, and more — all in one place.
                </p>
                <div class="hero-buttons">
                    <a href="buildpc.php" class="btn-primary">Start Building</a>
                    <a href="buildservices.php" class="btn-secondary">Explore More</a>
                </div>
            </div>
        </div>
        
        <div class="scroll-indicator">
            <span>Scroll</span>
            <div class="scroll-arrow"></div>
        </div>
    </section>

    <!-- Promo Section -->
    <section class="promo-section" id="promo-section">
        <div class="promo-container">
            <?php if ($promoItem): ?>
            <div class="promo-banner">
                <div class="promo-content">
                    <span class="promo-tag">HOT DEAL</span>
                    <h2 class="promo-title"><?= htmlspecialchars($promoItem['part_name']) ?></h2>
                    <p class="promo-category"><?= htmlspecialchars($promoItem['category']) ?></p>
                    <div class="promo-prices">
                        <span class="promo-old-price">RM<?= number_format($promoItem['selling_price'], 2) ?></span>
                        <span class="promo-new-price">RM<?= number_format($promoItem['promo_price'], 2) ?></span>
                        <?php if ($discountPercent > 0): ?>
                        <span class="promo-discount">SAVE <?= $discountPercent ?>%</span>
                        <?php endif; ?>
                    </div>
                    <a href="buildpc.php" class="btn-primary">Build Now</a>
                </div>
            </div>
            <?php else: ?>
            <div class="no-promo">
                <h3>No Active Promotions</h3>
                <p>Stay tuned for exclusive deals and offers!</p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features-section">
        <div class="features-container">
            <div class="section-header">
                <h2 class="section-title">BUILD WITH TRUSTED BRANDS</h2>
            </div>

            <!-- Brands Section -->
            <section class="brands-section">
                <div class="brand-grid">
                    <div class="brand-card">
                        <img src="../picture/nvidia.png" alt="NVIDIA">
                    </div>
                    <div class="brand-card">
                        <img src="../picture/intel.png" alt="Intel">
                    </div>
                    <div class="brand-card">
                        <img src="../picture/amd.png" alt="AMD">
                    </div>
                    <div class="brand-card">
                        <img src="../picture/corsair.png" alt="Corsair">
                    </div>
                    <div class="brand-card">
                        <img src="../picture/kingston.png" alt="Kingston">
                    </div>
                    <div class="brand-card">
                        <img src="../picture/msi.png" alt="MSI">
                    </div>
                    <div class="brand-card">
                        <img src="../picture/asus.png" alt="ASUS">
                    </div>
                    <div class="brand-card">
                        <img src="../picture/razer.png" alt="Razer">
                    </div>
                </div>
            </section>

            <!-- Services Section -->
            <section class="services-section" id="services-section">
                <h2 class="section-title">OUR PREMIUM SERVICES</h2>
                <div class="services-container">
                    <div class="service-card">
                        <div class="service-icon">🖥️💻</div>
                        <div class="service-title">Build PC</div>
                        <div class="service-description">Build your dream PC with custom components for gaming or workstation setup</div>
                        <a href="buildpc.php" class="service-btn">Choose</a>
                    </div>

                    <div class="service-card">
                        <div class="service-icon">🔧🛡️</div>
                        <div class="service-title">Repair & Warranty</div>
                        <div class="service-description">Warranty claims and repair services for products purchased from our store</div>
                        <a href="repair_warranty.php" class="service-btn">Choose</a>
                    </div>

                    <div class="service-card">
                        <div class="service-icon">🧹⚙️</div>
                        <div class="service-title">Other Services</div>
                        <div class="service-description">PC cleaning, upgrades, diagnostics and more for all types of computers</div>
                        <a href="other_services.php" class="service-btn">Choose</a>
                    </div>
                </div>
            </section>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script>
        // Smooth scroll for scroll indicator
        document.querySelector('.scroll-indicator').addEventListener('click', () => {
            window.scrollTo({
                top: window.innerHeight,
                behavior: 'smooth'
            });
        });
    </script>
</body>
</html>