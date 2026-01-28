<?php
session_start();
include("../config/db.php");
cancelExpiredBookings($conn);

header('Content-Type: application/json');

// Return bookings count per date (excluding cancelled)
$q = $conn->query("
  SELECT event_date, COUNT(*) AS total
  FROM bookings
  WHERE status <> 'cancelled'
  GROUP BY event_date
");

$out = [];
while ($row = $q->fetch_assoc()) {
  $out[$row['event_date']] = (int)$row['total'];
}

echo json_encode($out);