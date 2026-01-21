<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;

try {
    $dompdf = new Dompdf();
    
    $html = '<h1>Hello World!</h1><p>DomPDF is working!</p>';
    
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    
    echo "✅ DomPDF installed successfully!<br><br>";
    echo "<a href='?pdf=1'>Click to Generate PDF</a>";
    
    if (isset($_GET['pdf'])) {
        $dompdf->stream('test.pdf');
        exit;
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>