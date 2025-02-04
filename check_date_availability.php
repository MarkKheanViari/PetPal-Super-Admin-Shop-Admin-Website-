<?php
require 'db.php'; // Ensure database connection

header("Content-Type: application/json");

// Fetch already booked dates
$sql = "SELECT selected_date FROM service_requests WHERE status = 'confirmed'";
$result = $conn->query($sql);

$bookedDates = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $bookedDates[] = $row['selected_date'];
    }
}

echo json_encode(["booked_dates" => $bookedDates]);
?>
