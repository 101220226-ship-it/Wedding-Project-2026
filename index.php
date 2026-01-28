<?php
session_start();
include("config/db.php");
cancelExpiredBookings($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Zone - Wedding Planner</title>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>

<!-- NAVBAR -->
<header class="navbar">
    <div class="logo">Event <span>Zone</span></div>
    <nav>
        <a href="#about">About</a>
        <a href="#services">Services</a>
        <a href="#how">How it Works</a>
        <a href="pages/login.php" class="btn-nav">Login</a>
        <a href="admin/login.php" class="btn-nav admin-btn">Admin</a>
    </nav>
</header>

<!-- HERO SECTION -->
<section class="hero" id="home">
    <div class="hero-content">
        <h1>Event Zone</h1>
        <p>Your dream wedding starts here. Book your date and design your perfect celebration.</p>

        <div class="hero-buttons">
            <a href="pages/login.php" class="btn-primary">Book Your Wedding</a>
            <a href="#services" class="btn-secondary">Explore Services</a>
        </div>
    </div>
</section>

<!-- ABOUT -->
<section class="section" id="about">
    <h2>About Event Zone</h2>
    <p>
        Event Zone is a professional wedding planning service that helps couples reserve their special date online,
        manage bookings, and customize decorations to match their dream wedding. Our goal is to make wedding planning smooth,
        organized, and unforgettable.
    </p>
</section>

<!-- SERVICES -->
<section class="section light" id="services">
    <h2>Our Services</h2>

    <div class="cards">
        <div class="card">
            <h3>Online Booking</h3>
            <p>Reserve your wedding date easily and securely through our platform.</p>
        </div>

        <div class="card">
            <h3>Decoration Packages</h3>
            <p>Select your preferred theme, flowers, seating, lighting, and setup.</p>
        </div>

        <div class="card">
            <h3>Budget Planning</h3>
            <p>Plan your wedding within your budget and choose items that match your needs.</p>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="section" id="how">
    <h2>How It Works</h2>

    <div class="steps">
        <div class="step">
            <span>1</span>
            <p>Login using your name and phone number.</p>
        </div>

        <div class="step">
            <span>2</span>
            <p>Book your wedding date and select your preferred decorations.</p>
        </div>

        <div class="step">
            <span>3</span>
            <p>Confirm your booking by completing the deposit within 24 hours.</p>
        </div>
    </div>

    <div style="text-align:center; margin-top:30px;">
        <a href="pages/login.php" class="btn-primary">Start Now</a>
    </div>
</section>

<!-- FOOTER -->
<footer class="footer">
    <p>© <?php echo date("Y"); ?> Event Zone - All Rights Reserved</p>
</footer>

</body>
</html>
