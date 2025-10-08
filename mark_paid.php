<?php
require_once 'config.php';
$db = getDB();

// Get invoice ID
$id = (int)$_GET['id'];

// Handle payment confirmation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = $db->real_escape_string($_POST['payment_method']);
    $payment_date = date('Y-m-d');
    
    // Update invoice status to paid
    $db->query("
        UPDATE invoices 
        SET status = 'paid', 
            payment_date = '$payment_date', 
            payment_method = '$payment_method' 
        WHERE id = $id
    ");
    
    header('Location: view_invoice.php?id=' . $id);
    exit;
}

// Get invoice details
$invoice = $db->query("
    SELECT i.*, c.name as client_name 
    FROM invoices i 
    JOIN clients c ON i.client_id = c.id 
    WHERE i.id = $id
")->fetch_assoc();

if (!$invoice) {
    die("Invoice not found");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Invoice as Paid</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 600px; margin: 50px auto; }
        .card { background: white; padding: 30px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { margin-bottom: 20px; color: #333; }
        .invoice-info { background: #f8f8f8; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .invoice-info p { margin-bottom: 8px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; }
        select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px; }
        .btn { padding: 12px 30px; border: none; border-radius: 3px; cursor: pointer; margin-right: 10px; }
        .btn-success { background: #27ae60; color: white; }
        .btn-cancel { background: #95a5a6; color: white; text-decoration: none; display: inline-block; }
        .alert { padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2>💰 Mark Invoice as Paid</h2>
            
            <div class="alert">
                ⚠️ This action will update the invoice status to "PAID". Please confirm the payment details below.
            </div>

            <div class="invoice-info">
                <p><strong>Invoice #:</strong> <?= $invoice['invoice_number'] ?></p>
                <p><strong>Client:</strong> <?= $invoice['client_name'] ?></p>
                <p><strong>Amount:</strong> KES <?= number_format($invoice['total'], 2) ?></p>
                <p><strong>Due Date:</strong> <?= date('d M Y', strtotime($invoice['due_date'])) ?></p>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label>Payment Method *</label>
                    <select name="payment_method" required>
                        <option value="">-- Select Payment Method --</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="M-Pesa">M-Pesa</option>
                        <option value="Cash">Cash</option>
                        <option value="Cheque">Cheque</option>
                        <option value="PayPal">PayPal</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">✓ Confirm Payment</button>
                <a href="view_invoice.php?id=<?= $id ?>" class="btn btn-cancel">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>