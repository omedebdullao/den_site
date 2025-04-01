<?php
session_start();

if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}
include 'db_connect.php';

$userid = $_SESSION['userid'];

$sql = "UPDATE user SET profile_image = NULL WHERE userid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userid);

if ($stmt->execute()) {
    header("Location: profile.php");
    exit();
} else {
    echo "Error deleting profile image: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
