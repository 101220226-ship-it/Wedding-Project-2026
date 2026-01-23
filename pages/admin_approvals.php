<?php
session_start();
// For demo, no admin authentication. Add your own admin login check here.
include("../config/db.php");

// Get all pending bookings
$sql = "SELECT b.booking_id, b.event_date, b.location, b.guest_count, b.budget, u.name, u.phone FROM bookings b JOIN users u ON b.user_id = u.user_id WHERE b.status = 'pending' ORDER BY b.event_date ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Approvals</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        body { background: #f8f6fc; font-family: 'Poppins', sans-serif; padding: 40px; }
        h1 { color: #5b2c83; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th, td { padding: 12px; border: 1px solid #ccc; text-align: center; }
        th { background: #5b2c83; color: white; }
        form { display: inline; }
        button { padding: 7px 18px; border-radius: 7px; border: none; background: #5b2c83; color: white; font-weight: 600; cursor: pointer; }
        button:hover { background: #6f36a0; }
    </style>
</head>
<body>
    <h1>Pending Bookings for Approval</h1>
    <table>
        <tr>
            <th>Customer</th>
            <th>Phone</th>
            <th>Event Date</th>
            <th>Location</th>
            <th>Guests</th>
            <th>Budget</th>
            <th>Action</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['phone']) ?></td>
            <td><?= htmlspecialchars($row['event_date']) ?></td>
            <td><?= htmlspecialchars($row['location']) ?></td>
            <td><?= htmlspecialchars($row['guest_count']) ?></td>
            <td><?= htmlspecialchars($row['budget']) ?></td>
            <td>
                <form action="../actions/approve_booking.php" method="POST">
                    <input type="hidden" name="booking_id" value="<?= $row['booking_id'] ?>">
                    <input type="hidden" name="user_phone" value="<?= htmlspecialchars($row['phone']) ?>">
                    <button type="submit">Approve</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
