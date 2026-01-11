<?php 
// 1. SESSION & AUTHENTICATION
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

$PROJECT_ROOT = '/Hotel%20Management%20system';
require_once('../includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $PROJECT_ROOT . '/auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. FETCH ALL BOOKINGS (Unified Stay & Dining Query)
$query = "
    SELECT 
        b.booking_id, b.check_in, b.check_out, b.status, b.total_price, b.created_at,
        r.room_type, r.room_no,
        t.table_no, t.capacity AS table_capacity
    FROM bookings b
    LEFT JOIN rooms r ON b.room_id = r.room_id
    LEFT JOIN tables_list t ON b.table_id = t.table_id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [
    'upcoming' => [],
    'past' => [],
    'cancelled' => []
];

while ($row = $result->fetch_assoc()) {
    $status = strtolower($row['status']);
    if ($status == 'cancelled') {
        $bookings['cancelled'][] = $row;
    } elseif ($status == 'completed') {
        $bookings['past'][] = $row;
    } else {
        $bookings['upcoming'][] = $row;
    }
}
$stmt->close();

include('../includes/header.php'); 
?>

<style>
    /* --- RESERVATIONS HUB STYLES --- */
    .reservations-hub {
        padding: 40px 0;
        background-color: #f8f9fa;
        min-height: 85vh;
    }

    .hub-header {
        margin-bottom: 40px;
    }

    .hub-header h1 {
        font-family: 'Playfair Display', serif;
        font-size: 2.5rem;
        color: var(--color-text-dark);
        margin-bottom: 10px;
    }

    .stats-pill {
        display: inline-flex;
        align-items: center;
        background: #fff;
        padding: 8px 20px;
        border-radius: 50px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        font-weight: 600;
        color: var(--color-brand);
    }

    /* Tab Navigation */
    .hub-tabs {
        display: flex;
        gap: 30px;
        margin-bottom: 40px;
        border-bottom: 2px solid #eee;
    }

    .hub-tab-btn {
        background: none;
        border: none;
        padding: 15px 5px;
        font-size: 1rem;
        font-weight: 700;
        color: #888;
        cursor: pointer;
        position: relative;
        transition: color 0.3s ease;
    }

    .hub-tab-btn.active {
        color: var(--color-brand);
    }

    .hub-tab-btn.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 100%;
        height: 3px;
        background-color: var(--color-brand);
        border-radius: 3px;
    }

    /* Grid Layout */
    .bookings-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 25px;
    }

    .booking-card {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid #f0f0f0;
        display: flex;
        flex-direction: column;
    }

    .booking-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.08);
    }

    .card-accent {
        height: 5px;
        width: 100%;
    }

    .accent-upcoming { background: var(--color-brand); }
    .accent-past { background: #27ae60; }
    .accent-cancelled { background: #e74c3c; }

    .card-main {
        padding: 25px;
        flex-grow: 1;
    }

    .card-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .category-tag {
        font-size: 0.75rem;
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: 1px;
        padding: 4px 12px;
        border-radius: 4px;
        background: #f0f7ff;
        color: var(--color-brand);
    }

    .booking-id {
        font-size: 0.85rem;
        color: #aaa;
        font-family: monospace;
    }

    .card-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.4rem;
        margin-bottom: 15px;
        color: #2d3436;
    }

    .info-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-bottom: 20px;
    }

    .info-box {
        background: #fafafa;
        padding: 10px;
        border-radius: 8px;
    }

    .info-box label {
        display: block;
        font-size: 0.65rem;
        text-transform: uppercase;
        color: #999;
        margin-bottom: 3px;
    }

    .info-box span {
        font-weight: 700;
        font-size: 0.9rem;
        color: #333;
    }

    .card-footer {
        padding: 20px 25px;
        background: #fcfcfc;
        border-top: 1px solid #f0f0f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .price-wrap .label {
        font-size: 0.75rem;
        color: #888;
        display: block;
    }

    .price-wrap .val {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--color-text-dark);
    }

    .action-btns {
        display: flex;
        gap: 10px;
    }

    /* Animation */
    .group-section.hidden { display: none; }
    
    .fade-in-up {
        animation: fadeInUp 0.5s ease forwards;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Empty State */
    .empty-hub {
        text-align: center;
        padding: 80px 20px;
    }

    .empty-hub i {
        font-size: 4rem;
        color: #dfe6e9;
        margin-bottom: 20px;
    }
</style>

<div class="reservations-hub">
    <div class="container">
        
        <!-- Hub Header -->
        <header class="hub-header d-flex justify-content-between align-items-end">
            <div>
                <h1 class="fade-in-up">My Reservations</h1>
                <p class="text-muted">Welcome to your personal concierge dashboard.</p>
            </div>
            <div class="stats-pill fade-in-up">
                <i class="fas fa-calendar-check me-2"></i>
                <?= count($bookings['upcoming']); ?> Active Reservations
            </div>
        </header>

        <!-- Hub Navigation -->
        <nav class="hub-tabs fade-in-up" style="animation-delay: 0.1s;">
            <button class="hub-tab-btn active" onclick="switchHubTab('upcoming')">Upcoming & Active</button>
            <button class="hub-tab-btn" onclick="switchHubTab('past')">Past Stays</button>
            <button class="hub-tab-btn" onclick="switchHubTab('cancelled')">Cancelled</button>
        </nav>

        <!-- Bookings Container -->
        <div id="hub-content-wrapper">
            <?php foreach ($bookings as $category => $list): ?>
                <div id="<?= $category ?>-section" class="group-section <?= $category == 'upcoming' ? 'active' : 'hidden' ?>">
                    <?php if (empty($list)): ?>
                        <div class="empty-hub card">
                            <i class="fas fa-calendar-day"></i>
                            <h3>No <?= $category ?> reservations</h3>
                            <p class="mb-4">It looks like you haven't made any <?= $category ?> plans yet.</p>
                            <a href="<?= $PROJECT_ROOT ?>/rooms.php" class="btn btn-primary">Discover Rooms</a>
                        </div>
                    <?php else: ?>
                        <div class="bookings-grid">
                            <?php 
                            $i = 0;
                            foreach ($list as $booking): 
                                $i++;
                                $isRoom = !empty($booking['room_type']);
                                $typeLabel = $isRoom ? 'Accomodation' : 'Dining';
                                $title = $isRoom ? $booking['room_type'] . ' Room' : 'Dining Table ' . $booking['table_no'];
                                $accentClass = 'accent-' . $category;
                            ?>
                                <div class="booking-card fade-in-up" style="animation-delay: <?= 0.1 + ($i * 0.05) ?>s;">
                                    <div class="card-accent <?= $accentClass ?>"></div>
                                    <div class="card-main">
                                        <div class="card-top">
                                            <span class="category-tag">
                                                <i class="fas <?= $isRoom ? 'fa-bed' : 'fa-utensils' ?> me-1"></i> <?= $typeLabel ?>
                                            </span>
                                            <span class="booking-id">#ID-<?= $booking['booking_id'] ?></span>
                                        </div>
                                        
                                        <h3 class="card-title"><?= htmlspecialchars($title) ?></h3>
                                        
                                        <div class="info-row">
                                            <div class="info-box">
                                                <label><?= $isRoom ? 'Check In' : 'Date' ?></label>
                                                <span><?= date('M d, Y', strtotime($booking['check_in'])) ?></span>
                                            </div>
                                            <div class="info-box">
                                                <label><?= $isRoom ? 'Check Out' : 'Time' ?></label>
                                                <span>
                                                    <?php 
                                                        if($isRoom) echo date('M d, Y', strtotime($booking['check_out']));
                                                        else echo "Evening Session"; // Or actual time if stored in DB
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-footer">
                                        <div class="price-wrap">
                                            <span class="label">Total Amount</span>
                                            <span class="val">₹<?= number_format($booking['total_price'], 2) ?></span>
                                        </div>
                                        <div class="action-btns">
                                            <?php if ($category == 'upcoming'): ?>
                                                <button class="btn btn-outline-danger btn-sm" onclick="handleCancel(<?= $booking['booking_id'] ?>)">Cancel</button>
                                            <?php endif; ?>
                                            
                                            <?php if ($category == 'past'): ?>
                                                <a href="view_invoice.php?id=<?= $booking['booking_id'] ?>" class="btn btn-secondary btn-sm">Invoice</a>
                                            <?php endif; ?>
                                            
                                            <a href="booking_details.php?id=<?= $booking['booking_id'] ?>" class="btn btn-primary btn-sm">Details</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
function switchHubTab(category) {
    // Buttons
    document.querySelectorAll('.hub-tab-btn').forEach(btn => btn.classList.remove('active'));
    event.currentTarget.classList.add('active');

    // Content
    document.querySelectorAll('.group-section').forEach(sec => {
        sec.classList.add('hidden');
        sec.classList.remove('active');
    });
    
    const target = document.getElementById(category + '-section');
    target.classList.remove('hidden');
    target.classList.add('active');
}

function handleCancel(id) {
    if(confirm('Are you sure you wish to cancel this reservation? This cannot be undone.')) {
        window.location.href = '../bookings/booking_process.php?action=cancel&id=' + id;
    }
}
</script>

<?php include('../includes/footer.php'); ?>