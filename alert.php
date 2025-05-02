<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$servername = "localhost";
$username = "root";  // Default XAMPP username
$password = "";     // Default XAMPP password (empty)
$db_name = "database";

// Initialize variables
$thankYouMessage = "";

// Only process if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize form data with null coalescing operator
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    // Basic validation
    if (empty($name) || empty($email) || empty($message)) {
        $thankYouMessage = "Please fill all required fields.";
    } else {
        try {
            // Create connection
            $conn = new mysqli($servername, $username, $password, $db_name);
            
            // Check connection
            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }

            // Prepare statement
            $stmt = $conn->prepare("INSERT INTO contact_submissions (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $subject, $message);
            
            if ($stmt->execute()) {
                $thankYouMessage = "Thank you, $name! Your information has been sent.";
            } else {
                throw new Exception("Error saving to database.");
            }
            
            $stmt->close();
            $conn->close();
            
        } catch(Exception $e) {
            $thankYouMessage = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contact Form</title>
    <style>
        .message { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <?php if (!empty($thankYouMessage)): ?>
        <div class="message <?= strpos($thankYouMessage, 'Thank you') !== false ? 'success' : 'error' ?>">
            <?= $thankYouMessage ?>
        </div>
    <?php endif; ?>
    
    <p><a href="contact.php">← Back to form</a></p>
</body>
</html>