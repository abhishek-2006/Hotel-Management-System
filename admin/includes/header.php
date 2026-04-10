<?php 

if (!isset($PROJECT_ROOT)) {
    $PROJECT_ROOT = '/Hotel%20Management%20system';
}

require_once('config.php');

$currentPage = basename($_SERVER['PHP_SELF']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Command | The Citadel Retreat</title>
    
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">

    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (via CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            darkMode: 'media',
            theme: {
                extend: {
                    colors: {
                        admin: {
                            primary: '#4318FF',
                            dark: '#0b1437',
                            surface: '#111c44',
                            accent: '#7551FF'
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    }
                }
            }
        }
    </script>

    <style>
        /* Custom Smooth Transitions */
        .nav-link-active {
            color: #4318FF !important;
            font-weight: 700;
        }
        
        @media (prefers-color-scheme: dark) {
            .nav-link-active {
                color: #7551FF !important;
            }
            body { background-color: #0b1437; color: white; }
        }

        .glass-header {
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .active-indicator {
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background-color: currentColor;
        }
    </style>
</head>
<body class="bg-[#f4f7fe] dark:bg-admin-dark text-slate-900 dark:text-white transition-colors duration-300">

    <header class="glass-header sticky top-0 z-50 w-full border-b border-gray-200/50 dark:border-white/10 bg-white/70 dark:bg-admin-surface/70">
        <div class="max-w-[1600px] mx-auto px-6 h-20 flex justify-between items-center">
            
            <!-- Branding / Logo -->
            <div class="flex items-center gap-4">
                <a href="<?= $PROJECT_ROOT ?>/admin/dashboard.php" class="flex items-center gap-3">
                    <a href="<?= $PROJECT_ROOT ?>/admin/dashboard.php" class="h-12 w-12 rounded-lg flex items-center justify-center">
                        <img src="assets/images/logo.png" alt="Citadel Logo" class="h-12 w-12 object-contain">
                    </a>
                    <div class="hidden md:block">
                        <h1 class="text-lg font-extrabold tracking-tight leading-none text-slate-800 dark:text-white">
                            CITADEL <span class="text-admin-primary dark:text-admin-accent">ADMIN</span>
                        </h1>
                        <p class="text-[10px] uppercase tracking-widest font-bold opacity-50">Command Center</p>
                    </div>
                </a>
            </div>

            <!-- Main Navigation -->
            <nav class="hidden lg:block">
                <ul class="flex items-center gap-8">
                    <?php 
                    $navItems = [
                        ['Dashboard', 'dashboard.php', 'fa-chart-pie'],
                        ['Rooms', 'rooms.php', 'fa-bed'],
                        ['Bookings', 'bookings.php', 'fa-calendar-check'],
                        ['Users', 'users.php', 'fa-user-shield'],
                        ['Settings', 'settings.php', 'fa-cog']
                    ];
                    foreach($navItems as $item): 
                        $isActive = ($currentPage == $item[1]);
                    ?>
                    <li class="relative group">
                        <a href="<?= $PROJECT_ROOT ?>/admin/<?= $item[1] ?>" 
                           class="flex items-center gap-2 text-sm font-semibold transition-all duration-200 <?= $isActive ? 'text-admin-primary dark:text-admin-accent' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white' ?>">
                            <i class="fas <?= $item[2] ?> text-xs opacity-70"></i>
                            <?= $item[0] ?>
                            <?php if($isActive): ?>
                                <span class="active-indicator"></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <!-- User Area -->
            <div class="flex items-center gap-5 border-l border-gray-200 dark:border-white/10 pl-5">
                <div class="hidden sm:block text-right">
                    <p class="text-xs font-bold opacity-50 uppercase tracking-tighter">Session Admin</p>
                    <p class="text-sm font-bold"><?= htmlspecialchars($_SESSION['full_name'] ?? 'System Manager') ?></p>
                </div>
                
                <!-- Logout Button -->
                <a href="<?= $PROJECT_ROOT ?>/logout.php" 
                   class="h-10 w-10 flex items-center justify-center rounded-xl bg-red-50 text-red-600 hover:bg-red-600 hover:text-white dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500 transition-all duration-300 shadow-sm"
                   title="Logout from System">
                    <i class="fas fa-sign-out-alt"></i>
                </a>

                <!-- Mobile Menu Trigger -->
                <button class="lg:hidden h-10 w-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-white/5">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <main class="max-w-[1600px] mx-auto p-6 min-h-[calc(100vh-80px)]">