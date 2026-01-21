<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
// db_config.php
$host = 'localhost';
$dbname = 'aguzzzco2_htdeal';
$username = 'aguzzzco2_aguzzz'; // tukar ikut your MySQL username
$password = 'U?o$ra*=Dx*1IUGe'; // tukar ikut your MySQL password

// $db_server = "localhost";
// $db_user   = "aguzzzco2_aguzzz";
// $db_pw     = 'U?o$ra*=Dx*1IUGe';
// $db_name   = "aguzzzco2_spinv";


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Company Information for Invoices
define('COMPANY_NAME', 'Ha-Ikal Tech Enterprise');
define('COMPANY_SSM', 'LA0025383-W');
define('COMPANY_ADDRESS', 'Blok D M-23 Jalan PJU 10/4A Apartment Suria Damansara Damai Petaling Jaya, Selangor');
define('COMPANY_PHONE', '6019-2501153');
define('COMPANY_EMAIL', 'heykalmykal90@gmail.com');
define('BANK_NAME', 'MAYBANK');
define('BANK_ACCOUNT', '5627 5973 6405');

// Invoice Settings
define('INVOICE_PATH', __DIR__ . '/invoices/pdf/');
?>