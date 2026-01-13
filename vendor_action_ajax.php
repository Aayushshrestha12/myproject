<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['loginType'] !== 'admins') {
    echo json_encode(['success'=>false, 'message'=>'Unauthorized']);
    exit();
}

if (!isset($_POST['id'], $_POST['action'])) {
    echo json_encode(['success'=>false, 'message'=>'Invalid request']);
    exit();
}

$id = intval($_POST['id']);
$action = $_POST['action'];

// Fetch vendor application
$stmt = $conn->prepare("SELECT * FROM vendor_applications WHERE id=? AND status='pending'");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success'=>false, 'message'=>'Vendor not found or already processed']);
    exit();
}

$vendor = $result->fetch_assoc();
$stmt->close();

if ($action === 'approve') {
    // Insert into main vendors table
    $insert = $conn->prepare("
        INSERT INTO vendors (name, email, skills, location, phone, experience, is_approved, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 1, NOW())
    ");
    $insert->bind_param(
        "sssssss",
        $vendor['name'],
        $vendor['email'],
        $vendor['skills'],
        $vendor['location'],
        $vendor['phone'],
        $vendor['experience'],
        $vendor['password'] // if you store password in applications table
    );
    $insert->execute();
    $insert->close();

    // Update application status
    $update = $conn->prepare("UPDATE vendor_applications SET status='approved' WHERE id=?");
    $update->bind_param("i", $id);
    $update->execute();
    $update->close();

    echo json_encode(['success'=>true, 'message'=>'Vendor approved']);
    exit();
}

if ($action === 'reject') {
    $stmt = $conn->prepare("UPDATE vendor_applications SET status='rejected' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success'=>true, 'message'=>'Vendor rejected']);
    exit();
}

echo json_encode(['success'=>false, 'message'=>'Unknown action']);
exit();
