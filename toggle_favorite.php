<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['userid'])) {
    die("Unauthorized access");
}

$userid = $_SESSION['userid'];
$propertyid = $_POST['propertyid'];

// Check if the property is already saved as favorite
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
        $response = ["status" => "added"];
    } else {
        $response = ["status" => "error", "message" => $stmt->error];
    }
} else {
    $sql = "DELETE FROM favorites WHERE userid = ? AND propertyid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userid, $propertyid);
    if ($stmt->execute()) {
        $response = ["status" => "removed"];
    } else {
        $response = ["status" => "error", "message" => $stmt->error];
    }
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>
