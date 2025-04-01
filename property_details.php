<?php
session_start();
include 'db_connect.php';

if (isset($_GET['id'])) {
    $propertyid = $_GET['id'];
    $sql = "SELECT * FROM property WHERE propertyid='$propertyid'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    $userid = $row['userid'];
    $user_sql = "SELECT * FROM user WHERE userid='$userid'";
    $user_result = $conn->query($user_sql);
    $user = $user_result->fetch_assoc();
} else {
    die("No property ID provided.");
}

$current_userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;

$favoritePropertyIds = [];
if ($current_userid) {
    $fav_sql = "SELECT propertyid FROM favorites WHERE userid = ?";
    $fav_stmt = $conn->prepare($fav_sql);
    $fav_stmt->bind_param("i", $current_userid);
    $fav_stmt->execute();
    $fav_result = $fav_stmt->get_result();
    while ($fav_row = $fav_result->fetch_assoc()) {
        $favoritePropertyIds[] = $fav_row['propertyid'];
    }
    $fav_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Details | Duhok Estate Navigator</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/property_details.css">
</head>
<style>
    
:root {
    --primary-color: #0C2D48;      /* Deep Ocean Blue */
    --secondary-color: #1A3C5B;    /* Rich Steel Blue */
    --accent-color: #4C83B7;       /* Elegant Sky Blue */
    --light-color: #A7C6ED;        /* Soft Powder Blue */
    --dark-color: #02182B;         /* Pure Midnight Blue */
    --success-color: #2D9B6A;      /* Lush Emerald Green */
    --danger-color: #E74C3C;       /* Bold Ruby Red */
    --border-radius: 8px;
    --box-shadow: 0 4px 12px rgba(76, 131, 183, 0.3); /* Subtle Sky Blue Glow */
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    line-height: 1.6;
    color: var(--dark-color);
    background-color: #f8f9fa;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Header Styles */
header {
    background-color: var(--dark-color);
    box-shadow: var(--box-shadow);
    position: sticky;
    top: 0;
    z-index: 100;
}

.header-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
}

.logo {
    display: flex;
    align-items: center;
    text-decoration: none;
}

.logo img {
    height: 25px;
    margin-right: 10px;
}

.logo-text {
    font-size: 24px;
    font-weight: 700;
    color: var(--primary-color);
}

/* Navigation Styles */
nav ul {
    display: flex;
    list-style: none;
}

nav ul li {
    margin-left: 25px;
}

nav ul li a {
    text-decoration: none;
    color: #f8f9fa;
    font-weight: 500;
    display: flex;
    align-items: center;
    transition: color 0.3s;
}

nav ul li a:hover {
    color: var(--accent-color);
}

nav ul li a i {
    margin-right: 8px;
    font-size: 18px;
}

/* Property Details */
.property-details-container {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 30px;
    margin: 40px auto;
    max-width: 1000px;
}

.property-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
}

.property-title {
    font-size: 28px;
    color: var(--primary-color);
    margin-bottom: 5px;
}

.property-price {
    font-size: 24px;
    color: var(--accent-color);
    font-weight: 700;
}

