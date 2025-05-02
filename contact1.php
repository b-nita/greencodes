<?php 
include("connection.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link rel="stylesheet" href="contact_css.css">
    <script src="js/validation.js" defer></script>
    <style>
        .message-table {
            margin-top: 20px;
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #ddd;
            display: none; /* Hidden by default */
        }
        .message-table th, .message-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .message-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .message-table tr:hover {
            background-color: #f5f5f5;
        }
        .message-content {
            white-space: pre-wrap;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .toggle-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
            font-size: 16px;
        }
        .toggle-btn:hover {
            background-color: #45a049;
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
        
        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="success-message"><?= htmlspecialchars($_GET['message']) ?></div>
        <?php endif; ?>
        
        <form action="alert.php" method="POST">
            <div>
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div>
                <label for="subject">Subject:</label>
                <input type="text" id="subject" name="subject">
            </div>
            
            <div>
                <label for="message">Message:</label>
                <textarea id="message" name="message" rows="5" required></textarea>
            </div>
            
            <button type="submit">Send Message</button>
        </form>

        <button class="toggle-btn" onclick="toggleMessages()">Show Previous Messages</button>
        
        <div id="messagesContainer">
            <?php
            // Database connection
            $conn = new mysqli('localhost', 'root', '', 'database');
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Fetch all messages
            $result = $conn->query("SELECT * FROM contact_submissions ORDER BY created_at DESC");
            
            if ($result->num_rows > 0) {
                echo '<table class="message-table" id="messagesTable">';
                echo '<thead><tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Date</th>
                      </tr></thead>';
                echo '<tbody>';
                
                while($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>'.htmlspecialchars($row['name']).'</td>';
                    echo '<td>'.htmlspecialchars($row['email']).'</td>';
                    echo '<td>'.(!empty($row['subject']) ? htmlspecialchars($row['subject']) : '<em>No subject</em>').'</td>';
                    echo '<td class="message-content">'.nl2br(htmlspecialchars($row['message'])).'</td>';
                    echo '<td>'.date('M j, Y g:i a', strtotime($row['created_at'])).'</td>';
                    echo '</tr>';
                }
                
                echo '</tbody></table>';
            } else {
                echo '<p id="noMessages" style="display:none;">No messages yet. Be the first to contact us!</p>';
            }
            
            $conn->close();
            ?>
        </div>
    </div>

    <?php 
include("connection.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link rel="stylesheet" href="contact_css.css">
    <script src="js/validation.js" defer></script>
    <style>
        .message-table {
            margin-top: 20px;
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #ddd;
            display: none; /* Hidden by default */
        }
        .message-table th, .message-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .message-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .message-table tr:hover {
            background-color: #f5f5f5;
        }
        .message-content {
            white-space: pre-wrap;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .toggle-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
            font-size: 16px;
        }
        .toggle-btn:hover {
            background-color: #45a049;
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
        
        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="success-message"><?= htmlspecialchars($_GET['message']) ?></div>
        <?php endif; ?>
        
        <form action="alert.php" method="POST">
            <div>
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div>
                <label for="subject">Subject:</label>
                <input type="text" id="subject" name="subject">
            </div>
            
            <div>
                <label for="message">Message:</label>
                <textarea id="message" name="message" rows="5" required></textarea>
            </div>
            
            <button type="submit">Send Message</button>
        </form>

        <button class="toggle-btn" onclick="toggleMessages()">Show Previous Messages</button>
        
        <div id="messagesContainer">
            <?php
            // Database connection
            $conn = new mysqli('localhost', 'root', '', 'database');
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Fetch all messages
            $result = $conn->query("SELECT * FROM contact_submissions ORDER BY created_at DESC");
            
            if ($result->num_rows > 0) {
                echo '<table class="message-table" id="messagesTable">';
                echo '<thead><tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Date</th>
                      </tr></thead>';
                echo '<tbody>';
                
                while($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>'.htmlspecialchars($row['name']).'</td>';
                    echo '<td>'.htmlspecialchars($row['email']).'</td>';
                    echo '<td>'.(!empty($row['subject']) ? htmlspecialchars($row['subject']) : '<em>No subject</em>').'</td>';
                    echo '<td class="message-content">'.nl2br(htmlspecialchars($row['message'])).'</td>';
                    echo '<td>'.date('M j, Y g:i a', strtotime($row['created_at'])).'</td>';
                    echo '</tr>';
                }
                
                echo '</tbody></table>';
            } else {
                echo '<p id="noMessages" style="display:none;">No messages yet. Be the first to contact us!</p>';
            }
            
            $conn->close();
            ?>
        </div>
    </div>

    <script>
        function toggleMessages() {
            const table = document.getElementById('messagesTable');
            const noMessages = document.getElementById('noMessages');
            const button = document.querySelector('.toggle-btn');
            
            if (table) {
                if (table.style.display === 'none') {
                    table.style.display = 'table';
                    button.textContent = 'Hide Previous Messages';
                } else {
                    table.style.display = 'none';
                    button.textContent = 'Show Previous Messages';
                }
            } else if (noMessages) {
                if (noMessages.style.display === 'none') {
                    noMessages.style.display = 'block';
                    button.textContent = 'Hide Previous Messages';
                } else {
                    noMessages.style.display = 'none';
                    button.textContent = 'Show Previous Messages';
                }
            }
        }
    </script>
</body>
</html>
</body>
</html>