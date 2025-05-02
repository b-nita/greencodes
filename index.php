<?php
// Start session for user data if needed
session_start();

// Set page variables
$page_title = "Welcome to Our Website";
$current_year = date('Y');
$company_email = "binitat533@gmail.com";
$company_phone = "1234567890";
$company_name = "Your Company Name";

// Include configuration
require_once 'connection.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="style1.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="navbar-container">
                <div class="navbar-brand">
                    <a href="index.php">
                        <img src="images/logo.jpg" alt="logo" class="logo">
                    </a>
                </div>
        
                <!-- Hamburger Menu Button -->
                <input type="checkbox" id="navbar-check" class="navbar-check">
                <label for="navbar-check" class="navbar-toggler">
                    <i class="fas fa-bars"></i>
                </label>
        
                <!-- Navbar Menu -->
                <ul class="navbar-menu">
                    <label for="navbar-check" class="close-menu">
                        <i class="fas fa-times"></i>
                    </label>
            
                    <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="products.php" class="nav-link">Products</a></li>
                    <li class="nav-item"><a href="services.php" class="nav-link">Services</a></li>
                    <li class="nav-item"><a href="contact.php" class="nav-link">Contact us</a></li>
                </ul>
        
                <!-- Search Bar -->
                <div class="navbar-search">
                    <form action="search.php" method="GET">
                        <input type="text" name="q" placeholder="Search..." class="search-input">
                        <button type="submit" class="search-icon">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <h1>Welcome to Our Awesome App!</h1>
            <?php
            // Dynamic content can be loaded here
            // Example: include('content/home-content.php');
            
            // For now using static content with PHP variables
            echo "<p>Welcome to $company_name. We're glad to have you here in $current_year.</p>";
            ?>
            
            <p>Ut scelerisque, sapien a placerat vulputate, mauris risus pulvinar quam, sit amet elementum erat mauris sed nulla...</p>
        </div>
    </main>

    <footer>
        <div class="footer-container">
            <div class="footer-logo">
                <img src="images/bg.jpg" alt="<?php echo htmlspecialchars($company_name); ?> Logo">
            </div>
            <div class="footer-nav">
                <h3>Navigation</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="services.php">Services</a></li>
                    <li><a href="contact.php">About</a></li>
                </ul>
            </div>
            <div class="footer-social">
                <h3>Follow Us</h3>
                <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
            </div>
            <div class="footer-contact">
                <h3>Contact</h3>
                <p>Email: <?php echo htmlspecialchars($company_email); ?></p>
                <p>Phone: <?php echo htmlspecialchars($company_phone); ?></p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo $current_year . ' ' . htmlspecialchars($company_name); ?>. All rights reserved.</p>
        </div>
    </footer>
    
    <script src="search.js"></script>
</body>
</html>