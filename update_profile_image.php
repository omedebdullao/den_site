<?php
session_start();

if (!isset($_SESSION['userid'])) {
    die("Unauthorized access");
}
include 'db_connect.php';

$userid = $_SESSION['userid'];

if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
    $profile_image = file_get_contents($_FILES['profile_image']['tmp_name']);

    $sql = "UPDATE user SET profile_image = ? WHERE userid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $profile_image, $userid);

    if ($stmt->execute()) {
        echo 'data:image/jpeg;base64,' . base64_encode($profile_image);
    } else {
        http_response_code(500);
        echo "Error updating profile image: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    http_response_code(400);
    echo "No image uploaded or upload error.";
}
?>
