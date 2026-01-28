<?php
include("config/db.php");

echo "<h1>Admin Debug</h1>";

// Check if table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'admins'");
if ($tableCheck->num_rows == 0) {
    echo "<p style='color:red;'>❌ admins table does NOT exist!</p>";
    echo "<p>Creating table now...</p>";
    
    $conn->query("
        CREATE TABLE admins (
            admin_id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<p style='color:green;'>✅ Table created!</p>";
} else {
    echo "<p style='color:green;'>✅ admins table exists</p>";
}

// Show all admins
echo "<h2>Current Admins in Database:</h2>";
$result = $conn->query("SELECT admin_id, name, email, password FROM admins");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Password Hash</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['admin_id']}</td>";
        echo "<td>{$row['name']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td style='font-size:10px;word-break:break-all;max-width:300px;'>{$row['password']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:orange;'>No admins found in database!</p>";
}

// Test password
echo "<h2>Create New Admin:</h2>";
$testPassword = "admin123";
$newHash = password_hash($testPassword, PASSWORD_DEFAULT);

echo "<p>Password: <strong>admin123</strong></p>";
echo "<p>New Hash: <code style='font-size:10px;'>$newHash</code></p>";

// Delete and recreate
$conn->query("DELETE FROM admins WHERE email = 'admin@eventzone.com'");

$stmt = $conn->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
$name = "Admin";
$email = "admin@eventzone.com";
$stmt->bind_param("sss", $name, $email, $newHash);

if ($stmt->execute()) {
    echo "<p style='color:green;font-size:20px;'>✅ Admin created successfully!</p>";
    
    // Verify it works
    echo "<h2>Verification Test:</h2>";
    $verify = $conn->query("SELECT password FROM admins WHERE email = 'admin@eventzone.com'");
    $row = $verify->fetch_assoc();
    $storedHash = $row['password'];
    
    echo "<p>Stored Hash: <code style='font-size:10px;'>$storedHash</code></p>";
    echo "<p>Testing password_verify('admin123', stored_hash): ";
    if (password_verify('admin123', $storedHash)) {
        echo "<strong style='color:green;'>✅ PASS</strong></p>";
    } else {
        echo "<strong style='color:red;'>❌ FAIL</strong></p>";
    }
    
    echo "<hr>";
    echo "<h2>Login Now:</h2>";
    echo "<p><strong>Email:</strong> admin@eventzone.com</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    echo "<p><a href='admin/login.php' style='background:#5b2c83;color:white;padding:15px 30px;text-decoration:none;border-radius:8px;font-size:18px;display:inline-block;margin-top:10px;'>Go to Admin Login →</a></p>";
} else {
    echo "<p style='color:red;'>❌ Error: " . $stmt->error . "</p>";
}
?>
