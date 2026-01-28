<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['booking_id'])) {
    header("Location: ../pages/booking_form.php");
    exit();
}

$booking_id  = (int)$_SESSION['booking_id'];
$category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
$item_id     = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
$qty         = isset($_POST['qty']) ? max(1, (int)$_POST['qty']) : 1;

if ($category_id <= 0 || $item_id <= 0) {
    header("Location: ../pages/decoration_by_category.php");
    exit();
}

// Get item price
$p = $conn->prepare("SELECT price FROM decoration_items WHERE item_id=? AND category_id=? LIMIT 1");
$p->bind_param("ii", $item_id, $category_id);
$p->execute();
$r = $p->get_result();

if ($r->num_rows === 0) {
    header("Location: ../pages/decoration_by_category.php");
    exit();
}

$row = $r->fetch_assoc();
$unit_price = (float)$row['price'];
$line_total = $unit_price * $qty;

// Remove previous selection for this category
$del = $conn->prepare("
    DELETE bd FROM booking_decorations bd
    JOIN decoration_items di ON di.item_id = bd.item_id
    WHERE bd.booking_id=? AND di.category_id=?
");
$del->bind_param("ii", $booking_id, $category_id);
$del->execute();

// Insert new selection
$ins = $conn->prepare("
    INSERT INTO booking_decorations (booking_id, item_id, quantity, unit_price, line_total)
    VALUES (?, ?, ?, ?, ?)
");
$ins->bind_param("iiidd", $booking_id, $item_id, $qty, $unit_price, $line_total);
$ins->execute();

// Redirect back
header("Location: ../pages/decoration_by_category.php");
exit();
