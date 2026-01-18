<?php
session_start();
include("../config/db.php");

$name  = trim($_POST['name']);
$phone = trim($_POST['phone']);

// Check if customer exists
$sql = "SELECT * FROM users WHERE phone = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $phone);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    $user = $result->fetch_assoc();
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = $user['role'];
} else {
    // Create new customer
    $sql2 = "INSERT INTO users (name, phone, role) VALUES (?, ?, 'customer')";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("ss", $name, $phone);
    $stmt2->execute();

    $_SESSION['user_id'] = $conn->insert_id;
    $_SESSION['name'] = $name;
    $_SESSION['role'] = 'customer';
}

// Redirect to booking form
header("Location: ../pages/booking_form.php");
exit();
?>