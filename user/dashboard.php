<?php 
// 1. SESSION & INITIALIZATION
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

require_once("../includes/config.php");

// 2. AUTHENTICATION GUARD
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Please login to access your dashboard.";
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'] ?? 'Guest';

// 3. FETCH DATA
// Active Stays & Dining
$active_bookings_query = $conn->prepare("
    SELECT b.booking_id, b.check_in, b.check_out, b.status, b.total_price,
           r.room_type, r.room_no,
           t.table_id
    FROM bookings b
    LEFT JOIN rooms r ON b.room_id = r.room_id
    LEFT JOIN tables t ON b.table_id = t.table_id
    WHERE b.user_id = ? AND b.status IN ('Confirmed','Pending')
    ORDER BY b.check_in ASC
");
$active_bookings_query->bind_param("i", $user_id);
$active_bookings_query->execute();
$active_bookings = $active_bookings_query->get_result();

// Recent History
$history_query = $conn->prepare("
    SELECT booking_id, check_in, status, total_price
    FROM bookings
    WHERE user_id = ? AND status IN ('Completed','Cancelled')
    ORDER BY check_in DESC LIMIT 4
");
$history_query->bind_param("i", $user_id);
$history_query->execute();
$history = $history_query->get_result();

// Include Global Header (Includes Favicon & Logo Logic)
include("../includes/header.php"); 
?>

<div class="dashboard-wrapper">
    <div class="container">
        
        <!-- Welcome Hero Section -->
        <header class="dashboard-hero fade-in-up">
            <div class="hero-content">
                <h1>Welcome Home, <?= htmlspecialchars($user_name) ?></h1>
                <p>Your sanctuary at The Citadel Retreat awaits. Manage your luxury experience below.</p>
            </div>
            <div class="hero-stats">
                <div class="stat-pill">
                    <span class="val"><?= $active_bookings->num_rows ?></span>
                    <span class="lbl">Active Bookings</span>
                </div>
            </div>
        </header>

        <!-- Notification Area -->
        <?php if (isset($_SESSION['success_message']) || isset($_SESSION['error_message'])): ?>
            <div class="alert-container fade-in">
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Quick Experience Actions -->
        <section class="section-group">
            <h2 class="section-title">Exclusive Experiences</h2>
            <div class="action-grid grid-4">
                <a href="../rooms.php" class="action-card card">
                    <div class="card-icon"><i class="fas fa-bed"></i></div>
                    <h3>Luxury Stays</h3>
                    <p>Reserve a suite with bespoke views.</p>
                    <span class="action-link">Book Room <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="../dining.php" class="action-card card">
                    <div class="card-icon"><i class="fas fa-utensils"></i></div>
                    <h3>Fine Dining</h3>
                    <p>Secure a table for an evening of flavor.</p>
                    <span class="action-link">Reserve Table <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="../spa.php" class="action-card card">
                    <div class="card-icon"><i class="fas fa-spa"></i></div>
                    <h3>Wellness Spa</h3>
                    <p>Rejuvenate with curated treatments.</p>
                    <span class="action-link">Explore Spa <i class="fas fa-arrow-right"></i></span>
                </a>
                <a href="../user/profile.php" class="action-card card">
                    <div class="card-icon"><i class="fas fa-user-shield"></i></div>
                    <h3>Concierge Profile</h3>
                    <p>Manage your preferences & identity.</p>
                    <span class="action-link">Your Details <i class="fas fa-arrow-right"></i></span>
                </a>
            </div>
        </section>

        <div class="dashboard-main-grid mt-5">
            <!-- Left Column: Active Bookings & Dining -->
            <div class="main-content">
                
                <!-- Upcoming Reservations -->
                <section class="mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="section-title">Current Reservations</h2>
                        <a href="my_bookings.php" class="btn btn-link btn-sm">Manage All</a>
                    </div>
                    
                    <?php if ($active_bookings->num_rows > 0): ?>
                        <div class="active-list">
                            <?php while ($b = $active_bookings->fetch_assoc()): ?>
                                <div class="reservation-pill card fade-in-up">
                                    <div class="res-type">
                                        <i class="fas <?= $b['room_type'] ? 'fa-hotel' : 'fa-wine-glass' ?>"></i>
                                    </div>
                                    <div class="res-info">
                                        <h4><?= $b['room_type'] ? $b['room_type']." (Suite ".$b['room_no'].")" : "Dining Table ".$b['table_no'] ?></h4>
                                        <p class="small text-muted">
                                            <?= date("D, M d", strtotime($b['check_in'])) ?> 
                                            <?= $b['check_out'] ? " — ".date("D, M d", strtotime($b['check_out'])) : "" ?>
                                        </p>
                                    </div>
                                    <div class="res-status">
                                        <span class="badge badge-<?= strtolower($b['status']) ?>"><?= $b['status'] ?></span>
                                    </div>
                                    <div class="res-action">
                                        <a href="view_invoice.php?id=<?= $b['booking_id'] ?>" class="btn-icon"><i class="fas fa-receipt"></i></a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-placeholder card text-center p-5">
                            <i class="fas fa-calendar-day mb-3"></i>
                            <p>No active reservations at the moment.</p>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- Featured Dining (Redesigned per Image 1) -->
                <section class="mb-5">
                    <h2 class="section-title">Today's Featured Dining</h2>
                    
                    <!-- Orange Center Action Button -->
                    <div class="mb-4">
                        <a href="../menu.php" class="btn btn-action btn-full-width">View Full Menu</a>
                    </div>
                    
                    <div class="menu-showcase-grid grid-3">
                        <?php
                        $daily_seed = date('Ymd');
                        // Sample items matching screenshot categories
                        $featured = $conn->query("SELECT * FROM food_menu WHERE food_type IN ('Dinner', 'Dessert', 'Starter') ORDER BY RAND($daily_seed) LIMIT 3");
                        while ($item = $featured->fetch_assoc()):
                        ?>
                            <div class="menu-showcase-card card">
                                <div class="menu-card-header">
                                    <i class="fas <?= get_food_icon($item['food_type']) ?>"></i>
                                    <span class="menu-category-name"><?= $item['food_type'] ?></span>
                                </div>
                                <h3 class="menu-item-name"><?= htmlspecialchars($item['food_name']) ?></h3>
                                <div class="menu-item-price">₹<?= number_format($item['price'], 2) ?></div>
                                <div class="menu-card-accent"></div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </section>

                <!-- History Section (Redesigned per Image 2) -->
                <section class="history-section mt-5">
                    <h2 class="section-title">Recent Booking History</h2>
                    
                    <div class="history-content-area">
                        <?php if ($history->num_rows > 0): ?>
                            <div class="history-list">
                                <?php while ($h = $history->fetch_assoc()): ?>
                                    <div class="history-row">
                                        <div class="h-date"><?= date("M d", strtotime($h['check_in'])) ?></div>
                                        <div class="h-info">
                                            <p>#<?= $h['booking_id'] ?></p>
                                            <span class="status status-<?= strtolower($h['status']) ?>"><?= $h['status'] ?></span>
                                        </div>
                                        <div class="h-price">₹<?= number_format($h['total_price']) ?></div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="p-4 text-center text-muted">No previous bookings found.</div>
                        <?php endif; ?>
                        
                        <!-- Full-width View All Button per screenshot -->
                        <div class="mt-3">
                            <a href="booking_history.php" class="btn btn-secondary btn-full-width">View All</a>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Right Column: Sidebar -->
            <aside class="dashboard-sidebar">
                <div class="support-card card">
                    <h3>Need Assistance?</h3>
                    <p>Our 24/7 concierge is ready to help you with your stay.</p>
                    <a href="tel:+919876543210" class="btn btn-action btn-block mt-3"><i class="fas fa-phone-alt"></i> Call Reception</a>
                </div>
            </aside>
        </div>
    </div>
</div>

<style>
/* --- ADAPTIVE THEME VARIABLES --- */
:root {
    --dash-bg: #f8faff;
    --dash-surface: #ffffff;
    --dash-text: #1a2a3a;
    --dash-text-muted: #6c757d;
    --dash-accent: #0077b6;
    --dash-accent-soft: rgba(0, 119, 182, 0.08);
    --dash-border: #eef2f7;
    --dash-shadow: 0 10px 30px rgba(0,0,0,0.05);
    --brand-orange: #f4a261;
}

@media (prefers-color-scheme: dark) {
    :root {
        --dash-bg: #0b111a;
        --dash-surface: #151e2b;
        --dash-text: #e1e8f0;
        --dash-text-muted: #94a3b8;
        --dash-accent: #00b4d8;
        --dash-accent-soft: rgba(0, 180, 216, 0.1);
        --dash-border: #232d3d;
        --dash-shadow: 0 10px 40px rgba(0,0,0,0.3);
    }
}

.dashboard-wrapper {
    background-color: var(--dash-bg);
    min-height: 100vh;
    padding: 60px 0;
    color: var(--dash-text);
}

.dashboard-hero {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 50px;
    padding: 40px;
    background: linear-gradient(135deg, var(--dash-accent) 0%, #023e8a 100%);
    border-radius: 24px;
    color: white;
    box-shadow: 0 15px 35px rgba(2, 62, 138, 0.2);
}

.dashboard-hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: 2.8rem;
    margin-bottom: 10px;
}

.stat-pill {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    padding: 15px 30px;
    border-radius: 16px;
    text-align: center;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.stat-pill .val { font-size: 2rem; font-weight: 800; display: block; }
.stat-pill .lbl { font-size: 0.8rem; text-transform: uppercase; opacity: 0.8; }

/* Section Title Style */
.section-title {
    font-family: 'Playfair Display', serif;
    font-size: 2.2rem;
    margin-bottom: 25px;
    color: var(--dash-text);
}

/* Action Buttons per screenshot */
.btn-action {
    background-color: var(--brand-orange) !important;
    color: white !important;
    border: none;
    padding: 12px 25px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
}

.btn-action:hover {
    background-color: #e76f51 !important;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(244, 162, 97, 0.3);
}

.btn-full-width {
    display: block;
    width: 100%;
    text-align: center;
}

/* Featured Dining Cards per Image 1 */
.menu-showcase-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 25px;
}

.menu-showcase-card {
    position: relative;
    padding: 30px !important;
    border-bottom: 1px solid var(--dash-accent) !important;
}

.menu-card-header {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #888;
    margin-bottom: 12px;
}

.menu-card-header i { font-size: 0.8rem; }
.menu-category-name { font-size: 0.8rem; font-weight: 600; }

.menu-item-name {
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 1.4rem;
    margin-bottom: 15px;
    color: var(--dash-text);
}

.menu-item-price {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--dash-text);
}

.menu-card-accent {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: transparent;
    transition: background 0.3s;
}

.menu-showcase-card:hover .menu-card-accent {
    background: var(--dash-accent);
}

/* History Section per Image 2 */
.history-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid var(--dash-border);
}

