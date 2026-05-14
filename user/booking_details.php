<?php 
// 1. SESSION INITIALIZATION
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// 2. DEFINE PATHS & INCLUDES
require_once('../includes/config.php');

// 3. AUTHENTICATION & VALIDATION
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Please log in to view booking details.";
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$booking_id = intval($_GET['id'] ?? 0);

if ($booking_id <= 0) {
    $_SESSION['error_message'] = "Invalid booking request.";
    header("Location: my_bookings.php");
    exit;
}

// 4. FETCH DATA
$stmt = $conn->prepare("
    SELECT 
        b.booking_id, b.invoice_no, b.check_in, b.check_out, 
        b.total_price, b.status, b.created_at, b.rooms_booked,
        u.full_name, u.email, u.phone,
        r.room_type, r.room_no, r.price_per_night, r.image as room_image, r.capacity as room_capacity,
        t.table_no, t.capacity as table_capacity
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    LEFT JOIN rooms r ON b.room_id = r.room_id
    LEFT JOIN tables_list t ON b.table_id = t.table_id
    WHERE b.booking_id = ? AND b.user_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Booking not found or access denied.";
    header("Location: my_bookings.php");
    exit;
}

$booking = $result->fetch_assoc();
$stmt->close();

$isRoom = !empty($booking['room_type']);

// Calculate stay duration
$date1 = new DateTime($booking['check_in']);
$date2 = new DateTime($booking['check_out']);
$nights = $date1->diff($date2)->days;
$nights = ($nights <= 0 && $isRoom) ? 1 : $nights; 

$statusClass = strtolower($booking['status']);

include('../includes/header.php'); 
?>

