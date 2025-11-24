<?php
include('../../includes/config.php');

// Define pagination parameters
$limit = 6; // Number of rooms to load per scroll
$offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;

// Query the database with LIMIT and OFFSET
$query = mysqli_query($conn, "SELECT * FROM rooms ORDER BY price_per_night ASC LIMIT $limit OFFSET $offset");

$html = '';
if(mysqli_num_rows($query) > 0){
    while($row = mysqli_fetch_assoc($query)){
        $isAvailable = $row['status'];
        $availabilityText = $isAvailable ? 'Available' : 'Booked';
        
        // Build the HTML card structure exactly as in rooms.php
        $html .= '<div class="room-card card ' . (!$isAvailable ? 'card-booked' : '') . '">';
        $html .= '  <div class="room-image-wrapper">';
        $html .= '      <img src="assets/images/rooms/' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['room_type']) . ' Room Image" class="room-image">';
        $html .= '      <span class="room-status room-status-' . ($isAvailable ? 'available' : 'booked') . '">';
        $html .= '          ' . $availabilityText;
        $html .= '      </span>';
        $html .= '  </div>';
        $html .= '  <div class="room-details">';
        $html .= '      <h3>' . htmlspecialchars($row['room_type']) . ' Room</h3>';
        $html .= '      <ul class="room-specs">';
        $html .= '          <li><i class="fas fa-fan"></i> Type: <strong>' . htmlspecialchars($row['ac_type']) . '</strong></li>';
        $html .= '          <li><i class="fas fa-users"></i> Max Guests: <strong>' . htmlspecialchars($row['max_occupancy'] ?? 'N/A') . '</strong></li>';
        $html .= '      </ul>';
        $html .= '      <p class="room-price">';
        $html .= '          <span>Price:</span> ';
        $html .= '          <strong>₹' . number_format($row['price_per_night']) . '</strong> / night';
        $html .= '      </p>';
        $html .= '      <a href="user/book_room.php?room_id=' . $row['room_id'] . '" ';
        $html .= '         class="btn btn-action btn-full-width" ' . (!$isAvailable ? 'disabled' : '') . '>';
        $html .= '          ' . ($isAvailable ? 'Book This Room' : 'View Details');
        $html .= '      </a>';
        $html .= '  </div>';
        $html .= '</div>';
    }
}

// Output the generated HTML to the AJAX request
echo $html;

// Stop execution here
exit;