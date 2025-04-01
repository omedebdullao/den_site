<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['userid']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'super_admin')) {
    header("Location: login.php");
    exit;
}

$actions = $conn->query("SELECT a.*, u.name as admin_name 
                        FROM admin_actions a 
                        JOIN user u ON a.admin_id = u.userid
                        ORDER BY a.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log | Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/admin_act.css">
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
                    <?php if ($_SESSION['role'] == 'super_admin'): ?>
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
            <h1 class="section-title">Activity Log</h1>
            <p>View all administrative actions taken on the platform</p>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>Admin</th>
                    <th>Target ID</th>
                    <th>Description</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($action = $actions->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center;">
                            <?php if ($action['action_type'] == 'property_removal'): ?>
                                <div class="action-icon action-property">
                                    <i class="fas fa-home"></i>
                                </div>
                                <span>Property Removal</span>
                            <?php elseif ($action['action_type'] == 'user_block'): ?>
                                <div class="action-icon action-user">
                                    <i class="fas fa-user"></i>
                                </div>
                                <span>User Block</span>
                            <?php else: ?>
                                <div class="action-icon action-admin">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <span>Admin Creation</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($action['admin_name']); ?></td>
                    <td><?php echo $action['target_id']; ?></td>
                    <td><?php echo htmlspecialchars($action['description']); ?></td>
                    <td><?php echo date('M j, Y H:i', strtotime($action['created_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
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
<?php $conn->close(); ?>