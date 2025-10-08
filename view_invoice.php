<?php
require_once 'config.php';
$db = getDB();

// Get invoice details
$id = (int)$_GET['id'];
$result = $db->query("
    SELECT i.*, c.name as client_name, c.email, c.company_name, c.phone 
    FROM invoices i 
    JOIN clients c ON i.client_id = c.id 
    WHERE i.id = $id
");

if ($result->num_rows == 0) {
    die("Invoice not found");
}

$invoice = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?= $invoice['invoice_number'] ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        
        /* Print styles */
        @media print {
            body { background: white; padding: 0; }
            .no-print { display: none; }
        }
        
        /* Action buttons */
        .actions { text-align: center; margin-bottom: 20px; }
        .btn { padding: 10px 20px; text-decoration: none; border-radius: 3px; margin: 5px; display: inline-block; }
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-back { background: #95a5a6; color: white; }
        
        /* Invoice card */
        .invoice { background: white; padding: 40px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .invoice-header { border-bottom: 3px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .invoice-header h1 { font-size: 32px; margin-bottom: 10px; }
        .company-details { margin-bottom: 30px; }
        .company-details h2 { font-size: 20px; margin-bottom: 10px; }
        
        /* Two column layout */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px; }
        .info-section h3 { font-size: 14px; color: #666; margin-bottom: 10px; }
        .info-section p { margin-bottom: 5px; }
        
        /* Invoice details table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 12px; text-align: left; }
        th { background: #f8f8f8; font-weight: bold; }
        .description-col { width: 60%; }
        .amount-col { text-align: right; }
        
        /* Totals section */
        .totals { margin-left: auto; width: 300px; }
        .totals table { margin-bottom: 0; }
        .totals td { border-top: 1px solid #ddd; }
        .total-row { font-size: 18px; font-weight: bold; background: #f8f8f8; }
        
        /* Status badge */
        .status-badge { display: inline-block; padding: 8px 15px; border-radius: 3px; margin-bottom: 20px; }
        .status-paid { background: #27ae60; color: white; }
        .status-pending { background: #f39c12; color: white; }
        .status-overdue { background: #e74c3c; color: white; }
        
        /* Footer */
        .invoice-footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Action Buttons (hidden when printing) -->
        <div class="actions no-print">
            <a href="index.php" class="btn btn-back">← Back to Dashboard</a>
            <button onclick="printInvoice()" class="btn btn-primary">🖨️ Print Invoice</button>
            <button onclick="downloadPDF()" class="btn btn-primary">📄 Download PDF</button>
            <?php if($invoice['status'] != 'paid'): ?>
            <a href="mark_paid.php?id=<?= $invoice['id'] ?>" class="btn btn-success">✓ Mark as Paid</a>
            <?php endif; ?>
        </div>
        
        <script>
        // Print function
        function printInvoice() {
            window.print();
        }
        
        // Download as PDF (uses browser's print to PDF)
        function downloadPDF() {
            alert('Use the Print button and select "Save as PDF" in the print dialog');
            window.print();
        }
        </script>

        <!-- Invoice Document -->
        <div class="invoice">
            <!-- Header -->
            <div class="invoice-header">
                <h1>INVOICE</h1>
                <div class="status-badge status-<?= $invoice['status'] ?>">
                    <?= strtoupper($invoice['status']) ?>
                </div>
            </div>

            <!-- Company Details -->
            <div class="company-details">
                <h2><?= COMPANY_NAME ?></h2>
                <p><?= COMPANY_ADDRESS ?></p>
                <p>Email: <?= COMPANY_EMAIL ?></p>
                <p>Phone: <?= COMPANY_PHONE ?></p>
            </div>

            <!-- Invoice & Client Info -->
            <div class="info-grid">
                <div class="info-section">
                    <h3>BILL TO:</h3>
                    <p><strong><?= $invoice['client_name'] ?></strong></p>
                    <p><?= $invoice['company_name'] ?></p>
                    <p><?= $invoice['email'] ?></p>
                    <p><?= $invoice['phone'] ?></p>
                </div>
                <div class="info-section">
                    <h3>INVOICE DETAILS:</h3>
                    <p><strong>Invoice #:</strong> <?= $invoice['invoice_number'] ?></p>
                    <p><strong>Issue Date:</strong> <?= date('d M Y', strtotime($invoice['issue_date'])) ?></p>
                    <p><strong>Due Date:</strong> <?= date('d M Y', strtotime($invoice['due_date'])) ?></p>
                    <?php if($invoice['payment_date']): ?>
                    <p><strong>Paid On:</strong> <?= date('d M Y', strtotime($invoice['payment_date'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Service Description -->
            <table>
                <thead>
                    <tr>
                        <th class="description-col">Description</th>
                        <th class="amount-col">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= nl2br($invoice['description']) ?></td>
                        <td class="amount-col">KES <?= number_format($invoice['amount'], 2) ?></td>
                    </tr>
                </tbody>
            </table>

            <!-- Totals -->
            <div class="totals">
                <table>
                    <tr>
                        <td>Subtotal:</td>
                        <td class="amount-col">KES <?= number_format($invoice['amount'], 2) ?></td>
                    </tr>
                    <tr>
                        <td>Tax (<?= (TAX_RATE * 100) ?>%):</td>
                        <td class="amount-col">KES <?= number_format($invoice['tax'], 2) ?></td>
                    </tr>
                    <tr class="total-row">
                        <td>TOTAL:</td>
                        <td class="amount-col">KES <?= number_format($invoice['total'], 2) ?></td>
                    </tr>
                </table>
            </div>

            <!-- Footer -->
            <div class="invoice-footer">
                <p>Thank you for your business!</p>
                <p>Payment terms: Due within <?= (strtotime($invoice['due_date']) - strtotime($invoice['issue_date'])) / 86400 ?> days</p>
            </div>
        </div>
    </div>
</body>
</html>