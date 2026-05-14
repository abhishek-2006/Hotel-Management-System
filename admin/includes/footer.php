</main> <!-- Closing the main tag opened in header.php -->

    <footer class="mt-auto border-t border-gray-200/50 dark:border-white/10 bg-white/80 dark:bg-admin-surface/80 backdrop-blur-xl">
        <div class="max-w-[1600px] mx-auto px-6 py-10">
            <div class="grid grid-cols-1 md:grid-cols-3 items-center gap-8">
                
                <!-- Section 1: Admin Branding -->
                <div class="flex flex-col items-center md:items-start gap-3">
                    <div class="flex items-center gap-3">
                        <a href="/admin/dashboard.php" class="flex items-center gap-3">
                            <img src="assets/images/logo.png" alt="Citadel Logo" class="h-12 w-auto opacity-40 grayscale hover:grayscale-0 transition-all duration-500">
                        </a>
                        <span class="text-[10px] uppercase tracking-[0.2em] font-black text-slate-400 dark:text-slate-500">
                            Admin Portal
                        </span>
                    </div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
                        &copy; <?= date('Y'); ?> <span class="text-slate-900 dark:text-white font-bold">The Citadel Retreat</span>. 
                    </p>
                </div>

                <!-- Section 2: Creator Credit & Links -->
                <div class="flex flex-col items-center gap-4">
                    <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                        Made with <span class="animate-pulse">❤️</span> by 
                        <a href="https://abhishekshah-portfolio.vercel.app" target="_blank" class="text-admin-primary dark:text-admin-accent hover:underline transition-all duration-200">
                            Abhishek Shah
                        </a>
                    </p>
                    <ul class="flex gap-8 text-[10px] uppercase tracking-widest font-extrabold text-slate-400 dark:text-slate-500">
                        <li><a href="#" class="hover:text-admin-primary dark:hover:text-admin-accent transition-all">Manual</a></li>
                        <li><a href="#" class="hover:text-admin-primary dark:hover:text-admin-accent transition-all">Logs</a></li>
                        <li><a href="#" class="hover:text-admin-primary dark:hover:text-admin-accent transition-all">Support</a></li>
                    </ul>
                </div>

                <!-- Section 3: System Health -->
                <div class="flex justify-center md:justify-end">
                    <div class="flex items-center gap-4 px-5 py-2.5 rounded-2xl bg-slate-50 dark:bg-white/5 border border-gray-200/50 dark:border-white/5 shadow-sm">
                        <div class="text-right">
                            <p class="text-[9px] font-black uppercase text-slate-400 dark:text-slate-500 leading-none mb-1">System Health</p>
                            <p class="text-[11px] font-bold text-slate-700 dark:text-slate-300">All Nodes Active</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Global Admin Logic -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Observer for dashboard cards animation
            const observerOptions = { threshold: 0.1 };
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('opacity-100');
                        entry.target.classList.remove('translate-y-4');
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.fade-in-up').forEach(el => observer.observe(el));
        });
    </script>
</body>
</html>