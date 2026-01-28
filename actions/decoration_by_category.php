<?php
session_start();
include("../config/db.php");
cancelExpiredBookings($conn);

if (!isset($_SESSION['booking_id'])) {
    header("Location: ../pages/booking_form.php");
    exit();
}

$booking_id = (int)$_SESSION['booking_id'];

if (!isset($_POST['selected']) || !is_array($_POST['selected'])) {
    die("Invalid request.");
}

$selected = $_POST['selected'];          // selected[category_id] = item_id
$qtys     = $_POST['qty'] ?? [];         // qty[item_id] = number

// Remove previous selections for this booking (edit mode)
$del = $conn->prepare("DELETE FROM booking_decorations WHERE booking_id = ?");
$del->bind_param("i", $booking_id);
$del->execute();

// Prepare statements
$getPrice = $conn->prepare("SELECT price FROM decoration_items WHERE item_id = ? LIMIT 1");

$insert = $conn->prepare("
    INSERT INTO booking_decorations (booking_id, item_id, quantity, unit_price, line_total)
    VALUES (?, ?, ?, ?, ?)
");

$total_decor = 0.0;

// Loop through each category selection
foreach ($selected as $category_id => $item_id) {
    $item_id = (int)$item_id;
    if ($item_id <= 0) continue;

    $qty = 1;
    if (isset($qtys[$item_id])) {
        $qty = (int)$qtys[$item_id];
    }
    if ($qty <= 0) $qty = 1;

    // Fetch price from DB
    $getPrice->bind_param("i", $item_id);
    $getPrice->execute();
    $res = $getPrice->get_result();
    if ($res->num_rows === 0) continue;

    $row = $res->fetch_assoc();
    $unit_price = (float)$row['price'];
    $line_total = $unit_price * $qty;

    $insert->bind_param("iiidd", $booking_id, $item_id, $qty, $unit_price, $line_total);
    $insert->execute();

    $total_decor += $line_total;
}

// Update booking status + expiry
$upd = $conn->prepare("
    UPDATE bookings
    SET status='pending_payment',
        expires_at = DATE_ADD(NOW(), INTERVAL 24 HOUR)
    WHERE booking_id = ?
");
$upd->bind_param("i", $booking_id);
$upd->execute();

// Optional: store for display
$_SESSION['decor_total'] = $total_decor;

// Go to “Booking Submitted” page (your luxury page)
header("Location: ../pages/booking_submitted.php");
exit();