.property-meta {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.property-meta-item {
    display: flex;
    align-items: center;
    color: var(--secondary-color);
}

.property-meta-item i {
    margin-right: 8px;
    color: var(--accent-color);
}

.property-image {
    width: 100%;
    max-height: 500px;
    object-fit: cover;
    border-radius: var(--border-radius);
    margin-bottom: 25px;
}

.property-features {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.feature-card {
    background-color: var(--light-color);
    padding: 15px;
    border-radius: var(--border-radius);
    text-align: center;
}

.feature-card i {
    font-size: 24px;
    color: var(--primary-color);
    margin-bottom: 10px;
}

.feature-card h4 {
    color: var(--secondary-color);
    margin-bottom: 5px;
}

.feature-card p {
    font-weight: 600;
    color: var(--primary-color);
}

.property-description {
    margin-bottom: 30px;
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: var(--border-radius);
}

.property-description h3 {
    color: var(--secondary-color);
    margin-bottom: 15px;
}

.property-actions {
    text-decoration: none;
    display: flex;
    gap: 15px;
    margin-bottom: 30px;
}

.btn {
    padding: 12px 20px;
    border: none;
    border-radius: var(--border-radius);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
}

.btn i {
    text-decoration: none;
    margin-right: 8px;
}

.btn-primary {
    text-decoration: none;
    background-color: var(--accent-color);
    color: white;
}

.btn-primary:hover {
    background-color: var(--secondary-color);
    transform: translateY(-2px);
}

.btn-outline {
    text-decoration: none;
    background-color: transparent;
    border: 1px solid var(--primary-color);
    color: var(--primary-color);
}

.btn-outline:hover {
    background-color: var(--primary-color);
    color: white;
}

.btn-danger {
    background-color: var(--danger-color);
    color: white;
    text-decoration: none;
}

.btn-danger:hover {
    background-color: #c0392b;
    transform: translateY(-2px);
}

.favorite-btn {
    position: absolute;
    top: 20px;
    right: 20px;
    background-color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    z-index: 2;
    transition: all 0.3s;
}

.favorite-btn i {
    color: var(--danger-color);
    font-size: 20px;
    transition: all 0.3s;
}

.favorite-btn:hover i {
    transform: scale(1.2);
}

.favorite-btn.active {
    background-color: var(--danger-color);
}

.favorite-btn.active i {
    color: white;
}

/* Owner Info */
.owner-info {
    background-color: #f8f9fa;
    border-radius: var(--border-radius);
    padding: 25px;
    margin-top: 40px;
}

.owner-header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.owner-avatar {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 20px;
    border: 3px solid var(--accent-color);
}

.owner-name {
    font-size: 20px;
    color: var(--primary-color);
    margin-bottom: 5px;
}

.owner-contact {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.contact-item {
    display: flex;
    align-items: center;
}

.contact-item i {
    margin-right: 10px;
    color: var(--accent-color);
    width: 20px;
    text-align: center;
}

.social-links {
    display: flex;
    gap: 15px;
    margin-top: 15px;
}

.social-links a {
    color: var(--secondary-color);
    font-size: 20px;
    transition: color 0.3s;
}

.social-links a:hover {
    color: var(--accent-color);
}

/* Delete Confirmation Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.modal-overlay.active {
    opacity: 1;
    visibility: visible;
}

.modal-content {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 30px;
    max-width: 500px;
    width: 90%;
    transform: translateY(-20px);
    transition: transform 0.3s ease;
}

.modal-overlay.active .modal-content {
    transform: translateY(0);
}

.modal-header {
    text-align: center;
    margin-bottom: 20px;
}

.modal-header h2 {
    color: var(--danger-color);
    margin-bottom: 10px;
}

.modal-body {
    text-align: center;
    margin-bottom: 25px;
}

.modal-body p {
    color: var(--secondary-color);
    margin-bottom: 15px;
}

.modal-footer {
    display: flex;
    justify-content: center;
    gap: 15px;
}

.modal-btn {
    padding: 10px 20px;
    border: none;
    border-radius: var(--border-radius);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.modal-btn-cancel {
    background-color: #f8f9fa;
    color: var(--secondary-color);
}

.modal-btn-cancel:hover {
    background-color: #e9ecef;
}

.modal-btn-confirm {
    background-color: var(--danger-color);
    color: white;
}

.modal-btn-confirm:hover {
    background-color: #c0392b;
}

/* Footer Styles */
footer {
    background: linear-gradient(135deg, var(--dark-color), #0d0d3a);
    color: white;
    padding: 50px 0 20px;
}

.footer-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 30px;
    margin-bottom: 30px;
}

.footer-logo {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}

.footer-logo img {
    height: 25px;
    margin-right: 10px;
}

.footer-logo-text {
    font-size: 20px;
    font-weight: 700;
    color: white;
}

.footer-about p {
    margin-bottom: 15px;
    opacity: 0.8;
}

.footer-social-links {
    display: flex;
    gap: 15px;
}

.footer-social-links a {
    color: white;
    font-size: 20px;
    transition: color 0.3s;
}

.footer-social-links a:hover {
    color: var(--accent-color);
}

.footer-links h3 {
    font-size: 18px;
    margin-bottom: 20px;
    position: relative;
    padding-bottom: 10px;
}

.footer-links h3::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 40px;
    height: 2px;
    background-color: var(--accent-color);
}

.footer-links ul {
    list-style: none;
}

.footer-links li {
    margin-bottom: 10px;
}

.footer-links a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: color 0.3s;
}

.footer-links a:hover {
    color: white;
}

.footer-contact p {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    opacity: 0.8;
}

.footer-contact i {
    margin-right: 10px;
    color: var(--accent-color);
}

.copyright {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    opacity: 0.7;
    font-size: 14px;
}

/* Responsive Styles */
@media (max-width: 768px) {
    .header-container {
        flex-direction: column;
        padding: 15px 0;
    }

    .logo {
        margin-bottom: 15px;
    }

    nav ul {
        flex-wrap: wrap;
        justify-content: center;
    }

    nav ul li {
        margin: 0 10px 10px;
    }

    .property-header {
        flex-direction: column;
    }

    .property-features {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }

    .owner-header {
        flex-direction: column;
        text-align: center;
    }

    .owner-avatar {
        margin-right: 0;
        margin-bottom: 15px;
    }
    
    .modal-footer {
        flex-direction: column;
    }
    
    .modal-btn {
        width: 100%;
    }
}
</style>
<body>
    <header>
        <div class="container header-container">
            <a href="home.php" class="logo">
                <img src="icons/denwhite.svg" alt="DEN LOGO">
                <span class="logo-text"></span>
            </a>
            <nav>
                <ul>
                    <li><a href="home.php"><i class="fas fa-home"></i> Home</a></li>
                    <?php if ($current_userid): ?>
                        <li><a href="my_properties.php"><i class="fas fa-building"></i> My Properties</a></li>
                        <li><a href="favorites.php"><i class="fas fa-heart"></i> Favorites</a></li>
                        <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'super_admin')): ?>
                    <li><a href="admin_dashboard.php"><i class="fas fa-user-shield"></i> Admin</a></li>
                    <?php endif; ?>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    <?php else: ?>
                        <li><a href="signup.php"><i class="fas fa-user-plus"></i> Signup</a></li>
                        <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="property-details-container">
            <div class="property-header">
                <div>
                    <h1 class="property-title"><?php echo ucfirst($row['type']); ?> in <?php echo $row['location']; ?></h1>
                    <div class="property-meta">
                        <div class="property-meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo $row['location']; ?></span>
                        </div>
                        <div class="property-meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Posted: <?php echo date('M j, Y', strtotime($row['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
                <div class="property-price">$<?php echo number_format($row['price']); ?></div>
            </div>

            <div style="position: relative;">
                <?php if ($current_userid): ?>
                    <div id="favorite-icon" class="favorite-btn <?php echo in_array($propertyid, $favoritePropertyIds) ? 'active' : ''; ?>" onclick="toggleFavorite(<?php echo $propertyid; ?>)">
                        <i class="<?php echo in_array($propertyid, $favoritePropertyIds) ? 'fas' : 'far'; ?> fa-heart"></i>
                    </div>
                <?php endif; ?>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($row['image']); ?>" class="property-image" alt="Property Image">
            </div>

            <div class="property-features">
                <div class="feature-card">
                    <i class="fas fa-layer-group"></i>
                    <h4>Floors</h4>
                    <p><?php echo $row['floors']; ?></p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-bed"></i>
                    <h4>Bedrooms</h4>
                    <p><?php echo $row['beds']; ?></p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-bath"></i>
                    <h4>Bathrooms</h4>
                    <p><?php echo $row['baths']; ?></p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-utensils"></i>
                    <h4>Kitchens</h4>
                    <p><?php echo $row['kitchen']; ?></p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-car"></i>
                    <h4>Garage</h4>
                    <p><?php echo ucfirst($row['garage']); ?></p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-tree"></i>
                    <h4>Garden</h4>
                    <p><?php echo ucfirst($row['garden']); ?></p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-dumbbell"></i>
                    <h4>Gym</h4>
                    <p><?php echo ucfirst($row['gym']); ?></p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-swimming-pool"></i>
                    <h4>Pool</h4>
                    <p><?php echo ucfirst($row['pool']); ?></p>
                </div>
            </div>

            <div class="property-description">
                <h3>Description</h3>
                <p><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
            </div>

            <div class="property-details">
                <h3>Additional Details</h3>
                <p><strong>Direction:</strong> <?php echo ucfirst($row['direction']); ?></p>
                <p><strong>Status:</strong> <?php echo ucfirst($row['status']); ?></p>
            </div>

            <?php if ($row['userid'] == $current_userid): ?>
                <div class="property-actions">
                    <a href="edit_property.php?id=<?php echo $row['propertyid']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Property
                    </a>
                    <button class="btn btn-danger" id="deletePropertyBtn" data-propertyid="<?php echo $row['propertyid']; ?>">
                        <i class="fas fa-trash"></i> Delete Property
                    </button>
                </div>
            <?php endif; ?>

            <div class="owner-info">
                <div class="owner-header">
                    <?php if(!empty($user['profile_image'])): ?>
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($user['profile_image']); ?>" class="owner-avatar" alt="Owner Avatar">
                    <?php else: ?>
                        <div class="owner-avatar" style="background-color: var(--light-color); display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-user" style="font-size: 30px; color: var(--primary-color);"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h3 class="owner-name"><?php echo htmlspecialchars($user['name']); ?></h3>
                        <p>Property Owner</p>
                    </div>
                </div>

                <div class="owner-contact">
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span><?php echo htmlspecialchars($user['phone_number1']); ?></span>
                    </div>
                    <?php if(!empty($user['phone_number2'])): ?>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span><?php echo htmlspecialchars($user['phone_number2']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                </div>

                <div class="social-links">
                    <?php if(!empty($user['facebook'])): ?>
                        <a href="<?php echo htmlspecialchars($user['facebook']); ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <?php endif; ?>
                    <?php if(!empty($user['instagram'])): ?>
                        <a href="<?php echo htmlspecialchars($user['instagram']); ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                    <?php endif; ?>
                    <?php if(!empty($user['tiktok'])): ?>
                        <a href="<?php echo htmlspecialchars($user['tiktok']); ?>" target="_blank"><i class="fab fa-tiktok"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
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
                    <div class="footer-social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
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
        function toggleFavorite(propertyid) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "toggle_favorite.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    var icon = document.getElementById('favorite-icon');
                    if (response.status === 'added') {
                        icon.innerHTML = '<i class="fas fa-heart"></i>';
                        icon.classList.add('active');
                    } else {
                        icon.innerHTML = '<i class="far fa-heart"></i>';
                        icon.classList.remove('active');
                    }
                } else {
                    alert("Error toggling favorite status");
                }
            };
            xhr.send("propertyid=" + propertyid);
        }

        const deletePropertyModal = document.getElementById('deletePropertyModal');
        const deletePropertyBtn = document.getElementById('deletePropertyBtn');
        const cancelPropertyDeleteBtn = document.getElementById('cancelPropertyDelete');
        const confirmPropertyDeleteBtn = document.getElementById('confirmPropertyDelete');
        
        if (deletePropertyBtn) {
            deletePropertyBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const propertyId = deletePropertyBtn.getAttribute('data-propertyid');
                deletePropertyModal.classList.add('active');
                
                confirmPropertyDeleteBtn.href = `delete_property.php?id=${propertyId}`;
            });
        }
        
        cancelPropertyDeleteBtn.addEventListener('click', () => {
            deletePropertyModal.classList.remove('active');
        });
        
        window.addEventListener('click', (e) => {
            if (e.target === deletePropertyModal) {
                deletePropertyModal.classList.remove('active');
            }
        });
    </script>
</body>
</html>