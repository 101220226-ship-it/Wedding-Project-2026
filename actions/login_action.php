<?php
session_start();
include("../config/db.php");


// Get name and email
$name  = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
// Combine country code and phone number
$phone = '';

if (isset($_POST['country_code'], $_POST['phone'])) {
    $country_code = trim($_POST['country_code']);
    $phone_number = trim($_POST['phone']);

    // remove spaces
    $country_code = str_replace(' ', '', $country_code);
    $phone_number = preg_replace('/\s+/', '', $phone_number);

    // if user forgot "+"
    if ($country_code !== '' && $country_code[0] !== '+') {
        $country_code = '+' . $country_code;
    }

    $phone = $country_code . $phone_number;
    if ($name == '' || $phone == '') {
    header("Location: ../pages/login.php?err=missing");
    exit();
}
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