<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'super_admin') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['remove_property']) && is_numeric($_GET['remove_property'])) {
    $property_id = $_GET['remove_property'];
    $admin_id = $_SESSION['userid'];
    
    $conn->query("DELETE FROM favorites WHERE propertyid = $property_id");
    
    if ($conn->query("DELETE FROM property WHERE propertyid = $property_id")) {
        $conn->query("INSERT INTO admin_actions (admin_id, action_type, target_id, description) 
                     VALUES ($admin_id, 'property_removal', $property_id, 'Property removed by admin')");
        
        $_SESSION['success_message'] = "Property removed successfully.";
    } else {
        $_SESSION['error_message'] = "Error removing property: " . $conn->error;
    }
    
    header("Location: admin_properties.php");
    exit;
}

$properties = $conn->query("SELECT p.*, u.name as owner_name 
                           FROM property p 
                           JOIN user u ON p.userid = u.userid
                           ORDER BY p.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Properties | Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/admin_properties.css">
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
            <h1 class="section-title">Manage Properties</h1>
            <p>Review and manage all properties listed on the platform</p>
        </div>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error_message']; ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Property</th>
                    <th>Owner</th>
                    <th>Price</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($property = $properties->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php if (!empty($property['image'])): ?>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($property['image']); ?>" class="property-image" alt="Property Image">
                        <?php else: ?>
                            <div class="property-image" style="background-color: #eee; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-home" style="color: #999;"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?php echo ucfirst($property['type']); ?></strong><br>
                        <?php echo $property['beds']; ?> beds, <?php echo $property['baths']; ?> baths
                    </td>
                    <td><?php echo htmlspecialchars($property['owner_name']); ?></td>
                    <td>$<?php echo number_format($property['price']); ?></td>
                    <td><?php echo $property['location']; ?></td>
                    <td><?php echo ucfirst($property['status']); ?></td>
                    <td>
                        <a href="property_details.php?id=<?php echo $property['propertyid']; ?>" class="btn btn-primary btn-sm" target="_blank">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <button class="btn btn-danger btn-sm delete-property-btn" data-propertyid="<?php echo $property['propertyid']; ?>">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>

    <div class="modal-overlay" id="deletePropertyModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-exclamation-triangle"></i> Delete Property</h2>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this property?</p>
                <p>This action will permanently remove the property and cannot be undone.</p>
                <p><strong>All favorites for this property will also be removed!</strong></p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-cancel" id="cancelPropertyDelete">Cancel</button>
                <a href="#" class="modal-btn modal-btn-confirm" id="confirmPropertyDelete">
                    <i class="fas fa-trash"></i> Delete Property
                </a>
            </div>
        </div>
    </div>

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

    <script>
        const deletePropertyModal = document.getElementById('deletePropertyModal');
        const deletePropertyBtns = document.querySelectorAll('.delete-property-btn');
        const cancelPropertyDeleteBtn = document.getElementById('cancelPropertyDelete');
        const confirmPropertyDeleteBtn = document.getElementById('confirmPropertyDelete');
        
        let currentPropertyIdToDelete = null;
        
        deletePropertyBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                currentPropertyIdToDelete = btn.getAttribute('data-propertyid');
                deletePropertyModal.classList.add('active');
            });
        });
        
        cancelPropertyDeleteBtn.addEventListener('click', () => {
            deletePropertyModal.classList.remove('active');
            currentPropertyIdToDelete = null;
        });
        
        confirmPropertyDeleteBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (currentPropertyIdToDelete) {
                window.location.href = `admin_properties.php?remove_property=${currentPropertyIdToDelete}`;
            }
        });
        
        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === deletePropertyModal) {
                deletePropertyModal.classList.remove('active');
                currentPropertyIdToDelete = null;
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>