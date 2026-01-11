<?php 
// 1. SESSION INITIALIZATION (Must be first)
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// 2. DEFINE PATHS & INCLUDES
$PROJECT_ROOT = '/Hotel%20Management%20system';
require_once('../includes/config.php');

// 3. AUTHENTICATION & VALIDATION
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Please log in to view your invoice.";
    header("Location: {$PROJECT_ROOT}/auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$booking_id = intval($_GET['id'] ?? 0);

if ($booking_id <= 0) {
    $_SESSION['error_message'] = "Invalid invoice request.";
    header("Location: {$PROJECT_ROOT}/user/my_bookings.php");
    exit;
}

// 4. FETCH DATA (Joins for User details, Rooms, and Tables)
$stmt = $conn->prepare("
    SELECT 
        b.booking_id, b.invoice_no, b.check_in, b.check_out, 
        b.total_price, b.status, b.created_at,
        u.full_name, u.email, u.phone,
        r.room_type, r.room_no, r.price_per_night,
        t.table_no, t.capacity
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
    $_SESSION['error_message'] = "Invoice not found or access denied.";
    header("Location: {$PROJECT_ROOT}/user/my_bookings.php");
    exit;
}

$inv = $result->fetch_assoc();
$stmt->close();

$isRoom = !empty($inv['room_type']);

// Calculate stay duration
$date1 = new DateTime($inv['check_in']);
$date2 = new DateTime($inv['check_out']);
$nights = $date1->diff($date2)->days;
$nights = ($nights <= 0 && $isRoom) ? 1 : $nights; 

// Branding Asset Paths
$logo_path = $_SERVER['DOCUMENT_ROOT'] . $PROJECT_ROOT . '/assets/logo.png';

// Include Global Header (which handles favicon and further UI logic)
include('../includes/header.php'); 
?>

<style>
    /* --- INVOICE PAGE STYLES --- */
    .invoice-page-bg {
        padding: 60px 0;
        background-color: #f8f9fa;
        min-height: 90vh;
    }

    /* Professional Paper Container */
    .invoice-paper {
        background: #fff;
        max-width: 850px;
        margin: 0 auto;
        padding: 60px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        border-radius: 2px;
        position: relative;
        overflow: hidden;
    }

    /* Watermark for status */
    .status-stamp {
        position: absolute;
        top: 35px;
        right: -45px;
        transform: rotate(45deg);
        font-weight: 800;
        font-size: 0.8rem;
        padding: 10px 60px;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: #fff;
        z-index: 10;
    }
    .stamp-completed { background-color: #27ae60; }
    .stamp-booked, .stamp-confirmed { background-color: var(--color-brand); }
    .stamp-cancelled { background-color: #e74c3c; }

    /* Header Styling */
    .inv-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-bottom: 2px solid #eee;
        padding-bottom: 30px;
        margin-bottom: 40px;
    }

    .brand-info h1 {
        font-family: 'Playfair Display', serif;
        font-size: 2.2rem;
        color: var(--color-text-dark);
        margin: 0;
    }

    .brand-info img {
        max-height: 80px;
        margin-bottom: 15px;
    }

    .inv-id-box {
        text-align: right;
    }

    .inv-id-box h2 {
        font-size: 1.8rem;
        color: var(--color-brand);
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    /* Billing Section */
    .inv-details-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 50px;
        margin-bottom: 50px;
    }

    .detail-col h4 {
        text-transform: uppercase;
        font-size: 0.75rem;
        color: #888;
        margin-bottom: 15px;
        letter-spacing: 1.5px;
        border-bottom: 1px solid #eee;
        padding-bottom: 5px;
    }

    .detail-col p {
        font-size: 1rem;
        line-height: 1.6;
        color: #333;
    }

    /* Table Styling */
    .inv-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 40px;
    }

    .inv-table th {
        background-color: #fcfcfc;
        text-align: left;
        padding: 15px;
        font-size: 0.8rem;
        text-transform: uppercase;
        color: #666;
        border-bottom: 2px solid #eee;
    }

    .inv-table td {
        padding: 20px 15px;
        border-bottom: 1px solid #f0f0f0;
        font-size: 1rem;
    }

    /* Total Section */
    .inv-summary-wrapper {
        display: flex;
        justify-content: flex-end;
    }

    .summary-box {
        width: 320px;
    }

    .summary-line {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        font-size: 1rem;
    }

    .summary-line.grand-total {
        border-top: 2px solid #333;
        margin-top: 10px;
        padding-top: 15px;
        font-weight: 700;
        font-size: 1.4rem;
        color: var(--color-brand);
    }

    /* Buttons */
    .inv-actions-nav {
        max-width: 850px;
        margin: 40px auto 0;
        display: flex;
        justify-content: space-between;
    }

    /* Print Overrides */
    @media print {
        .main-header, .main-footer, .inv-actions-nav { display: none !important; }
        body { background: #fff; padding: 0; }
        .invoice-page-bg { padding: 0; }
        .invoice-paper { box-shadow: none; border: none; width: 100%; padding: 0; }
        .status-stamp { border: 1px solid #000; color: #000; background: #fff; }
    }
</style>

<div class="invoice-page-bg">
    <div class="container">
        
        <div class="invoice-paper" id="invoice-content">
            <!-- Dynamic Status Stamp -->
            <div class="status-stamp stamp-<?= strtolower($inv['status']); ?>">
                <?= htmlspecialchars($inv['status']); ?>
            </div>

            <!-- Header Section -->
            <div class="inv-header">
                <div class="brand-info">
                    <?php if(file_exists($logo_path)): ?>
                        <img src="<?= $PROJECT_ROOT ?>/assets/images/logo.png" alt="Citadel Logo">
                    <?php endif; ?>
                    <h1>The Citadel Retreat</h1>
                    <p style="color: #777; font-size: 0.9rem;">
                        123 Coastal Drive, Highland Peaks<br>
                        booking@thecitadelretreat.com | +91 98765 43210
                    </p>
                </div>
                <div class="inv-id-box">
                    <h2>Invoice</h2>
                    <p><strong>Ref: #<?= htmlspecialchars($inv['invoice_no']); ?></strong></p>
                    <p style="font-size: 0.9rem; color: #777;">Date: <?= date('d M, Y', strtotime($inv['created_at'])); ?></p>
                </div>
            </div>

            <!-- Billing Info -->
            <div class="inv-details-grid">
                <div class="detail-col">
                    <h4>Customer Information</h4>
                    <p>
                        <strong><?= htmlspecialchars($inv['full_name']); ?></strong><br>
                        <?= htmlspecialchars($inv['email']); ?><br>
                        <?= htmlspecialchars($inv['phone']); ?>
                    </p>
                </div>
                <div class="detail-col">
                    <h4>Reservation Info</h4>
                    <p>
                        <strong>Booking ID:</strong> #B-<?= $inv['booking_id']; ?><br>
                        <strong>Type:</strong> <?= $isRoom ? 'Luxury Stay' : 'Fine Dining'; ?><br>
                        <strong>Period:</strong> <?= date('d M', strtotime($inv['check_in'])); ?> - <?= date('d M, Y', strtotime($inv['check_out'])); ?>
                    </p>
                </div>
            </div>

            <!-- Invoice Items Table -->
            <table class="inv-table">
                <thead>
                    <tr>
                        <th>Item Description</th>
                        <th>Unit Price</th>
                        <th>Qty / Nights</th>
                        <th style="text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($isRoom): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($inv['room_type']); ?> Room</strong><br>
                                <small style="color: #888;">Room No: <?= htmlspecialchars($inv['room_no']); ?></small>
                            </td>
                            <td>₹<?= number_format($inv['price_per_night'], 2); ?></td>
                            <td><?= $nights; ?> Night(s)</td>
                            <td style="text-align: right;">₹<?= number_format($inv['price_per_night'] * $nights, 2); ?></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td>
                                <strong>Restaurant Table Reservation</strong><br>
                                <small style="color: #888;">Table No: <?= htmlspecialchars($inv['table_no']); ?> (Capacity: <?= $inv['capacity']; ?>)</small>
                            </td>
                            <td>-</td>
                            <td>1 Booking</td>
                            <td style="text-align: right;">₹<?= number_format($inv['total_price'], 2); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Financial Summary -->
            <div class="inv-summary-wrapper">
                <div class="summary-box">
                    <div class="summary-line">
                        <span>Base Amount</span>
                        <span>₹<?= number_format($inv['total_price'] / 1.18, 2); ?></span>
                    </div>
                    <div class="summary-line">
                        <span>GST (18%)</span>
                        <span>₹<?= number_format($inv['total_price'] - ($inv['total_price'] / 1.18), 2); ?></span>
                    </div>
                    <div class="summary-line grand-total">
                        <span>Total Paid</span>
                        <span>₹<?= number_format($inv['total_price'], 2); ?></span>
                    </div>
                </div>
            </div>

            <!-- Footnote -->
            <div style="margin-top: 80px; border-top: 1px solid #eee; padding-top: 25px; text-align: center;">
                <p style="font-size: 0.85rem; color: #999; font-style: italic;">
                    Thank you for choosing The Citadel Retreat. We look forward to welcoming you again.
                    <br>This is an electronically generated invoice and does not require a physical signature.
                </p>
            </div>
        </div>

        <!-- External Controls -->
        <div class="inv-actions-nav">
            <a href="<?= $PROJECT_ROOT ?>/user/my_bookings.php" class="btn btn-outline-dark">
                <i class="fas fa-arrow-left"></i> Back to My Bookings
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Download as PDF
            </button>
        </div>

    </div>
</div>

<?php include('../includes/footer.php'); ?>