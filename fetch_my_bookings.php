<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'] ?? 0;
if(!$user_id) exit('No user');

$my_bookings = $conn->query("
   SELECT b.booking_id, b.booking_date, b.booking_time, b.status, 
       b.vendor_id, b.service_id,
       s.category, s.description, 
       v.name AS vendor_name, v.location,
       vs.price
   FROM bookings b
   JOIN services s ON b.service_id = s.service_id
   JOIN vendors v ON b.vendor_id = v.vendor_id
   JOIN vendor_services vs ON b.vendor_id = vs.vendor_id AND b.service_id = vs.service_id
   WHERE b.user_id = $user_id
   ORDER BY b.booking_date DESC, b.booking_time DESC
")->fetch_all(MYSQLI_ASSOC);

if(!empty($my_bookings)){
    foreach($my_bookings as $b){
        $status = $b['status'] ?? 'pending';
        echo '<div class="booking-card">';
        echo '<h3>'.htmlspecialchars($b['category']).'</h3>';
        echo '<p>'.htmlspecialchars($b['description']).'</p>';
        echo '<p>Vendor: '.htmlspecialchars($b['vendor_name']).' ('.htmlspecialchars($b['location']).') <br>';
        echo 'Date: '.htmlspecialchars($b['booking_date']).' | Time: '.htmlspecialchars($b['booking_time']).'<br>';
        echo '<strong>Price: ₹'.htmlspecialchars($b['price']).'</strong></p>';
        echo '<span class="status '.htmlspecialchars($status).'">'.ucfirst(htmlspecialchars($status)).'</span>';
        echo '</div>';
    }
} else {
    echo '<p>No bookings found.</p>';
}
