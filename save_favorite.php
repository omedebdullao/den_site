<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['userid'])) {
    die("Unauthorized access");
}

$userid = $_SESSION['userid'];
$propertyid = $_POST['propertyid'];

$sql = "SELECT * FROM favorites WHERE userid = ? AND propertyid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $userid, $propertyid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $sql = "INSERT INTO favorites (userid, propertyid) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userid, $propertyid);
    if ($stmt->execute()) {
        echo "Property saved as favorite.";
    } else {
        echo "Error saving property: " . $stmt->error;
    }
} else {
    echo "Property already saved as favorite.";
}

$stmt->close();
$conn->close();
?>
