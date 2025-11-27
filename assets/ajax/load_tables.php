<?php
include('../../includes/config.php'); 

// Define pagination parameters
$limit = 4; // Matches the initial limit in tables.php
$offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;

// Query the database with LIMIT and OFFSET
$query = mysqli_query($conn, "SELECT * FROM tables ORDER BY capacity ASC LIMIT $limit OFFSET $offset");

$html = '';
if(mysqli_num_rows($query) > 0){
    while($row = mysqli_fetch_assoc($query)){
        // Correctly check status using the ENUM value from the tables schema
        $isAvailable = ($row['status'] === 'Available');
        $availabilityText = $row['status']; // Display the actual status string
        
        // Build the HTML card structure exactly as in tables.php
        $html .= ' 
        <div class="table-card card ' . (!$isAvailable ? 'card-reserved' : '') . '">
            <div class="table-image-wrapper">
                <img src="assets/images/tables/' . htmlspecialchars($row['image']) . '" 
                    alt="Table for ' . htmlspecialchars($row['capacity']) . ' Image" 
                    class="table-image">
                <span class="table-status table-status-' . ($isAvailable ? 'available' : 'reserved') . '">
                    ' . $availabilityText . '
                </span>
            </div>
            
            <div class="table-details">
                <h3>' . htmlspecialchars($row['table_type']) . '</h3> 
                <p class="table-location">
                    <span>Capacity:</span> 
                    <strong>' . htmlspecialchars($row['capacity']) . ' Guests</strong>
                </p>
                <p class="table-price">
                    <span>Hourly Price:</span> 
                    <strong>₹' . number_format($row['price_per_hour']) . '</strong>
                </p>
                
                <a href="user/reserve_table.php?table_id=' . $row['table_id'] . '" 
                    class="btn btn-action btn-full-width"
                    ' . (!$isAvailable ? 'disabled style="pointer-events: none; opacity: 0.6;"' : '') . '>
                    ' . ($isAvailable ? 'Reserve This Table' : 'View Details') . '
                </a>
            </div>
        </div>';
    }
}
echo $html;
exit;