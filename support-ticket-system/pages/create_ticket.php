<?php
require_once __DIR__ . '/../includes/header.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: /support-ticket-system/pages/login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject']);
    $description = trim($_POST['description']);
    $priority = trim($_POST['priority']);
    
    $errors = [];
    
    if(empty($subject)) {
        $errors[] = "Subject is required";
    }
    
    if(empty($description)) {
        $errors[] = "Description is required";
    }
    
    if(empty($errors)) {
        $stmt = $db->prepare("INSERT INTO tickets (user_id, subject, description, priority) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $subject, $description, $priority]);
        
        $_SESSION['success'] = "Ticket created successfully!";
        header("Location: /support-ticket-system/pages/dashboard_user.php");
        exit();
    }
}
?>

<div class="form-container">
    <h2>Create New Ticket</h2>
    <?php if(!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label for="subject">Subject</label>
            <input type="text" id="subject" name="subject" required>
        </div>
        <div class="form-group">
            <label for="priority">Priority</label>
            <select id="priority" name="priority" required>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
                <option value="high">High</option>
            </select>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5" required></textarea>
        </div>
        <button type="submit" class="btn">Create Ticket</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>