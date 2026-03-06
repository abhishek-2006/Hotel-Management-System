<?php 
// 1. SESSION & CONFIG
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

require_once("includes/config.php");

// 2. FETCH TABLES
// We assume 'price_per_hour' column exists. If price_per_hour > 0, it's a special table.
$query = "SELECT * FROM tables ORDER BY price_per_hour ASC, table_id ASC";
$result = mysqli_query($conn, $query);

// Include Global Header (Handles Meta, Favicon, Logo, and Navbar)
include("includes/header.php"); 
?>

<div class="dining-page-wrapper">
    <div class="container">
        
        <!-- Header Section -->
        <section class="dining-header text-center fade-in-up">
            <span class="sub-title">Fine Dining & Experiences</span>
            <h1>Reserve Your Table</h1>
            <p class="lead-text">
                From casual garden seating to curated candlelight experiences, 
                find the perfect setting for your next culinary journey.
            </p>
            <div class="dining-notice">
                <i class="fas fa-info-circle"></i> 
                General tables are complimentary. Premium experiences may incur a reservation fee.
            </div>
        </section>

        <!-- Tables Grid -->
        <div class="dining-grid">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($table = mysqli_fetch_assoc($result)): 
                    $is_special = ($table['price_per_hour'] > 0);
                    $price_text = $is_special ? '₹' . number_format($table['price_per_hour']) : 'Complimentary';
                    $badge_text = $is_special ? 'Special Experience' : 'General Seating';
                ?>
                    <div class="table-card <?= $is_special ? 'premium-card' : ''; ?> fade-in-up">
                        <div class="table-image-area">
                            <!-- Placeholder image logic -->
                            <img src="assets/images/tables/<?= !empty($table['image']) ? $table['image'] : 'default-table.jpg' ?>" 
                                 alt="Table <?= $table['table_type'] ?>" 
                                 onerror="this.src='https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?q=80&w=800&auto=format&fit=crop'">
                            
                            <span class="table-badge <?= $is_special ? 'badge-premium' : 'badge-general'; ?>">
                                <?= $badge_text ?>
                            </span>
                        </div>

                        <div class="table-content">
                            <div class="table-meta">
                                <h3><?= htmlspecialchars($table['table_type']) ?></h3>
                                <div class="capacity">
                                    <i class="fas fa-users"></i> Seats up to <?= $table['capacity'] ?>
                                </div>
                            </div>

                            <p class="table-desc">
                                <?= htmlspecialchars($table['description'] ?? 'Enjoy a beautiful dining environment with world-class service.'); ?>
                            </p>

                            <div class="table-footer">
                                <div class="price-tag">
                                    <span class="label">Booking Fee</span>
                                    <span class="amount <?= !$is_special ? 'free' : '' ?>"><?= $price_text ?></span>
                                </div>
                                
                                <a href="book_table.php?table_id=<?= $table['table_id'] ?>" 
                                   class="btn <?= $is_special ? 'btn-premium' : 'btn-outline-primary' ?> btn-sm">
                                    Reserve Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-utensils"></i>
                    <h3>No tables available at the moment.</h3>
                    <p>Please check back later or contact our reception.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* --- THEME DESIGN TOKENS (LIGHT/DARK) --- */
:root {
    --dining-bg: #fdfdfd;
    --card-bg: #ffffff;
    --text-main: #1d3557;
    --text-muted: #636e72;
    --border-color: #eee;
    --premium-accent: #d4af37; /* Gold */
    --premium-glow: rgba(212, 175, 55, 0.2);
}

@media (prefers-color-scheme: dark) {
    :root {
        --dining-bg: #121212;
        --card-bg: #1e1e1e;
        --text-main: #f1f1f1;
        --text-muted: #a0a0a0;
        --border-color: #333;
        --premium-accent: #ffcc33;
        --premium-glow: rgba(255, 204, 51, 0.15);
    }
}

.dining-page-wrapper {
    background-color: var(--dining-bg);
    padding: 60px 0;
    min-height: 100vh;
    transition: background 0.3s ease;
}

.dining-header {
    max-width: 800px;
    margin: 0 auto 60px;
}

.dining-header .sub-title {
    text-transform: uppercase;
    letter-spacing: 3px;
    font-weight: 700;
    color: var(--color-brand);
    font-size: 0.85rem;
    display: block;
    margin-bottom: 10px;
}

.dining-header h1 {
    font-family: 'Playfair Display', serif;
    font-size: 3rem;
    color: var(--text-main);
}

.dining-notice {
    display: inline-block;
    margin-top: 20px;
    padding: 8px 20px;
    background: rgba(0, 119, 182, 0.1);
    border-radius: 50px;
    font-size: 0.9rem;
    color: var(--color-brand);
}

/* Grid */
.dining-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 30px;
}

/* Card */
.table-card {
    background: var(--card-bg);
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}

.table-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.1);
}

.premium-card {
    border: 1px solid var(--premium-accent);
    box-shadow: 0 0 20px var(--premium-glow);
}

.table-image-area {
    position: relative;
    height: 220px;
}

.table-image-area img {
    width: 100%;
    height: 100%;
    object-fit: crop;
    object-position: center;
}

.table-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    padding: 5px 12px;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.badge-premium { background: var(--premium-accent); color: #000; }
.badge-general { background: rgba(0,0,0,0.6); color: #fff; }

.table-content {
    padding: 25px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.table-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.table-meta h3 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    color: var(--text-main);
}

.capacity {
    font-size: 0.85rem;
    color: var(--text-muted);
}

.table-desc {
    font-size: 0.95rem;
    color: var(--text-muted);
    line-height: 1.6;
    margin-bottom: 25px;
    flex-grow: 1;
}

.table-footer {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    padding-top: 20px;
    border-top: 1px solid var(--border-color);
}

.price-tag .label {
    display: block;
    font-size: 0.7rem;
    text-transform: uppercase;
    color: var(--text-muted);
}

.price-tag .amount {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--text-main);
}

.amount.free {
    color: #27ae60;
}

.btn-premium {
    background: var(--premium-accent);
    color: #000;
    font-weight: 700;
}

.btn-premium:hover {
    background: #c19b2e;
}

/* Animations */
.fade-in-up {
    animation: fadeInUp 0.6s ease forwards;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<?php include("includes/footer.php"); ?>