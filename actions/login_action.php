<?php
session_start();
include("../config/db.php");


// Get name and email
$name  = trim($_POST['name']);
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
// Combine country code and phone number
$phone = '';
if (isset($_POST['country_code']) && isset($_POST['phone'])) {
    $country_code = trim($_POST['country_code']);
    $phone_number = trim($_POST['phone']);
    $phone = $country_code . $phone_number;
}

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
    $_SESSION['email'] = $user['email'];
} else {
    // Create new customer
    $sql2 = "INSERT INTO users (name, phone, email, role) VALUES (?, ?, ?, 'customer')";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("sss", $name, $phone, $email);
    $stmt2->execute();

    $_SESSION['user_id'] = $conn->insert_id;
    $_SESSION['name'] = $name;
    $_SESSION['role'] = 'customer';
    $_SESSION['email'] = $email;
}

// Redirect to booking form
header("Location: ../pages/booking_form.php");
exit();
?>