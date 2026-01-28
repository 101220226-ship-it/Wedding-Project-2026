<?php
session_start();
include("../config/db.php");

// Accept booking_id from URL first, then fallback to session
if (isset($_GET['booking_id'])) {
    $booking_id = (int)$_GET['booking_id'];
    $_SESSION['booking_id'] = $booking_id; // store it again
} elseif (isset($_SESSION['booking_id'])) {
    $booking_id = (int)$_SESSION['booking_id'];
} else {
    header("Location: booking_form.php");
    exit();
}

// Get booking info
$stmt = $conn->prepare("SELECT status, expires_at FROM bookings WHERE booking_id=? LIMIT 1");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

$status = $booking['status'] ?? 'pending_payment';
$expires_at = $booking['expires_at'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Booking Submitted</title>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
body{
    font-family:'Poppins', sans-serif;
    background:#f4f6fb;
    margin:0;
    padding:40px 15px;
}
.card{
    max-width:750px;
    margin:0 auto;
    background:#fff;
    border-radius:16px;
    padding:32px;
    box-shadow:0 12px 35px rgba(0,0,0,0.08);
    text-align:center;
}
h1{
    margin:0;
    color:#5b2c83;
    font-family:'Playfair Display', serif;
    font-size:32px;
}
.subtitle{
    margin-top:10px;
    color:#444;
    font-size:15px;
    line-height:1.7;
}
.infoBox{
    margin-top:24px;
    background:#fbfbff;
    border:1px solid #ececff;
    padding:18px;
    border-radius:14px;
    text-align:left;
}
.row{
    display:flex;
    justify-content:space-between;
    padding:12px 0;
    border-bottom:1px solid #eee;
}
.row:last-child{ border-bottom:none; }
.label{
    color:#7a7a7a;
    font-weight:500;
}
.value{
    font-weight:600;
    color:#222;
}
.note{
    margin-top:18px;
    background:#fff7e6;
    border:1px solid #ffd38a;
    padding:16px;
    border-radius:12px;
    line-height:1.6;
    color:#4a3b18;
}
.btns{
    margin-top:22px;
    display:flex;
    justify-content:center;
}
a.btn{
    text-decoration:none;
    padding:12px 18px;
    border-radius:12px;
    font-weight:600;
    background:#5b2c83;
    color:#fff;
}
a.btn:hover{ background:#6f36a0; }
</style>
</head>

<body>

<div class="card">
    <div style="font-size:46px;margin-bottom:6px;">✨</div>

    <h1>Booking Submitted</h1>

    <p class="subtitle">
        To secure your date, please complete the <b>$50 deposit payment</b> within the next <b>24 hours</b>.
        <br><br>
        Your decoration selection has been successfully recorded, and your booking is currently pending deposit confirmation.
    </p>

    <div class="infoBox">
        <div class="row">
            <div class="label">Reference</div>
            <div class="value">#<?= $booking_id ?></div>
        </div>

        <div class="row">
            <div class="label">Current Status</div>
            <div class="value"><?= htmlspecialchars($status) ?></div>
        </div>

        <div class="row">
            <div class="label">Deposit Deadline</div>
            <div class="value">
                <?= $expires_at ? htmlspecialchars($expires_at) : "Within 24 hours" ?>
            </div>
        </div>
    </div>

    <div class="note">
        <b>Important:</b> If the deposit is not completed within 24 hours, the booking will be automatically cancelled.
    </div>

</div>

</body>
</html>