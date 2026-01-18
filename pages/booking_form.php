<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Wedding - Event Zone</title>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/style.css">

    <style>
        body{ background: url("../public/images/hero.jpeg") no-repeat center center/cover; }
        .box{
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
        }
        .form-box{
            width:450px;
            background:rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            padding:40px;
            border-radius:16px;
            text-align:center;
            border:1px solid rgba(255,255,255,0.25);
            box-shadow:0 10px 25px rgba(0,0,0,0.25);
        }
        h1{
            font-family:'Playfair Display';
            color:white;
        }
        input{
            width:100%;
            padding:12px;
            margin:10px 0;
            border:none;
            border-radius:10px;
        }
        button{
            width:100%;
            padding:12px;
            border:none;
            border-radius:10px;
            background:#5b2c83;
            color:white;
            font-weight:600;
        }
    </style>
</head>

<body>
<div class="box">
    <div class="form-box">
        <h1>Wedding Booking</h1>

        <form action="../actions/booking_action.php" method="POST">
            <input type="date" id="event_date" name="event_date" required>
            <input type="text" name="location" placeholder="Wedding Location" required>
            <input type="number" name="guest_count" placeholder="Number of Guests" required>
            <input type="number" name="budget" placeholder="Budget ($)" required>
            <button type="submit">Continue to Deposit Payment ($50)</button>
        </form>
        <script>
        const dateInput = document.getElementById('event_date');
        const today = new Date();
        today.setDate(today.getDate() + 1);
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        dateInput.min = `${yyyy}-${mm}-${dd}`;
        </script>
    </div>
</div>
</body>
</html>
