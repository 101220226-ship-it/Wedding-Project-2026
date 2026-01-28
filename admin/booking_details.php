<?php
session_start();
include("../config/db.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($booking_id <= 0) {
    header("Location: dashboard.php");
    exit();
}

// Get booking details
$stmt = $conn->prepare("
    SELECT b.*, u.name AS customer_name, u.phone, u.email
    FROM bookings b
    JOIN users u ON u.user_id = b.user_id
    WHERE b.booking_id = ?
");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    header("Location: dashboard.php");
    exit();
}

// Get decorations for this booking
$dec_stmt = $conn->prepare("
    SELECT di.item_name, dc.category_name, bd.quantity, bd.unit_price, bd.line_total, di.image_path
    FROM booking_decorations bd
    JOIN decoration_items di ON di.item_id = bd.item_id
    JOIN decoration_categories dc ON dc.category_id = di.category_id
    WHERE bd.booking_id = ?
    ORDER BY dc.category_name
");
$dec_stmt->bind_param("i", $booking_id);
$dec_stmt->execute();
$decorations = $dec_stmt->get_result();

// Calculate total decorations
$total_decorations = 0;
$dec_list = [];
while ($row = $decorations->fetch_assoc()) {
    $total_decorations += $row['line_total'];
    $dec_list[] = $row;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_status'])) {
    $new_status = $_POST['new_status'];
    $valid_statuses = ['pending', 'approved', 'rejected', 'pending_payment', 'cancelled'];
    
    if (in_array($new_status, $valid_statuses)) {
        $update = $conn->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
        $update->bind_param("si", $new_status, $booking_id);
        $update->execute();
        
        // Refresh
        header("Location: booking_details.php?id=$booking_id&updated=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Booking #<?= $booking_id ?> - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Poppins', sans-serif;
    background: #f4f6f9;
    min-height: 100vh;
    padding: 30px;
}
.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: #1a1a2e;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    margin-bottom: 20px;
}
.back-btn:hover { background: #2a2a4e; }

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}
.header h1 { color: #1a1a2e; }

.success-msg {
    background: #d4edda;
    color: #155724;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
}

.card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}
.card h2 {
    font-size: 18px;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
    color: #1a1a2e;
}
.card.full { grid-column: 1 / -1; }

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}
.info-row:last-child { border-bottom: none; }
.info-row .label { color: #888; font-size: 14px; }
.info-row .value { font-weight: 600; color: #333; }

.status {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}
.status.pending { background: #fff3cd; color: #856404; }
.status.approved { background: #d4edda; color: #155724; }
.status.pending_payment { background: #cce5ff; color: #004085; }
.status.cancelled, .status.rejected { background: #f8d7da; color: #721c24; }

.status-form {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}
.status-form select {
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
}
.status-form button {
    padding: 10px 20px;
    background: #5b2c83;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}
.status-form button:hover { background: #7b4ce2; }

/* Decorations Table */
.dec-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}
.dec-table th, .dec-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}
.dec-table th {
    background: #f8f9fa;
    font-size: 12px;
    text-transform: uppercase;
    color: #666;
}
.dec-table .total-row {
    font-weight: 700;
    background: #f8f9fa;
}
.dec-table .total-row td { border-bottom: none; }

.item-img {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    object-fit: cover;
}
</style>
</head>
<body>

<a href="dashboard.php" class="back-btn">← Back to Dashboard</a>

<div class="header">
    <h1>Booking #<?= $booking_id ?> Details</h1>
</div>

<?php if (isset($_GET['updated'])): ?>
    <div class="success-msg">✅ Booking status updated successfully!</div>
<?php endif; ?>

<div class="grid">
    <!-- Customer Info -->
    <div class="card">
        <h2>👤 Customer Information</h2>
        <div class="info-row">
            <span class="label">Name</span>
            <span class="value"><?= htmlspecialchars($booking['customer_name']) ?></span>
        </div>
        <div class="info-row">
            <span class="label">Phone</span>
            <span class="value"><?= htmlspecialchars($booking['phone']) ?></span>
        </div>
        <div class="info-row">
            <span class="label">Email</span>
            <span class="value"><?= htmlspecialchars($booking['email']) ?></span>
        </div>
    </div>
    
    <!-- Event Info -->
    <div class="card">
        <h2>📅 Event Details</h2>
        <div class="info-row">
            <span class="label">Event Date</span>
            <span class="value"><?= date('F j, Y', strtotime($booking['event_date'])) ?></span>
        </div>
        <div class="info-row">
            <span class="label">Venue Type</span>
            <span class="value"><?= ucfirst($booking['venue_type']) ?></span>
        </div>
        <div class="info-row">
            <span class="label">Location</span>
            <span class="value"><?= htmlspecialchars($booking['location']) ?></span>
        </div>
        <div class="info-row">
            <span class="label">Guest Count</span>
            <span class="value"><?= $booking['guest_count'] ?> guests</span>
        </div>
        <div class="info-row">
            <span class="label">Budget</span>
            <span class="value">$<?= number_format($booking['budget'], 2) ?></span>
        </div>
    </div>
    
    <!-- Status -->
    <div class="card">
        <h2>📋 Booking Status</h2>
        <div class="info-row">
            <span class="label">Current Status</span>
            <span class="status <?= $booking['status'] ?>"><?= ucfirst(str_replace('_', ' ', $booking['status'])) ?></span>
        </div>
        <div class="info-row">
            <span class="label">Created At</span>
            <span class="value"><?= date('M j, Y g:i A', strtotime($booking['created_at'])) ?></span>
        </div>
        
        <form method="POST" class="status-form">
            <select name="new_status">
                <option value="pending" <?= $booking['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= $booking['status'] == 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="pending_payment" <?= $booking['status'] == 'pending_payment' ? 'selected' : '' ?>>Pending Payment</option>
                <option value="cancelled" <?= $booking['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                <option value="rejected" <?= $booking['status'] == 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
            <button type="submit">Update Status</button>
        </form>
    </div>
    
    <!-- Financials -->
    <div class="card">
        <h2>💰 Financial Summary</h2>
        <div class="info-row">
            <span class="label">Customer Budget</span>
            <span class="value">$<?= number_format($booking['budget'], 2) ?></span>
        </div>
        <div class="info-row">
            <span class="label">Decorations Total</span>
            <span class="value">$<?= number_format($total_decorations, 2) ?></span>
        </div>
        <div class="info-row">
            <span class="label">Remaining Budget</span>
            <span class="value" style="color: <?= ($booking['budget'] - $total_decorations) >= 0 ? 'green' : 'red' ?>">
                $<?= number_format($booking['budget'] - $total_decorations, 2) ?>
            </span>
        </div>
    </div>
    
    <!-- Decorations -->
    <div class="card full">
        <h2>🎨 Selected Decorations</h2>
        <?php if (count($dec_list) > 0): ?>
        <table class="dec-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Category</th>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dec_list as $dec): ?>
                <tr>
                    <td>
                        <?php if ($dec['image_path']): ?>
                            <img src="../<?= htmlspecialchars($dec['image_path']) ?>" class="item-img" alt="">
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($dec['category_name']) ?></td>
                    <td><?= htmlspecialchars($dec['item_name']) ?></td>
                    <td><?= $dec['quantity'] ?></td>
                    <td>$<?= number_format($dec['unit_price'], 2) ?></td>
                    <td>$<?= number_format($dec['line_total'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="5" style="text-align:right;">Total:</td>
                    <td>$<?= number_format($total_decorations, 2) ?></td>
                </tr>
            </tbody>
        </table>
        <?php else: ?>
            <p style="color:#888; text-align:center; padding:30px;">No decorations selected yet.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
