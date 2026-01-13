<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['loginType'] !== 'vendors') {
    die("Unauthorized");
}

$vendor_id = (int)$_SESSION['user_id'];

$vendor_service_id = $_POST['vendor_service_id'] ?? null;
$service_id        = $_POST['service_id'] ?? null;
$price             = $_POST['price'] ?? null;
$available_from    = $_POST['available_from'] ?? null;
$available_to      = $_POST['available_to'] ?? null;

/* 🔍 HARD DEBUG (TEMPORARY)
echo "<pre>";
var_dump($_POST);
exit;
*/

// VALIDATION
if (!$service_id || !$price || !$available_from || !$available_to) {
    die("Missing fields");
}

// Convert datetime-local → MySQL DATETIME
$available_from = date('Y-m-d H:i:s', strtotime($available_from));
$available_to   = date('Y-m-d H:i:s', strtotime($available_to));

if ($vendor_service_id) {
    $stmt = $conn->prepare("
        UPDATE vendor_services
        SET service_id=?, price=?, available_from=?, available_to=?
        WHERE id=? AND vendor_id=?
    ");
    $stmt->bind_param(
        "idssii",
        $service_id,
        $price,
        $available_from,
        $available_to,
        $vendor_service_id,
        $vendor_id
    );
} else {
    $stmt = $conn->prepare("
        INSERT INTO vendor_services
        (vendor_id, service_id, price, available_from, available_to)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "iidss",
        $vendor_id,
        $service_id,
        $price,
        $available_from,
        $available_to
    );
}

$stmt->execute();
$stmt->close();
$conn->close();
echo json_encode([
    'success' => true,
    'message' => 'Service saved'
]);
exit;

