<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Event Zone</title>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../public/css/style.css">

    <style>
        body{
            background: url("../public/images/hero.jpeg") no-repeat center center/cover;
        }
        .login-container{
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:20px;
        }
        .login-box{
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius:16px;
            padding:40px 35px;
            width:100%;
            max-width:420px;
            text-align:center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.25);
        }
        .login-box h1{
            font-family:'Playfair Display', serif;
            font-size:40px;
            color:white;
            margin-bottom:10px;
        }
        .login-box p{
            color:white;
            margin-bottom:25px;
            font-size:15px;
        }
        .login-box input{
            width:100%;
            padding:14px;
            border-radius:10px;
            border:none;
            margin-bottom:15px;
            font-size:15px;
            outline:none;
        }
        .login-box button{
            width:100%;
            padding:14px;
            border:none;
            border-radius:10px;
            background:#5b2c83;
            color:white;
            font-size:16px;
            font-weight:600;
            cursor:pointer;
            transition:0.3s;
        }
        .login-box button:hover{
            background:#6f36a0;
        }
        .back-home{
            display:block;
            margin-top:18px;
            color:white;
            text-decoration:none;
            font-size:14px;
        }
        .back-home:hover{
            text-decoration:underline;
        }
    </style>
</head>

<body>
<div class="login-container">
    <div class="login-box">
        <h1>Event Zone</h1>
        <p>Login to start booking your wedding</p>

        <form action="../actions/login_action.php" method="POST">
            <input type="text" name="name" placeholder="Enter your name" required>
            <input type="text" name="phone" placeholder="Enter your phone number" required>
            <button type="submit">Login</button>
        </form>

        <a class="back-home" href="../index.php">← Back to Home</a>
    </div>
</div>
</body>
</html>