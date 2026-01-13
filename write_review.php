<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['booking_id'])) {
    $booking_id = intval($_GET['booking_id']);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $review = $_POST['review'];
    $rating = $_POST['rating'];
    $user_id = $_SESSION['user_id'];

    // insert review
    $stmt = $conn->prepare("INSERT INTO reviews (booking_id, user_id, review, rating, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iisi", $booking_id, $user_id, $review, $rating);
    if ($stmt->execute()) {
        header("Location: user_dashboard.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<form method="POST">
    <h3>Write Review</h3>
    <textarea name="review" required></textarea><br>
    <label>Rating (1-5):</label>
    <input type="number" name="rating" min="1" max="5" required><br>
    <button type="submit">Submit</button>
</form>
