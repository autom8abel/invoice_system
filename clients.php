<?php
require_once 'config.php';
$db = getDB();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $db->real_escape_string($_POST['name']);
    $email = $db->real_escape_string($_POST['email']);
    $company = $db->real_escape_string($_POST['company_name']);
    $phone = $db->real_escape_string($_POST['phone']);
    
    $db->query("INSERT INTO clients (name, email, company_name, phone) VALUES ('$name', '$email', '$company', '$phone')");
    header('Location: clients.php');
    exit;
}

// Get all clients
$clients = $db->query("SELECT * FROM clients ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        header { background: #333; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        nav a { color: white; text-decoration: none; margin-right: 20px; }
        nav a:hover { text-decoration: underline; }
        .form-card { background: white; padding: 30px; border-radius: 5px; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px; }
        .btn { padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer; }
        .btn-primary { background: #3498db; color: white; }
        table { width: 100%; background: white; border-collapse: collapse; border-radius: 5px; overflow: hidden; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #333; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>👥 Client Management</h1>
            <nav>
                <a href="index.php">Dashboard</a>
                <a href="clients.php">Clients</a>
                <a href="create_invoice.php">+ New Invoice</a>
            </nav>
        </header>

        <!-- Add Client Form -->
        <div class="form-card">
            <h2>Add New Client</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Client Name *</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Company Name</label>
                    <input type="text" name="company_name">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone">
                </div>
                <button type="submit" class="btn btn-primary">Add Client</button>
            </form>
        </div>

        <!-- Clients Table -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Company</th>
                    <th>Phone</th>
                    <th>Added On</th>
                </tr>
            </thead>
            <tbody>
                <?php while($client = $clients->fetch_assoc()): ?>
                <tr>
                    <td><?= $client['id'] ?></td>
                    <td><?= $client['name'] ?></td>
                    <td><?= $client['email'] ?></td>
                    <td><?= $client['company_name'] ?></td>
                    <td><?= $client['phone'] ?></td>
                    <td><?= date('d M Y', strtotime($client['created_at'])) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>