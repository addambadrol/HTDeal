<?php
session_start();
require_once '../db_config.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['account_id']) || $_SESSION['role'] != 'penjual') {
    header("Location: ../login/loginpenjual.php");
    exit();
}

$success = '';
$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = $_POST['rating'];
    $review_text = trim($_POST['review_text']);
    $review_image = null;
    
    // Validate input
    if (empty($rating) || empty($review_text)) {
        $error = "Please provide both rating and review text!";
    } elseif ($rating < 1 || $rating > 5) {
        $error = "Rating must be between 1 and 5 stars!";
    } else {
        // Handle image upload
        if (isset($_FILES['review_image']) && $_FILES['review_image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['review_image']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($filetype), $allowed)) {
                $newname = 'review_' . time() . '.' . $filetype;
                $upload_path = '../uploads/reviews/' . $newname;
                
                // Create directory if not exists
                if (!file_exists('../uploads/reviews/')) {
                    mkdir('../uploads/reviews/', 0777, true);
                }
                
                if (move_uploaded_file($_FILES['review_image']['tmp_name'], $upload_path)) {
                    $review_image = $upload_path;
                } else {
                    $error = "Failed to upload image!";
                }
            } else {
                $error = "Invalid file type. Only JPG, JPEG, PNG & GIF allowed!";
            }
        }
        
        // Insert review if no error
        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO reviews (account_id, reviewer_type, rating, review_text, review_image, status) VALUES (?, 'penjual', ?, ?, ?, 'active')");
                
                if ($stmt->execute([$_SESSION['account_id'], $rating, $review_text, $review_image])) {
                    $success = "Review submitted successfully! Redirecting...";
                    header("refresh:2;url=review.php");
                } else {
                    $error = "Failed to submit review. Please try again.";
                }
            } catch(PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - Add Review</title>
  <link rel="stylesheet" href="./style.css" />
<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  body {
    background-color: #0a0a0a;
    color: #fff;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }
  
  /* Navbar - Same as seller pages */
  header {
    background-color: #6e22dd;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
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
  
  .profile-icon {
    margin-left: 20px;
  }
  
  .profile-icon img {
    width: 35px;
    height: 35px;
    cursor: pointer;
  }
  
  .container {
    max-width: 700px;
    margin: 50px auto;
    padding: 0 20px;
  }
  
  .form-card {
    background: linear-gradient(145deg, #1a1a2e 0%, #16162a 100%);
    border: 2px solid #6e22dd;
    border-radius: 15px;
    padding: 40px;
    box-shadow: 0 8px 32px rgba(110, 34, 221, 0.3);
  }
  
  .form-header {
    text-align: center;
    margin-bottom: 35px;
  }
  
  .form-header h1 {
    font-size: 28px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 8px;
    background: linear-gradient(135deg, #8b4dff 0%, #6e22dd 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }
  
  .form-header p {
    color: #aaa;
    font-size: 14px;
  }
  
  .back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #8b4dff;
    text-decoration: none;
    font-weight: 600;
    margin-bottom: 25px;
    transition: all 0.3s ease;
  }
  
  .back-link:hover {
    color: #6e22dd;
    transform: translateX(-5px);
  }
  
  .back-link::before {
    content: "←";
    font-size: 20px;
  }
  
  .form-group {
    margin-bottom: 25px;
  }
  
  .form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #ddd;
    font-size: 14px;
  }
  
  .form-group label span {
    color: #ff4444;
  }
  
  .form-group input,
  .form-group textarea {
    width: 100%;
    padding: 12px 15px;
    background-color: #0a0a0a;
    border: 2px solid #333;
    border-radius: 8px;
    color: #fff;
    font-size: 14px;
    transition: all 0.3s ease;
    font-family: inherit;
  }
  
  .form-group textarea {
    resize: vertical;
    min-height: 120px;
  }
  
  .form-group input:focus,
  .form-group textarea:focus {
    outline: none;
    border-color: #6e22dd;
    box-shadow: 0 0 0 3px rgba(110, 34, 221, 0.2);
  }
  
  .form-group input::placeholder,
  .form-group textarea::placeholder {
    color: #666;
  }
  
  /* Star Rating */
  .rating-group {
    display: flex;
    gap: 10px;
    margin-top: 8px;
  }
  
  .rating-group input[type="radio"] {
    display: none;
  }
  
  .rating-group label {
    cursor: pointer;
    font-size: 35px;
    color: #333;
    transition: all 0.3s ease;
    margin: 0;
  }
  
  .rating-group label:hover,
  .rating-group label:hover ~ label {
    color: #ffd700;
    transform: scale(1.1);
  }
  
  .rating-group input[type="radio"]:checked ~ label {
    color: #ffd700;
  }
  
  .rating-group {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
  }
  
  /* Image Upload */
  .image-upload-wrapper {
    position: relative;
    margin-top: 8px;
  }
  
  .image-upload-label {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 150px;
    border: 2px dashed #333;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    background-color: #0a0a0a;
  }
  
  .image-upload-label:hover {
    border-color: #6e22dd;
    background-color: rgba(110, 34, 221, 0.05);
  }
  
  .upload-content {
    text-align: center;
  }
  
  .upload-icon {
    font-size: 50px;
    margin-bottom: 10px;
    opacity: 0.5;
  }
  
  .upload-text {
    color: #888;
    font-size: 14px;
  }
  
  .image-preview {
    width: 100%;
    max-height: 300px;
    object-fit: cover;
    border-radius: 8px;
    display: none;
  }
  
  .btn-group {
    display: flex;
    gap: 15px;
    margin-top: 35px;
  }
  
  .btn {
    flex: 1;
    padding: 14px;
    border: none;
    border-radius: 8px;
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  
  .btn-primary {
    background: linear-gradient(135deg, #6e22dd 0%, #8b4dff 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(110, 34, 221, 0.4);
  }
  
  .btn-primary:hover {
    background: linear-gradient(135deg, #5a1bb8 0%, #7a3ee6 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(110, 34, 221, 0.5);
  }
  
  .btn-secondary {
    background-color: #333;
    color: #fff;
  }
  
  .btn-secondary:hover {
    background-color: #444;
  }
  
  .alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 25px;
    font-weight: 500;
  }
  
  .alert-error {
    background-color: rgba(255, 68, 68, 0.1);
    border: 1px solid #ff4444;
    color: #ff6666;
  }
  
  .alert-success {
    background-color: rgba(76, 175, 80, 0.1);
    border: 1px solid #4CAF50;
    color: #4CAF50;
  }
  
  .form-hint {
    font-size: 12px;
    color: #888;
    margin-top: 5px;
  }
  
  @media (max-width: 768px) {
    .nav-links {
      display: none;
    }
    
    .form-card {
      padding: 25px;
    }
    
    .rating-group label {
      font-size: 30px;
    }
  }
</style>
</head>
<body>
  <header>
    <div class="navbar">
      <div class="logo">
        <a href="index.php">
          <img src="../picture/logo.png" alt="Logo" class="logo-image" />
        </a>
      </div>
      <div class="nav-links">
        <a href="homepage.php">HOME</a>
        <a href="appointment.php">APPOINTMENT</a>
        <a href="invoices.php">INVOICE</a>
        <a href="review.php">REVIEW</a>
      </div>
      <div class="profile-icon">
        <a href="profile.php">
          <img src="../picture/profileicon.png" alt="Profile" />
        </a>
      </div>
    </div>
  </header>

  <div class="container">
    <a href="review.php" class="back-link">Back to Reviews</a>
    
    <div class="form-card">
      <div class="form-header">
        <h1>Add Team Review</h1>
        <p>Share your team's experience and showcase our work</p>
      </div>
      
      <?php if(!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      
      <?php if(!empty($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>
      
      <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
          <label>Rating <span>*</span></label>
          <div class="rating-group">
            <input type="radio" id="star5" name="rating" value="5" required />
            <label for="star5">★</label>
            <input type="radio" id="star4" name="rating" value="4" />
            <label for="star4">★</label>
            <input type="radio" id="star3" name="rating" value="3" />
            <label for="star3">★</label>
            <input type="radio" id="star2" name="rating" value="2" />
            <label for="star2">★</label>
            <input type="radio" id="star1" name="rating" value="1" />
            <label for="star1">★</label>
          </div>
          <div class="form-hint">Click on the stars to rate</div>
        </div>
        
        <div class="form-group">
          <label>Review Content <span>*</span></label>
          <textarea name="review_text" placeholder="Share your experience, showcase completed projects, or highlight our service quality..." required><?php echo isset($_POST['review_text']) ? htmlspecialchars($_POST['review_text']) : ''; ?></textarea>
          <div class="form-hint">Write a detailed review about our services and projects</div>
        </div>
        
        <div class="form-group">
          <label>Upload Photo (Optional)</label>
          <div class="image-upload-wrapper">
            <input type="file" id="review_image" name="review_image" accept="image/*" style="display: none;" onchange="previewImage(event)" />
            <label for="review_image" class="image-upload-label" id="uploadLabel">
              <div class="upload-content">
                <div class="upload-icon">📷</div>
                <div class="upload-text">Click to upload an image</div>
              </div>
            </label>
            <img id="imagePreview" class="image-preview" />
          </div>
          <div class="form-hint">Supported formats: JPG, JPEG, PNG, GIF (Max 5MB)</div>
        </div>
        
        <div class="btn-group">
          <button type="button" class="btn btn-secondary" onclick="window.location.href='review.php'">Cancel</button>
          <button type="submit" class="btn btn-primary">Submit Review</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function previewImage(event) {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          const preview = document.getElementById('imagePreview');
          const label = document.getElementById('uploadLabel');
          
          preview.src = e.target.result;
          preview.style.display = 'block';
          label.style.display = 'none';
        }
        reader.readAsDataURL(file);
      }
    }
  </script>
</body>
</html>