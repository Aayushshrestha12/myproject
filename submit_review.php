<?php
session_start();
include 'db.php';
if(!isset($_SESSION['user_id'])) exit('Not logged in');

$data = json_decode(file_get_contents("php://input"), true);

// Check if review exists
$check = $conn->prepare("SELECT id FROM reviews WHERE user_id=? AND service_name=?");
$check->bind_param("is", $_SESSION['user_id'], $data['service']);
$check->execute();
$res = $check->get_result();

if($res->num_rows > 0){
    $row = $res->fetch_assoc();
    $stmt = $conn->prepare("UPDATE reviews SET rating=?, review_text=? WHERE id=?");
    $stmt->bind_param("isi", $data['rating'], $data['review'], $row['id']);
} else {
    $stmt = $conn->prepare("INSERT INTO reviews (user_id, service_name, rating, review_text) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isis", $_SESSION['user_id'], $data['service'], $data['rating'], $data['review']);
}

if($stmt->execute()){
    echo "Review submitted successfully!";
}else{
    echo "Failed to submit review.";
}
?>
