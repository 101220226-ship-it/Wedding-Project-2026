<?php
session_start();
require_once "../config/db.php"; // change if needed

if (!isset($_SESSION['booking_id'])) {
    header("Location: ../pages/booking_form.php");
    exit();
}

$booking_id  = (int)$_SESSION['booking_id'];
$category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;

$selected_items = $_POST['selected_items'] ?? [];
$qtyMap = $_POST['qty'] ?? [];

if ($category_id <= 0) {
    header("Location: decoration_by_category.php");
    exit();
}

/**
 * Get category name
 */
$catStmt = $conn->prepare("SELECT category_name FROM decoration_categories WHERE category_id = ?");
$catStmt->bind_param("i", $category_id);
$catStmt->execute();
$catRow = $catStmt->get_result()->fetch_assoc();

if (!$catRow) {
    header("Location: decoration_by_category.php");
    exit();
}

$category_name = $catRow['category_name'];

/**
 * Quantity allowed ONLY for Tables and Chairs.
 * Flowers, Lighting, Setup, Welcome Sign => quantity always 1.
 */
$qtyAllowed = in_array($category_name, ["Tables", "Chairs"], true);

/**
 * Clear previous selections for THIS booking + THIS category only
 * (so user can edit category without deleting all decorations)
 */
$del = $conn->prepare("
    DELETE bd
    FROM booking_decorations bd
    JOIN decoration_items i ON i.item_id = bd.item_id
    WHERE bd.booking_id = ? AND i.category_id = ?
");
$del->bind_param("ii", $booking_id, $category_id);
$del->execute();

/**
 * If user selected nothing, just go back to categories
 * (totals will remain correct)
 */
if (empty($selected_items)) {
    header("Location: decoration_by_category.php");
    exit();
}

/**
 * Prepare queries for safe insert
 */
$getItem = $conn->prepare("
    SELECT item_id, price
    FROM decoration_items
    WHERE item_id = ? AND is_active = 1
");

$insert = $conn->prepare("
    INSERT INTO booking_decorations
    (booking_id, item_id, quantity, unit_price, line_total)
    VALUES (?, ?, ?, ?, ?)
");

/**
 * Save selections and compute totals
 */
foreach ($selected_items as $item_id) {
    $item_id = (int)$item_id;

    $getItem->bind_param("i", $item_id);
    $getItem->execute();
    $row = $getItem->get_result()->fetch_assoc();

    if (!$row) continue;

    $unit_price = (float)$row['price'];

    if ($qtyAllowed) {
        $qty = isset($qtyMap[$item_id]) ? max(1, (int)$qtyMap[$item_id]) : 1;
    } else {
        $qty = 1; // Flowers + Lighting + Setup + Welcome Sign
    }

    $line_total = $qty * $unit_price;

    $insert->bind_param("iiidd", $booking_id, $item_id, $qty, $unit_price, $line_total);
    $insert->execute();
}

/**
 * ✅ Redirect back to category page
 * Category page will automatically show updated totals from DB.
 */
header("Location: ../pages/decoration_by_category.php");
exit();