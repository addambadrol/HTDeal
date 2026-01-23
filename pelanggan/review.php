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

  /* ===== HEADER (UNCHANGED) ===== */
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

  /* ===== PAGE HEADER ===== */
  .page-header {
    text-align: center;
    padding: 50px 20px 70px;
    background: #fff;
    color: #000;
  }

  .page-title {
    font-size: 36px;
    font-weight: 800;
    letter-spacing: 2px;
    margin-bottom: 8px;
  }

  .page-subtitle {
    font-size: 14px;
    color: #444;
  }

  /* ===== GRID REVIEW LAYOUT ===== */
  .review-grid-container{
    max-width: 1300px;
    margin: 0 auto 80px;
    padding: 0 40px;
  }

  .review-grid{
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 60px 50px;
    justify-items: center;
  }

  .review-box{
    width: 100%;
    max-width: 320px;
    height: 420px;
    border: 4px solid #000;
    padding: 18px;
    background: #fff;
    position: relative;
  }

  .review-inner{
    width: 100%;
    height: 220px;
    border: 4px solid #000;
    margin-bottom: 15px;
    overflow: hidden;
    background:#f5f5f5;
    display:flex;
    align-items:center;
    justify-content:center;
  }

  .review-inner img{
    width:100%;
    height:100%;
    object-fit:cover;
  }

  .review-name{
    font-size:14px;
    font-weight:700;
    color:#000;
    margin-bottom:6px;
  }

  .review-rating{
    margin-bottom:8px;
  }

  .review-rating span{
    color:#f5b301;
    font-size:14px;
  }

  .review-text{
    font-size:13px;
    line-height:1.5;
    color:#222;
    overflow:hidden;
    display:-webkit-box;
    -webkit-line-clamp:6;
    -webkit-box-orient:vertical;
  }

  /* ===== EMPTY STATE ===== */
  .empty-state{
    grid-column:1 / -1;
    text-align:center;
    padding:80px 20px;
    border:3px dashed #aaa;
    color:#444;
    background:#fafafa;
  }

  /* ===== FLOATING ADD REVIEW BUTTON ===== */
  .floating-add-btn{
    position:fixed;
    right:25px;
    bottom:25px;
    width:60px;
    height:60px;
    border-radius:50%;
    background:#000;
    color:#fff;
    text-decoration:none;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:32px;
    font-weight:700;
    box-shadow:0 10px 30px rgba(0,0,0,0.4);
    transition:0.3s;
    z-index:999;
  }

  .floating-add-btn:hover{
    transform:scale(1.1) rotate(90deg);
    background:#333;
  }

  /* ===== FOOTER ===== */
  footer {
    text-align: center;
    padding: 30px 20px;
    background-color: #0a0a0a;
    font-size: 12px;
    color: #666;
    margin-top: auto;
    border-top: 1px solid rgba(110, 34, 221, 0.2);
  }

  /* ===== RESPONSIVE ===== */
  @media(max-width:1000px){
    .review-grid{
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media(max-width:600px){
    .review-grid{
      grid-template-columns: 1fr;
    }
  }

  </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="page-header">
  <h1 class="page-title">CUSTOMER REVIEW</h1>
  <p class="page-subtitle">Leave your review to us</p>
</div>

<div class="review-grid-container">
  <div class="review-grid">

    <?php if (empty($customerReviews)): ?>
      <div class="empty-state">
        <h3>No Reviews Yet</h3>
        <p>Be the first to share your experience with us.</p>
      </div>
    <?php else: ?>
      <?php foreach ($customerReviews as $review): ?>
        <div class="review-box">
          
          <div class="review-inner">
            <?php if (!empty($review['review_image'])): ?>
              <img src="<?php echo htmlspecialchars($review['review_image']); ?>" alt="Review">
            <?php else: ?>
              📷
            <?php endif; ?>
          </div>

          <div class="review-name">
            <?php echo htmlspecialchars($review['first_name'].' '.$review['last_name']); ?>
          </div>

          <div class="review-rating">
            <?php for($i=1;$i<=5;$i++): ?>
              <span><?php echo $i <= $review['rating'] ? '★' : '☆'; ?></span>
            <?php endfor; ?>
          </div>

          <div class="review-text">
            <?php echo htmlspecialchars($review['review_text']); ?>
          </div>

        </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </div>
</div>

<?php if ($isLoggedIn && $userRole == 'pelanggan'): ?>
  <a href="addreview.php" class="floating-add-btn">+</a>
<?php endif; ?>

<?php include 'footer.php'; ?>

</body>
</html>
