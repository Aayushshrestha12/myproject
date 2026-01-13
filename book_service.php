<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$vendor_id = intval($_POST['vendor_id']);
$service_id = intval($_POST['service_id']);
$date = $_POST['date'] ?? '';
$time = $_POST['time'] ?? '';
$bookingDateTime = "$date $time";

if (!$vendor_id || !$service_id || !$date || !$time) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

// Prevent past date/time
if (strtotime($bookingDateTime) < time()) {
    echo json_encode(['success' => false, 'message' => 'Cannot book past date/time']);
    exit();
}

// 1️⃣ Get vendor daily limit
$stmt = $conn->prepare("SELECT daily_limit FROM vendors WHERE vendor_id=?");
$stmt->bind_param("i", $vendor_id);
$stmt->execute();
$dailyLimit = $stmt->get_result()->fetch_assoc()['daily_limit'] ?? 0;
$stmt->close();

// 2️⃣ Count today's bookings
if ($dailyLimit > 0) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total 
        FROM bookings 
        WHERE vendor_id=? AND DATE(booking_date)=? 
          AND status IN ('pending','accepted')
    ");
    $stmt->bind_param("is", $vendor_id, $date);
    $stmt->execute();
    $current = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt->close();

    if ($current >= $dailyLimit) {
        // Optionally record rejected booking
        $rej = $conn->prepare("
            INSERT INTO bookings
            (user_id, vendor_id, service_id, booking_date, status, rejection_reason)
            VALUES (?, ?, ?, ?, 'rejected', 'Vendor booking limit full')
        ");
        $rej->bind_param("iiis", $user_id, $vendor_id, $service_id, $bookingDateTime);
        $rej->execute();
        $rej->close();

        echo json_encode(['success'=>false, 'message'=>'Vendor booking limit reached']);
        exit();
    }
}

// 3️⃣ Check exact time slot availability
$stmt = $conn->prepare("
    SELECT 1 FROM bookings 
    WHERE vendor_id=? AND booking_date=? 
      AND status IN ('pending','accepted')
");
$stmt->bind_param("is", $vendor_id, $bookingDateTime);
$stmt->execute();
$existing = $stmt->get_result();
if ($existing->num_rows > 0) {
    echo json_encode(['success'=>false, 'message'=>'Vendor not available at this time']);
    exit();
}
$stmt->close();

// 4️⃣ Insert booking
$insert = $conn->prepare("
    INSERT INTO bookings (user_id, vendor_id, service_id, booking_date, status)
    VALUES (?, ?, ?, ?, 'pending')
");
$insert->bind_param("iiis", $user_id, $vendor_id, $service_id, $bookingDateTime);
if ($insert->execute()) {
    $booking_id = $insert->insert_id; // get inserted ID for notifications
    $insert->close();
    echo json_encode(['success'=>true, 'booking_id'=>$booking_id]);
} else {
    echo json_encode(['success'=>false, 'message'=>$insert->error]);
    $insert->close();
}
?>
