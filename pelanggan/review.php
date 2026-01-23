<?php
session_start();
require_once '../db_config.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['account_id']);
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : null;

// Fetch customer reviews
try {
    $stmt = $pdo->query("
        SELECT r.*, a.first_name, a.last_name
        FROM reviews r 
        JOIN account a ON r.account_id = a.account_id 
        WHERE r.reviewer_type = 'pelanggan' AND r.status = 'active' 
        ORDER BY r.created_at DESC
    ");
    $customerReviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $customerReviews = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - Reviews</title>
  <link rel="stylesheet" href="./style.css" />
  <style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }

  body {
    background-color: #121212;
    color: #fff;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }

  header {
    background-color: #6e22dd;
    padding: 10px 20px;
  }

  .navbar {
    display: flex;
    align-items: center;
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
  }

  .logo img {
    height: 35px;
  }

  .nav-links {
    display: flex;
    gap: 25px;
    margin-left: auto;
  }

  .nav-links a {
    color: #fff;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: color 0.3s;
  }

  .nav-links a:hover {
    color: #ccc;
  }

  .profile-icon img {
    width: 35px;
    height: 35px;
    cursor: pointer;
    margin-left: 20px;
  }

  .page-header {
    text-align: center;
    padding: 50px 20px 100px;
    background: linear-gradient(180deg, rgba(110, 34, 221, 0.2) 0%, transparent 100%);
    position: relative;
    overflow: hidden;
  }

  .page-title {
    font-size: 48px;
    font-weight: 900;
    background: linear-gradient(135deg, #8b4dff, #6e22dd);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 15px;
    letter-spacing: 1px;
    text-transform: uppercase;
  }

  .page-subtitle {
    font-size: 18px;
    color: #bbb;
    font-weight: 400;
  }

  .section-container {
    max-width: 1200px;
    margin: 30px auto 60px;
    padding: 0 20px;
  }

  .review-section {
    background: linear-gradient(145deg, #1a1a2e 0%, #16162a 100%);
    border: 2px solid #6e22dd;
    border-radius: 20px;
    padding: 40px 30px;
    margin-bottom: 40px;
    box-shadow: 0 8px 32px rgba(110, 34, 221, 0.3);
  }

  .section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid rgba(110, 34, 221, 0.3);
  }

  .section-title {
    font-size: 24px;
    font-weight: 800;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .add-review-btn {
    background: linear-gradient(135deg, #6e22dd 0%, #8b4dff 100%);
    color: #fff;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    border: none;
    font-size: 28px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    text-decoration: none;
    box-shadow: 0 4px 15px rgba(110, 34, 221, 0.4);
  }

  .add-review-btn:hover {
    background: linear-gradient(135deg, #5a1bb8 0%, #7a3ee6 100%);
    transform: rotate(90deg) scale(1.1);
    box-shadow: 0 6px 20px rgba(110, 34, 221, 0.6);
  }

  .carousel-container {
    position: relative;
    overflow: hidden;
    padding: 0 60px;
  }

  .carousel-wrapper {
    display: flex;
    gap: 25px;
    overflow-x: auto;
    scroll-behavior: smooth;
    scrollbar-width: none;
    padding: 10px 0;
    scroll-snap-type: x mandatory;
  }

  .carousel-wrapper::-webkit-scrollbar {
    display: none;
  }

  .arrow-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(110, 34, 221, 0.9);
    border: none;
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    font-size: 24px;
    cursor: pointer;
    z-index: 10;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(110, 34, 221, 0.4);
  }

  .arrow-btn:hover {
    background: #6e22dd;
    transform: translateY(-50%) scale(1.1);
    box-shadow: 0 6px 20px rgba(110, 34, 221, 0.6);
  }

  .arrow-btn.left {
    left: 0;
  }

  .arrow-btn.right {
    right: 0;
  }

  .review-card {
    min-width: 280px;
    max-width: 280px;
    background: linear-gradient(135deg, #1a1a1a 0%, #252525 100%);
    border: 2px solid #333;
    border-radius: 15px;
    padding: 20px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    scroll-snap-align: start;
  }

  .review-card:hover {
    transform: translateY(-5px);
    border-color: #6e22dd;
    box-shadow: 0 8px 25px rgba(110, 34, 221, 0.4);
  }

  .review-image {
    width: 100%;
    aspect-ratio: 3 / 4;
    border-radius: 12px;
    object-fit: cover;
    margin-bottom: 15px;
    border: 2px solid #333;
  }

  .placeholder-image {
    width: 100%;
    aspect-ratio: 3 / 4;
    background: rgba(110, 34, 221, 0.05);
    border: 2px dashed rgba(110, 34, 221, 0.3);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 60px;
    margin-bottom: 15px;
    color: rgba(110, 34, 221, 0.3);
  }

  .rating {
    display: flex;
    gap: 3px;
    margin-bottom: 12px;
  }

  .star {
    color: #fbbf24;
    font-size: 16px;
  }

  .star.empty {
    color: rgba(255, 255, 255, 0.1);
  }

  .reviewer-name {
    font-size: 14px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 8px;
  }

  .review-text {
    font-size: 14px;
    line-height: 1.6;
    color: #aaa;
    display: -webkit-box;
    -webkit-line-clamp: 5;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .empty-state {
    text-align: center;
    padding: 80px 20px;
    background: rgba(110, 34, 221, 0.05);
    border: 2px dashed rgba(110, 34, 221, 0.3);
    border-radius: 15px;
  }

  .empty-state-icon {
    font-size: 80px;
    margin-bottom: 20px;
    opacity: 0.3;
  }

  .empty-state-title {
    font-size: 20px;
    font-weight: 700;
    color: #6e22dd;
    margin-bottom: 10px;
  }

  .empty-state-text {
    font-size: 14px;
    color: #888;
  }

  footer {
    text-align: center;
    padding: 30px 20px;
    background-color: #0a0a0a;
    font-size: 12px;
    color: #666;
    margin-top: auto;
    border-top: 1px solid rgba(110, 34, 221, 0.2);
  }

  @media (max-width: 768px) {
    .nav-links {
      display: none;
    }

    .page-title {
      font-size: 28px;
    }

    .review-section {
      padding: 30px 20px;
    }

    .carousel-container {
      padding: 0 50px;
    }

    .arrow-btn {
      width: 40px;
      height: 40px;
      font-size: 20px;
    }

    .arrow-btn.left {
      left: 5px;
    }

    .arrow-btn.right {
      right: 5px;
    }
  }
  </style>
</head>
<body>
  <?php include 'header.php'; ?>

  <div class="page-header">
    <h1 class="page-title">CUSTOMER REVIEWS</h1>
    <p class="page-subtitle">Leave your review to us</p>
  </div>

  <div class="section-container">
    <div class="review-section">
      <div class="section-header">
        <h2 class="section-title">Your Reviews</h2>
        <?php if ($isLoggedIn && $userRole == 'pelanggan'): ?>
          <a href="addreview.php" class="add-review-btn">+</a>
        <?php endif; ?>
      </div>

      <div class="carousel-container">
        <button class="arrow-btn left" onclick="scrollCarousel('yourReviews', -1)">‹</button>
        <!-- <div class="carousel-wrapper" id="yourReviews">
          <?php if (empty($customerReviews)): ?>
            <div class="empty-state" style="width: 100%; min-width: 100%;">
              <div class="empty-state-icon">📝</div>
              <div class="empty-state-title">No Reviews Yet</div>
              <p class="empty-state-text">Be the first to share your experience with us!</p>
            </div>
          <?php else: ?>
            <?php foreach ($customerReviews as $review): ?>
              <div class="review-card">
                <?php if (!empty($review['review_image'])): ?>
                  <img src="<?php echo htmlspecialchars($review['review_image']); ?>" alt="Review" class="review-image" />
                <?php else: ?>
                  <div class="placeholder-image">📷</div>
                <?php endif; ?>

                <div class="rating">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="star <?php echo $i <= $review['rating'] ? '' : 'empty'; ?>">★</span>
                  <?php endfor; ?>
                </div>

                <div class="reviewer-name">
                  <?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?>
                </div>

                <p class="review-text"><?php echo htmlspecialchars($review['review_text']); ?></p>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div> -->
        <button class="arrow-btn right" onclick="scrollCarousel('yourReviews', 1)">›</button>
      </div>
    </div>
  </div>

  <?php include 'footer.php'; ?>

  <script>
    function scrollCarousel(id, direction) {
      const carousel = document.getElementById(id);
      const cardWidth = 280;
      const gap = 25;
      const scrollAmount = (cardWidth + gap) * 3 * direction;
      
      carousel.scrollBy({
        left: scrollAmount,
        behavior: 'smooth'
      });
    }

    // Touch/Swipe support for mobile
    document.querySelectorAll('.carousel-wrapper').forEach(carousel => {
      let isDown = false;
      let startX;
      let scrollLeft;

      carousel.addEventListener('mousedown', (e) => {
        isDown = true;
        startX = e.pageX - carousel.offsetLeft;
        scrollLeft = carousel.scrollLeft;
        carousel.style.cursor = 'grabbing';
      });

      carousel.addEventListener('mouseleave', () => {
        isDown = false;
        carousel.style.cursor = 'grab';
      });

      carousel.addEventListener('mouseup', () => {
        isDown = false;
        carousel.style.cursor = 'grab';
      });

      carousel.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - carousel.offsetLeft;
        const walk = (x - startX) * 2;
        carousel.scrollLeft = scrollLeft - walk;
      });

      carousel.style.cursor = 'grab';
    });
  </script>
</body>
</html>