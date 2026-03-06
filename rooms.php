<?php 
// 1. SESSION & CONFIG
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

require_once("includes/config.php");

// 2. INPUT PROCESSING (POST ONLY)
$check_in  = $_POST['check_in']  ?? date('Y-m-d');
$check_out = $_POST['check_out'] ?? date('Y-m-d', strtotime('+1 day'));
$guests    = max(1, (int)($_POST['guests'] ?? 1));
$rooms_req = max(1, (int)($_POST['rooms'] ?? 1));

// Sanitize inputs
$check_in_safe  = mysqli_real_escape_string($conn, $check_in);
$check_out_safe = mysqli_real_escape_string($conn, $check_out);

// 3. CORE AVAILABILITY QUERY
$query_sql = "
SELECT 
    r.*,
    COALESCE(SUM(b.rooms_booked), 0) AS booked_rooms,
    (
        SELECT COALESCE(SUM(bb.rooms_booked), 0)
        FROM bookings bb
        WHERE bb.room_id = r.room_id
        AND bb.status IN ('Confirmed','Completed')
    ) AS total_booked_all_time
FROM rooms r
LEFT JOIN bookings b 
    ON b.room_id = r.room_id
    AND b.status IN ('Confirmed','Pending')
    AND b.check_in < '$check_out_safe'
    AND b.check_out > '$check_in_safe'
WHERE r.capacity >= $guests
  AND r.status = 'Available'
GROUP BY r.room_id
HAVING (r.total_rooms - booked_rooms) >= $rooms_req
ORDER BY total_booked_all_time DESC, r.price_per_night ASC
";

$result = mysqli_query($conn, $query_sql);
if (!$result) {
    error_log("Inventory Query Error: " . mysqli_error($conn));
}

// Include Global Header (Handles Logo, Favicon, and CSS)
include("includes/header.php"); 
?>

<div class="rooms-listing-wrapper">
    <div class="container">
        
        <!-- Hero Search Section -->
        <section class="rooms-hero fade-in-up">
            <div class="hero-content text-center">
                <span class="badge-accent">Inventory Discovery</span>
                <h1>Find Your Sanctuary</h1>
                <p class="lead-text">
                    Available for <strong><?= date('M d', strtotime($check_in)); ?></strong> — <strong><?= date('M d, Y', strtotime($check_out)); ?></strong>
                </p>
            </div>

            <!-- Modern Compact Search Widget -->
            <form class="modern-search-widget" action="rooms.php" method="POST">
                <div class="search-flex">
                    <div class="field-group">
                        <label><i class="fas fa-calendar-alt"></i> Check-in</label>
                        <input type="date" name="check_in" value="<?= $check_in ?>" required min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="field-group">
                        <label><i class="fas fa-calendar-check"></i> Check-out</label>
                        <input type="date" name="check_out" value="<?= $check_out ?>" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                    </div>
                    <div class="field-group">
                        <label><i class="fas fa-users"></i> Guests</label>
                        <input type="number" name="guests" value="<?= $guests ?>" min="1" max="10" required>
                    </div>
                    <div class="field-group">
                        <label><i class="fas fa-door-open"></i> Rooms</label>
                        <select name="rooms" required>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>" <?= $i == $rooms_req ? 'selected' : '' ?>><?= $i ?> Room<?= $i > 1 ? 's' : '' ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-action-main">
                        <i class="fas fa-sync-alt"></i> Update
                    </button>
                </div>
            </form>
        </section>

        <!-- Rooms Grid Layout -->
        <div class="rooms-grid">
            <?php 
            if (mysqli_num_rows($result) > 0): 
                $count = 0;
                while ($row = mysqli_fetch_assoc($result)):
                    $count++;
                    $available_rooms = $row['total_rooms'] - $row['booked_rooms'];
                    $isPopular = $row['total_booked_all_time'] >= 10;
                    $delay = $count * 0.05;
            ?>

            <div class="room-grid-card card fade-in-up" style="animation-delay: <?= $delay ?>s">
                <div class="room-card-image">
                    <img src="assets/images/rooms/<?= htmlspecialchars($row['image']); ?>" 
                         alt="<?= htmlspecialchars($row['room_type']); ?>"
                         onerror="this.src='https://images.unsplash.com/photo-1590490360182-c33d57733427?q=80&w=800&auto=format&fit=crop'">
                    
                    <div class="visual-overlays">
                        <span class="inventory-status">
                            <?= $available_rooms ?> rooms left
                        </span>
                        <?php if ($isPopular): ?>
                            <span class="popular-badge"><i class="fas fa-fire"></i> Popular</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="room-card-body">
                    <div class="room-type-info">
                        <h3><?= htmlspecialchars($row['room_type']); ?></h3>
                        <div class="info-specs">
                            <span><i class="fas fa-user-friends"></i> Max <?= $row['capacity']; ?></span>
                            <span><i class="fas fa-snowflake"></i> <?= htmlspecialchars($row['ac_type'] ?? 'AC'); ?></span>
                        </div>
                    </div>

                    <p class="room-description"><?= htmlspecialchars($row['description'] ?? 'Experience unmatched comfort in our signature suites.'); ?></p>

                    <div class="room-footer">
                        <div class="price-container">
                            <span class="price-val">₹<?= number_format($row['price_per_night']); ?></span>
                            <span class="price-unit">/ night</span>
                        </div>

                        <form action="user/book_room.php" method="POST">
                            <input type="hidden" name="room_id" value="<?= $row['room_id']; ?>">
                            <input type="hidden" name="check_in" value="<?= $check_in; ?>">
                            <input type="hidden" name="check_out" value="<?= $check_out; ?>">
                            <input type="hidden" name="guests" value="<?= $guests; ?>">
                            <input type="hidden" name="rooms" value="<?= $rooms_req; ?>">

                            <button type="submit" class="btn btn-book-grid">
                                Book Now
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <?php endwhile; else: ?>
                <div class="empty-state-inventory grid-full-width">
                    <i class="fas fa-search-minus fa-3x mb-3"></i>
                    <h2>No Rooms Found</h2>
                    <p>Try adjusting your dates or guest count.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* --- ADAPTIVE THEME DESIGN --- */
