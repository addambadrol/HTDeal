<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTDeal - Log In Your Profile</title>
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
        
        /* Navbar */
        /*header {
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
        }*/
        
        /* Page Header */
        .page-header {
            text-align: center;
    padding: 80px 20px 100px;
    background: linear-gradient(180deg, rgba(110, 34, 221, 0.2) 0%, transparent 100%);
    position: relative;
    overflow: hidden;
        }
        
        .page-header h1 {
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
        
        .page-header p {
            font-size: 18px;
    color: #bbb;
    font-weight: 400;
        }
        
        /* Main Container */
        main {
            max-width: 800px;
            margin: 40px auto 60px auto;
            padding: 0 20px;
            width: 100%;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* No Account Card */
        .no-account-card {
            background: linear-gradient(135deg, #1a1a1a 0%, #252525 100%);
            border: 2px solid #6e22dd;
            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(110, 34, 221, 0.3);
            width: 100%;
            max-width: 600px;
        }
        
        .profile-icon-large {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            background: rgba(110, 34, 221, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #6e22dd;
        }
        
        .profile-icon-large img {
            width: 80px;
            height: 80px;
            opacity: 0.7;
        }
        
        .no-account-title {
            font-size: 28px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 15px;
        }
        
        .no-account-text {
            font-size: 16px;
            color: #aaa;
            margin-bottom: 35px;
            line-height: 1.6;
        }
        
        .login-actions {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-width: 400px;
            margin: 0 auto;
        }
        
        .btn-login {
            padding: 16px 40px;
            background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 6px 20px rgba(110, 34, 221, 0.4);
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(110, 34, 221, 0.5);
        }
        
        .btn-home {
            padding: 16px 40px;
            background: #333;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-home:hover {
            background: #444;
            transform: translateY(-2px);
        }
        
        .features-list {
            margin-top: 40px;
            padding-top: 40px;
            border-top: 2px solid rgba(110, 34, 221, 0.2);
        }
        
        .features-title {
            font-size: 18px;
            font-weight: 700;
            color: #6e22dd;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            text-align: left;
        }
        
        .feature-icon {
            font-size: 24px;
            min-width: 40px;
            height: 40px;
            background: rgba(110, 34, 221, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .feature-text {
            font-size: 14px;
            color: #ccc;
            text-align: center;
        }
        
        /* Footer */
        footer {
            text-align: center;
            padding: 30px 20px;
            background-color: #0a0a0a;
            font-size: 12px;
            color: #666;
            border-top: 1px solid rgba(110, 34, 221, 0.2);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .page-header h1 {
                font-size: 28px;
            }
            
            .no-account-card {
                padding: 40px 25px;
            }
            
            .no-account-title {
                font-size: 24px;
            }
            
            .no-account-text {
                font-size: 14px;
            }
            
            .profile-icon-large {
                width: 100px;
                height: 100px;
            }
            
            .profile-icon-large img {
                width: 65px;
                height: 65px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="page-header">
        <h1>My Profile</h1>
        <p>Access your account to view profile and appointments</p>
    </div>

    <main>
        <div class="no-account-card">
            <div class="profile-icon-large">
                <img src="../picture/profileicon.png" alt="Profile" />
            </div>
            
            <h2 class="no-account-title">No Account Logged In</h2>
            <p class="no-account-text">
                You need to log in to access your profile, view appointments, and manage your orders. 
                Create an account or log in to get started with HTDeal services.
            </p>
            
            <div class="login-actions">
                <a href="loginpelanggan.php" class="btn-login">Log In to Your Account</a>
                <a href="homepage.php" class="btn-home">Back to Home</a>
            </div>
            
            <div class="features-list">
                <h3 class="features-title">What You Can Do After Login</h3>
                
                <div class="feature-item">
                    <div class="feature-text">View and manage your appointments</div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-text">Access your invoices and receipts</div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-text">Build and customize your PC</div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-text">Leave reviews and feedback</div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>  
</body>
</html>