.h-date { font-weight: 700; font-size: 1rem; width: 80px; }
.h-info { flex-grow: 1; margin: 0 20px; }
.h-info p { margin: 0; font-size: 0.9rem; font-weight: 700; }
.h-price { font-weight: 800; color: var(--dash-accent); font-size: 1.1rem; }

/* Grid Layouts */
.dashboard-main-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 40px;
}

.action-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

/* Global Card Styling */
.card {
    background: var(--dash-surface);
    border: 1px solid var(--dash-border);
    border-radius: 20px;
    padding: 25px;
    box-shadow: var(--dash-shadow);
    transition: transform 0.3s ease, border-color 0.3s ease;
}

.action-card {
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.action-card:hover {
    transform: translateY(-8px);
    border-color: var(--dash-accent);
}

.card-icon {
    width: 60px;
    height: 60px;
    background: var(--dash-accent-soft);
    color: var(--dash-accent);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 15px;
}

.action-link {
    margin-top: auto;
    font-weight: 700;
    color: var(--dash-accent);
    font-size: 0.9rem;
}

/* Sidebar Specifics */
.membership-card {
    background: linear-gradient(135deg, #1d3557 0%, #457b9d 100%);
    color: white;
}

.progress-bar-container {
    height: 8px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    overflow: hidden;
    margin-top: 10px;
}

.progress-bar-fill {
    height: 100%;
    background: #ffcc33;
}

/* Animations */
.fade-in-up { animation: fadeInUp 0.6s ease forwards; }
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 992px) {
    .dashboard-main-grid, .action-grid { grid-template-columns: 1fr; }
    .menu-showcase-grid { grid-template-columns: 1fr; }
    .dashboard-hero { flex-direction: column; text-align: center; }
}
</style>

<?php include("../includes/footer.php"); ?>