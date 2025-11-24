<?php
session_start();
include('includes/config.php'); // Include database connection

// --- 1. ACCESS CONTROL ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_type = $_SESSION['user_type'];
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// --- 2. DATA FETCHING LOGIC ---

// Fetch data specific to the user type
if ($user_type === 'customer') {
    $title = "Customer Dashboard";
    $heading = "Your Reservations";
    $bookings = [];

    // Fetch only the customer's bookings
    $stmt = $conn->prepare("
        SELECT b.*, r.room_name, r.price_per_night
        FROM bookings b
        JOIN rooms r ON b.room_id = r.room_id
        WHERE b.customer_id = ? 
        ORDER BY b.check_in_date DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    $stmt->close();

} elseif ($user_type === 'admin') {
    $title = "Admin Dashboard";
    $heading = "All System Overview";
    $stats = [
        'total_rooms' => 0,
        'available_rooms' => 0,
        'total_bookings' => 0
    ];
    $recent_bookings = [];

    // Fetch system statistics
    $stats_result = $conn->query("SELECT COUNT(*) as total_rooms, SUM(CASE WHEN room_status = 'Available' THEN 1 ELSE 0 END) as available_rooms FROM rooms");
    if ($stats_result->num_rows > 0) {
        $stats = array_merge($stats, $stats_result->fetch_assoc());
    }

    $bookings_result = $conn->query("SELECT COUNT(*) as total_bookings FROM bookings");
    if ($bookings_result->num_rows > 0) {
        $stats = array_merge($stats, $bookings_result->fetch_assoc());
    }

    // Fetch recent bookings for the admin view (e.g., last 5)
    $recent_q = "
        SELECT b.*, r.room_name, c.first_name, c.last_name
        FROM bookings b
        JOIN rooms r ON b.room_id = r.room_id
        JOIN customers c ON b.customer_id = c.customer_id
        ORDER BY b.booking_date DESC LIMIT 5
    ";
    $recent_result = $conn->query($recent_q);
    while($row = $recent_result->fetch_assoc()) {
        $recent_bookings[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
        .header { background: #333; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .header a { color: white; margin-left: 15px; text-decoration: none; }
        .container { max-width: 1200px; margin: 40px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-box { background: #f0f8ff; padding: 20px; border-radius: 6px; text-align: center; border: 1px solid #cceeff; }
        .stat-box h3 { margin: 0 0 5px 0; color: #007bff; }
        .booking-card, .admin-booking-card { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-left: 5px solid #007bff; border-radius: 4px; }
        .admin-booking-card { border-left-color: #28a745; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="header">
        <h1><?php echo $title; ?></h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>

    <div class="container">
        <h2>Hello, <?php echo htmlspecialchars($username); ?>!</h2>
        <?php
        if (isset($_SESSION['booking_success'])) {
            echo "<p style='color:green; border: 1px solid green; padding: 10px; background-color: #e6ffe6;'>";
            echo htmlspecialchars($_SESSION['booking_success']);
            echo "</p>";
            unset($_SESSION['booking_success']); // Clear the message after displaying it
        }
        ?>
        <h3><?php echo $heading; ?></h3>
        <hr style="margin-bottom: 30px;">

        <?php if ($user_type === 'customer'): ?>
            <?php if (count($bookings) > 0): ?>
                <?php foreach ($bookings as $booking): ?>
                    <div class="booking-card">
                        <p><strong>Booking ID:</strong> #<?php echo htmlspecialchars($booking['booking_id']); ?></p>
                        <p><strong>Room:</strong> <?php echo htmlspecialchars($booking['room_name']); ?> ($<?php echo number_format($booking['price_per_night'], 2); ?>/night)</p>
                        <p><strong>Dates:</strong> <?php echo htmlspecialchars($booking['check_in_date']); ?> to <?php echo htmlspecialchars($booking['check_out_date']); ?></p>
                        <p style="font-size: 1.2em; color: #dc3545;"><strong>Total Paid:</strong> $<?php echo number_format($booking['total_price'], 2); ?></p>
                        </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>You have no active or past bookings. Check out our <a href="index.php">available rooms</a>!</p>
            <?php endif; ?>

        <?php elseif ($user_type === 'admin'): ?>
            <div class="stat-grid">
                <div class="stat-box">
                    <h3>Total Rooms</h3>
                    <p style="font-size: 2em; color: #007bff;"><?php echo $stats['total_rooms']; ?></p>
                </div>
                <div class="stat-box">
                    <h3>Rooms Available</h3>
                    <p style="font-size: 2em; color: #28a745;"><?php echo $stats['available_rooms']; ?></p>
                </div>
                <div class="stat-box">
                    <h3>Total Bookings</h3>
                    <p style="font-size: 2em; color: #ffc107;"><?php echo $stats['total_bookings']; ?></p>
                </div>
            </div>

            <div style="margin-top: 40px;">
                <h3>📊 Recent Bookings (Last 5)</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Room</th>
                            <th>Check-in</th>
                            <th>Total Price</th>
                            <th>Booked On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recent_bookings) > 0): ?>
                            <?php foreach ($recent_bookings as $booking): ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($booking['booking_id']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['room_name']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['check_in_date']); ?></td>
                                    <td>$<?php echo number_format($booking['total_price'], 2); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($booking['booking_date'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align: center;">No recent bookings found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div style="margin-top: 30px;">
                    <h3>🔗 Quick Admin Links</h3>
                    <p>In a full system, these links would lead to dedicated management pages:</p>
                    <ul>
                        <li><a href="#">Manage Room Inventory</a></li>
                        <li><a href="#">View All Customer Accounts</a></li>
                        <li><a href="#">Generate Reports</a></li>
                    </ul>
                </div>
            </div>

        <?php endif; ?>
    </div>
</body>
</html>