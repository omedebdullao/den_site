<?php
session_start();
$error = "";
$email = "";
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM user WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['userid'] = $row['userid'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['role'] = $row['role'];
            
            if ($row['role'] == 'admin' || $row['role'] == 'super_admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: home.php");
            }
            exit();
        }else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No user found with this email.";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/account.css">
    <style>
    </style>
</head>
<body>
    <header>
        <div class="container header-container">
            <a href="public_properties.php" class="logo">
                <img src="icons/denwhite.svg" alt="den Logo">
                <span class="logo-text"></span>
            </a>
            <nav>
                <ul>
                    <li><a href="public_properties.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="signup.php"><i class="fas fa-user-plus"></i> Signup</a></li>
                    <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="auth-container">
        <div class="auth-form">
            <h2>Welcome Back</h2>
            
            <?php if(isset($error) && $error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form action="login.php" method="post">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div class="auth-footer">
                Don't have an account? <a href="signup.php">Sign up here</a>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="footer-container">
                <div class="footer-about">
                    <div class="footer-logo">
                        <img src="icons/denwhite.svg" alt="DEN LOGO">
                        <span class="footer-logo-text"></span>
                    </div>
                    <p>Your trusted partner in finding the perfect property. We connect buyers and sellers with the best real estate opportunities.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                <div class="footer-links">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="public_properties.php">Home</a></li>
                        <li><a href="signup.php">Signup</a></li>
                        <li><a href="login.php">Login</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h3>Property Types</h3>
                    <ul>
                        <li><a href="public_properties.php?type=house">Houses</a></li>
                        <li><a href="public_properties.php?type=apartment">Apartments</a></li>
                        <li><a href="public_properties.php?type=building">Buildings</a></li>
                        <li><a href="public_properties.php?type=land">Land</a></li>
                        <li><a href="public_properties.php?type=farm">Farms</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3>Contact Us</h3>
                    <p><i class="fas fa-phone"></i> +964 (751) 202-2694</p>
                    <p><i class="fas fa-envelope"></i> info@duhokestatenavigator.com</p>
                    <p><i class="fas fa-clock"></i> Mon-Fri: 9AM - 6PM</p>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> Duhok Estate Navigator. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>