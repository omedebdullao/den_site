<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['userid']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'super_admin')) {
    header("Location: login.php");
    exit;
}

$current_user_role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/admin_dashboard.css">
</head>
<body>
    <header>
        <div class="container header-container">
            <a href="admin_dashboard.php" class="logo">
                <img src="icons/denwhite.svg" alt="DEN LOGO">
                <span class="logo-text">Admin Panel</span>
            </a>
            <nav>
                <ul>
                    <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="admin_properties.php"><i class="fas fa-home"></i> Properties</a></li>
                    <li><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li>
                    <?php if ($current_user_role == 'super_admin'): ?>
                        <li><a href="admin_management.php"><i class="fas fa-user-shield"></i> Admins</a></li>
                    <?php endif; ?>
                    <li><a href="home.php"><i class="fas fa-home"></i> Public Site</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container dashboard-container">
        <div class="dashboard-header">
            <h1 class="section-title">Admin Dashboard</h1>
            <p>Welcome back, <?php echo $_SESSION['name']; ?> (<?php echo ucfirst(str_replace('_', ' ', $_SESSION['role'])); ?>)</p>
        </div>

        <div class="admin-cards">
            <div class="admin-card">
                <h3><i class="fas fa-home"></i> Properties</h3>
                <p>Manage all properties listed on the platform. Review, approve, or remove suspicious listings.</p>
                <a href="admin_properties.php" class="btn btn-primary">Manage Properties</a>
            </div>
            
            <div class="admin-card">
                <h3><i class="fas fa-users"></i> Users</h3>
                <p>View and manage user accounts. Block suspicious users or verify legitimate ones.</p>
                <a href="admin_users.php" class="btn btn-primary">Manage Users</a>
            </div>
            
            <?php if ($current_user_role == 'super_admin'): ?>
            <div class="admin-card">
                <h3><i class="fas fa-user-shield"></i> Admin Management</h3>
                <p>Create and manage admin accounts. Assign roles and permissions.</p>
                <a href="admin_management.php" class="btn btn-primary">Manage Admins</a>
            </div>
            <?php endif; ?>
            
            <div class="admin-card">
                <h3><i class="fas fa-history"></i> Activity Log</h3>
                <p>View all administrative actions taken on the platform for accountability.</p>
                <a href="admin_activity.php" class="btn btn-primary">View Logs</a>
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
                        <li><a href="home.php">Public Site</a></li>
                        <li><a href="admin_dashboard.php">Dashboard</a></li>
                        <li><a href="admin_properties.php">Properties</a></li>
                        <li><a href="admin_users.php">Users</a></li>
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