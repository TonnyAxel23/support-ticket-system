<?php
require_once __DIR__ . '/../includes/header.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: /support-ticket-system/pages/login.php");
    exit();
}

// Get user's tickets
$stmt = $db->prepare("SELECT * FROM tickets WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$tickets = $stmt->fetchAll();
?>

<h2>My Tickets</h2>

<a href="/support-ticket-system/pages/create_ticket.php" class="btn">Create New Ticket</a>

<?php if(empty($tickets)): ?>
    <p>You haven't created any tickets yet.</p>
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
