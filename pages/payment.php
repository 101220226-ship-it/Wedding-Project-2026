<?php
session_start();
if(!isset($_SESSION['booking_id'])){
    header("Location: booking_form.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Deposit Payment - Event Zone</title>

  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../public/css/style.css">

  <style>
    body{
      background: url("../public/images/hero.jpeg") no-repeat center center/cover;
      min-height:100vh;
      display:flex;
      justify-content:center;
      align-items:center;
    }
    .pay-box{
      width:430px;
      padding:40px;
      border-radius:16px;
      background: rgba(255,255,255,0.15);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255,255,255,0.25);
      text-align:center;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    h1{
      font-family:'Playfair Display', serif;
      color:white;
      font-size:36px;
      margin-bottom:10px;
    }
    p{
      color:white;
      font-family:'Poppins', sans-serif;
      margin-bottom:20px;
    }
    .payment-options {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-bottom: 15px;
      align-items: flex-start;
    }
    .payment-option-label {
      display: flex;
      align-items: center;
      color: white;
      font-size: 1.1em;
      gap: 8px;
    }
    button{
      width:100%;
      padding:12px;
      border:none;
      border-radius:10px;
      background:#5b2c83;
      color:white;
      font-size:16px;
      font-weight:600;
      cursor:pointer;
    }
    button:hover{
      background:#6f36a0;
    }
  </style>
</head>

<body>
  <div class="pay-box">
    <h1>Deposit Payment</h1>
    <p>You must pay a <strong>$50 deposit</strong> to confirm your booking.</p>

    <form id="paymentForm" action="../actions/payment_action.php" method="POST">
      <div class="payment-options">
        <label class="payment-option-label"><input type="radio" name="payment_method" value="cash" checked>Pay with Cash</label>
        <label class="payment-option-label"><input type="radio" name="payment_method" value="whish">Pay with Whish Money</label>
      </div>

      <div id="whishInfo" style="display:none; color:white; margin-bottom:15px;">
        <strong>To pay by Whish Money:</strong><br>
        Send $50 to <b>81644198</b> using the Whish Money app.<br>
        After payment, click below to confirm.
      </div>

      <button type="submit">Submit</button>
    </form>
    <script>
      const paymentForm = document.getElementById('paymentForm');
      const whishInfo = document.getElementById('whishInfo');
      paymentForm.payment_method.forEach(radio => {
        radio.addEventListener('change', function() {
          if (this.value === 'whish') {
            whishInfo.style.display = 'block';
          } else {
            whishInfo.style.display = 'none';
          }
        });
      });
    </script>
  </div>
</body>
</html>