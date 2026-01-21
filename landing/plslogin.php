<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTDeal - Log In Your Account</title>
    <link rel="stylesheet" href="./style.css">

    <style>
        /* Reset */
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

  /* Navbar */
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

  .profile-icon img {
    width: 35px;
    height: 35px;
    cursor: pointer;
    margin-left: 20px;
  }
  .services-list a {
    display: inline-block;
    background-color: #6e22dd;
    color: white;
    font-weight: 600;
    text-decoration: none;
    padding: 10px 20px;
    border-radius: 20px;
    transition: background-color 0.3s ease;
    font-size: 14px;
}
.services-list a:hover {
    background-color: #520dbf;
}
        /* Footer sentiasa di bawah */
  footer {
    text-align: center;
    padding: 10px 0;
    background-color: #1a1a1a;
    font-size: 14px;
    color: #777;
  }

  /* Profile image fixed saiz */
  .profile-image {
    width: 160px;
    height: 160px;
    margin-bottom: 10px;
  }
    </style>
    
</head>
<body>
    <header>
        <div class="navbar">
    <!-- Logo on the left -->
    <div class="logo">
        <a href="index.php">
            <img src="../picture/logo.png" alt="Logo" class="logo-image">
        </a>
    </div>

    <!-- Navbar Links aligned to the right -->
    <div class="nav-links">
        <a href="plslogin.php">HOME</a>
        <a href="plslogin.php">BUILD & SERVICES</a>
        <a href="plslogin.php">REVIEW</a>
        <a href="plslogin.php">ABOUT</a>
    </div>
        
        <!-- Profile Icon -->
        <div class="profile-icon">
            <a href="plslogin.php">
                <img style="width: 35px; height: 35px;" src="../picture/profileicon.png" alt="Profile" class="profile-image">
            </a>
    </div>
</div>

    </header>

    <br><br><br><br><br><br><br><br>

    <center><img style="width: 160px; height: 160px;" src="../picture/profileicon2.png" alt="Profile" class="profile-image">

        <h2>Log In Your Account</h2>
        <div class="services-list">
            <br>
            <a href="loginpelanggan.php">LOG IN</a>
        </div>

    <br><br><br><br><br><br><br><br><br>

    </center>
    
    

    <footer>
        <p>&copy; 2025 HTDeal - SYSTEM TEMU JANJI DAN MENGURUSKAN JUAL BELI KOMPUTER HA-KAL TECH</p>
    </footer>
</body>
</html>
