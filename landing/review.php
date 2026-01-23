<?php
// Fetch customer reviews
try {
    $stmt = $pdo->query("
        SELECT r.*, a.first_name, a.last_name
        FROM reviews r 
        LEFT JOIN account a ON r.account_id = a.account_id 
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

  .page-header {
    text-align: center;
    padding: 60px 20px 40px;
    background: linear-gradient(180deg, rgba(110, 34, 221, 0.2) 0%, transparent 100%);
    position: relative;
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

  .add-review-btn {
    position: fixed;
    bottom: 40px;
    right: 40px;
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #6e22dd 0%, #8b4dff 100%);
    color: #fff;
    border-radius: 50%;
    border: none;
    font-size: 36px;
    font-weight: 300;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 8px 30px rgba(110, 34, 221, 0.6);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
  }

  .add-review-btn:hover {
    background: linear-gradient(135deg, #5a1bb8 0%, #7a3ee6 100%);
    transform: scale(1.15) rotate(90deg);
    box-shadow: 0 12px 40px rgba(110, 34, 221, 0.8);
  }

  .add-review-btn::before {
    content: '+';
  }

  .reviews-container {
    max-width: 1400px;
    margin: 60px auto;
    padding: 0 40px;
  }

  .reviews-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
    margin-top: 40px;
  }

  .review-card {
    background: linear-gradient(135deg, #1a1a1a 0%, #252525 100%);
    border: 2px solid #333;
    border-radius: 15px;
    padding: 0;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    overflow: hidden;
    display: flex;
    flex-direction: column;
  }

  .review-card:hover {
    transform: translateY(-8px);
    border-color: #6e22dd;
    box-shadow: 0 12px 35px rgba(110, 34, 221, 0.5);
  }

  .review-image {
    width: 100%;
    aspect-ratio: 1;
    object-fit: cover;
    border-bottom: 2px solid #333;
  }

  .placeholder-image {
    width: 100%;
    aspect-ratio: 1;
    background: rgba(110, 34, 221, 0.05);
    border-bottom: 2px solid rgba(110, 34, 221, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 80px;
    color: rgba(110, 34, 221, 0.3);
  }

  .review-content {
    padding: 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
  }

  .rating {
    display: flex;
    gap: 3px;
    margin-bottom: 12px;
  }

  .star {
    color: #fbbf24;
    font-size: 18px;
  }

  .star.empty {
    color: rgba(255, 255, 255, 0.1);
  }

  .reviewer-name {
    font-size: 16px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 12px;
  }

  .review-text {
    font-size: 14px;
    line-height: 1.7;
    color: #aaa;
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .empty-state {
    text-align: center;
    padding: 100px 20px;
    grid-column: 1 / -1;
  }

  .empty-state-icon {
    font-size: 100px;
    margin-bottom: 25px;
    opacity: 0.2;
  }

  .empty-state-title {
    font-size: 28px;
    font-weight: 700;
    color: #6e22dd;
    margin-bottom: 15px;
  }

  .empty-state-text {
    font-size: 16px;
    color: #888;
    margin-bottom: 30px;
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

  @media (max-width: 1200px) {
    .reviews-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 25px;
    }
  }

  @media (max-width: 768px) {
    .page-title {
      font-size: 32px;
    }

    .page-subtitle {
      font-size: 16px;
    }

    .reviews-container {
      padding: 0 20px;
      margin: 40px auto;
    }

    .reviews-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
    }

    .add-review-btn {
      width: 60px;
      height: 60px;
      font-size: 32px;
      bottom: 30px;
      right: 30px;
    }
  }

  @media (max-width: 480px) {
    .reviews-grid {
      grid-template-columns: 1fr;
    }

    .add-review-btn {
      width: 55px;
      height: 55px;
      font-size: 28px;
      bottom: 20px;
      right: 20px;
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

  <?php if ($isLoggedIn && $userRole == 'pelanggan'): ?>
    <a href="addreview.php" class="add-review-btn"></a>
  <?php endif; ?>

  <div class="reviews-container">
    <div class="reviews-grid">
      <?php if (empty($customerReviews)): ?>
        <div class="empty-state">
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

            <div class="review-content">
              <div class="rating">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <span class="star <?php echo $i <= $review['rating'] ? '' : 'empty'; ?>">★</span>
                <?php endfor; ?>
              </div>

              <div class="reviewer-name">
    <?php 
    if (!empty($review['first_name']) && !empty($review['last_name'])) {
        echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']);
    } else {
        echo 'Anonymous User'; // atau 'Deleted User'
    }
    ?>
</div>

              <p class="review-text"><?php echo htmlspecialchars($review['review_text']); ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <?php include 'footer.php'; ?>
</body>
</html>