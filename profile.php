<?php
session_start();

if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}

include 'db_connect.php';

$userid = $_SESSION['userid'];
$error = "";
$success = "";

$sql = "SELECT * FROM user WHERE userid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $image = $_FILES['profile_image'];
    
    if ($image['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (in_array($image['type'], $allowed_types) && $image['size'] <= $max_size) {
            $image_data = file_get_contents($image['tmp_name']);
            
            $sql = "UPDATE user SET profile_image = ? WHERE userid = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $image_data, $userid);
            
            if ($stmt->execute()) {
                $success = "Profile image updated successfully.";
                $stmt = $conn->prepare("SELECT * FROM user WHERE userid = ?");
                $stmt->bind_param("i", $userid);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
            } else {
                $error = "Error updating profile image: " . $stmt->error;
            }
        } else {
            $error = "Invalid image file. Only JPEG, PNG, or GIF files up to 2MB are allowed.";
        }
    } else {
        $upload_errors = [
            0 => "There is no error, the file uploaded with success",
            1 => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
            2 => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
            3 => "The uploaded file was only partially uploaded",
            4 => "No file was uploaded",
            6 => "Missing a temporary folder",
            7 => "Failed to write file to disk",
            8 => "A PHP extension stopped the file upload"
        ];
        $error = "Error uploading image: " . ($upload_errors[$image['error']] ?? "Unknown error");
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_image'])) {
    $sql = "UPDATE user SET profile_image = NULL WHERE userid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userid);
    
    if ($stmt->execute()) {
        $success = "Profile image deleted successfully.";
        $stmt = $conn->prepare("SELECT * FROM user WHERE userid = ?");
        $stmt->bind_param("i", $userid);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    } else {
        $error = "Error deleting profile image: " . $stmt->error;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $bio = htmlspecialchars($_POST['bio']);
    $phone_number1 = htmlspecialchars($_POST['phone_number1']);
    $phone_number2 = htmlspecialchars($_POST['phone_number2']);
    $location = htmlspecialchars($_POST['location']);
    $facebook = htmlspecialchars($_POST['facebook']);
    $instagram = htmlspecialchars($_POST['instagram']);
    $tiktok = htmlspecialchars($_POST['tiktok']);

    $sql = "UPDATE user SET name = ?, email = ?, bio = ?, phone_number1 = ?, phone_number2 = ?, location = ?, facebook = ?, instagram = ?, tiktok = ? WHERE userid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssi", $name, $email, $bio, $phone_number1, $phone_number2, $location, $facebook, $instagram, $tiktok, $userid);

    if ($stmt->execute()) {
        $success = "Profile updated successfully.";
        $stmt = $conn->prepare("SELECT * FROM user WHERE userid = ?");
        $stmt->bind_param("i", $userid);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    } else {
        $error = "Error updating profile: " . $stmt->error;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    try {
        $conn->begin_transaction();
        
        $stmt = $conn->prepare("DELETE f FROM favorites f JOIN property p ON f.propertyid = p.propertyid WHERE p.userid = ?");
        $stmt->bind_param("i", $userid);
        $stmt->execute();
        
        $stmt = $conn->prepare("DELETE FROM property WHERE userid = ?");
        $stmt->bind_param("i", $userid);
        $stmt->execute();
        
        $stmt = $conn->prepare("DELETE FROM favorites WHERE userid = ?");
        $stmt->bind_param("i", $userid);
        $stmt->execute();
        
        $stmt = $conn->prepare("DELETE FROM user WHERE userid = ?");
        $stmt->bind_param("i", $userid);
        $stmt->execute();
        
        $conn->commit();
        
        session_destroy();
        header("Location: signup.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error deleting account: " . $e->getMessage();
    }
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/profile.css">
</head>
<body>
    <header>
        <div class="container header-container">
            <a href="home.php" class="logo">
                <img src="icons/denwhite.svg" alt="Den Logo">
                <span class="logo-text"></span>
            </a>
            <nav>
                <ul>
                    <li><a href="home.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="my_properties.php"><i class="fas fa-building"></i> My Properties</a></li>
                    <li><a href="favorites.php"><i class="fas fa-heart"></i> Favorites</a></li>
                    <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'super_admin')): ?>
                    <li><a href="admin_dashboard.php"><i class="fas fa-user-shield"></i> Admin</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="profile-container">
            <div class="profile-header">
                <h1>My Profile</h1>
                <div class="profile-image-container">
                    <?php if (!empty($user['profile_image'])): ?>
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($user['profile_image']); ?>" alt="Profile Image" class="profile-image" id="profileImage">
                    <?php else: ?>
                        <div class="profile-image-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="profile-image-actions">
                        <label for="profile_image" class="profile-image-btn edit" title="Upload new image">
                            <i class="fas fa-camera"></i>
                            <input type="file" name="profile_image" id="profile_image" accept="image/*" form="imageUploadForm">
                        </label>
                        <form method="post" id="imageDeleteForm" style="display: none;">
                            <input type="hidden" name="delete_image" value="1">
                        </form>
                        <button type="submit" form="imageDeleteForm" class="profile-image-btn delete" title="Remove image" onclick="return confirm('Are you sure you want to delete your profile image?');">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                
                <?php if($error): ?>
                    <div class="message error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <?php if($success): ?>
                    <div class="message success">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>
            </div>

            <form action="profile.php" method="post" class="profile-form" enctype="multipart/form-data" id="imageUploadForm">
                <div class="form-group">

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea name="bio" id="bio" class="form-control"><?php echo htmlspecialchars($user['bio']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="phone_number1">Primary Phone Number</label>
                    <input type="tel" name="phone_number1" id="phone_number1" class="form-control" value="<?php echo htmlspecialchars($user['phone_number1']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone_number2">Secondary Phone Number</label>
                    <input type="tel" name="phone_number2" id="phone_number2" class="form-control" value="<?php echo htmlspecialchars($user['phone_number2']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" name="location" id="location" class="form-control" value="<?php echo htmlspecialchars($user['location']); ?>">
                </div>
                
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Social Media Links</label>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                        <div>
                            <label for="facebook">Facebook</label>
                            <div style="display: flex; align-items: center;">
                                <span style="margin-right: 10px; color: var(--secondary-color);"><i class="fab fa-facebook-f"></i></span>
                                <input type="text" name="facebook" id="facebook" class="form-control" value="<?php echo htmlspecialchars($user['facebook']); ?>">
                            </div>
                        </div>
                        <div>
                            <label for="instagram">Instagram</label>
                            <div style="display: flex; align-items: center;">
                                <span style="margin-right: 10px; color: var(--secondary-color);"><i class="fab fa-instagram"></i></span>
                                <input type="text" name="instagram" id="instagram" class="form-control" value="<?php echo htmlspecialchars($user['instagram']); ?>">
                            </div>
                        </div>
                        <div>
                            <label for="tiktok">TikTok</label>
                            <div style="display: flex; align-items: center;">
                                <span style="margin-right: 10px; color: var(--secondary-color);"><i class="fab fa-tiktok"></i></span>
                                <input type="text" name="tiktok" id="tiktok" class="form-control" value="<?php echo htmlspecialchars($user['tiktok']); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="btn-group" style="grid-column: 1 / -1;">
                    <button type="submit" name="update" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                    <button type="button" id="deleteAccountBtn" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Account
                    </button>
                </div>
            </form>
        </div>
    </main>

    <div class="modal-overlay" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-exclamation-triangle"></i> Delete Account</h2>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete your account?</p>
                <p>This action will permanently remove all your data including properties and favorites.</p>
                <p><strong>This cannot be undone!</strong></p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-cancel" id="cancelDelete">Cancel</button>
                <form method="post" style="display: inline;">
                    <button type="submit" name="delete" class="modal-btn modal-btn-confirm">
                        <i class="fas fa-trash"></i> Delete Account
                    </button>
                </form>
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
                    <div class="footer-social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                <div class="footer-links">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="home.php">Home</a></li>
                        <li><a href="my_properties.php">My Properties</a></li>
                        <li><a href="favorites.php">Favorites</a></li>
                        <li><a href="profile.php">Profile</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h3>Property Types</h3>
                    <ul>
                        <li><a href="home.php?type=house">Houses</a></li>
                        <li><a href="home.php?type=apartment">Apartments</a></li>
                        <li><a href="home.php?type=building">Buildings</a></li>
                        <li><a href="home.php?type=land">Land</a></li>
                        <li><a href="home.php?type=farm">Farms</a></li>
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
        document.getElementById('profile_image').addEventListener('change', function() {
    if (this.files && this.files[0] && this.files[0].size > 0) {
        document.getElementById('imageUploadForm').submit();
    }
});

        const deleteModal = document.getElementById('deleteModal');
        const deleteBtn = document.getElementById('deleteAccountBtn');
        const cancelBtn = document.getElementById('cancelDelete');

        deleteBtn.addEventListener('click', () => {
            deleteModal.classList.add('active');
        });

        cancelBtn.addEventListener('click', () => {
            deleteModal.classList.remove('active');
        });

        window.addEventListener('click', (e) => {
            if (e.target === deleteModal) {
                deleteModal.classList.remove('active');
            }
        });
    </script>
</body>
</html>