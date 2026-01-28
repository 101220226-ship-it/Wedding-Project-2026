<?php
session_start();
include("../config/db.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['admin_name'];

// Get all bookings with user and decoration details
$bookings_sql = "
SELECT 
    b.booking_id,
    b.event_date,
    b.venue_type,
    b.location,
    b.guest_count,
    b.budget,
    b.status,
    b.created_at,
    u.name AS customer_name,
    u.phone AS customer_phone,
    u.email AS customer_email,
    COALESCE(SUM(bd.line_total), 0) AS decoration_total
FROM bookings b
JOIN users u ON u.user_id = b.user_id
LEFT JOIN booking_decorations bd ON bd.booking_id = b.booking_id
GROUP BY b.booking_id
ORDER BY b.created_at DESC
";

$bookings = $conn->query($bookings_sql);

// Get statistics
$stats_sql = "SELECT 
    COUNT(*) as total_bookings,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'pending_payment' THEN 1 ELSE 0 END) as pending_payment
FROM bookings";
$stats = $conn->query($stats_sql)->fetch_assoc();

// Get total revenue
$revenue_sql = "SELECT COALESCE(SUM(line_total), 0) as total FROM booking_decorations";
$revenue = $conn->query($revenue_sql)->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Event Zone</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Poppins', sans-serif;
    background: #f4f6f9;
    min-height: 100vh;
}

/* Sidebar */
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 260px;
    height: 100vh;
    background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
    padding: 20px;
    color: white;
}
.sidebar .logo {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 10px;
    color: #fff;
}
.sidebar .logo span { color: #c7a4ff; }
.sidebar .admin-info {
    padding: 15px;
    background: rgba(255,255,255,0.1);
    border-radius: 10px;
    margin-bottom: 30px;
}
.sidebar .admin-info p { font-size: 12px; color: #aaa; }
.sidebar .admin-info h4 { font-size: 14px; margin-top: 5px; }

.nav-menu { list-style: none; }
.nav-menu li { margin-bottom: 5px; }
.nav-menu a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 15px;
    color: #ccc;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s;
}
.nav-menu a:hover, .nav-menu a.active {
    background: rgba(199,164,255,0.2);
    color: #fff;
}
.nav-menu .icon { font-size: 18px; }

.logout-btn {
    position: absolute;
    bottom: 20px;
    left: 20px;
    right: 20px;
    padding: 12px;
    background: rgba(255,100,100,0.2);
    color: #ff6b6b;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    text-align: center;
    display: block;
}
.logout-btn:hover { background: rgba(255,100,100,0.3); }

/* Main Content */
.main {
    margin-left: 260px;
    padding: 30px;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}
.header h1 { font-size: 28px; color: #1a1a2e; }
.header .date { color: #666; font-size: 14px; }

/* Stats Cards */
.stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}
.stat-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}
.stat-card .icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 15px;
}
.stat-card.purple .icon { background: #f0e6ff; }
.stat-card.blue .icon { background: #e6f3ff; }
.stat-card.orange .icon { background: #fff3e6; }
.stat-card.green .icon { background: #e6ffe6; }
.stat-card h3 { font-size: 28px; color: #1a1a2e; margin-bottom: 5px; }
.stat-card p { color: #666; font-size: 13px; }

/* Bookings Table */
.bookings-section {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}
.bookings-section h2 {
    font-size: 20px;
    margin-bottom: 20px;
    color: #1a1a2e;
}

.table-container { overflow-x: auto; }
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
}
th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
    font-size: 13px;
    text-transform: uppercase;
}
td { font-size: 14px; color: #444; }

.customer-info { display: flex; flex-direction: column; }
.customer-info .name { font-weight: 600; color: #1a1a2e; }
.customer-info .contact { font-size: 12px; color: #888; }

.status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    display: inline-block;
}
.status.pending { background: #fff3cd; color: #856404; }
.status.approved { background: #d4edda; color: #155724; }
.status.pending_payment { background: #cce5ff; color: #004085; }
.status.cancelled { background: #f8d7da; color: #721c24; }
.status.rejected { background: #f8d7da; color: #721c24; }

.btn-view {
    padding: 8px 16px;
    background: #5b2c83;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
    text-decoration: none;
}
.btn-view:hover { background: #7b4ce2; }

.no-data {
    text-align: center;
    padding: 50px;
    color: #888;
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="logo">Event <span>Zone</span></div>
    <div class="admin-info">
        <p>Welcome back,</p>
        <h4><?= htmlspecialchars($admin_name) ?></h4>
    </div>
    
    <ul class="nav-menu">
        <li><a href="dashboard.php" class="active"><span class="icon">📊</span> Dashboard</a></li>
    </ul>
    
    <a href="logout.php" class="logout-btn">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="main">
    <div class="header">
        <h1>Dashboard</h1>
        <div class="date"><?= date('l, F j, Y') ?></div>
    </div>
    
    <!-- Stats -->
    <div class="stats">
        <div class="stat-card purple">
            <div class="icon">📋</div>
            <h3><?= $stats['total_bookings'] ?? 0 ?></h3>
            <p>Total Bookings</p>
        </div>
        <div class="stat-card orange">
            <div class="icon">⏳</div>
            <h3><?= $stats['pending'] ?? 0 ?></h3>
            <p>Pending Approval</p>
        </div>
        <div class="stat-card blue">
            <div class="icon">💳</div>
            <h3><?= $stats['pending_payment'] ?? 0 ?></h3>
            <p>Awaiting Payment</p>
        </div>
        <div class="stat-card green">
            <div class="icon">💰</div>
            <h3>$<?= number_format($revenue, 2) ?></h3>
            <p>Total Revenue</p>
        </div>
    </div>
    
    <!-- Bookings Table -->
    <div class="bookings-section">
        <h2>Recent Wedding Bookings</h2>
        
        <div class="table-container">
            <?php if ($bookings && $bookings->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Event Date</th>
                        <th>Venue</th>
                        <th>Guests</th>
                        <th>Budget</th>
                        <th>Decorations</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($booking = $bookings->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $booking['booking_id'] ?></td>
                        <td>
                            <div class="customer-info">
                                <span class="name"><?= htmlspecialchars($booking['customer_name']) ?></span>
                                <span class="contact"><?= htmlspecialchars($booking['customer_phone']) ?></span>
                                <span class="contact"><?= htmlspecialchars($booking['customer_email']) ?></span>
                            </div>
                        </td>
                        <td><?= date('M j, Y', strtotime($booking['event_date'])) ?></td>
                        <td>
                            <?= ucfirst($booking['venue_type']) ?><br>
                            <small style="color:#888;"><?= htmlspecialchars($booking['location']) ?></small>
                        </td>
                        <td><?= $booking['guest_count'] ?></td>
                        <td>$<?= number_format($booking['budget'], 2) ?></td>
                        <td>$<?= number_format($booking['decoration_total'], 2) ?></td>
                        <td><span class="status <?= $booking['status'] ?>"><?= ucfirst(str_replace('_', ' ', $booking['status'])) ?></span></td>
                        <td><a href="booking_details.php?id=<?= $booking['booking_id'] ?>" class="btn-view">View</a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-data">
                <p>No bookings found yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
