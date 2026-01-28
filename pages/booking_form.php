<?php
session_start();
include("../config/db.php");
cancelExpiredBookings($conn);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/*
  We will fetch booking counts per date:
  - 2 bookings => FULL (red)
  - 1 booking  => LIMITED (yellow)
*/
$counts = [];
$q = $conn->query("
    SELECT event_date, COUNT(*) AS total
    FROM bookings
    WHERE status <> 'cancelled'
    GROUP BY event_date
");
while ($row = $q->fetch_assoc()) {
    $counts[$row['event_date']] = (int)$row['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Wedding Booking</title>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<!-- FLATPICKR (calendar library) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
body{
    background:url("../images/hero.jpeg") no-repeat center center/cover;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    font-family:'Poppins',sans-serif;
}

.form-box{
    width:460px;
    background:rgba(255,255,255,0.15);
    backdrop-filter:blur(12px);
    padding:40px;
    border-radius:18px;
    border:1px solid rgba(255,255,255,0.25);
    box-shadow:0 20px 40px rgba(0,0,0,0.35);
}

h1{
    font-family:'Playfair Display',serif;
    text-align:center;
    color:white;
    margin-bottom:22px;
}

input[type="text"],
input[type="number"],
input[type="text"].date-input{
    width:100%;
    padding:14px;
    margin:10px 0;
    border:none;
    border-radius:12px;
    font-size:15px;
}

.field-label{
    display:block;
    margin:14px 0 6px;
    color:rgba(255,255,255,0.9);
    font-weight:500;
}

.radio-group{
    display:flex;
    gap:28px;
    margin-bottom:12px;
}

.radio-option{
    display:flex;
    align-items:center;
    gap:8px;
    color:white;
    font-size:15px;
}

button{
    width:100%;
    padding:14px;
    margin-top:14px;
    border:none;
    border-radius:14px;
    background:#5b2c83;
    color:white;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
}
button:hover{ background:#6f36a0; }

/* ===== Flatpickr luxury purple style ===== */
.flatpickr-calendar{
    border-radius:14px !important;
    box-shadow:0 18px 40px rgba(0,0,0,0.35) !important;
    overflow:hidden;
}
.flatpickr-months{
    background:#5b2c83 !important;
    color:white !important;
}
.flatpickr-current-month,
.flatpickr-monthDropdown-months,
.flatpickr-weekday{
    color:white !important;
}
.flatpickr-day.selected{
    background:#5b2c83 !important;
    border-color:#5b2c83 !important;
}

/* FULL DATE = RED CIRCLE */
.flatpickr-day.full-date{
    position:relative;
    color:#111 !important;
}
.flatpickr-day.full-date::after{
    content:"";
    position:absolute;
    width:34px;
    height:34px;
    border-radius:50%;
    left:50%;
    top:50%;
    transform:translate(-50%,-50%);
    background:rgba(255,0,0,0.35);
    border:2px solid rgba(255,0,0,0.8);
    z-index:-1;
}

/* 1 BOOKING = YELLOW CIRCLE */
.flatpickr-day.half-date{
    position:relative;
    color:#111 !important;
}
.flatpickr-day.half-date::after{
    content:"";
    position:absolute;
    width:34px;
    height:34px;
    border-radius:50%;
    left:50%;
    top:50%;
    transform:translate(-50%,-50%);
    background:rgba(255,215,0,0.35);
    border:2px solid rgba(255,215,0,0.85);
    z-index:-1;
}
</style>
</head>

<body>

<div class="form-box">
    <h1>Wedding Booking</h1>

    <form action="../actions/booking_action.php" method="POST">
        <!-- Custom calendar input -->
        <input class="date-input" type="text" name="event_date" id="event_date" placeholder="Select Event Date" required>

        <label class="field-label">Venue Type</label>
        <div class="radio-group">
            <label class="radio-option">
                <input type="radio" name="venue_type" value="indoor" checked>
                Indoor
            </label>
            <label class="radio-option">
                <input type="radio" name="venue_type" value="outdoor">
                Outdoor
            </label>
        </div>

        <input type="text" name="location" placeholder="Wedding Location" required>
        <input type="number" name="guest_count" placeholder="Number of Guests" required>
        <input type="number" name="budget" placeholder="Budget ($)" required>

        <button type="submit">Continue</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
const bookingCounts = <?= json_encode($counts) ?>;

// Min date = today + 10 days (your rule)
const minDate = new Date();
minDate.setDate(minDate.getDate() + 10);

flatpickr("#event_date", {
  dateFormat: "Y-m-d",
  minDate: minDate,
  disableMobile: true,

  // we mark dates after calendar renders
  onDayCreate: function(dObj, dStr, fp, dayElem) {
    const date = dayElem.dateObj.toISOString().split("T")[0];
    const total = bookingCounts[date] || 0;

    if (total >= 2) {
      dayElem.classList.add("full-date");   // RED
    } else if (total === 1) {
      dayElem.classList.add("half-date");   // YELLOW
    }
  }
});
</script>

</body>
</html>