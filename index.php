<?php
require_once 'config.php';
$db = getDB();

// Get summary statistics
$stats = $db->query("
    SELECT 
        COUNT(*) as total_invoices,
        SUM(CASE WHEN status = 'paid' THEN total ELSE 0 END) as total_paid,
        SUM(CASE WHEN status = 'pending' THEN total ELSE 0 END) as total_pending,
        SUM(CASE WHEN status = 'overdue' THEN total ELSE 0 END) as total_overdue
    FROM invoices
")->fetch_assoc();

// Update overdue invoices
$db->query("UPDATE invoices SET status = 'overdue' WHERE due_date < CURDATE() AND status = 'pending'");

// Get recent invoices
$invoices = $db->query("
    SELECT i.*, c.name as client_name, c.company_name 
    FROM invoices i 
    JOIN clients c ON i.client_id = c.id 
    ORDER BY i.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Management System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        header { background: #333; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        header h1 { margin-bottom: 10px; }
        nav a { color: white; text-decoration: none; margin-right: 20px; }
        nav a:hover { text-decoration: underline; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .stat-card h3 { color: #666; font-size: 14px; margin-bottom: 10px; }
        .stat-card .amount { font-size: 24px; font-weight: bold; }
        .stat-card.paid .amount { color: #27ae60; }
        .stat-card.pending .amount { color: #f39c12; }
        .stat-card.overdue .amount { color: #e74c3c; }
        table { width: 100%; background: white; border-collapse: collapse; border-radius: 5px; overflow: hidden; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #333; color: white; }
        .badge { padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge.paid { background: #27ae60; color: white; }
        .badge.pending { background: #f39c12; color: white; }
        .badge.overdue { background: #e74c3c; color: white; }
        .btn { padding: 8px 15px; text-decoration: none; border-radius: 3px; display: inline-block; margin: 2px; }
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-view { background: #95a5a6; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>📊 Invoice Management Dashboard</h1>
            <nav>
                <a href="index.php">Dashboard</a>
                <a href="clients.php">Clients</a>
                <a href="create_invoice.php">+ New Invoice</a>
            </nav>
        </header>

        <!-- Summary Statistics -->
        <div class="stats">
            <div class="stat-card">
                <h3>Total Invoices</h3>
                <div class="amount"><?= $stats['total_invoices'] ?></div>
            </div>
            <div class="stat-card paid">
                <h3>Paid Amount</h3>
                <div class="amount">KES <?= number_format($stats['total_paid'], 2) ?></div>
            </div>
            <div class="stat-card pending">
                <h3>Pending Amount</h3>
                <div class="amount">KES <?= number_format($stats['total_pending'], 2) ?></div>
            </div>
            <div class="stat-card overdue">
                <h3>Overdue Amount</h3>
                <div class="amount">KES <?= number_format($stats['total_overdue'], 2) ?></div>
            </div>
        </div>

        <!-- Invoices Table -->
        <table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Client</th>
                    <th>Amount</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($inv = $invoices->fetch_assoc()): ?>
                <tr>
                    <td><?= $inv['invoice_number'] ?></td>
                    <td><?= $inv['client_name'] ?> (<?= $inv['company_name'] ?>)</td>
                    <td>KES <?= number_format($inv['total'], 2) ?></td>
                    <td><?= date('d M Y', strtotime($inv['issue_date'])) ?></td>
                    <td><?= date('d M Y', strtotime($inv['due_date'])) ?></td>
                    <td><span class="badge <?= $inv['status'] ?>"><?= strtoupper($inv['status']) ?></span></td>
                    <td>
                        <a href="view_invoice.php?id=<?= $inv['id'] ?>" class="btn btn-view">View</a>
                        <?php if($inv['status'] != 'paid'): ?>
                        <a href="mark_paid.php?id=<?= $inv['id'] ?>" class="btn btn-success">Mark Paid</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>