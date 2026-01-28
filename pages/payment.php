<?php
session_start();
include("../config/db.php");
cancelExpiredBookings($conn);

if (!isset($_SESSION['booking_id'])) {
    header("Location: booking_form.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Deposit Payment</title>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
body{
    background:url("../public/images/hero.jpeg") no-repeat center center/cover;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    font-family:'Poppins', sans-serif;
}

.pay-box{
    width:430px;
    padding:40px;
    border-radius:18px;
    background:rgba(255,255,255,0.18);
    backdrop-filter: blur(12px);
    border:1px solid rgba(255,255,255,0.3);
    text-align:center;
    box-shadow:0 12px 35px rgba(0,0,0,0.35);
}

h1{
    font-family:'Playfair Display', serif;
    color:white;
    margin-bottom:10px;
}

p{
    color:white;
    font-size:14px;
    line-height:1.6;
}

.payment-options{
    margin:20px 0;
    text-align:left;
}

.option{
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:10px;
    color:white;
}

button{
    width:100%;
    padding:14px;
    border:none;
    border-radius:14px;
    background:#5b2c83;
    color:white;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
}

button:hover{
    background:#6f36a0;
}

#whishInfo{
    display:none;
    margin-top:12px;
    font-size:13px;
    color:white;
}
</style>
</head>

<body>

<div class="pay-box">
    <h1>Deposit Payment</h1>

    <p>You must pay a <strong>$50 deposit</strong> to confirm your booking.</p>
    <p>⚠️ Please complete the deposit within <strong>24 hours</strong>.  
    If unpaid, your booking will be cancelled automatically.</p>

    <form method="POST" action="../actions/payment_action.php">

        <div class="payment-options">
            <label class="option">
                <input type="radio" name="payment_method" value="cash" checked>
                Pay with Cash
            </label>

            <label class="option">
                <input type="radio" name="payment_method" value="whish">
                Pay with Whish Money
            </label>
        </div>

        <div id="whishInfo">
            Send <strong>$50</strong> to <b>81644198</b> using the Whish Money app.
            <br>After payment, click <b>Continue</b>.
        </div>
        <!-- ONLY ONE BUTTON -->
        <button type="submit">Continue</button>
    </form>
</div>

<script>
const radios = document.querySelectorAll('input[name="payment_method"]');
const info = document.getElementById('whishInfo');

radios.forEach(r => {
    r.addEventListener('change', () => {
        info.style.display = r.value === 'whish' ? 'block' : 'none';
    });
});
</script>

</body>
</html>