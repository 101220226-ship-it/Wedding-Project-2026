<?php
// Approve booking and send email to customer
include("../config/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $booking_id = intval($_POST['booking_id']);
    // Get user email from booking
    $sql = "SELECT u.name, u.email FROM bookings b JOIN users u ON b.user_id = u.user_id WHERE b.booking_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Update booking status to approved
    $update = "UPDATE bookings SET status = 'approved' WHERE booking_id = ?";
    $stmt2 = $conn->prepare($update);
    $stmt2->bind_param("i", $booking_id);
    $stmt2->execute();

    // Send email to customer (for demo, use phone as email if needed)
    $customer_email = $user['email'];
    $subject = "Your Wedding Booking is Approved!";
    $message = "Dear " . $user['name'] . ",\n\nYour wedding booking has been approved! You can now continue to select your wedding decoration.\n\nThank you.";
    $headers = "From: noreply@yourdomain.com";
    @mail($customer_email, $subject, $message, $headers);

    // Redirect back to admin approvals
    header("Location: ../pages/admin_approvals.php?approved=1");
    exit();
} else {
    header("Location: ../pages/admin_approvals.php");
    exit();
}
