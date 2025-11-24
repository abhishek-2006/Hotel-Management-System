<?php 
include('includes/header.php'); 
include('includes/config.php');
error_reporting(E_ALL);

// Define pagination parameters
$initial_limit = 6;
$initial_offset = 0;

// Get total room count for checking when to stop loading
$count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM rooms");
$total_rooms = mysqli_fetch_assoc($count_query)['total'];
?>

<div class="container rooms-page-container">
    <h2 class="page-title text-center">Our Available Rooms</h2>

    <div class="rooms-grid" id="rooms-container">
        <?php
        $query = mysqli_query($conn, "SELECT * FROM rooms ORDER BY price_per_night ASC LIMIT $initial_limit OFFSET $initial_offset");
        
        if(mysqli_num_rows($query) > 0){
            while($row = mysqli_fetch_assoc($query)){
                $isAvailable = $row['status'];
                $availabilityText = $isAvailable ? 'Available' : 'Booked';
        ?>
        
        <div class="room-card card <?= !$isAvailable ? 'card-booked' : ''; ?>">
            <div class="room-image-wrapper">
                <img src="assets/images/rooms/<?= htmlspecialchars($row['image']); ?>" 
                    alt="<?= htmlspecialchars($row['room_type']); ?> Room Image" 
                    class="room-image">
                <span class="room-status room-status-<?= $isAvailable ? 'available' : 'booked'; ?>">
                    <?= $availabilityText; ?>
                </span>
            </div>
            
            <div class="room-details">
                <h3><?= htmlspecialchars($row['room_type']); ?> Room</h3>
                
                <ul class="room-specs">
                    <li><i class="fas fa-fan"></i> Type: <strong><?= htmlspecialchars($row['ac_type']); ?></strong></li>
                    <li><i class="fas fa-users"></i> Max Guests: <strong><?= htmlspecialchars($row['capacity'] ?? 'N/A'); ?></strong></li>
                </ul>

                <p class="room-price">
                    <span>Price:</span> 
                    <strong>₹<?= number_format($row['price_per_night']); ?></strong> / night
                </p>
                
                <a href="user/book_room.php?room_id=<?= $row['room_id']; ?>" 
                    class="btn btn-action btn-full-width"
                    
                    <?php 
                    // Safely output the 'disabled' attribute and inline style if NOT available
                    if (!$isAvailable) {
                        echo 'disabled style="pointer-events: none; opacity: 0.6;"'; 
                    } 
                    ?>>
                    <?= $isAvailable ? 'Book This Room' : 'View Details'; ?>
                </a>
            </div>
        </div>

        <?php 
            }
        } else {
            echo "<p class='text-center empty-state'>We are sorry, but no rooms match your criteria or are available at the moment.</p>";
        }
        ?>
    </div>
    
    <div class="text-center" id="loading-indicator" style="display:none; margin:20px 0;">
        <i class="fas fa-spinner fa-spin fa-2x" style="color:var(--color-brand);"></i>
        <p style="color:var(--color-text-light);">Loading more rooms...</p>
    </div>

    <input type="hidden" id="room-offset" value="<?= $initial_limit; ?>">
    <input type="hidden" id="room-total" value="<?= $total_rooms; ?>">
    <input type="hidden" id="room-limit" value="<?= $initial_limit; ?>">
</div>

<?php include('includes/footer.php'); ?>