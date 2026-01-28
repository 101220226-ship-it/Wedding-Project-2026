<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/booking_form.php");
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit;
}

// Collect
$event_date  = trim($_POST['event_date'] ?? '');
$venue_type  = trim($_POST['venue_type'] ?? 'indoor');
$location    = trim($_POST['location'] ?? '');
$guest_count = (int)($_POST['guest_count'] ?? 0);
$budget      = (float)($_POST['budget'] ?? 0);

// Save old values so page doesn't feel "cleared"
$_SESSION['booking_old'] = [
    'event_date'  => $event_date,
    'venue_type'  => $venue_type,
    'location'    => $location,
    'guest_count' => $guest_count,
    'budget'      => $budget,
];

$errors = [];

// Validation
if ($event_date === '') $errors['event_date'] = "Event date is required.";
if (!in_array($venue_type, ['indoor', 'outdoor'], true)) $errors['venue_type'] = "Invalid venue type.";
if ($location === '') $errors['location'] = "Location is required.";
if ($guest_count <= 0) $errors['guest_count'] = "Guest count must be at least 1.";
if ($budget <= 0) $errors['budget'] = "Budget must be greater than 0.";

if (!empty($errors)) {
    $_SESSION['booking_errors'] = $errors;
    header("Location: ../pages/booking_form.php");
    exit;
}

// DB connection check
if (!$conn || $conn->connect_errno) {
    $_SESSION['booking_errors'] = ['db' => 'Database connection failed. Restart MySQL in XAMPP.'];
    header("Location: ../pages/booking_form.php");
    exit;
}

try {
    // IMPORTANT: make sure these columns exist in your bookings table:
    // user_id, event_date, venue_type, location, guest_count, budget
    // (status is optional; remove it if not in your table)
    $sql = "INSERT INTO bookings (user_id, event_date, venue_type, location, guest_count, budget, status)
            VALUES (?, ?, ?, ?, ?, ?, 'pending')";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // i s s s i d
    $user_id = (int)$_SESSION['user_id'];
    $stmt->bind_param("isssid", $user_id, $event_date, $venue_type, $location, $guest_count, $budget);

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $booking_id = (int)$conn->insert_id;
    $_SESSION['booking_id'] = $booking_id;

    // Clear old cache after success
    unset($_SESSION['booking_old'], $_SESSION['booking_errors']);

    // Go to decoration categories
    header("Location: ../pages/decoration_by_category.php");
    exit;

} catch (Throwable $e) {
    $_SESSION['booking_errors'] = ['db' => $e->getMessage()];
    header("Location: ../pages/booking_form.php");
    exit;
}