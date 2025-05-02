<?php 
include("connection.php");

// Process form submission if POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $subject = $conn->real_escape_string($_POST['subject'] ?? '');
    $message = $conn->real_escape_string($_POST['message'] ?? '');

    // Validate required fields
    if (empty($name) || empty($email) || empty($message)) {
        $error = "Please fill all required fields";
    } else {
        // Insert into database
        $sql = "INSERT INTO contact_submissions (name, email, subject, message) 
                VALUES ('$name', '$email', '$subject', '$message')";
        
        if ($conn->query($sql)) {
            $success = "Thank you, $name! Your message has been sent.";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link rel="stylesheet" href="contact_css.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            color: #333;
        }
        header {
            background: #f8f9fa;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }
        nav ul {
            display: flex;
            list-style: none;
            padding: 0;
        }
        nav ul li {
            margin-left: 1rem;
        }
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        form div {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        textarea {
            min-height: 150px;
        }
        button[type="submit"] {
            background: #007bff;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }
        button[type="submit"]:hover {
            background: #0056b3;
        }
        .message-list {
            margin-top: 3rem;
            border-top: 1px solid #eee;
            padding-top: 1rem;
        }
        .message-item {
            background: #f9f9f9;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            border-left: 4px solid #4CAF50;
        }
        .message-meta {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <h1>Welcome!</h1>
            <ul>
                <li><a href="index.php">Home</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h2>Contact Us</h2>
        <p>Please fill out this form to get in touch with us.</p>
        
        <?php if (isset($success)): ?>
            <div class="success-message"><?= htmlspecialchars($success) ?></div>
        <?php elseif (isset($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div>
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required value="<?= isset($name) ? htmlspecialchars($name) : '' ?>">
            </div>
            
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
            </div>
            
            <div>
                <label for="subject">Subject:</label>
                <input type="text" id="subject" name="subject" value="<?= isset($subject) ? htmlspecialchars($subject) : '' ?>">
            </div>
            
            <div>
                <label for="message">Message:</label>
                <textarea id="message" name="message" rows="5" required><?= isset($message) ? htmlspecialchars($message) : '' ?></textarea>
            </div>
            
            <button type="submit">Send Message</button>
        </form>

        <div class="message-list">
            <h3>Previous Messages</h3>
            <?php
            // Fetch all messages
            $result = $conn->query("SELECT * FROM contact_submissions ORDER BY created_at DESC");
            
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo '<div class="message-item">';
                    echo '<div class="message-meta">';
                    echo '<strong>'.htmlspecialchars($row['name']).'</strong> ('.htmlspecialchars($row['email']).')';
                    echo ' <span>on '.date('M j, Y g:i a', strtotime($row['created_at'])).'</span>';
                    echo '</div>';
                    
                    if (!empty($row['subject'])) {
                        echo '<div><em>Subject: '.htmlspecialchars($row['subject']).'</em></div>';
                    }
                    
                    echo '<p>'.nl2br(htmlspecialchars($row['message'])).'</p>';
                    echo '</div>';
                }
            } else {
                echo '<p>No messages yet. Be the first to contact us!</p>';
            }
            
            $conn->close();
            ?>
        </div>
    </div>
</body>
</html>