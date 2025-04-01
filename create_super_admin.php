<?php
include 'db_connect.php'; 

$password = password_hash('2002omed', PASSWORD_BCRYPT); 

$sql = "INSERT INTO user (name, email, password, role) VALUES ('omed', 'omedebdullao@den.com', ?, 'super_admin')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $password);
if ($stmt->execute()) {
    echo "Super admin created successfully.";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
