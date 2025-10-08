<?php
require_once 'config.php';
$db = getDB();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $client_id = (int)$_POST['client_id'];
    $description = $db->real_escape_string($_POST['description']);
    $amount = (float)$_POST['amount'];
    $tax = $amount * TAX_RATE; // Calculate tax
    $total = $amount + $tax;
    $due_days = (int)$_POST['due_days'];
    
    // Generate invoice number
    $invoice_number = 'INV-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    $issue_date = date('Y-m-d');
    $due_date = date('Y-m-d', strtotime("+$due_days days"));
    
    // Insert invoice
    $db->query("
        INSERT INTO invoices (invoice_number, client_id, description, amount, tax, total, issue_date, due_date) 
        VALUES ('$invoice_number', $client_id, '$description', $amount, $tax, $total, '$issue_date', '$due_date')
    ");
    
    header('Location: view_invoice.php?id=' . $db->insert_id);
    exit;
}

// Get all clients for dropdown
$clients = $db->query("SELECT * FROM clients ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Invoice</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        header { background: #333; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        nav a { color: white; text-decoration: none; margin-right: 20px; }
        .form-card { background: white; padding: 30px; border-radius: 5px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px; }
        textarea { resize: vertical; min-height: 100px; }
        .info-box { background: #e8f4f8; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .btn { padding: 12px 30px; border: none; border-radius: 3px; cursor: pointer; font-size: 16px; }
        .btn-primary { background: #3498db; color: white; }
        .btn-primary:hover { background: #2980b9; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>📝 Create New Invoice</h1>
            <nav>
                <a href="index.php">Dashboard</a>
                <a href="clients.php">Clients</a>
                <a href="create_invoice.php">+ New Invoice</a>
            </nav>
        </header>

        <div class="form-card">
            <div class="info-box">
                ℹ️ Tax rate is automatically calculated at <?= (TAX_RATE * 100) ?>%
            </div>

            <form method="POST">
                <div class="form-group">
                    <label>Select Client *</label>
                    <select name="client_id" required>
                        <option value="">-- Choose Client --</option>
                        <?php while($client = $clients->fetch_assoc()): ?>
                            <option value="<?= $client['id'] ?>">
                                <?= $client['name'] ?> (<?= $client['company_name'] ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Service Description *</label>
                    <textarea name="description" required placeholder="E.g., Website Development, SEO Services..."></textarea>
                </div>

                <div class="form-group">
                    <label>Amount (KES) *</label>
                    <input type="number" name="amount" step="0.01" required placeholder="50000.00">
                </div>

                <div class="form-group">
                    <label>Payment Due In (Days) *</label>
                    <select name="due_days" required>
                        <option value="7">7 Days</option>
                        <option value="14" selected>14 Days</option>
                        <option value="30">30 Days</option>
                        <option value="45">45 Days</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Generate Invoice</button>
            </form>
        </div>
    </div>
</body>
</html>