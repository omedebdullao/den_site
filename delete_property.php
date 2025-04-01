<?php
session_start();
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}
include 'db_connect.php';

if (isset($_GET['id'])) {
    $propertyid = $_GET['id'];
    $userid = $_SESSION['userid'];

    $sql = "SELECT userid FROM property WHERE propertyid=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $propertyid);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['userid'] == $userid) {
        $sql = "DELETE FROM favorites WHERE propertyid=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $propertyid);
        $stmt->execute();

        $sql = "DELETE FROM property WHERE propertyid=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $propertyid);
        if ($stmt->execute()) {
            header("Location: home.php");
        } else {
            echo "Error deleting property: " . $stmt->error;
        }
    } else {
        echo "You do not have permission to delete this property.";
    }

    $stmt->close();
} else {
    echo "No property ID provided.";
}

$conn->close();
?>
