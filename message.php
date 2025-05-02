<!DOCTYPE html>
<html>
<head>
    <title>Contact Form</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .form-container { background: #f9f9f9; padding: 20px; border-radius: 5px; margin-bottom: 30px; }
        .message-list { margin-top: 30px; }
        .message-item { background: #fff; border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; }
        .success-message { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .message-header { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .message-meta { color: #666; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Contact Us</h2>
        <form action="submit.php" method="post">
            <label>Name: <input type="text" name="name" required></label><br><br>
            <label>Email: <input type="email" name="email" required></label><br><br>
            <label>Subject: <input type="text" name="subject"></label><br><br>
            <label>Message: <textarea name="message" required></textarea></label><br><br>
            <input type="submit" value="Send Message">
        </form>
    </div>

    <?php
    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'database');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Show success message if redirected from submission
    if (isset($_GET['status']) && $_GET['status'] == 'success') {
        echo '<div class="success-message">'.htmlspecialchars($_GET['message']).'</div>';
    }

    // Fetch all messages
    $result = $conn->query("SELECT * FROM contact_submissions ORDER BY created_at DESC");
    
    if ($result->num_rows > 0) {
        echo '<div class="message-list">';
        echo '<h3>All Messages</h3>';
        
        while($row = $result->fetch_assoc()) {
            echo '<div class="message-item">';
            echo '<div class="message-header">';
            echo '<strong>'.htmlspecialchars($row['name']).'</strong>';
            echo '<span class="message-meta">'.date('M j, Y g:i a', strtotime($row['created_at'])).'</span>';
            echo '</div>';
            
            if (!empty($row['email'])) {
                echo '<div class="message-meta">Email: '.htmlspecialchars($row['email']).'</div>';
            }
            
            if (!empty($row['subject'])) {
                echo '<div><em>Subject: '.htmlspecialchars($row['subject']).'</em></div>';
            }
            
            echo '<p>'.nl2br(htmlspecialchars($row['message'])).'</p>';
            echo '</div>';
        }
        
        echo '</div>';
    } else {
        echo '<p>No messages yet. Be the first to contact us!</p>';
    }
    
    $conn->close();
    ?>
</body>
</html>