<style>
    /* --- BOOKING DETAILS STYLES --- */
    .details-page-bg {
        padding: 60px 0;
        background-color: #f8f9fa;
        min-height: 90vh;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .details-container {
        max-width: 900px;
        margin: 0 auto;
    }

    /* Header Section */
    .details-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .details-title {
        font-family: 'Playfair Display', serif;
        font-size: 2.5rem;
        color: var(--color-text-dark);
        margin: 0;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #666;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s;
    }

    .btn-back:hover {
        color: var(--color-brand);
    }

    /* Main Card */
    .details-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.05);
        overflow: hidden;
        margin-bottom: 40px;
    }

    /* Card Top Banner */
    .card-banner {
        padding: 30px 40px;
        color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
    }

    .card-banner::before {
        content: '';
        position: absolute;
        top:0; left:0; right:0; bottom:0;
        background: linear-gradient(135deg, var(--color-brand-dark) 0%, var(--color-brand) 100%);
        z-index: 1;
    }

    .banner-content {
        position: relative;
        z-index: 2;
    }

    .banner-content h2 {
        margin: 0;
        font-size: 1.8rem;
        font-family: 'Playfair Display', serif;
    }

    .banner-content p {
        margin: 5px 0 0 0;
        opacity: 0.9;
        font-size: 0.95rem;
    }

    .status-badge {
        position: relative;
        z-index: 2;
        background: rgba(255,255,255,0.2);
        padding: 8px 20px;
        border-radius: 50px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255,255,255,0.3);
    }

    .status-confirmed { background: #27ae60; border-color: #2ecc71; }
    .status-completed { background: #2980b9; border-color: #3498db; }
    .status-cancelled { background: #c0392b; border-color: #e74c3c; }

    /* Card Body */
    .card-body {
        padding: 40px;
    }

    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        margin-bottom: 40px;
    }

    .info-group {
        background: #fdfdfd;
        border: 1px solid #eee;
        padding: 20px;
        border-radius: 12px;
    }

    .info-group h4 {
        color: #888;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 0;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .info-item {
        margin-bottom: 15px;
    }

    .info-item:last-child {
        margin-bottom: 0;
    }

    .info-label {
        display: block;
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 4px;
    }

    .info-val {
        display: block;
        font-size: 1.1rem;
        color: #333;
        font-weight: 600;
    }

    /* Summary Section */
    .summary-section {
        border-top: 2px dashed #eee;
        padding-top: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .total-price-box {
        text-align: right;
    }

    .total-label {
        display: block;
        color: #888;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .total-val {
        display: block;
        font-size: 2.2rem;
        color: var(--color-brand);
        font-weight: 700;
    }

    /* Image Preview */
    .image-preview {
        width: 100%;
        height: 200px;
        border-radius: 12px;
        object-fit: cover;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    /* Actions */
    .details-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
    }

    .btn-action-custom {
        padding: 12px 25px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-invoice {
        background: #f1f3f5;
        color: #333;
        border: 1px solid #ddd;
    }

    .btn-invoice:hover {
        background: #e9ecef;
    }

    .btn-cancel {
        background: #ffe5e5;
        color: #d63031;
        border: 1px solid #ffcccc;
    }

    .btn-cancel:hover {
        background: #ffcccc;
    }

    @media (max-width: 768px) {
        .card-banner {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        .summary-section {
            flex-direction: column;
            align-items: flex-start;
            gap: 20px;
        }
        .total-price-box {
            text-align: left;
        }
        .details-actions {
            width: 100%;
            flex-direction: column;
        }
        .btn-action-custom {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="details-page-bg">
    <div class="container details-container">
        
        <div class="details-header fade-in-up">
            <h1 class="details-title">Booking Details</h1>
            <a href="my_bookings.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Hub</a>
        </div>

        <div class="details-card fade-in-up" style="animation-delay: 0.1s;">
            <div class="card-banner">
                <div class="banner-content">
                    <h2><?= $isRoom ? htmlspecialchars($booking['room_type']) . ' Room' : 'Table Reservation' ?></h2>
                    <p>Booking ID: #B-<?= $booking['booking_id'] ?> | Booked on: <?= date('M d, Y', strtotime($booking['created_at'])) ?></p>
                </div>
                <div class="status-badge status-<?= $statusClass ?>">
                    <?= htmlspecialchars($booking['status']) ?>
                </div>
            </div>

            <div class="card-body">
                
                <?php if ($isRoom && !empty($booking['room_image'])): ?>
                    <img src="../assets/images/rooms/<?= htmlspecialchars($booking['room_image']) ?>" alt="<?= htmlspecialchars($booking['room_type']) ?>" class="image-preview">
                <?php endif; ?>

                <div class="info-grid">
                    <!-- Schedule Info -->
                    <div class="info-group">
                        <h4><i class="far fa-calendar-alt"></i> Schedule Details</h4>
                        
                        <div class="info-item">
                            <span class="info-label"><?= $isRoom ? 'Check In' : 'Date' ?></span>
                            <span class="info-val"><?= date('l, M d, Y', strtotime($booking['check_in'])) ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label"><?= $isRoom ? 'Check Out' : 'Time' ?></span>
                            <span class="info-val">
                                <?php 
                                    if($isRoom) echo date('l, M d, Y', strtotime($booking['check_out']));
                                    else echo "Evening Session"; 
                                ?>
                            </span>
                        </div>

                        <?php if ($isRoom): ?>
                        <div class="info-item">
                            <span class="info-label">Duration</span>
                            <span class="info-val"><?= $nights ?> Night(s)</span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Accommodation/Dining Info -->
                    <div class="info-group">
                        <h4><i class="fas <?= $isRoom ? 'fa-bed' : 'fa-utensils' ?>"></i> <?= $isRoom ? 'Room Details' : 'Table Details' ?></h4>
                        
                        <?php if ($isRoom): ?>
                            <div class="info-item">
                                <span class="info-label">Room Type</span>
                                <span class="info-val"><?= htmlspecialchars($booking['room_type']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Room Number</span>
                                <span class="info-val"><?= htmlspecialchars($booking['room_no'] ?? 'To be assigned') ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Rooms Booked</span>
                                <span class="info-val"><?= htmlspecialchars($booking['rooms_booked'] ?? 1) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Max Capacity (Per Room)</span>
                                <span class="info-val"><?= htmlspecialchars($booking['room_capacity']) ?> Guests</span>
                            </div>
                        <?php else: ?>
                            <div class="info-item">
                                <span class="info-label">Table Number</span>
                                <span class="info-val"><?= htmlspecialchars($booking['table_no'] ?? 'To be assigned') ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Table Capacity</span>
                                <span class="info-val"><?= htmlspecialchars($booking['table_capacity']) ?> Guests</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Guest Info -->
                    <div class="info-group">
                        <h4><i class="far fa-user"></i> Guest Information</h4>
                        <div class="info-item">
                            <span class="info-label">Primary Guest</span>
                            <span class="info-val"><?= htmlspecialchars($booking['full_name']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email Address</span>
                            <span class="info-val"><?= htmlspecialchars($booking['email']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Contact Number</span>
                            <span class="info-val"><?= htmlspecialchars($booking['phone']) ?></span>
                        </div>
                    </div>
                </div>

                <div class="summary-section">
                    <div class="guest-notes">
                        <!-- Space for future notes or special requests -->
                        <p style="color: #888; font-size: 0.9rem; margin:0;"><i class="fas fa-info-circle"></i> Need changes? Contact our front desk.</p>
                    </div>
                    <div class="total-price-box">
                        <span class="total-label">Total Amount</span>
                        <span class="total-val">₹<?= number_format($booking['total_price'], 2) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="details-actions fade-in-up" style="animation-delay: 0.2s;">
            <?php if ($statusClass == 'completed' || $statusClass == 'confirmed' || $statusClass == 'past'): ?>
                <a href="view_invoice.php?id=<?= $booking['booking_id'] ?>" class="btn-action-custom btn-invoice">
                    <i class="fas fa-file-invoice"></i> View Invoice
                </a>
            <?php endif; ?>

            <?php if ($statusClass == 'pending' || $statusClass == 'confirmed'): ?>
                <button onclick="confirmCancellation(<?= $booking['booking_id'] ?>)" class="btn-action-custom btn-cancel">
                    <i class="fas fa-times-circle"></i> Cancel Booking
                </button>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
function confirmCancellation(bookingId) {
    if (confirm('Are you sure you want to cancel this booking? This action cannot be undone.')) {
        window.location.href = `../bookings/cancel_booking.php?id=${bookingId}`;
    }
}
</script>

<?php include('../includes/footer.php'); ?>
