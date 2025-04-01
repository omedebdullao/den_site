<?php
include 'db_connect.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$location = isset($_GET['location']) ? $_GET['location'] : '';

$sql = "SELECT * FROM property WHERE (type LIKE '%$search%' OR location LIKE '%$search%')";
if ($type) {
    $sql .= " AND type='$type'";
}
if ($location) {
    $sql .= " AND location='$location'";
}
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/public_properties.css">
</head>
<body>
    <header>
        <div class="container header-container">
            <a href="public_properties.php" class="logo">
                <img src="icons/den.svg" alt="DEN LOGO">
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
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="properties-grid">
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="property-card">
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
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-search fa-3x" style="color: #ccc; margin-bottom: 20px;"></i>
                <h3>No properties found matching your criteria</h3>
                <p>Try adjusting your search filters</p>
            </div>
        <?php endif; ?>
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
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
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