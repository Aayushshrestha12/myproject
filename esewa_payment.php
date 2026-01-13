<?php
session_start();
include 'db.php';

if (!isset($_POST['booking_id'])) {
    die("Invalid request");
}

$booking_id = intval($_POST['booking_id']);
$vendor_id  = intval($_POST['vendor_id']);
$service_id = intval($_POST['service_id']);
$amount     = floatval($_POST['amount']);
$user_id    = $_SESSION['user_id'];

// eSewa merchant info
$merchant_code = 'YOUR_MERCHANT_CODE';
$su = 'http://yourdomain.com/esewa_success.php';
$fu = 'http://yourdomain.com/esewa_failure.php';

// Redirect to eSewa for payment
$pid = 'BK'.$booking_id.'_'.time(); // unique payment ID

// Save pending payment in DB
$stmt = $conn->prepare("INSERT INTO payments (booking_id, user_id, amount, pay_method, vendor_id, service_id) VALUES (?, ?, ?, ?, ?, ?)");
$method = 'eSewa';
$stmt->bind_param("iidsii", $booking_id, $user_id, $amount, $method, $vendor_id, $service_id);
$stmt->execute();
$stmt->close();

// eSewa URL
$esewa_url = "https://uat.esewa.com.np/epay/main";
?>

<form id="esewaForm" action="<?= $esewa_url ?>" method="POST">
    <input value="<?= $amount ?>" name="amt">
    <input value="0" name="psc">
    <input value="0" name="pdc">
    <input value="<?= $amount ?>" name="tAmt">
    <input value="<?= $pid ?>" name="pid">
    <input value="<?= $merchant_code ?>" name="scd">
    <input value="<?= $su ?>" name="su">
    <input value="<?= $fu ?>" name="fu">
</form>

<script>
    document.getElementById('esewaForm').submit();
</script>
