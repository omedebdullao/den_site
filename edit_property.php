<?php
session_start();
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}
include 'db_connect.php';

$userid = $_SESSION['userid'];
$user_check = $conn->query("SELECT is_active FROM user WHERE userid = '$userid'");
if($user_check->num_rows > 0){
    $user_status = $user_check->fetch_assoc();
    $is_blocked = ($user_status['is_active'] == 0);
} else {
    $is_blocked = true;
}

// Fetch property data if we're editing
$property_id = isset($_GET['id']) ? $_GET['id'] : null;
$property_data = null;

if ($property_id) {
    $property_query = $conn->query("SELECT * FROM property WHERE propertyid = '$property_id' AND userid = '$userid'");
    if ($property_query->num_rows > 0) {
        $property_data = $property_query->fetch_assoc();
    } else {
        // Property doesn't exist or doesn't belong to user
        header("Location: my_properties.php");
        exit;
    }
} else {
    // No property ID provided
    header("Location: my_properties.php");
    exit;
}

if (!$is_blocked && $_SERVER["REQUEST_METHOD"] == "POST") {
    $type = $_POST['type'];
    $location = $_POST['location'];
    $price = $_POST['price'];
    $floors = $_POST['floors'];
    $direction = $_POST['direction'];
    $beds = $_POST['beds'];
    $baths = $_POST['baths'];
    $kitchen = $_POST['kitchen'];
    $garage = $_POST['garage'];
    $garden = $_POST['garden'];
    $gym = $_POST['gym'];
    $pool = $_POST['pool'];
    $status = $_POST['status'];
    $description = $_POST['description'];
    
    // Handle image upload if a new one was provided
    if ($_FILES['image']['size'] > 0) {
        $image = addslashes(file_get_contents($_FILES['image']['tmp_name']));
        $image_sql = ", image = '$image'";
    } else {
        $image_sql = "";
    }

    $sql = "UPDATE property SET 
            type = '$type', 
            location = '$location', 
            price = '$price', 
            floors = '$floors', 
            direction = '$direction', 
            beds = '$beds', 
            baths = '$baths', 
            kitchen = '$kitchen', 
            garage = '$garage', 
            garden = '$garden', 
            gym = '$gym', 
            pool = '$pool', 
            status = '$status', 
            description = '$description'
            $image_sql
            WHERE propertyid = '$property_id' AND userid = '$userid'";

    if ($conn->query($sql) === TRUE) {
        header("Location: my_properties.php");
        exit;
    } else {
        $error = "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/edit_property.css">
</head>
<body>
    <?php if ($is_blocked): ?>
    <div class="modal-overlay active" id="blockedUserModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-ban"></i> Account Blocked</h2>
            </div>
            <div class="modal-body">
                <p>Your account has been restricted by the company.</p>
                <p>You cannot edit properties while your account is blocked.</p>
                <p><strong>Please contact support if you believe this is an error.</strong></p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-confirm" onclick="window.location.href='home.php'">
                    <i class="fas fa-home"></i> Return Home
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

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
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'super_admin')): ?>
                    <li><a href="admin_dashboard.php"><i class="fas fa-user-shield"></i> Admin</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="property-form-container">
        <div class="property-form <?php echo $is_blocked ? 'disabled-form' : ''; ?>">
            <h2><i class="fas fa-edit"></i> Edit Property</h2>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form action="edit_property.php?id=<?php echo $property_id; ?>" method="post" enctype="multipart/form-data" class="form-grid">
                <div class="form-group">
                    <label for="type">Property Type</label>
                    <select name="type" id="type" class="form-control" required>
                        <option value="">Select Type</option>
                        <option value="house" <?php echo ($property_data['type'] == 'house') ? 'selected' : ''; ?>>House</option>
                        <option value="apartment" <?php echo ($property_data['type'] == 'apartment') ? 'selected' : ''; ?>>Apartment</option>
                        <option value="building" <?php echo ($property_data['type'] == 'building') ? 'selected' : ''; ?>>Building</option>
                        <option value="land" <?php echo ($property_data['type'] == 'land') ? 'selected' : ''; ?>>Land</option>
                        <option value="farm" <?php echo ($property_data['type'] == 'farm') ? 'selected' : ''; ?>>Farm</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="location">Location</label>
                    <select name="location" id="location" class="form-control" required>
                        <option value="">Select Location</option>
                        <option value="Masike" <?php echo ($property_data['location'] == 'Masike') ? 'selected' : ''; ?>>Masike</option>
                        <option value="Zrka" <?php echo ($property_data['location'] == 'Zrka') ? 'selected' : ''; ?>>Zrka</option>
                        <option value="Nizarke" <?php echo ($property_data['location'] == 'Nizarke') ? 'selected' : ''; ?>>Nizarke</option>
                        <option value="Bazar" <?php echo ($property_data['location'] == 'Bazar') ? 'selected' : ''; ?>>Bazar</option>
                        <option value="Hay-Alskari" <?php echo ($property_data['location'] == 'Hay-Alskari') ? 'selected' : ''; ?>>Hay-Alskari</option>
                        <option value="Hay-Alshorta" <?php echo ($property_data['location'] == 'Hay-Alshorta') ? 'selected' : ''; ?>>Hay-Alshorta</option>
                        <option value="Shele" <?php echo ($property_data['location'] == 'Shele') ? 'selected' : ''; ?>>Shele</option>
                        <option value="KRO" <?php echo ($property_data['location'] == 'KRO') ? 'selected' : ''; ?>>KRO</option>
                        <option value="Malta" <?php echo ($property_data['location'] == 'Malta') ? 'selected' : ''; ?>>Malta</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="price">Price (USD)</label>
                    <input type="number" name="price" id="price" class="form-control" min="10000" placeholder="Enter price" value="<?php echo htmlspecialchars($property_data['price']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="image">Property Image</label>
                    <div class="file-input-wrapper">
                        <input type="file" name="image" id="image" class="form-control">
                        <label for="image" class="file-input-label">
                            <i class="fas fa-cloud-upload-alt"></i> Choose a new image (optional)
                        </label>
                    </div>
                    <?php if ($property_data['image']): ?>
                    <div class="current-image-preview">
                        <p>Current Image:</p>
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($property_data['image']); ?>" alt="Current Property Image" style="max-width: 200px; max-height: 150px;">
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="floors">Number of Floors</label>
                    <input type="number" name="floors" id="floors" class="form-control" min="0" placeholder="Enter number of floors" value="<?php echo htmlspecialchars($property_data['floors']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="direction">Direction</label>
                    <select name="direction" id="direction" class="form-control" required>
                        <option value="">Select Direction</option>
                        <option value="east" <?php echo ($property_data['direction'] == 'east') ? 'selected' : ''; ?>>East</option>
                        <option value="west" <?php echo ($property_data['direction'] == 'west') ? 'selected' : ''; ?>>West</option>
                        <option value="north" <?php echo ($property_data['direction'] == 'north') ? 'selected' : ''; ?>>North</option>
                        <option value="south" <?php echo ($property_data['direction'] == 'south') ? 'selected' : ''; ?>>South</option>
                        <option value="northeast" <?php echo ($property_data['direction'] == 'northeast') ? 'selected' : ''; ?>>Northeast</option>
                        <option value="northwest" <?php echo ($property_data['direction'] == 'northwest') ? 'selected' : ''; ?>>Northwest</option>
                        <option value="southeast" <?php echo ($property_data['direction'] == 'southeast') ? 'selected' : ''; ?>>Southeast</option>
                        <option value="southwest" <?php echo ($property_data['direction'] == 'southwest') ? 'selected' : ''; ?>>Southwest</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="beds">Number of Bedrooms</label>
                    <input type="number" name="beds" id="beds" class="form-control" min="0" placeholder="Enter number of bedrooms" value="<?php echo htmlspecialchars($property_data['beds']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="baths">Number of Bathrooms</label>
                    <input type="number" name="baths" id="baths" class="form-control" min="0" placeholder="Enter number of bathrooms" value="<?php echo htmlspecialchars($property_data['baths']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="kitchen">Number of Kitchens</label>
                    <input type="number" name="kitchen" id="kitchen" class="form-control" min="0" placeholder="Enter number of kitchens" value="<?php echo htmlspecialchars($property_data['kitchen']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="garage">Garage</label>
                    <select name="garage" id="garage" class="form-control" required>
                        <option value="">Select Option</option>
                        <option value="yes" <?php echo ($property_data['garage'] == 'yes') ? 'selected' : ''; ?>>Yes</option>
                        <option value="no" <?php echo ($property_data['garage'] == 'no') ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="garden">Garden</label>
                    <select name="garden" id="garden" class="form-control" required>
                        <option value="">Select Option</option>
                        <option value="yes" <?php echo ($property_data['garden'] == 'yes') ? 'selected' : ''; ?>>Yes</option>
                        <option value="no" <?php echo ($property_data['garden'] == 'no') ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="gym">Gym</label>
                    <select name="gym" id="gym" class="form-control" required>
                        <option value="">Select Option</option>
                        <option value="yes" <?php echo ($property_data['gym'] == 'yes') ? 'selected' : ''; ?>>Yes</option>
                        <option value="no" <?php echo ($property_data['gym'] == 'no') ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="pool">Pool</label>
                    <select name="pool" id="pool" class="form-control" required>
                        <option value="">Select Option</option>
                        <option value="yes" <?php echo ($property_data['pool'] == 'yes') ? 'selected' : ''; ?>>Yes</option>
                        <option value="no" <?php echo ($property_data['pool'] == 'no') ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="">Select Status</option>
                        <option value="available" <?php echo ($property_data['status'] == 'available') ? 'selected' : ''; ?>>Available</option>
                        <option value="sold" <?php echo ($property_data['status'] == 'sold') ? 'selected' : ''; ?>>Sold</option>
                    </select>
                </div>
                
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="4" placeholder="Enter property description" required><?php echo htmlspecialchars($property_data['description']); ?></textarea>
                </div>
                
                <button type="submit" class="btn" style="grid-column: 1 / -1;">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>
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
</body>
</html>