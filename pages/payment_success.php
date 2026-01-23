<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Successful</title>

  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

  <style>
    body{
      font-family:'Poppins', sans-serif;
      text-align:center;
      padding:80px;
    }
    h1{
      font-family:'Playfair Display', serif;
      font-size:50px;
      color:#5b2c83;
    }
    p{
      font-size:18px;
      margin:20px 0;
    }
    a{
      display:inline-block;
      padding:12px 25px;
      background:#5b2c83;
      color:white;
      border-radius:10px;
      text-decoration:none;
      font-weight:600;
    }
    a:hover{
      background:#6f36a0;
    }
  </style>
</head>

<body>
  <h1>Payment Successful ✅</h1>
  <?php
  include("../config/db.php");
  $show_decoration = false;
  if (isset($_SESSION['booking_id'])) {
      $bid = intval($_SESSION['booking_id']);
      $q = $conn->query("SELECT status FROM bookings WHERE booking_id = $bid");
      if ($q && $row = $q->fetch_assoc()) {
          if ($row['status'] === 'approved') {
              $show_decoration = true;
          }
      }
  }
  if ($show_decoration): ?>
    <p style="color:green;">Your booking has been approved! You can now select your wedding decoration.</p>
    <div class="button-group">
      <a href="decoration_selection.php">Continue to Decoration Selection</a>
      <a href="../index.php">Back to Home</a>
    </div>
  <?php endif; ?>
  
  <style>
    .button-group {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 22px;
      margin-top: 30px;
    }
  </style>
  <script>
    // Simulate admin approval for demo. Replace with real approval check in production.
    // If you have a way to check approval via PHP, use PHP to show/hide the div instead.
    // For now, show the button after 3 seconds for demonstration.
    setTimeout(function() {
      document.getElementById('after-approval').style.display = 'block';
    }, 3000);
  </script>
</body>
</html>
