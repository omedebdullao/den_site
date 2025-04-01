<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}

$userid = $_SESSION['userid'];

$favoritesSql = "SELECT propertyid FROM favorites WHERE userid = ?";
$favoritesStmt = $conn->prepare($favoritesSql);
if (!$favoritesStmt) {
    die("Prepare statement failed: " . $conn->error);
}
$favoritesStmt->bind_param("i", $userid);
$favoritesStmt->execute();
$favoritesResult = $favoritesStmt->get_result();
if (!$favoritesResult) {
    die("Query execution failed: " . $favoritesStmt->error);
}
$favoritePropertyIds = [];
while ($favoriteRow = $favoritesResult->fetch_assoc()) {
    $favoritePropertyIds[] = $favoriteRow['propertyid'];
}
$favoritesStmt->close();

$sql = "SELECT property.* FROM property 
        INNER JOIN favorites ON property.propertyid = favorites.propertyid 
        WHERE favorites.userid = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare statement failed: " . $conn->error);
}
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();
if (!$result) {
    die("Query execution failed: " . $stmt->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorites</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/favorites.css">
</head>
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
        <div class="properties-header">
            <h1 class="section-title">Your Favorite Properties</h1>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="properties-grid">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="property-card">
                        <button class="favorite-btn" onclick="toggleFavorite(<?php echo $row['propertyid']; ?>)">
                            <i class="fas fa-heart"></i>
                        </button>
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($row['image']); ?>" alt="<?php echo htmlspecialchars($row['type']); ?>" class="property-image">
                        <div class="property-details">
                            <span class="property-type"><?php echo ucfirst($row['type']); ?></span>
                            <h3 class="property-price">$<?php echo number_format($row['price']); ?></h3>
                            <div class="property-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($row['location']); ?></span>
                            </div>
                            <div class="property-actions">
                                <a href="property_details.php?id=<?php echo $row['propertyid']; ?>" class="btn btn-outline">
                                    <i class="fas fa-eye"></i> See More
                                </a>
                                <?php if ($row['userid'] == $userid): ?>
                                    <div class="owner-actions">
                                        <a href="edit_property.php?id=<?php echo $row['propertyid']; ?>" class="btn btn-sm btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <button class="btn btn-sm btn-delete delete-property-btn" data-propertyid="<?php echo $row['propertyid']; ?>">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="far fa-heart"></i>
                <h3>No favorites yet</h3>
                <p>You haven't added any properties to your favorites. Browse properties and click the heart icon to save them here.</p>
                <a href="home.php" class="btn" style="background-color: var(--accent-color);">
                    <i class="fas fa-search"></i> Browse Properties
                </a>
            </div>
        <?php endif; ?>
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
        function toggleFavorite(propertyId) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "toggle_favorite.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'removed') {
                        if (window.location.pathname.includes('favorites.php')) {
                            window.location.reload();
                        }
                    }
                }
            };
            xhr.send("propertyid=" + propertyId);
        }

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
                window.location.href = `delete_property.php?id=${currentPropertyIdToDelete}`;
            }
        });
        
        window.addEventListener('click', (e) => {
            if (e.target === deletePropertyModal) {
                deletePropertyModal.classList.remove('active');
                currentPropertyIdToDelete = null;
            }
        });
    </script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>