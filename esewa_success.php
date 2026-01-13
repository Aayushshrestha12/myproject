<?php
include 'db.php';

$amt = $_POST['amt'] ?? $_GET['amt'];
$pid = $_POST['pid'] ?? $_GET['pid'];
$scd = 'YOUR_MERCHANT_CODE';

// Verify payment with eSewa
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://uat.esewa.com.np/epay/transrec",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => "amt=$amt&pid=$pid&scd=$scd"
]);
$response = curl_exec($curl);
curl_close($curl);

if (strpos($response, "Success") !== false) {
    // Payment successful, update DB
    $stmt = $conn->prepare("UPDATE bookings b
                            JOIN payments p ON b.booking_id=p.booking_id
                            SET b.payment_status='paid', p.pay_date=NOW()
                            WHERE p.pid=?");
    $stmt->bind_param("s", $pid);
    $stmt->execute();
    $stmt->close();

    echo "<h2>Payment Successful ✅</h2>";
    echo "<a href='user_dashboard.php'>Go to Dashboard</a>";
} else {
    echo "<h2>Payment Verification Failed ❌</h2>";
    echo "<a href='user_dashboard.php'>Go to Dashboard</a>";
}
