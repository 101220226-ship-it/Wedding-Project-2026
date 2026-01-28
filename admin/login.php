<?php
session_start();
include("../config/db.php");

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if ($email && $password) {
        // Check admin credentials
        $stmt = $conn->prepare("SELECT admin_id, name, email, password FROM admins WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            // Debug: show what we're comparing
            // echo "Stored hash: " . $admin['password'] . "<br>";
            // echo "Password entered: " . $password . "<br>";
            // echo "Verify result: " . (password_verify($password, $admin['password']) ? 'true' : 'false');
            // exit;
            
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_email'] = $admin['email'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "Admin not found.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - Event Zone</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}
.login-box {
    background: rgba(255,255,255,0.95);
    padding: 50px 40px;
    border-radius: 20px;
    box-shadow: 0 25px 50px rgba(0,0,0,0.3);
    width: 100%;
    max-width: 420px;
}
.login-box h1 {
    text-align: center;
    color: #1a1a2e;
    margin-bottom: 10px;
    font-size: 28px;
}
.login-box .subtitle {
    text-align: center;
    color: #666;
    margin-bottom: 30px;
    font-size: 14px;
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-weight: 500;
}
.form-group input {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 15px;
    transition: border-color 0.3s;
}
.form-group input:focus {
    outline: none;
    border-color: #5b2c83;
}
.error {
    background: #ffe6e6;
    color: #c00;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    font-size: 14px;
}
.btn {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #5b2c83, #7b4ce2);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}
.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(91,44,131,0.3);
}
.back-link {
    display: block;
    text-align: center;
    margin-top: 20px;
    color: #666;
    text-decoration: none;
    font-size: 14px;
}
.back-link:hover { color: #5b2c83; }
</style>
</head>
<body>
<div class="login-box">
    <h1>🔐 Admin Portal</h1>
    <p class="subtitle">Event Zone Management System</p>
    
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="admin@eventzone.com" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter password" required>
        </div>
        <button type="submit" class="btn">Login to Dashboard</button>
    </form>
    
    <a href="../index.php" class="back-link">← Back to Website</a>
</div>
</body>
</html>
