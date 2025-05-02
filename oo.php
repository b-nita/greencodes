<?php
session_start();

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$db_name = "database";

// Initialize variables
$alertMessage = isset($_SESSION['alertMessage']) ? $_SESSION['alertMessage'] : "";
$alertType = isset($_SESSION['alertType']) ? $_SESSION['alertType'] : "";
$scrollToForm = false;
$errors = [];

// Clear session alerts
unset($_SESSION['alertMessage'], $_SESSION['alertType']);

// Create database connection
$conn = new mysqli($servername, $username, $password, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// CSRF Token Generation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Pagination settings
$recordsPerPage = 5;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;
$offset = ($currentPage - 1) * $recordsPerPage;

// Fetch edit data if edit_id is provided
$editData = [];
if (isset($_GET['edit_id']) && is_numeric($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM contact_submissions WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $editData = $result->fetch_assoc();
    }
    $stmt->close();
    $scrollToForm = true;
}

// Process form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['alertMessage'] = "Invalid CSRF token.";
        $_SESSION['alertType'] = "error";
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    $id = $_POST['id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validate inputs
    if (empty($name)) {
        $errors['name'] = "Name is required";
    } elseif (!preg_match("/^[a-zA-Z ]*$/", $name)) {
        $errors['name'] = "Only letters and spaces allowed";
    } elseif (strlen($name) > 100) {
        $errors['name'] = "Name must be less than 100 characters";
    }

    if (empty($email)) {
        $errors['email'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format";
    } elseif (strlen($email) > 255) {
        $errors['email'] = "Email must be less than 255 characters";
    } elseif (empty($id)) {
        // Check for unique email for new records
        $stmt = $conn->prepare("SELECT id FROM contact_submissions WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors['email'] = "This email is already registered";
        }
        $stmt->close();
    }

    if (strlen($subject) > 255) {
        $errors['subject'] = "Subject must be less than 255 characters";
    }

    if (empty($message)) {
        $errors['message'] = "Message is required";
    } elseif (strlen($message) > 1000) {
        $errors['message'] = "Message must be less than 1000 characters";
    }

    // Handle delete action
    if (isset($_POST['delete_id'])) {
        $delete_id = (int)$_POST['delete_id'];
        $stmt = $conn->prepare("DELETE FROM contact_submissions WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $_SESSION['alertMessage'] = "Message deleted successfully.";
            $_SESSION['alertType'] = "success";
        } else {
            $_SESSION['alertMessage'] = "Error deleting message.";
            $_SESSION['alertType'] = "error";
        }
        $stmt->close();
        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?page=$currentPage");
        exit;
    }

    // Proceed with insert/update if no validation errors
    if (empty($errors)) {
        if (!empty($id)) {
            // Update existing record
            $stmt = $conn->prepare("UPDATE contact_submissions SET name=?, email=?, subject=?, message=? WHERE id=?");
            $stmt->bind_param("ssssi", $name, $email, $subject, $message, $id);
            if ($stmt->execute()) {
                $_SESSION['alertMessage'] = "Message updated successfully.";
                $_SESSION['alertType'] = "success";
                header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?page=$currentPage");
                exit;
            } else {
                $_SESSION['alertMessage'] = "Update failed.";
                $_SESSION['alertType'] = "error";
            }
            $stmt->close();
        } else {
            // Insert new record
            $stmt = $conn->prepare("INSERT INTO contact_submissions (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $subject, $message);
            if ($stmt->execute()) {
                $_SESSION['alertMessage'] = "Thank you, $name! Your message has been sent.";
                $_SESSION['alertType'] = "success";
                header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?page=$currentPage");
                exit;
            } else {
                $_SESSION['alertMessage'] = "Failed to send message.";
                $_SESSION['alertType'] = "error";
            }
            $stmt->close();
        }
    } else {
        $_SESSION['alertMessage'] = "Please correct the errors in the form.";
        $_SESSION['alertType'] = "error";
        $scrollToForm = true;
    }
}

// Get total number of records for pagination
$totalRecordsQuery = $conn->query("SELECT COUNT(*) AS total FROM contact_submissions");
$totalRecords = $totalRecordsQuery->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Fetch paginated records
$sql = "SELECT * FROM contact_submissions ORDER BY created_at DESC LIMIT $recordsPerPage OFFSET $offset";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <style>
        /* Base Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f9f9f9;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        /* Form Styles */
        #contactForm {
            background: #d4edda;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 600px;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        input[type="text"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: all 0.3s;
        }
        input:focus, textarea:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 5px rgba(52,152,219,0.3);
        }
        textarea {
            resize: vertical;
            min-height: 120px;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: -15px;
            margin-bottom: 15px;
        }
        .error-field {
            border-color: #dc3545 !important;
        }

        /* Button Styles */
        button, .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        button[type="submit"] {
            background-color: #3498db;
            color: white;
        }
        button[type="submit"]:hover {
            background-color: #2980b9;
        }
        .btn-edit {
            background-color: #f39c12;
            color: white;
        }
        .btn-edit:hover {
            background-color: #d35400;
        }
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        .btn-danger:hover {
            background-color: #c0392b;
        }
        .toggle-btn {
            background-color: #2ecc71;
            color: white;
            padding: 10px 20px;
        }
        .toggle-btn:hover {
            background-color: #27ae60;
        }

        /* Alert Messages */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-weight: bold;
            border-left: 5px solid;
            animation: fadeIn 0.5s;
        }
        .alert.success {
            background-color: #d4edda;
            color: #155724;
            border-color: #28a745;
        }
        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #dc3545;
        }

        /* Message Table */
        .message-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            display: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .message-table th,
        .message-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .message-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .message-table tr:hover {
            background-color: #f9f9f9;
        }
        .message-content {
            white-space: pre-wrap;
            max-width: 300px;
        }
        .actions {
            white-space: nowrap;
        }
        .actions form {
            display: inline-block;
            margin: 0;
        }

        /* Pagination Styles */
        .pagination {
            display: flex;
            justify-content: center;
            margin: 20px 0;
            padding: 0;
            list-style: none;
        }
        .pagination li {
            margin: 0 5px;
        }
        .pagination a {
            display: block;
            padding: 8px 12px;
            background-color: #f1f1f1;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.3s;
        }
        .pagination a:hover {
            background-color: #3498db;
            color: white;
        }
        .pagination .active a {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }
        .pagination .disabled a {
            color: #aaa;
            pointer-events: none;
            cursor: default;
        }

        /* Utility Classes */
        .toggle-container {
            text-align: center;
            margin: 20px 0;
        }
        .no-messages {
            text-align: center;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: none;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes highlight {
            0% { background-color: inherit; }
            20% { background-color: #ffff99; }
            100% { background-color: inherit; }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            #contactForm {
                padding: 15px;
            }
            .message-table {
                display: block;
                overflow-x: auto;
            }
            .message-table th,
            .message-table td {
                font-size: 14px;
            }
            button, .btn {
                font-size: 14px;
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($alertMessage): ?>
            <div class="alert <?= htmlspecialchars($alertType) ?>" id="alertMessage"><?= htmlspecialchars($alertMessage) ?></div>
        <?php endif; ?>

        <div id="contactForm">
            <h2>Contact Form</h2>
            <form method="POST">
                <input type="hidden" name="id" value="<?= htmlspecialchars($editData['id'] ?? '') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                
                <label for="name">Name*:</label>
                <input type="text" id="name" name="name" required
                       value="<?= htmlspecialchars($editData['name'] ?? ($_POST['name'] ?? '')) ?>"
                       class="<?= isset($errors['name']) ? 'error-field' : '' ?>">
                <?php if (isset($errors['name'])): ?>
                    <div class="error-message"><?= htmlspecialchars($errors['name']) ?></div>
                <?php endif; ?>

                <label for="email">Email*:</label>
                <input type="email" id="email" name="email" required
                       value="<?= htmlspecialchars($editData['email'] ?? ($_POST['email'] ?? '')) ?>"
                       class="<?= isset($errors['email']) ? 'error-field' : '' ?>">
                <?php if (isset($errors['email'])): ?>
                    <div class="error-message"><?= htmlspecialchars($errors['email']) ?></div>
                <?php endif; ?>

                <label for="subject">Subject:</label>
                <input type="text" id="subject" name="subject"
                       value="<?= htmlspecialchars($editData['subject'] ?? ($_POST['subject'] ?? '')) ?>"
                       class="<?= isset($errors['subject']) ? 'error-field' : '' ?>">
                <?php if (isset($errors['subject'])): ?>
                    <div class="error-message"><?= htmlspecialchars($errors['subject']) ?></div>
                <?php endif; ?>

                <label for="message">Message*:</label>
                <textarea id="message" name="message" required
                          class="<?= isset($errors['message']) ? 'error-field' : '' ?>"><?= 
                    htmlspecialchars($editData['message'] ?? ($_POST['message'] ?? '')) 
                ?></textarea>
                <?php if (isset($errors['message'])): ?>
                    <div class="error-message"><?= htmlspecialchars($errors['message']) ?></div>
                <?php endif; ?>

                <button type="submit">
                    <?= !empty($editData) ? 'Update Message' : 'Send Message' ?>
                </button>
            </form>
        </div>

        <div class="toggle-container">
            <button class="toggle-btn" onclick="toggleMessages()">Show Previous Messages</button>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <table class="message-table" id="messagesTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['subject']) ?></td>
                            <td class="message-content"><?= nl2br(htmlspecialchars($row['message'])) ?></td>
                            <td><?= date("M j, Y g:i a", strtotime($row['created_at'])) ?></td>
                            <td class="actions">
                                <a href="?page=<?= $currentPage ?>&edit_id=<?= $row['id'] ?>#contactForm">
                                    <button class="btn-edit">Edit</button>
                                </a>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this message?')">
                                    <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                    <button class="btn-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <ul class="pagination">
                <li <?= ($currentPage == 1) ? 'class="disabled"' : '' ?>>
                    <a href="?page=1<?= isset($_GET['edit_id']) ? '&edit_id=' . $_GET['edit_id'] : '' ?>">First</a>
                </li>
                <li <?= ($currentPage == 1) ? 'class="disabled"' : '' ?>>
                    <a href="?page=<?= ($currentPage - 1) ?><?= isset($_GET['edit_id']) ? '&edit_id=' . $_GET['edit_id'] : '' ?>">Previous</a>
                </li>
                <?php
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);
                for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <li <?= ($i == $currentPage) ? 'class="active"' : '' ?>>
                        <a href="?page=<?= $i ?><?= isset($_GET['edit_id']) ? '&edit_id=' . $_GET['edit_id'] : '' ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li <?= ($currentPage == $totalPages) ? 'class="disabled"' : '' ?>>
                    <a href="?page=<?= ($currentPage + 1) ?><?= isset($_GET['edit_id']) ? '&edit_id=' . $_GET['edit_id'] : '' ?>">Next</a>
                </li>
                <li <?= ($currentPage == $totalPages) ? 'class="disabled"' : '' ?>>
                    <a href="?page=<?= $totalPages ?><?= isset($_GET['edit_id']) ? '&edit_id=' . $_GET['edit_id'] : '' ?>">Last</a>
                </li>
            </ul>
        <?php else: ?>
            <div class="no-messages" id="noMessages">
                <p>No messages found.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleMessages() {
            const table = document.getElementById('messagesTable');
            const noMessages = document.getElementById('noMessages');
            const button = document.querySelector('.toggle-btn');

            if (table) {
                table.style.display = table.style.display === 'none' ? 'table' : 'none';
                button.textContent = table.style.display === 'none' ? 
                    'Show Previous Messages' : 'Hide Previous Messages';
            } else if (noMessages) {
                noMessages.style.display = noMessages.style.display === 'none' ? 
                    'block' : 'none';
                button.textContent = noMessages.style.display === 'none' ? 
                    'Show Previous Messages' : 'Hide Previous Messages';
            }
        }

        <?php if ($scrollToForm): ?>
        document.addEventListener('DOMContentLoaded', () => {
            const formElement = document.getElementById('contactForm');
            if (formElement) {
                formElement.scrollIntoView({ behavior: 'smooth' });
                const alertMessage = document.getElementById('alertMessage');
                if (alertMessage) {
                    alertMessage.style.animation = 'highlight 2s';
                }
            }
        });
        <?php endif; ?>

        document.addEventListener('DOMContentLoaded', () => {
            if (window.location.hash === '#contactForm' && 
                (document.getElementById('messagesTable') || document.getElementById('noMessages'))) {
                toggleMessages();
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>
<?php
session_start();

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$db_name = "database";

// Initialize variables
$alertMessage = isset($_SESSION['alertMessage']) ? $_SESSION['alertMessage'] : "";
$alertType = isset($_SESSION['alertType']) ? $_SESSION['alertType'] : "";
$scrollToForm = false;
$errors = [];

// Clear session alerts
unset($_SESSION['alertMessage'], $_SESSION['alertType']);

// Create database connection
$conn = new mysqli($servername, $username, $password, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// CSRF Token Generation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Pagination settings
$recordsPerPage = 5;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;
$offset = ($currentPage - 1) * $recordsPerPage;

// Fetch edit data if edit_id is provided
$editData = [];
if (isset($_GET['edit_id']) && is_numeric($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM contact_submissions WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $editData = $result->fetch_assoc();
    }
    $stmt->close();
    $scrollToForm = true;
}

// Process form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['alertMessage'] = "Invalid CSRF token.";
        $_SESSION['alertType'] = "error";
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    $id = $_POST['id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validate inputs
    if (empty($name)) {
        $errors['name'] = "Name is required";
    } elseif (!preg_match("/^[a-zA-Z ]*$/", $name)) {
        $errors['name'] = "Only letters and spaces allowed";
    } elseif (strlen($name) > 100) {
        $errors['name'] = "Name must be less than 100 characters";
    }

    if (empty($email)) {
        $errors['email'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format";
    } elseif (strlen($email) > 255) {
        $errors['email'] = "Email must be less than 255 characters";
    } elseif (empty($id)) {
        // Check for unique email for new records
        $stmt = $conn->prepare("SELECT id FROM contact_submissions WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors['email'] = "This email is already registered";
        }
        $stmt->close();
    }

    if (strlen($subject) > 255) {
        $errors['subject'] = "Subject must be less than 255 characters";
    }

    if (empty($message)) {
        $errors['message'] = "Message is required";
    } elseif (strlen($message) > 1000) {
        $errors['message'] = "Message must be less than 1000 characters";
    }

    // Handle delete action
    if (isset($_POST['delete_id'])) {
        $delete_id = (int)$_POST['delete_id'];
        $stmt = $conn->prepare("DELETE FROM contact_submissions WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $_SESSION['alertMessage'] = "Message deleted successfully.";
            $_SESSION['alertType'] = "success";
        } else {
            $_SESSION['alertMessage'] = "Error deleting message.";
            $_SESSION['alertType'] = "error";
        }
        $stmt->close();
        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?page=$currentPage");
        exit;
    }

    // Proceed with insert/update if no validation errors
    if (empty($errors)) {
        if (!empty($id)) {
            // Update existing record
            $stmt = $conn->prepare("UPDATE contact_submissions SET name=?, email=?, subject=?, message=? WHERE id=?");
            $stmt->bind_param("ssssi", $name, $email, $subject, $message, $id);
            if ($stmt->execute()) {
                $_SESSION['alertMessage'] = "Message updated successfully.";
                $_SESSION['alertType'] = "success";
                header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?page=$currentPage");
                exit;
            } else {
                $_SESSION['alertMessage'] = "Update failed.";
                $_SESSION['alertType'] = "error";
            }
            $stmt->close();
        } else {
            // Insert new record
            $stmt = $conn->prepare("INSERT INTO contact_submissions (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $subject, $message);
            if ($stmt->execute()) {
                $_SESSION['alertMessage'] = "Thank you, $name! Your message has been sent.";
                $_SESSION['alertType'] = "success";
                header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?page=$currentPage");
                exit;
            } else {
                $_SESSION['alertMessage'] = "Failed to send message.";
                $_SESSION['alertType'] = "error";
            }
            $stmt->close();
        }
    } else {
        $_SESSION['alertMessage'] = "Please correct the errors in the form.";
        $_SESSION['alertType'] = "error";
        $scrollToForm = true;
    }
}

// Get total number of records for pagination
$totalRecordsQuery = $conn->query("SELECT COUNT(*) AS total FROM contact_submissions");
$totalRecords = $totalRecordsQuery->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Fetch paginated records
$sql = "SELECT * FROM contact_submissions ORDER BY created_at DESC LIMIT $recordsPerPage OFFSET $offset";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <style>
        /* Base Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f9f9f9;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        /* Form Styles */
        #contactForm {
            background: #d4edda;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 600px;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        input[type="text"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: all 0.3s;
        }
        input:focus, textarea:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 5px rgba(52,152,219,0.3);
        }
        textarea {
            resize: vertical;
            min-height: 120px;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: -15px;
            margin-bottom: 15px;
        }
        .error-field {
            border-color: #dc3545 !important;
        }

        /* Button Styles */
        button, .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        button[type="submit"] {
            background-color: #3498db;
            color: white;
        }
        button[type="submit"]:hover {
            background-color: #2980b9;
        }
        .btn-edit {
            background-color: #f39c12;
            color: white;
        }
        .btn-edit:hover {
            background-color: #d35400;
        }
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        .btn-danger:hover {
            background-color: #c0392b;
        }
        .toggle-btn {
            background-color: #2ecc71;
            color: white;
            padding: 10px 20px;
        }
        .toggle-btn:hover {
            background-color: #27ae60;
        }

        /* Alert Messages */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-weight: bold;
            border-left: 5px solid;
            animation: fadeIn 0.5s;
        }
        .alert.success {
            background-color: #d4edda;
            color: #155724;
            border-color: #28a745;
        }
        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #dc3545;
        }

        /* Message Table */
        .message-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            display: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .message-table th,
        .message-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .message-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .message-table tr:hover {
            background-color: #f9f9f9;
        }
        .message-content {
            white-space: pre-wrap;
            max-width: 300px;
        }
        .actions {
            white-space: nowrap;
        }
        .actions form {
            display: inline-block;
            margin: 0;
        }

        /* Pagination Styles */
        .pagination {
            display: flex;
            justify-content: center;
            margin: 20px 0;
            padding: 0;
            list-style: none;
        }
        .pagination li {
            margin: 0 5px;
        }
        .pagination a {
            display: block;
            padding: 8px 12px;
            background-color: #f1f1f1;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.3s;
        }
        .pagination a:hover {
            background-color: #3498db;
            color: white;
        }
        .pagination .active a {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }
        .pagination .disabled a {
            color: #aaa;
            pointer-events: none;
            cursor: default;
        }

        /* Utility Classes */
        .toggle-container {
            text-align: center;
            margin: 20px 0;
        }
        .no-messages {
            text-align: center;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: none;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes highlight {
            0% { background-color: inherit; }
            20% { background-color: #ffff99; }
            100% { background-color: inherit; }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            #contactForm {
                padding: 15px;
            }
            .message-table {
                display: block;
                overflow-x: auto;
            }
            .message-table th,
            .message-table td {
                font-size: 14px;
            }
            button, .btn {
                font-size: 14px;
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($alertMessage): ?>
            <div class="alert <?= htmlspecialchars($alertType) ?>" id="alertMessage"><?= htmlspecialchars($alertMessage) ?></div>
        <?php endif; ?>

        <div id="contactForm">
            <h2>Contact Form</h2>
            <form method="POST">
                <input type="hidden" name="id" value="<?= htmlspecialchars($editData['id'] ?? '') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                
                <label for="name">Name*:</label>
                <input type="text" id="name" name="name" required
                       value="<?= htmlspecialchars($editData['name'] ?? ($_POST['name'] ?? '')) ?>"
                       class="<?= isset($errors['name']) ? 'error-field' : '' ?>">
                <?php if (isset($errors['name'])): ?>
                    <div class="error-message"><?= htmlspecialchars($errors['name']) ?></div>
                <?php endif; ?>

                <label for="email">Email*:</label>
                <input type="email" id="email" name="email" required
                       value="<?= htmlspecialchars($editData['email'] ?? ($_POST['email'] ?? '')) ?>"
                       class="<?= isset($errors['email']) ? 'error-field' : '' ?>">
                <?php if (isset($errors['email'])): ?>
                    <div class="error-message"><?= htmlspecialchars($errors['email']) ?></div>
                <?php endif; ?>

                <label for="subject">Subject:</label>
                <input type="text" id="subject" name="subject"
                       value="<?= htmlspecialchars($editData['subject'] ?? ($_POST['subject'] ?? '')) ?>"
                       class="<?= isset($errors['subject']) ? 'error-field' : '' ?>">
                <?php if (isset($errors['subject'])): ?>
                    <div class="error-message"><?= htmlspecialchars($errors['subject']) ?></div>
                <?php endif; ?>

                <label for="message">Message*:</label>
                <textarea id="message" name="message" required
                          class="<?= isset($errors['message']) ? 'error-field' : '' ?>"><?= 
                    htmlspecialchars($editData['message'] ?? ($_POST['message'] ?? '')) 
                ?></textarea>
                <?php if (isset($errors['message'])): ?>
                    <div class="error-message"><?= htmlspecialchars($errors['message']) ?></div>
                <?php endif; ?>

                <button type="submit">
                    <?= !empty($editData) ? 'Update Message' : 'Send Message' ?>
                </button>
            </form>
        </div>

        <div class="toggle-container">
            <button class="toggle-btn" onclick="toggleMessages()">Show Previous Messages</button>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <table class="message-table" id="messagesTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['subject']) ?></td>
                            <td class="message-content"><?= nl2br(htmlspecialchars($row['message'])) ?></td>
                            <td><?= date("M j, Y g:i a", strtotime($row['created_at'])) ?></td>
                            <td class="actions">
                                <a href="?page=<?= $currentPage ?>&edit_id=<?= $row['id'] ?>#contactForm">
                                    <button class="btn-edit">Edit</button>
                                </a>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this message?')">
                                    <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                    <button class="btn-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <ul class="pagination">
                <li <?= ($currentPage == 1) ? 'class="disabled"' : '' ?>>
                    <a href="?page=1<?= isset($_GET['edit_id']) ? '&edit_id=' . $_GET['edit_id'] : '' ?>">First</a>
                </li>
                <li <?= ($currentPage == 1) ? 'class="disabled"' : '' ?>>
                    <a href="?page=<?= ($currentPage - 1) ?><?= isset($_GET['edit_id']) ? '&edit_id=' . $_GET['edit_id'] : '' ?>">Previous</a>
                </li>
                <?php
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);
                for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <li <?= ($i == $currentPage) ? 'class="active"' : '' ?>>
                        <a href="?page=<?= $i ?><?= isset($_GET['edit_id']) ? '&edit_id=' . $_GET['edit_id'] : '' ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li <?= ($currentPage == $totalPages) ? 'class="disabled"' : '' ?>>
                    <a href="?page=<?= ($currentPage + 1) ?><?= isset($_GET['edit_id']) ? '&edit_id=' . $_GET['edit_id'] : '' ?>">Next</a>
                </li>
                <li <?= ($currentPage == $totalPages) ? 'class="disabled"' : '' ?>>
                    <a href="?page=<?= $totalPages ?><?= isset($_GET['edit_id']) ? '&edit_id=' . $_GET['edit_id'] : '' ?>">Last</a>
                </li>
            </ul>
        <?php else: ?>
            <div class="no-messages" id="noMessages">
                <p>No messages found.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleMessages() {
            const table = document.getElementById('messagesTable');
            const noMessages = document.getElementById('noMessages');
            const button = document.querySelector('.toggle-btn');

            if (table) {
                table.style.display = table.style.display === 'none' ? 'table' : 'none';
                button.textContent = table.style.display === 'none' ? 
                    'Show Previous Messages' : 'Hide Previous Messages';
            } else if (noMessages) {
                noMessages.style.display = noMessages.style.display === 'none' ? 
                    'block' : 'none';
                button.textContent = noMessages.style.display === 'none' ? 
                    'Show Previous Messages' : 'Hide Previous Messages';
            }
        }

        <?php if ($scrollToForm): ?>
        document.addEventListener('DOMContentLoaded', () => {
            const formElement = document.getElementById('contactForm');
            if (formElement) {
                formElement.scrollIntoView({ behavior: 'smooth' });
                const alertMessage = document.getElementById('alertMessage');
                if (alertMessage) {
                    alertMessage.style.animation = 'highlight 2s';
                }
            }
        });
        <?php endif; ?>

        document.addEventListener('DOMContentLoaded', () => {
            if (window.location.hash === '#contactForm' && 
                (document.getElementById('messagesTable') || document.getElementById('noMessages'))) {
                toggleMessages();
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>