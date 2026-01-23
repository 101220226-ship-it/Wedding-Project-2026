<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}
// Here you would check if the booking is approved, and only then show the decoration selection.
// For now, just a placeholder page for decoration selection.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Wedding Decoration</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        body { background: #f8f6fc; font-family: 'Poppins', sans-serif; text-align: center; padding: 80px; }
        h1 { color: #5b2c83; }
        .decoration-list { margin: 30px auto; max-width: 500px; text-align: left; }
        .decoration-list label { display: block; margin-bottom: 12px; font-size: 18px; }
        button { padding: 12px 25px; background: #5b2c83; color: white; border-radius: 10px; border: none; font-weight: 600; }
        button:hover { background: #6f36a0; }
    </style>
</head>
<body>
    <h1>Select Your Wedding Decoration</h1>
    <form action="../actions/decoration_action.php" method="POST">
        <div class="decoration-list">
            <label><input type="checkbox" name="decorations[]" value="flowers"> Flowers</label>
            <label><input type="checkbox" name="decorations[]" value="lights"> Lights</label>
            <label><input type="checkbox" name="decorations[]" value="table_centerpieces"> Table Centerpieces</label>
            <label><input type="checkbox" name="decorations[]" value="stage"> Stage Decoration</label>
            <label><input type="checkbox" name="decorations[]" value="entrance"> Entrance Arch</label>
        </div>
        <button type="submit">Submit Decoration Choices</button>
    </form>
</body>
</html>
