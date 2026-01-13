<?php
include 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$name       = $_POST['Name'] ?? '';
$skills     = $_POST['skills'] ?? '';
$location   = $_POST['location'] ?? '';
$experience = $_POST['experience'] ?? '';
$email      = $_POST['email'] ?? '';
$phone      = $_POST['phone'] ?? '';
$status     = 'pending';

if (!$name || !$email || !$skills) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO vendor_applications 
    (name, skills, location, experience, email, phone, status)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sssssss",
    $name,
    $skills,
    $location,
    $experience,
    $email,
    $phone,
    $status
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}
