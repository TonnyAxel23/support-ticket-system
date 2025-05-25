<?php
require_once __DIR__ . '/../includes/header.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: /support-ticket-system/pages/login.php");
    exit();
}

if(!isset($_GET['ticket_id'])) {
    header("Location: /support-ticket-system/pages/dashboard_".$_SESSION['role'].".php");
    exit();
}

$ticket_id = $_GET['ticket_id'];

// Verify ticket exists and user has access
$stmt = $db->prepare("SELECT t.*, u.username FROM tickets t JOIN users u ON t.user_id = u.id WHERE t.id = ?");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch();

if(!$ticket) {
    header("Location: /support-ticket-system/pages/dashboard_".$_SESSION['role'].".php");
    exit();
}

// Check authorization
if($_SESSION['role'] === 'user' && $ticket['user_id'] !== $_SESSION['user_id']) {
    header("Location: /support-ticket-system/pages/dashboard_user.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message']);
    
    if(!empty($message)) {
        // Add reply
        $stmt = $db->prepare("INSERT INTO replies (ticket_id, user_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$ticket_id, $_SESSION['user_id'], $message]);
        
        // If admin, allow status update
        if($_SESSION['role'] === 'admin') {
            $new_status = $_POST['status'] ?? $ticket['status'];
            $stmt = $db->prepare("UPDATE tickets SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $ticket_id]);
        }
        
        header("Location: /support-ticket-system/pages/view_ticket.php?id=".$ticket_id);
        exit();
    } else {
        $error = "Message cannot be empty";
    }
}
?>

<div class="form-container">
    <h2>Reply to Ticket: <?php echo htmlspecialchars($ticket['subject']); ?></h2>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="ticket-info">
        <p><strong>Status:</strong> 
            <span class="status-<?php echo str_replace(' ', '_', strtolower($ticket['status'])); ?>">
                <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
            </span>
        </p>
        <p><strong>Priority:</strong> 
            <span class="priority-<?php echo strtolower($ticket['priority']); ?>">
                <?php echo ucfirst($ticket['priority']); ?>
            </span>
        </p>
        <p><strong>Description:</strong></p>
        <p><?php echo nl2br(htmlspecialchars($ticket['description'])); ?></p>
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
            <label for="message">Your Reply</label>
            <textarea id="message" name="message" rows="5" required></textarea>
        </div>
        
        <button type="submit" class="btn">Submit Reply</button>
        <a href="/support-ticket-system/pages/view_ticket.php?id=<?php echo $ticket_id; ?>" class="btn">Cancel</a>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>