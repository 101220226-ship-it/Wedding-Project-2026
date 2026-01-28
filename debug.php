<?php
session_start();
include("config/db.php");

echo "<h1>Debug Test Page</h1>";

echo "<h2>1. Session Check</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>2. Database Connection</h2>";
if ($conn && !$conn->connect_error) {
    echo "<p style='color:green;'>✅ Database connected!</p>";
} else {
    echo "<p style='color:red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
}

echo "<h2>3. Tables Check</h2>";

// Check decoration_categories
$result = $conn->query("SELECT COUNT(*) as cnt FROM decoration_categories");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p>decoration_categories: {$row['cnt']} rows</p>";
} else {
    echo "<p style='color:red;'>❌ decoration_categories table error: " . $conn->error . "</p>";
}

// Check decoration_items
$result = $conn->query("SELECT COUNT(*) as cnt FROM decoration_items");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p>decoration_items: {$row['cnt']} rows</p>";
} else {
    echo "<p style='color:red;'>❌ decoration_items table error: " . $conn->error . "</p>";
}

// Check booking_decorations structure
$result = $conn->query("DESCRIBE booking_decorations");
if ($result) {
    echo "<p>booking_decorations columns:</p><ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>{$row['Field']} - {$row['Type']}</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:red;'>❌ booking_decorations table error: " . $conn->error . "</p>";
}

// Check bookings
$result = $conn->query("SELECT COUNT(*) as cnt FROM bookings");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p>bookings: {$row['cnt']} rows</p>";
} else {
    echo "<p style='color:red;'>❌ bookings table error: " . $conn->error . "</p>";
}

echo "<h2>4. Test Form Submission</h2>";
echo "<p>Go to this URL to test the form manually:</p>";
echo "<a href='pages/category_items.php?category_id=2'>Open Tables Category</a>";

echo "<h2>5. Manual Test Insert</h2>";
if (isset($_SESSION['booking_id'])) {
    $booking_id = (int)$_SESSION['booking_id'];
    echo "<p>Booking ID from session: $booking_id</p>";
    
    // Try a test insert
    $test_item_id = 6; // First table item
    $test_qty = 5;
    $test_price = 45.00;
    $test_total = $test_price * $test_qty;
    
    echo "<p>Attempting to insert: booking_id=$booking_id, item_id=$test_item_id, qty=$test_qty, price=$test_price, total=$test_total</p>";
    
    $ins = $conn->prepare("INSERT INTO booking_decorations (booking_id, item_id, quantity, unit_price, line_total) VALUES (?, ?, ?, ?, ?)");
    if (!$ins) {
        echo "<p style='color:red;'>❌ Prepare failed: " . $conn->error . "</p>";
    } else {
        $ins->bind_param("iiidd", $booking_id, $test_item_id, $test_qty, $test_price, $test_total);
        if ($ins->execute()) {
            echo "<p style='color:green;'>✅ Test insert successful! ID: " . $conn->insert_id . "</p>";
        } else {
            echo "<p style='color:red;'>❌ Insert failed: " . $ins->error . "</p>";
        }
    }
    
    // Check what's in booking_decorations now
    $check = $conn->query("SELECT * FROM booking_decorations WHERE booking_id = $booking_id");
    echo "<p>Current booking_decorations for booking $booking_id:</p>";
    echo "<pre>";
    while ($row = $check->fetch_assoc()) {
        print_r($row);
    }
    echo "</pre>";
} else {
    echo "<p style='color:orange;'>⚠️ No booking_id in session. Please login and create a booking first.</p>";
    echo "<p><a href='pages/login.php'>Go to Login</a></p>";
}
?>
