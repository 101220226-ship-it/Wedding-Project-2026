<?php

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "wedding_planner";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
function cancelExpiredBookings($conn) {
    $sql = "UPDATE bookings
            SET status = 'cancelled'
            WHERE status = 'pending_payment'
              AND expires_at IS NOT NULL
              AND expires_at < NOW()";
    $conn->query($sql);
}
$conn->set_charset("utf8mb4");
?>
