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
      background: #f8f6fc;
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
  <p>Your booking is now pending admin approval.</p>
  <a href="../index.php">Back to Home</a>
</body>
</html>
