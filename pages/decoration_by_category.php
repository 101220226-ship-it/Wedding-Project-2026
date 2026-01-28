<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['booking_id'])) {
    header("Location: booking_form.php");
    exit;
}

$booking_id = (int)$_SESSION['booking_id'];
$payment_done = !empty($_SESSION['payment_done']); // lock after payment

/**
 * Categories to show (MUST match DB exactly)
 */
$allowed = ["Setup", "Tables", "Chairs", "Flowers", "Lighting", "Welcome Sign"];
$placeholders = implode(',', array_fill(0, count($allowed), '?'));

/**
 * Category totals (price comes from decoration_items)
 */
$sql = "
SELECT 
    c.category_id,
    c.category_name,
    COALESCE(SUM(i.price), 0) AS category_total
FROM decoration_categories c
LEFT JOIN decoration_items i 
    ON i.category_id = c.category_id
LEFT JOIN booking_decorations bd 
    ON bd.item_id = i.item_id AND bd.booking_id = ?
WHERE c.category_name IN ($placeholders)
GROUP BY c.category_id, c.category_name
ORDER BY FIELD(c.category_name,'Setup','Tables','Chairs','Flowers','Lighting','Welcome Sign')
";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    str_repeat('s', count($allowed) + 1),
    $booking_id,
    ...$allowed
);
$stmt->execute();
$categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/**
 * Overall total
 */
$total_sql = "
SELECT COALESCE(SUM(i.price), 0) AS total
FROM booking_decorations bd
JOIN decoration_items i ON i.item_id = bd.item_id
WHERE bd.booking_id = ?
";
$total_stmt = $conn->prepare($total_sql);
$total_stmt->bind_param("i", $booking_id);
$total_stmt->execute();
$total_price = (float)$total_stmt->get_result()->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Decoration Categories</title>

<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background: #f4f3f7;
    padding: 40px;
}
.summary {
    background: #fff;
    padding: 20px 30px;
    border-radius: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}
.total {
    font-size: 22px;
    font-weight: bold;
    color: #5a2ea6;
}
.notice {
    background: #fff3cd;
    border: 1px solid #ffeeba;
    color: #856404;
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 20px;
}
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 25px;
}
.card {
    background: #fff;
    padding: 25px;
    border-radius: 18px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.06);
}
.price {
    font-weight: bold;
    color: #5a2ea6;
    margin: 15px 0;
}
.view-btn {
    background: #5a2ea6;
    color: #fff;
    padding: 10px 26px;
    border-radius: 10px;
    text-decoration: none;
    display: inline-block;
}
.view-btn.disabled {
    background: #bbb;
    pointer-events: none;
}
.continue-wrapper {
    display: flex;
    justify-content: flex-end;
    margin-top: 40px;
}
.continue-btn {
    background: linear-gradient(135deg,#5a2ea6,#7b4ce2);
    color: #fff;
    padding: 16px 42px;
    border-radius: 16px;
    text-decoration: none;
    display: inline-block;
}
.continue-btn.disabled {
    background: #c6c6c6;
    pointer-events: none;
}
</style>
</head>

<body>

<h1>Decoration Categories</h1>

<?php if ($payment_done): ?>
    <div class="notice">
        ✅ Payment already completed for this booking. Decorations are now locked.
    </div>
<?php endif; ?>

<div class="summary">
    <div>
        <strong>Total selected decorations</strong><br>
        Booking ID: <?= $booking_id ?>
    </div>
    <div class="total">$<?= number_format($total_price, 2) ?></div>
</div>

<div class="grid">
<?php foreach ($categories as $cat): ?>
    <div class="card">
        <h3><?= htmlspecialchars($cat['category_name']) ?></h3>
        <p>Browse items in this category</p>

        <div class="price">
            Category total: $<?= number_format((float)$cat['category_total'], 2) ?>
        </div>

        <?php if ($payment_done): ?>
            <a class="view-btn disabled" href="#">Locked</a>
        <?php else: ?>
            <a class="view-btn" href="category_items.php?category_id=<?= (int)$cat['category_id'] ?>">
                View
            </a>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
</div>

<div class="continue-wrapper">
    <?php if ($payment_done): ?>
        <a href="booking_submitted.php" class="continue-btn">Finish Booking</a>
    <?php else: ?>
        <a href="payment.php" class="continue-btn <?= ($total_price <= 0 ? 'disabled' : '') ?>">
            Submit
        </a>
    <?php endif; ?>
</div>

</body>
</html>