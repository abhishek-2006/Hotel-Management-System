<?php 

$ADMIN_ROOT = '/admin';

require_once('includes/config.php');

// 2. ADMIN AUTH GUARD (Uncomment when ready)
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { 
//     header("Location: " . "/auth/login.php"); 
//     exit; 
// }

// 3. FETCH DYNAMIC KPIS
$stats = [
    'bookings' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings"))['count'] ?? 0,
    'rooms'    => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM rooms WHERE status = 'Available'"))['count'] ?? 0,
    'users'    => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'] ?? 0,
    'pending'  => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE status = 'Pending'"))['count'] ?? 0
];
 
include('includes/header.php'); 
?>

<div class="admin-dashboard-container">
    
    <!-- Top Greeting Section -->
    <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 fade-in-up">
        <div>
            <span class="inline-block px-3 py-1 rounded-full bg-admin-primary/10 text-admin-primary dark:text-admin-accent text-[10px] font-extrabold uppercase tracking-widest mb-2">
                System Oversight
            </span>
            <h1 class="text-3xl font-extrabold tracking-tight">Executive Dashboard</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm">Monitoring health and inventory of The Citadel Retreat.</p>
        </div>
        <div class="px-5 py-3 bg-white dark:bg-admin-surface rounded-2xl shadow-sm border border-gray-200/50 dark:border-white/5 flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-admin-primary/10 text-admin-primary flex items-center justify-center">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold opacity-50 uppercase tracking-tighter">Current Date</p>
                <p class="text-sm font-bold"><?= date('l, F d'); ?></p>
            </div>
        </div>
    </header>

    <!-- KPI Metrics Grid -->
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- KPI 1 -->
        <div class="kpi-card group fade-in-up" style="animation-delay: 0.1s">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400">
                    <i class="fas fa-calendar-check text-xl"></i>
                </div>
                <span class="text-[10px] font-bold text-green-500">+12% vs last month</span>
            </div>
            <p class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">Total Bookings</p>
            <h2 class="text-3xl font-black mt-1"><?= number_format($stats['bookings']); ?></h2>
        </div>

        <!-- KPI 2 -->
        <div class="kpi-card group fade-in-up" style="animation-delay: 0.2s">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 rounded-2xl bg-green-500/10 text-green-600 dark:text-green-400">
                    <i class="fas fa-door-open text-xl"></i>
                </div>
                <span class="text-[10px] font-bold text-slate-400">Live Inventory</span>
            </div>
            <p class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">Available Rooms</p>
            <h2 class="text-3xl font-black mt-1"><?= $stats['rooms']; ?></h2>
        </div>

        <!-- KPI 3 -->
        <div class="kpi-card group fade-in-up" style="animation-delay: 0.3s">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 rounded-2xl bg-purple-500/10 text-purple-600 dark:text-purple-400">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <span class="text-[10px] font-bold text-green-500">5 New Today</span>
            </div>
            <p class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">Registered Users</p>
            <h2 class="text-3xl font-black mt-1"><?= number_format($stats['users']); ?></h2>
        </div>

        <!-- KPI 4 -->
        <div class="kpi-card group fade-in-up" style="animation-delay: 0.4s">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 rounded-2xl bg-orange-500/10 text-orange-600 dark:text-orange-400">
                    <i class="fas fa-hourglass-half text-xl"></i>
                </div>
                <span class="text-[10px] font-bold text-orange-500">Action Required</span>
            </div>
            <p class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">Pending Requests</p>
            <h2 class="text-3xl font-black mt-1"><?= $stats['pending']; ?></h2>
        </div>
    </section>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        
        <!-- Management Quick Access -->
        <div class="xl:col-span-2 space-y-6">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-th-large text-admin-primary"></i> Management Hub
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="<?= $ADMIN_ROOT ?>/manage_bookings.php" class="module-link card group">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-slate-100 dark:bg-white/5 flex items-center justify-center group-hover:bg-admin-primary group-hover:text-white transition-all duration-300">
                            <i class="fas fa-concierge-bell text-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-bold">Reservations</h4>
                            <p class="text-xs text-slate-500">Manage room & table stays</p>
                        </div>
                    </div>
                </a>
                <a href="<?= $ADMIN_ROOT ?>/manage_rooms.php" class="module-link card group">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-slate-100 dark:bg-white/5 flex items-center justify-center group-hover:bg-admin-primary group-hover:text-white transition-all duration-300">
                            <i class="fas fa-bed text-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-bold">Inventory</h4>
                            <p class="text-xs text-slate-500">Room categories & pricing</p>
                        </div>
                    </div>
                </a>
                <a href="<?= $ADMIN_ROOT ?>/manage_menu.php" class="module-link card group">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-slate-100 dark:bg-white/5 flex items-center justify-center group-hover:bg-admin-primary group-hover:text-white transition-all duration-300">
                            <i class="fas fa-utensils text-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-bold">Dining Menu</h4>
                            <p class="text-xs text-slate-500">Update Sprout restaurant items</p>
                        </div>
                    </div>
                </a>
                <a href="<?= $ADMIN_ROOT ?>/manage_users.php" class="module-link card group">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-slate-100 dark:bg-white/5 flex items-center justify-center group-hover:bg-admin-primary group-hover:text-white transition-all duration-300">
                            <i class="fas fa-user-shield text-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-bold">Guest Relations</h4>
                            <p class="text-xs text-slate-500">Profiles & access control</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Activity Feed -->
        <aside class="space-y-6">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <i class="fas fa-bolt text-orange-400"></i> Recent Activity
            </h3>
            <div class="card p-0 overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-white/5 flex justify-between items-center bg-slate-50/50 dark:bg-white/5">
                    <span class="text-xs font-bold uppercase tracking-widest opacity-60">Latest Bookings</span>
                    <button class="text-xs font-bold text-admin-primary hover:underline">Refresh</button>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php
                    $recent_query = mysqli_query($conn, "
                        SELECT b.booking_id, u.full_name, b.status, b.created_at 
                        FROM bookings b 
                        JOIN users u ON b.user_id = u.user_id 
                        ORDER BY b.created_at DESC LIMIT 5
                    ");
                    if (mysqli_num_rows($recent_query) > 0):
                        while($act = mysqli_fetch_assoc($recent_query)):
                            $status_color = 'bg-blue-500';
                            if(strtolower($act['status']) == 'pending') $status_color = 'bg-orange-500';
                            if(strtolower($act['status']) == 'cancelled') $status_color = 'bg-red-500';
                            if(strtolower($act['status']) == 'confirmed') $status_color = 'bg-green-500';
                    ?>
                        <div class="p-4 flex gap-4 hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                            <div class="mt-1.5">
                                <div class="h-2 w-2 rounded-full <?= $status_color ?>"></div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-bold leading-tight"><?= htmlspecialchars($act['full_name']); ?></p>
                                <p class="text-[11px] text-slate-500 mt-1">Booking #<?= $act['booking_id'] ?> • <?= $act['status'] ?></p>
                            </div>
                            <div class="text-[10px] font-bold opacity-40 text-right">
                                <?= date('H:i', strtotime($act['created_at'])); ?>
                            </div>
                        </div>
                    <?php endwhile; else: ?>
                        <div class="p-10 text-center text-sm text-slate-400 italic">No recent activity</div>
                    <?php endif; ?>
                </div>
                <a href="<?= $ADMIN_ROOT ?>/reports.php" class="block p-4 text-center text-xs font-bold bg-slate-50 dark:bg-white/5 hover:text-admin-primary transition-colors">
                    View Comprehensive Reports <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </aside>

    </div>
</div>

<style>
    /* Dashboard Specific Styles */
    .kpi-card {
        @apply bg-white dark:bg-admin-surface p-6 rounded-[24px] shadow-sm border border-gray-200/50 dark:border-white/5 transition-all duration-300;
    }
    .module-link {
        @apply no-underline text-inherit block p-6 rounded-[24px] border-2 border-transparent hover:border-admin-primary transition-all duration-300;
    }
    .card {
        @apply bg-white dark:bg-admin-surface rounded-[24px] shadow-sm border border-gray-200/50 dark:border-white/5;
    }
    .fade-in-up {
        animation: fadeInUp 0.6s ease forwards;
        opacity: 0;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<?php 
include('includes/footer.php'); 
?>