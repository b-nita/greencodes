<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "database";  // Change this to your DB name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. New data to insert
$name = "Binita Sharma";
$email = "binita@example.com";
$subject = "General Inquiry";
$message = "Hello! I would like to know more about your services.";

// 3. Prepare SQL statement
$stmt = $conn->prepare("INSERT INTO contact_submissions (name, email, subject, message) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $subject, $message);

// 4. Execute
if ($stmt->execute()) {
    echo "✔️ New contact message inserted successfully!";
} else {
    echo "❌ Error: " . $stmt->error;
}

// 5. Close connections
$stmt->close();
$conn->close();
?>
