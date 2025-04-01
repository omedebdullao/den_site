<?php
session_start();
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}
include 'db_connect.php';

$userid = $_SESSION['userid'];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$location = isset($_GET['location']) ? $_GET['location'] : '';

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

$sql = "SELECT * FROM property WHERE (type LIKE ? OR location LIKE ? OR price LIKE ? OR description LIKE ?)";
$params = ["%$search%", "%$search%", "%$search%", "%$search%"];
$types = "ssss";

if ($type) {
    $sql .= " AND type=?";
    $params[] = $type;
    $types .= "s";
}

if ($location) {
    $sql .= " AND location=?";
    $params[] = $location;
    $types .= "s";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/home.css">
</head>
<body>
    <header>
        <div class="container header-container">
            <a href="home.php" class="logo">
                <img src="icons/den.svg" alt="DEN LOGO">
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

    <section class="search-section">
        <div class="container">
            <form method="GET" action="" class="search-form">
                <div class="form-group">
                    <label for="search">Search Properties</label>
                    <input type="text" id="search" name="search" class="form-control" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="form-group">
                    <label for="type">Property Type</label>
                    <select id="type" name="type" class="form-control">
                        <option value="">All Types</option>
                        <option value="house" <?php echo ($type == 'house') ? 'selected' : ''; ?>>House</option>
                        <option value="apartment" <?php echo ($type == 'apartment') ? 'selected' : ''; ?>>Apartment</option>
                        <option value="building" <?php echo ($type == 'building') ? 'selected' : ''; ?>>Building</option>
                        <option value="land" <?php echo ($type == 'land') ? 'selected' : ''; ?>>Land</option>
                        <option value="farm" <?php echo ($type == 'farm') ? 'selected' : ''; ?>>Farm</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="location">Location</label>
                    <select id="location" name="location" class="form-control">
                        <option value="">All Locations</option>
                        <option value="Masike" <?php echo ($location == 'Masike') ? 'selected' : ''; ?>>Masike</option>
                        <option value="Zrka" <?php echo ($location == 'Zrka') ? 'selected' : ''; ?>>Zrka</option>
                        <option value="Nizarke" <?php echo ($location == 'Nizarke') ? 'selected' : ''; ?>>Nizarke</option>
                        <option value="Bazar" <?php echo ($location == 'Bazar') ? 'selected' : ''; ?>>Bazar</option>
                        <option value="Hay-Alskari" <?php echo ($location == 'Hay-Alskari') ? 'selected' : ''; ?>>Hay-Alskari</option>
                        <option value="Hay-Alshorta" <?php echo ($location == 'Hay-Alshorta') ? 'selected' : ''; ?>>Hay-Alshorta</option>
                        <option value="Shele" <?php echo ($location == 'Shele') ? 'selected' : ''; ?>>Shele</option>
                        <option value="KRO" <?php echo ($location == 'KRO') ? 'selected' : ''; ?>>KRO</option>
                        <option value="Malta" <?php echo ($location == 'Malta') ? 'selected' : ''; ?>>Malta</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                </div>
            </form>
        </div>
    </section>

    <main class="container">
        <div class="properties-header">
            <h1 class="section-title">Available Properties</h1>
            <?php if (isset($_SESSION['userid'])): ?>
                <a href="add_property.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Property</a>
            <?php endif; ?>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="properties-grid">
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="property-card">
                        <button class="favorite-btn <?php echo in_array($row['propertyid'], $favoritePropertyIds) ? 'active' : ''; ?>" onclick="toggleFavorite(<?php echo $row['propertyid']; ?>)">
                            <i class="<?php echo in_array($row['propertyid'], $favoritePropertyIds) ? 'fas' : 'far'; ?> fa-heart"></i>
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
                                <a href="property_details.php?id=<?php echo $row['propertyid']; ?>" class="btn btn-sm btn-outline">
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
                <i class="fas fa-search fa-3x" style="color: #ccc; margin-bottom: 20px;"></i>
                <h3>No properties found matching your criteria</h3>
                <p>Try adjusting your search filters or <a href="add_property.php">add a new property</a></p>
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
                    const btn = document.querySelector(`.favorite-btn[onclick="toggleFavorite(${propertyId})"]`);
                    
                    if (response.status === 'added') {
                        btn.classList.add('active');
                        btn.innerHTML = '<i class="fas fa-heart"></i>';
                    } else {
                        btn.classList.remove('active');
                        btn.innerHTML = '<i class="far fa-heart"></i>';
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