<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 'super_admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_admin'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $super_admin_id = $_SESSION['userid'];

    $check = $conn->query("SELECT * FROM user WHERE email = '$email'");
    if ($check->num_rows > 0) {
        $_SESSION['error'] = "Email already exists";
    } else {
        if ($conn->query("INSERT INTO user (name, email, password, role) VALUES ('$name', '$email', '$password', '$role')")) {
            $admin_id = $conn->insert_id;
            $conn->query("INSERT INTO admin_actions (admin_id, action_type, target_id, description) 
                         VALUES ($super_admin_id, 'admin_creation', $admin_id, 'Created new $role account')");
            
            $_SESSION['success'] = "Admin account created successfully";
        } else {
            $_SESSION['error'] = "Error creating admin: " . $conn->error;
        }
    }
    header("Location: admin_management.php");
    exit;
}

$admins = $conn->query("SELECT * FROM user WHERE role IN ('admin', 'super_admin') ORDER BY role DESC, created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management | Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/admin_management.css">
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
                    <li><a href="admin_management.php"><i class="fas fa-user-shield"></i> Admins</a></li>
                    <li><a href="home.php"><i class="fas fa-home"></i> Public Site</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container dashboard-container">
        <div class="dashboard-header">
            <h1 class="section-title">Admin Management</h1>
            <p>Create and manage admin accounts</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <div class="admin-form">
            <h3><i class="fas fa-plus-circle"></i> Create New Admin</h3>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required minlength="8">
                    </div>
                    <div class="form-group">
                        <label for="role">Admin Role</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="admin">Admin</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="create_admin" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Create Admin
                </button>
            </form>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Admin</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($admin = $admins->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center;">
                            <?php if (!empty($admin['profile_image'])): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($admin['profile_image']); ?>" class="user-avatar" alt="Admin Avatar">
                            <?php else: ?>
                                <div class="avatar-placeholder">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($admin['name']); ?>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($admin['email']); ?></td>
                    <td>
                        <span class="role-<?php echo str_replace('_', '-', $admin['role']); ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $admin['role'])); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($admin['is_active']): ?>
                            <span class="status-active">Active</span>
                        <?php else: ?>
                            <span class="status-inactive">Blocked</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($admin['created_at'])); ?></td>
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