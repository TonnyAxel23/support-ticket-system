<?php
require_once __DIR__ . '/../includes/header.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /support-ticket-system/pages/login.php");
    exit();
}

// Get all tickets
$stmt = $db->query("SELECT t.*, u.username FROM tickets t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC");
$tickets = $stmt->fetchAll();
?>

<h2>All Tickets</h2>

<div class="filters">
    <form method="GET" action="">
        <select name="status">
            <option value="">All Statuses</option>
            <option value="open">Open</option>
            <option value="in_progress">In Progress</option>
            <option value="closed">Closed</option>
        </select>
        <select name="priority">
            <option value="">All Priorities</option>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
        </select>
        <button type="submit" class="btn">Filter</button>
    </form>
</div>

<?php if(empty($tickets)): ?>
    <p>No tickets found.</p>
<?php else: ?>
    <ul class="ticket-list">
        <?php foreach($tickets as $ticket): ?>
           <li class="ticket-item">
    <h3>
        <a href="/support-ticket-system/pages/view_ticket.php?id=<?php echo $ticket['id']; ?>">
            <?php echo htmlspecialchars($ticket['subject']); ?>
        </a>
        <span class="status-badge status-<?php echo str_replace(' ', '_', strtolower($ticket['status'])); ?>">
            <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
        </span>
    </h3>
                <div class="ticket-meta">
                    User: <?php echo htmlspecialchars($ticket['username']); ?> |
                    Status: <span class="status-<?php echo str_replace(' ', '_', strtolower($ticket['status'])); ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                    </span> |
                    Priority: <span class="priority-<?php echo strtolower($ticket['priority']); ?>">
                        <?php echo ucfirst($ticket['priority']); ?>
                    </span> |
                    Created: <?php echo date('M j, Y g:i a', strtotime($ticket['created_at'])); ?>
                </div>
                <p><?php echo nl2br(htmlspecialchars(substr($ticket['description'], 0, 200))); ?>...</p>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>