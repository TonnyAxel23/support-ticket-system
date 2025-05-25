<?php
require_once __DIR__ . '/../includes/header.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: /support-ticket-system/pages/login.php");
    exit();
}

if(!isset($_GET['id'])) {
    header("Location: /support-ticket-system/pages/dashboard_".$_SESSION['role'].".php");
    exit();
}

$ticket_id = $_GET['id'];

// Get ticket details
$stmt = $db->prepare("SELECT t.*, u.username FROM tickets t JOIN users u ON t.user_id = u.id WHERE t.id = ?");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch();

if(!$ticket) {
    header("Location: /support-ticket-system/pages/dashboard_".$_SESSION['role'].".php");
    exit();
}

// Check if user is authorized to view this ticket
if($_SESSION['role'] === 'user' && $ticket['user_id'] !== $_SESSION['user_id']) {
    header("Location: /support-ticket-system/pages/dashboard_user.php");
    exit();
}

// Get replies for this ticket
$stmt = $db->prepare("SELECT r.*, u.username FROM replies r JOIN users u ON r.user_id = u.id WHERE r.ticket_id = ? ORDER BY r.created_at");
$stmt->execute([$ticket_id]);
$replies = $stmt->fetchAll();

// Update ticket status if admin is replying
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    
    if(!empty($message)) {
        // Add reply
        $stmt = $db->prepare("INSERT INTO replies (ticket_id, user_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$ticket_id, $_SESSION['user_id'], $message]);
        
        // Update status if admin
        if($_SESSION['role'] === 'admin') {
            $new_status = $_POST['status'] ?? $ticket['status'];
            $stmt = $db->prepare("UPDATE tickets SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $ticket_id]);
            $ticket['status'] = $new_status;
        }
        
        header("Location: /support-ticket-system/pages/view_ticket.php?id=".$ticket_id);
        exit();
    }
}
?>

<h2>Ticket: <?php echo htmlspecialchars($ticket['subject']); ?></h2>

<div class="ticket-details">
    <div class="ticket-meta">
        Created by: <?php echo htmlspecialchars($ticket['username']); ?> |
        Status: <span class="status-<?php echo str_replace(' ', '_', strtolower($ticket['status'])); ?>">
            <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
        </span> |
        Priority: <span class="priority-<?php echo strtolower($ticket['priority']); ?>">
            <?php echo ucfirst($ticket['priority']); ?>
        </span> |
        Created: <?php echo date('M j, Y g:i a', strtotime($ticket['created_at'])); ?>
    </div>
    <div class="ticket-description">
        <p><?php echo nl2br(htmlspecialchars($ticket['description'])); ?></p>
    </div>
</div>

<h3>Replies</h3>

<?php if(empty($replies)): ?>
    <p>No replies yet.</p>
<?php else: ?>
    <ul class="reply-list">
        <?php foreach($replies as $reply): ?>
            <li class="reply-item">
                <div class="reply-meta">
                    <?php echo htmlspecialchars($reply['username']); ?> replied on 
                    <?php echo date('M j, Y g:i a', strtotime($reply['created_at'])); ?>
                </div>
                <p><?php echo nl2br(htmlspecialchars($reply['message'])); ?></p>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<!-- Replace the reply form section with: -->
<div class="reply-form">
    <h3>Add Reply</h3>
    <a href="/support-ticket-system/pages/reply_ticket.php?ticket_id=<?php echo $ticket_id; ?>" class="btn">Reply to Ticket</a>
</div>
    <form method="POST">
        <?php if($_SESSION['role'] === 'admin'): ?>
            <div class="form-group">
                <label for="status">Update Status</label>
                <select id="status" name="status">
                    <option value="open" <?php echo $ticket['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                    <option value="in_progress" <?php echo $ticket['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="closed" <?php echo $ticket['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                </select>
            </div>
        <?php endif; ?>
        <div class="form-group">
            <label for="message">Message</label>
            <textarea id="message" name="message" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn">Submit Reply</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>