:root {
    --room-bg: #f8faff;
    --room-surface: #ffffff;
    --room-text: #1d3557;
    --room-text-muted: #6c757d;
    --room-border: #eef2f7;
    --room-shadow: 0 10px 30px rgba(0,0,0,0.05);
    --room-accent: #f4a261; /* Brand Orange */
    --room-blue: #0077b6;
}

@media (prefers-color-scheme: dark) {
    :root {
        --room-bg: #0b111a;
        --room-surface: #151e2b;
        --room-text: #e1e8f0;
        --room-text-muted: #94a3b8;
        --room-border: #232d3d;
        --room-shadow: 0 10px 50px rgba(0,0,0,0.3);
    }
}

.rooms-listing-wrapper {
    background-color: var(--room-bg);
    min-height: 100vh;
    padding: 60px 0;
    color: var(--room-text);
}

.rooms-hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: 3rem;
    margin-bottom: 10px;
}

.badge-accent {
    text-transform: uppercase;
    font-weight: 800;
    letter-spacing: 2px;
    font-size: 0.7rem;
    color: var(--room-blue);
}

/* Modern Compact Search Widget */
.modern-search-widget {
    background: var(--room-surface);
    padding: 10px;
    border-radius: 50px;
    box-shadow: var(--room-shadow);
    border: 1px solid var(--room-border);
    margin: 40px auto 60px;
    max-width: 1000px;
}

.search-flex {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.field-group {
    padding: 5px 20px;
    border-right: 1px solid var(--room-border);
    flex: 1;
}

.field-group:last-of-type { border-right: none; }

.field-group label {
    display: block;
    font-size: 0.65rem;
    text-transform: uppercase;
    font-weight: 700;
    color: var(--room-text-muted);
    margin-bottom: 2px;
}

.field-group input, .field-group select {
    background: none;
    border: none;
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--room-text);
    width: 100%;
}

.field-group input:focus { outline: none; }

.btn-action-main {
    background: var(--room-accent);
    color: white;
    border-radius: 40px;
    padding: 12px 30px;
    border: none;
    font-weight: 700;
    cursor: pointer;
    transition: 0.3s;
}

.btn-action-main:hover { background: #e76f51; }

/* Grid Layout */
.rooms-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 30px;
}

.grid-full-width {
    grid-column: 1 / -1;
    text-align: center;
    padding: 100px 0;
    color: var(--room-text-muted);
}

/* Room Grid Card Styling */
.room-grid-card {
    background: var(--room-surface);
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid var(--room-border);
    box-shadow: var(--room-shadow);
    transition: transform 0.3s, box-shadow 0.3s;
    display: flex;
    flex-direction: column;
}

.room-grid-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.1);
}

.room-card-image {
    position: relative;
    height: 230px;
    overflow: hidden;
}

.room-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s;
}

.room-grid-card:hover .room-card-image img {
    transform: scale(1.1);
}

.visual-overlays {
    position: absolute;
    bottom: 15px;
    left: 15px;
    display: flex;
    gap: 8px;
}

.inventory-status, .popular-badge {
    padding: 5px 12px;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    backdrop-filter: blur(5px);
}

.inventory-status { background: rgba(0, 119, 182, 0.8); color: white; }
.popular-badge { background: rgba(244, 162, 97, 0.9); color: white; }

.room-card-body {
    padding: 25px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.room-type-info h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.6rem;
    margin-bottom: 5px;
}

.info-specs {
    display: flex;
    gap: 15px;
    font-size: 0.8rem;
    color: var(--room-text-muted);
    margin-bottom: 15px;
}

.room-description {
    font-size: 0.9rem;
    line-height: 1.6;
    color: var(--room-text-muted);
    margin-bottom: 25px;
    flex-grow: 1;
}

.room-footer {
    border-top: 1px solid var(--room-border);
    padding-top: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.price-val {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--room-blue);
    display: block;
}

.price-unit {
    font-size: 0.75rem;
    color: var(--room-text-muted);
}

.btn-book-grid {
    background: var(--room-blue);
    color: white;
    padding: 10px 25px;
    border-radius: 10px;
    border: none;
    font-weight: 700;
    cursor: pointer;
    transition: 0.3s;
}

.btn-book-grid:hover {
    background: #023e8a;
}

/* Animations */
.fade-in-up {
    opacity: 0;
    animation: fadeInUp 0.6s ease forwards;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 992px) {
    .modern-search-widget { border-radius: 20px; padding: 20px; }
    .search-flex { flex-direction: column; align-items: stretch; gap: 15px; }
    .field-group { border-right: none; border-bottom: 1px solid var(--room-border); }
    .rooms-grid { grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); }
}
</style>

<?php include("includes/footer.php"); ?>