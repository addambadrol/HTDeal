<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HTDeal - Build & Services</title>
  <link rel="stylesheet" href="./style.css" />
  <style>
  /* Reset */
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

  /* Page Header */
  .page-header {
    text-align: center;
    padding: 80px 20px 130px;
    background: linear-gradient(180deg, rgba(110, 34, 221, 0.2) 0%, transparent 100%);
    position: relative;
    overflow: hidden;
  }

  .page-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0.5;
  }

  .page-header > * {
    position: relative;
    z-index: 1;
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

  /* Container */
  .container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 40px;
    max-width: 1200px;
    margin: 80px auto;
    padding: 0 20px;
  }

  /* Service Cards */
  .card {
    background: linear-gradient(135deg, #1a1a1a 0%, #252525 100%);
    border: 2px solid #6e22dd;
    border-radius: 20px;
    padding: 50px 35px;
    text-align: center;
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
  }

  .card::before {
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

  .card:hover::before {
    opacity: 1;
  }

  .card:hover {
    transform: translateY(-15px);
    border-color: #8b4dff;
    box-shadow: 0 15px 40px rgba(110, 34, 221, 0.5);
  }

  .card > * {
    position: relative;
    z-index: 1;
  }

  .icon {
    font-size: 80px;
    margin-bottom: 30px;
    filter: drop-shadow(0 4px 10px rgba(110, 34, 221, 0.4));
    transition: transform 0.4s ease;
  }

  .card:hover .icon {
    transform: scale(1.2);
  }

  .title {
    font-weight: 800;
    font-size: 24px;
    margin-bottom: 20px;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .desc {
    font-weight: 400;
    font-size: 16px;
    line-height: 1.7;
    margin-bottom: 35px;
    color: #bbb;
  }

  .btn {
    display: inline-block;
    background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
    color: white;
    padding: 16px 45px;
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

  .btn:hover {
    background: linear-gradient(135deg, #8b4dff 0%, #6e22dd 100%);
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(110, 34, 221, 0.6);
  }

  .btn:active {
    transform: translateY(0);
  }

  /* Card animations */
  .card:nth-child(1) {
    animation: fadeInUp 0.6s ease-out 0.1s both;
  }

  .card:nth-child(2) {
    animation: fadeInUp 0.6s ease-out 0.3s both;
  }

  .card:nth-child(3) {
    animation: fadeInUp 0.6s ease-out 0.5s both;
  }

  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(40px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Responsive */
  @media (max-width: 768px) {
    .page-header {
      padding: 60px 20px 40px;
    }

    .page-header h1 {
      font-size: 36px;
    }

    .page-header p {
      font-size: 16px;
    }

    .container {
      grid-template-columns: 1fr;
      gap: 30px;
      margin: 60px auto;
    }

    .card {
      padding: 40px 30px;
    }

    .icon {
      font-size: 70px;
    }

    .title {
      font-size: 22px;
    }

    .desc {
      font-size: 15px;
    }
  }
  </style>
</head>
<body>
  <?php include 'header.php'; ?>

  <div class="page-header">
    <h1>Build & Services</h1>
    <p>Select the service you need from the options below</p>
  </div>

  <div class="container">
    <div class="card">
      <div class="icon">🖥️💻</div>
      <div class="title">Build PC</div>
      <div class="desc">Build your dream PC with custom components for gaming or workstation setup</div>
      <a href="buildpc.php" class="btn">Choose</a>
    </div>

    <div class="card">
      <div class="icon">🔧🛡️</div>
      <div class="title">Repair & Warranty</div>
      <div class="desc">Warranty claims and repair services for products purchased from our store</div>
      <br><a href="repair_warranty.php" class="btn">Choose</a>
    </div>

    <div class="card">
      <div class="icon">🧹⚙️</div>
      <div class="title">Other Services</div>
      <div class="desc">PC cleaning, upgrades, diagnostics and more for all types of computers</div>
      <br><a href="other_services.php" class="btn">Choose</a>
    </div>
  </div>

  <?php include 'footer.php'; ?>
</body>
</html>