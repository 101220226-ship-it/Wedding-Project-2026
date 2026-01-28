<?php
// This script creates/resets the admin account
include("config/db.php");

$password = "admin123";
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<h1>Admin Setup</h1>";

// Check if admins table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'admins'");
if ($tableCheck->num_rows == 0) {
    // Create table
    $conn->query("
        CREATE TABLE admins (
            admin_id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<p>✅ Created admins table</p>";
}

// Delete existing admin
$conn->query("DELETE FROM admins WHERE email = 'admin@eventzone.com'");

// Insert new admin
$stmt = $conn->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
$name = "Admin";
$email = "admin@eventzone.com";
$stmt->bind_param("sss", $name, $email, $hash);

if ($stmt->execute()) {
    echo "<p style='color:green; font-size:20px;'>✅ Admin account created successfully!</p>";
    echo "<hr>";
    echo "<h2>Login Credentials:</h2>";
    echo "<p><strong>Email:</strong> admin@eventzone.com</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    echo "<p><a href='admin/login.php' style='background:#5b2c83;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Go to Admin Login</a></p>";
} else {
    echo "<p style='color:red;'>❌ Error: " . $stmt->error . "</p>";
}
?>
