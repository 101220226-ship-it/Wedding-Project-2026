<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['booking_id'])){
    header("Location: ../pages/booking_form.php");
    exit();
}

$booking_id = $_SESSION['booking_id'];
$amount = 50;

// Insert payment record
$sql = "INSERT INTO payments (booking_id, amount, payment_status) VALUES (?, ?, 'paid')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("id", $booking_id, $amount);
$stmt->execute();

// Redirect to success page
header("Location: ../pages/payment_success.php");
exit();
?>
