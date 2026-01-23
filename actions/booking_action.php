<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: ../pages/login.php");
    exit();
}


$user_id = $_SESSION['user_id'];
$event_date = $_POST['event_date'];
$location = $_POST['location'];
$guest_count = $_POST['guest_count'];
$budget = $_POST['budget'];
// Combine country code and phone if provided
$phone = null;
if (isset($_POST['country_code']) && isset($_POST['phone'])) {
    $country_code = trim($_POST['country_code']);
    $phone_number = trim($_POST['phone']);
    $phone = $country_code . $phone_number;
}

// First check how many events exist in same date (max 2)
$check = "SELECT COUNT(*) as total FROM bookings WHERE event_date = ?";
$stmt = $conn->prepare($check);
$stmt->bind_param("s", $event_date);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if($row['total'] >= 2){
    echo "<h2 style='color:red;text-align:center;'>Sorry! Only 2 events can be booked on the same day.</h2>";
    echo "<a href='../pages/booking_form.php'>Go Back</a>";
    exit();
}

// Insert booking (status pending until admin approves)
$sql = "INSERT INTO bookings (user_id, event_date, location, guest_count, budget, status)
        VALUES (?, ?, ?, ?, ?, 'pending')";
$stmt2 = $conn->prepare($sql);
$stmt2->bind_param("issid", $user_id, $event_date, $location, $guest_count, $budget);
$stmt2->execute();

// Save booking id in session to continue with payment
$_SESSION['booking_id'] = $conn->insert_id;

// Send email notification to admin
$admin_email = "fatimaezzedine04@gmail.com";
$subject = "New Wedding Booking Pending Approval";
$message = "A new wedding booking has been made and is pending your approval.\n\n"
    . "Event Date: $event_date\n"
    . "Location: $location\n"
    . "Number of Guests: $guest_count\n"
    . "Budget: $budget\n\n"
    . "Please log in to the admin panel to approve or reject this booking.";
$headers = "From: noreply@yourdomain.com";
@mail($admin_email, $subject, $message, $headers);

// Redirect to payment page
header("Location: ../pages/payment.php");
exit();
?>