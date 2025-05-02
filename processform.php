<?php
header('Content-Type: application/json');

// Get form data
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$message = $_POST['message'] ?? '';

// Validate data
$errors = [];

if (empty($name)) {
    $errors['name'] = 'Name is required';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Valid email is required';
}

if (empty($message)) {
    $errors['message'] = 'Message is required';
}

// If errors exist, return them
if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// Process the form (send email, save to database, etc.)
// For this example, we'll just save to a text file
$data = [
    'name' => $name,
    'email' => $email,
    'message' => $message,
    'date' => date('Y-m-d H:i:s')
];

file_put_contents('submissions.txt', json_encode($data).PHP_EOL, FILE_APPEND);

// Return success response
echo json_encode(['success' => true, 'message' => 'Thank you for your message!']);
?>