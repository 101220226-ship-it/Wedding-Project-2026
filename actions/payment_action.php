<?php
session_start();
include("../config/db.php");

// 1) Must have booking in session
if (!isset($_SESSION['booking_id'])) {
    header("Location: ../pages/booking_form.php");
    exit();
}

$booking_id = (int)$_SESSION['booking_id'];
$amount = 50.00;

// Payment method from form
$payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : 'cash';
if ($payment_method !== 'cash' && $payment_method !== 'whish') {
    $payment_method = 'cash';
}

// 2) Check booking exists + status + expiry
$check = $conn->prepare("SELECT status, expires_at FROM bookings WHERE booking_id=? LIMIT 1");
$check->bind_param("i", $booking_id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows === 0) {
    header("Location: ../pages/booking_form.php?err=notfound");
    exit();
}

$row = $res->fetch_assoc();

// Only allow payment if booking is pending_payment
if ($row['status'] !== 'pending_payment') {
    // If already confirmed, just show submitted page
    header("Location: ../pages/booking_submitted.php?booking_id=" . $booking_id);
    exit();
}

// Expired => cancel
if (!empty($row['expires_at']) && strtotime($row['expires_at']) < time()) {
    $c1 = $conn->prepare("UPDATE bookings SET status='cancelled' WHERE booking_id=?");
    $c1->bind_param("i", $booking_id);
    $c1->execute();

    $c2 = $conn->prepare("DELETE FROM booking_decorations WHERE booking_id=?");
    $c2->bind_param("i", $booking_id);
    $c2->execute();

    header("Location: ../pages/booking_form.php?err=expired");
    exit();
}

/*
    ✅ IMPORTANT (New workflow):
    - Do NOT confirm the booking here (because cash/whish may not be paid instantly).
    - We will just "submit payment request" and keep booking pending.
*/

// 3) Insert payment record as "pending" (adjust columns if yours are different)
$sql = "INSERT INTO payments (booking_id, amount, payment_method, status, payment_status)
        VALUES (?, ?, ?, 'pending', 'pending')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ids", $booking_id, $amount, $payment_method);
$stmt->execute();

// 4) Keep booking pending (do not set confirmed here)
// (Optional) update booking to be safe
$upd = $conn->prepare("UPDATE bookings SET status='pending_payment' WHERE booking_id=?");
$upd->bind_param("i", $booking_id);
$upd->execute();

// 5) Redirect to Booking Submitted (NOT booking form)
$_SESSION['booking_id'] = $booking_id; // keep session alive
header("Location: ../pages/booking_submitted.php?booking_id=" . $booking_id);
exit();
?>