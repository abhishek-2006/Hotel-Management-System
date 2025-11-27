<?php
session_start();
include('../includes/config.php'); 
$PROJECT_ROOT = '/Hotel%20Management%20system'; 

// Set timezone and error handling
date_default_timezone_set('Asia/Kolkata');
error_reporting(E_ALL);

// --- 1. PRE-CHECK AND AUTHENTICATION ---

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['action'])) {
    $_SESSION['error_message'] = "Invalid request method.";
    header("Location: {$PROJECT_ROOT}/index.php");
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    $_SESSION['error_message'] = "You must be logged in to make a reservation.";
    header("Location: {$PROJECT_ROOT}/auth/login.php");
    exit;
}

$action = $_POST['action'];

// Helper for Auditing (activity_logs table)
function log_activity($conn, $user_id, $action_description) {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $action_description, $ip_address);
    $stmt->execute();
    $stmt->close();
}

// Helper to generate a unique Invoice Number (BKG-Timestamp-UserID)
function generate_invoice_no($user_id) {
    return "BKG-" . date("YmdHis") . "-" . $user_id;
}


// --- 2. CORE BOOKING LOGIC ---

// Start Transaction Control (BKG-G-03)
mysqli_begin_transaction($conn);

try {
    if ($action === 'book_room') {
        // --- ROOM BOOKING LOGIC (BKG-R-XX) ---
        
        $room_id = (int)($_POST['room_id'] ?? 0);
        $check_in = $_POST['check_in'] ?? null;
        $check_out = $_POST['check_out'] ?? null;
        $food_included = isset($_POST['food_included']) ? 1 : 0;
        
        // BKG-G-02: Basic Validation
        if (!$room_id || !$check_in || !$check_out || $check_in >= $check_out) {
            throw new Exception("Invalid dates or missing room selection.");
        }
        
        // --- BKG-R-01 & R-02: Inventory Check (Room Availability) ---
        $stmt = $conn->prepare("
            SELECT b.booking_id 
            FROM bookings b
            WHERE b.room_id = ? 
            AND (
                (b.check_in < ? AND b.check_out > ?) OR
                (b.check_in < ? AND b.check_out > ?) OR
                (b.check_in >= ? AND b.check_out <= ?)
            )
            AND b.status IN ('Confirmed', 'Pending')");
        $stmt->bind_param("issssss", $room_id, $check_out, $check_out, $check_in, $check_in, $check_in, $check_out);
        // 1. Get Room Details & Price
        $stmt = $conn->prepare("SELECT price_per_night, capacity FROM rooms WHERE room_id = ?");
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $room_data = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$room_data || $room_data['capacity'] < 1) {
            throw new Exception("Room details not found or invalid capacity.");
        }
        
        $price_per_night = $room_data['price_per_night'];
        $nights = (new DateTime($check_in))->diff(new DateTime($check_out))->days;

        if ($nights <= 0) {
            throw new Exception("Stay duration must be at least one night.");
        }
        
        // 2. BKG-R-02: Base Price Calculation
        $base_price = $price_per_night * $nights;
        $total_price = $base_price;

        // 3. BKG-R-03: Food Cost Calculation (Simplified: assumes a fixed meal cost per person per day)
        $food_cost = 0;
        if ($food_included) {
            $fixed_meal_package_cost = 1500.00; 
            $total_price += $fixed_meal_package_cost * $nights;
            $food_cost = $fixed_meal_package_cost * $nights;
        }

        // 4. BKG-R-04: Final Price & Invoice No. Generation
        $invoice_no = generate_invoice_no($user_id);
        
        // --- BKG-R-05: Record Booking ---
        $status = 'Confirmed'; // Assuming direct confirmation for simplicity
        $stmt = $conn->prepare("INSERT INTO bookings 
            (user_id, room_id, check_in, check_out, food_included, total_price, status, invoice_no) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissddss", 
            $user_id, $room_id, $check_in, $check_out, $food_included, $total_price, $status, $invoice_no);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert room booking record.");
        }
        $booking_id = $stmt->insert_id;
        $stmt->close();
        
        // 5. BKG-R-07/R-08: Success & Redirect
        mysqli_commit($conn); // Commit the transaction (BKG-G-03)
        log_activity($conn, $user_id, "Room Booking confirmed: #$booking_id for $nights nights.");
        
        $_SESSION['success_message'] = "Room Reservation (#$booking_id) confirmed for $check_in to $check_out. Total: ₹" . number_format($total_price, 2);
        
        // Redirect to booking history or confirmation page
        header("Location: {$PROJECT_ROOT}/user/booking_history.php");
        exit;


    } elseif ($action === 'book_table') {
        // --- TABLE RESERVATION LOGIC (BKG-T-XX) ---
        
        $table_id = (int)($_POST['table_id'] ?? 0);
        $res_date = $_POST['dining_date'] ?? null;
        $time_slot = $_POST['time_slot'] ?? null;
        $party_size = (int)($_POST['party_size'] ?? 1);
        
        // BKG-G-02: Basic Validation
        if (!$table_id || !$res_date || !$time_slot || $party_size < 1) {
            throw new Exception("Invalid dining details or missing table selection.");
        }

        // --- BKG-T-01 & T-02: Inventory Check (Time Slot) ---
        // Check if the specific table is booked for this exact time slot
        $stmt = $conn->prepare("
            SELECT b.booking_id 
            FROM bookings b
            WHERE b.table_id = ? 
            AND b.check_in = ? 
            AND b.check_in_time = ? 
            AND b.status IN ('Confirmed', 'Pending')
        ");
        $stmt->bind_param("iss", $table_id, $res_date, $time_slot);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->close();
            throw new Exception("This specific time slot is no longer available.");
        }
        $stmt->close();
        
        // Get hourly price for total_price calculation
        $stmt = $conn->prepare("SELECT price_per_hour FROM tables_list WHERE table_id = ?");
        $stmt->bind_param("i", $table_id);
        $stmt->execute();
        $table_data = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $price_per_hour = $table_data['price_per_hour'] ?? 0.00;

        // BKG-T-03: Record Booking (Setting room_id and check_out to NULL)
        $status = 'Confirmed'; 
        $invoice_no = generate_invoice_no($user_id); 
        
        $stmt = $conn->prepare("INSERT INTO bookings 
            (user_id, table_id, check_in, check_in_time, total_price, status, invoice_no) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("iisdsds", 
            $user_id, $table_id, $res_date, $time_slot, $price_per_hour, $status, $invoice_no);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert table reservation record.");
        }
        $booking_id = $stmt->insert_id;
        $stmt->close();
        
        // BKG-T-04: Success & Redirect
        mysqli_commit($conn); // Commit the transaction (BKG-G-03)
        log_activity($conn, $user_id, "Table Reservation confirmed: #$booking_id for $res_date @ $time_slot.");
        
        $_SESSION['success_message'] = "Table Reservation (#$booking_id) confirmed for $res_date at $time_slot.";
        
        // Redirect to booking history or confirmation page
        header("Location: {$PROJECT_ROOT}/user/booking_history.php");
        exit;

    } else {
        throw new Exception("Unrecognized booking action.");
    }

} catch (Exception $e) {
    // If any exception is caught, rollback the entire transaction (BKG-G-03)
    mysqli_rollback($conn);
    log_activity($conn, $user_id, "Booking Failed: " . $e->getMessage());

    $_SESSION['error_message'] = "Reservation failed: " . $e->getMessage();
    
    // Redirect back to the originating form (simple redirect for now)
    $redirect_page = ($action === 'book_table') ? 'user/book_table.php' : 'rooms.php';
    header("Location: {$PROJECT_ROOT}/" . $redirect_page);
    exit;
}
?>