<?php
// This file generates the HTML for invoice
// It receives data via variables: $appointment, $customer, $items (array of build items)

// Format date
$formatted_date = date('d/m/Y', strtotime($appointment['appointment_date']));
$formatted_time = date('h:i A', strtotime($appointment['appointment_time']));
$invoice_date = date('d-m-Y', strtotime($appointment['created_at']));

// Calculate totals
$subtotal = 0;
if (isset($items) && is_array($items)) {
    foreach ($items as $item) {
        $subtotal += $item['unit_price'] * $item['quantity'];
    }
}

// Convert number to words function
function numberToWords($number) {
    $ones = array('', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine');
    $tens = array('', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety');
    $teens = array('Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen');
    
    if ($number < 10) {
        return $ones[$number];
    } elseif ($number < 20) {
        return $teens[$number - 10];
    } elseif ($number < 100) {
        return $tens[intval($number / 10)] . ' ' . $ones[$number % 10];
    } elseif ($number < 1000) {
        return $ones[intval($number / 100)] . ' Hundred ' . numberToWords($number % 100);
    } elseif ($number < 1000000) {
        return numberToWords(intval($number / 1000)) . ' Thousand ' . numberToWords($number % 1000);
    }
    return $number;
}

$amount_words = numberToWords(intval($appointment['total_amount'])) . ' Ringgit Only';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
            background: #fff;
        }
        
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            border: 2px solid #000;
        }
        
        .invoice-header {
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #000;
            margin-bottom: 8px;
        }
        
        .company-info {
            font-size: 10px;
            line-height: 1.5;
            color: #000;
        }
        
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            color: #000;
            margin: 15px 0;
            text-decoration: underline;
        }
        
        .invoice-meta {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .invoice-meta-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .meta-right {
            text-align: right;
        }
        
        .invoice-number {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .customer-section {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border: 2px solid #000;
        }
        
        .customer-col {
            display: table-cell;
            width: 50%;
            padding: 10px;
            vertical-align: top;
            border-right: 2px solid #000;
        }
        
        .customer-col:last-child {
            border-right: none;
        }
        
        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 8px;
            text-decoration: underline;
        }
        
        .customer-info {
            line-height: 1.6;
            font-size: 10px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border: 2px solid #000;
        }
        
        .items-table th {
            background-color: #000;
            color: #fff;
            padding: 8px;
            text-align: left;
            font-size: 11px;
            border: 1px solid #000;
        }
        
        .items-table td {
            padding: 8px;
            border: 1px solid #000;
            font-size: 10px;
        }
        
        .items-table .text-right {
            text-align: right;
        }
        
        .items-table .text-center {
            text-align: center;
        }
        
        .totals-section {
            margin-top: 20px;
        }
        
        .total-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        
        .total-label {
            display: table-cell;
            text-align: right;
            padding-right: 20px;
            font-weight: bold;
            width: 80%;
        }
        
        .total-value {
            display: table-cell;
            text-align: right;
            font-weight: bold;
            width: 20%;
        }
        
        .grand-total {
            font-size: 13px;
            border-top: 2px solid #000;
            padding-top: 8px;
        }
        
        .amount-words {
            margin: 15px 0;
            font-weight: bold;
            font-size: 11px;
        }
        
        .notes-section {
            margin-top: 20px;
            padding: 10px;
            border: 2px solid #000;
        }
        
        .notes-title {
            font-weight: bold;
            margin-bottom: 8px;
            text-decoration: underline;
        }
        
        .notes-list {
            margin-left: 15px;
            line-height: 1.8;
            font-size: 9px;
        }
        
        .notes-list li {
            margin-bottom: 3px;
        }
        
        .footer-section {
            display: table;
            width: 100%;
            margin-top: 30px;
            border-top: 2px solid #000;
            padding-top: 15px;
        }
        
        .footer-col {
            display: table-cell;
            width: 33.33%;
            vertical-align: top;
            padding: 0 10px;
        }
        
        .footer-title {
            font-weight: bold;
            margin-bottom: 5px;
            text-decoration: underline;
        }
        
        .signature-line {
            margin-top: 50px;
            border-top: 1px solid #000;
            width: 200px;
            text-align: center;
            padding-top: 5px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .invoice-box {
                border: none;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <!-- Header -->
        <div class="invoice-header">
            <div class="company-name"><?php echo COMPANY_NAME; ?></div>
            <div class="company-info">
                <?php echo COMPANY_ADDRESS; ?><br>
                <?php echo COMPANY_PHONE; ?><br>
                <?php echo COMPANY_EMAIL; ?><br>
                SSM - <?php echo COMPANY_SSM; ?>
            </div>
        </div>
        
        <!-- Invoice Title -->
        <div class="invoice-title">INVOICE</div>
        
        <!-- Invoice Number & Date -->
        <div class="invoice-meta">
            <div class="invoice-meta-col">
                <div class="invoice-number"><?php echo $customer['first_name'] . ' ' . $customer['last_name']; ?></div>
            </div>
            <div class="invoice-meta-col meta-right">
                <div class="invoice-number"><?php echo $appointment['invoice_number']; ?></div>
                <div><?php echo $invoice_date; ?></div>
            </div>
        </div>
        
        <!-- Customer Details -->
        <div class="customer-section">
            <div class="customer-col">
                <div class="section-title">Bill To</div>
                <div class="customer-info">
                    <?php echo $customer['first_name'] . ' ' . $customer['last_name']; ?><br>
                    <?php echo $customer['phone_number']; ?>
                </div>
            </div>
            <div class="customer-col">
                <div class="section-title">Ship To</div>
                <div class="customer-info">
                    <?php echo isset($customer['address']) ? $customer['address'] : 'N/A'; ?>
                </div>
            </div>
        </div>
        
        <!-- Items Table -->
        <!-- Items Table -->
<table class="items-table">
    <thead>
        <tr>
            <th style="width: 8%;">Sr No.</th>
            <th style="width: 52%;">Product</th>
            <th style="width: 10%;" class="text-center">Quantity</th>
            <th style="width: 15%;" class="text-right">Rate</th>
            <th style="width: 15%;" class="text-right">Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        if (isset($items) && is_array($items) && count($items) > 0):
            $sr = 1;
            foreach ($items as $item): 
                $amount = $item['unit_price'] * $item['quantity'];
        ?>
        <tr>
            <td class="text-center"><?php echo $sr; ?></td>
            <td>
                <?php echo htmlspecialchars($item['part_name']); ?><br>
                <small style="font-size: 9px; color: #666;">
                <?php 
                // Display warranty info based on category
                $category = strtoupper($item['category']);
                if (strpos($category, 'PROCESSOR') !== false || strpos($category, 'RAM') !== false) {
                    echo '# LIFETIME LIMITED WARRANTY';
                } elseif (strpos($category, 'GRAPHICS') !== false || strpos($category, 'SSD') !== false || strpos($category, 'DRIVE') !== false || strpos($category, 'POWER') !== false) {
                    echo '# 2 YEARS WARRANTY';
                } elseif (strpos($category, 'MOTHERBOARD') !== false || strpos($category, 'COOLER') !== false || strpos($category, 'CHASSIS') !== false) {
                    echo '# 1 YEAR WARRANTY';
                } else {
                    echo '# WARRANTY APPLIES';
                }
                ?>
                </small>
            </td>
            <td class="text-center"><?php echo number_format($item['quantity'], 2); ?></td>
            <td class="text-right">RM <?php echo number_format($item['unit_price'], 2); ?></td>
            <td class="text-right">RM <?php echo number_format($amount, 2); ?></td>
        </tr>
        <?php 
            $sr++;
            endforeach; 
        ?>
        <tr>
            <td class="text-center"><strong><?php echo $sr; ?></strong></td>
            <td><strong>TOTAL</strong></td>
            <td class="text-center"><strong>1.00</strong></td>
            <td class="text-right"><strong>RM <?php echo number_format($appointment['total_amount'], 2); ?></strong></td>
            <td class="text-right"><strong>RM <?php echo number_format($appointment['total_amount'], 2); ?></strong></td>
        </tr>
        <?php else: ?>
        <tr>
            <td colspan="5" style="text-align: center;">No items found</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>
        
        <!-- Totals -->
        <div class="totals-section">
            <div class="total-row">
                <div class="total-label">SubTotal</div>
                <div class="total-value">RM <?php echo number_format($appointment['total_amount'], 2); ?></div>
            </div>
            <div class="total-row">
                <div class="total-label">Paid <?php echo $invoice_date; ?></div>
                <div class="total-value">RM <?php echo number_format($appointment['total_amount'], 2); ?></div>
            </div>
            <div class="total-row grand-total">
                <div class="total-label">Grand Total</div>
                <div class="total-value">RM <?php echo number_format($appointment['total_amount'], 2); ?></div>
            </div>
        </div>
        
        <!-- Amount in Words -->
        <div class="amount-words">
            Amount In Words: <?php echo $amount_words; ?>
        </div>
        
        <!-- Notes -->
        <div class="notes-section">
            <div class="notes-title">Please Note</div>
            <ul class="notes-list">
                <li>The customer requires to return the product within the warranty period.</li>
                <li>Customer need to bare the warranty & returning fee.</li>
                <li>Goods sold are not refundable.</li>
                <li>The warranty does not cover damage resulting from misuse - physical damage - burn mark - crack Installation - modified bios.</li>
                <li>Deposit or booking fee is not refundable.</li>
                <li>Used item cover only 30days warranty</li>
            </ul>
        </div>
        
        <!-- Footer -->
        <div class="footer-section">
            <div class="footer-col">
                <div class="footer-title">Payable To</div>
                <div><?php echo COMPANY_NAME; ?></div>
            </div>
            <div class="footer-col">
                <div class="footer-title">Banking Details</div>
                <div><?php echo BANK_NAME; ?>: <?php echo BANK_ACCOUNT; ?></div>
            </div>
            <div class="footer-col">
                <div class="footer-title">Other Details</div>
                <div>Cash Pay</div>
            </div>
        </div>
        
        <!-- Signature -->
        <div style="text-align: right; margin-top: 40px;">
            <div class="signature-line">Signature</div>
        </div>
    </div>
</body>
</html>