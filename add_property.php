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

if (!$is_blocked && $_SERVER["REQUEST_METHOD"] == "POST") {
    $type = $_POST['type'];
    $location = $_POST['location'];
    $price = $_POST['price'];
    $userid = $_SESSION['userid'];
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
    

    $image = addslashes(file_get_contents($_FILES['image']['tmp_name']));


    $sql = "INSERT INTO property (userid, image, type, location, price, floors, direction, beds, baths, kitchen, garage, garden, gym, pool, status, description) 
            VALUES ('$userid', '$image', '$type', '$location', '$price', '$floors', '$direction', '$beds', '$baths', '$kitchen', '$garage', '$garden', '$gym', '$pool', '$status', '$description')";

    if ($conn->query($sql) === TRUE) {
        header("Location: home.php");
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
    <title>Add Property</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/add_property.css">
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
                <p>You cannot add new properties while your account is blocked.</p>
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
            <h2><i class="fas fa-plus-circle"></i> Add New Property</h2>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form action="add_property.php" method="post" enctype="multipart/form-data" class="form-grid">
                <div class="form-group">
                    <label for="type">Property Type</label>
                    <select name="type" id="type" class="form-control" required>
                        <option value="">Select Type</option>
                        <option value="house">House</option>
                        <option value="apartment">Apartment</option>
                        <option value="building">Building</option>
                        <option value="land">Land</option>
                        <option value="farm">Farm</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="location">Location</label>
                    <select name="location" id="location" class="form-control" required>
                        <option value="">Select Location</option>
                        <option value="Masike">Masike</option>
                        <option value="Zrka">Zrka</option>
                        <option value="Nizarke">Nizarke</option>
                        <option value="Bazar">Bazar</option>
                        <option value="Hay-Alskari">Hay-Alskari</option>
                        <option value="Hay-Alshorta">Hay-Alshorta</option>
                        <option value="Shele">Shele</option>
                        <option value="KRO">KRO</option>
                        <option value="Malta">Malta</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="price">Price (USD)</label>
                    <input type="number" name="price" id="price" class="form-control" min="10000" placeholder="Enter price" required>
                </div>
                
                <div class="form-group">
                    <label for="image">Property Image</label>
                    <div class="file-input-wrapper">
                        <input type="file" name="image" id="image" class="form-control" required>
                        <label for="image" class="file-input-label">
                            <i class="fas fa-cloud-upload-alt"></i> Choose an image
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="floors">Number of Floors</label>
                    <input type="number" name="floors" id="floors" class="form-control" min="0" placeholder="Enter number of floors" required>
                </div>
                
                <div class="form-group">
                    <label for="direction">Direction</label>
                    <select name="direction" id="direction" class="form-control" required>
                        <option value="">Select Direction</option>
                        <option value="east">East</option>
                        <option value="west">West</option>
                        <option value="north">North</option>
                        <option value="south">South</option>
                        <option value="northeast">Northeast</option>
                        <option value="northwest">Northwest</option>
                        <option value="southeast">Southeast</option>
                        <option value="southwest">Southwest</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="beds">Number of Bedrooms</label>
                    <input type="number" name="beds" id="beds" class="form-control" min="0" placeholder="Enter number of bedrooms" required>
                </div>
                
                <div class="form-group">
                    <label for="baths">Number of Bathrooms</label>
                    <input type="number" name="baths" id="baths" class="form-control" min="0" placeholder="Enter number of bathrooms" required>
                </div>
                
                <div class="form-group">
                    <label for="kitchen">Number of Kitchens</label>
                    <input type="number" name="kitchen" id="kitchen" class="form-control" min="0" placeholder="Enter number of kitchens" required>
                </div>
                
                <div class="form-group">
                    <label for="garage">Garage</label>
                    <select name="garage" id="garage" class="form-control" required>
                        <option value="">Select Option</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="garden">Garden</label>
                    <select name="garden" id="garden" class="form-control" required>
                        <option value="">Select Option</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="gym">Gym</label>
                    <select name="gym" id="gym" class="form-control" required>
                        <option value="">Select Option</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="pool">Pool</label>
                    <select name="pool" id="pool" class="form-control" required>
                        <option value="">Select Option</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="">Select Status</option>
                        <option value="available">Available</option>
                        <option value="sold">Sold</option>
                    </select>
                </div>
                
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="4" placeholder="Enter property description" required></textarea>
                </div>
                
                <button type="submit" class="btn" style="grid-column: 1 / -1;">
                    <i class="fas fa-plus-circle"></i> Add Property
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