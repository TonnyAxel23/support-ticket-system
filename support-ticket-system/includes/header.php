<?php
session_start();
require_once __DIR__ . '/../config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Ticket System</title>
    <link rel="stylesheet" href="/support-ticket-system/assets/styles.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">Support System</div>
            <ul>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="/support-ticket-system/pages/dashboard_<?php echo $_SESSION['role']; ?>.php">Dashboard</a></li>
                    <li><a href="/support-ticket-system/pages/create_ticket.php">Create Ticket</a></li>
                    <?php if($_SESSION['role'] === 'admin'): ?>
                        <li><a href="/support-ticket-system/pages/dashboard_admin.php">Admin Panel</a></li>
                    <?php endif; ?>
                    <li><a href="/support-ticket-system/pages/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="/support-ticket-system/pages/login.php">Login</a></li>
                    <li><a href="/support-ticket-system/pages/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>