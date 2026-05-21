        </div> <!-- Close container-fluid -->
        
        <footer class="mt-auto py-4 bg-white border-top">
            <div class="container-fluid px-4">
                <div class="d-flex align-items-center justify-content-between small">
                    <div class="text-muted">&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</div>
                    <div>
                        <a href="#" class="text-decoration-none text-muted">Privacy Policy</a>
                        &middot;
                        <a href="#" class="text-decoration-none text-muted">Terms &amp; Conditions</a>
                    </div>
                </div>
            </div>
        </footer>
    </main> <!-- Close main-content -->

    <button class="scroll-top" id="scrollTop" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (!sidebar) return;
            sidebar.classList.toggle('open');
            if (overlay) overlay.classList.toggle('show');
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (sidebar) sidebar.classList.remove('open');
            if (overlay) overlay.classList.remove('show');
        }

        // Sidebar auto-show on hover (desktop only)
        (function() {
            var sidebar = document.getElementById('sidebar');
            if (!sidebar) return;

            var hoverTimeout;
            var inside = false;

            function isDesktop() {
                return window.innerWidth > 768;
            }

            function openHoverSidebar() {
                if (!isDesktop()) return;
                clearTimeout(hoverTimeout);
                sidebar.classList.add('open');
            }

            function closeHoverSidebar() {
                if (!isDesktop()) return;
                clearTimeout(hoverTimeout);
                hoverTimeout = setTimeout(function() {
                    if (!inside) {
                        sidebar.classList.remove('open');
                    }
                }, 300);
            }

            document.addEventListener('mousemove', function(e) {
                if (!isDesktop()) return;
                if (e.clientX <= 12) {
                    openHoverSidebar();
                }
            });

            sidebar.addEventListener('mouseenter', function() {
                inside = true;
                if (isDesktop()) openHoverSidebar();
            });

            sidebar.addEventListener('mouseleave', function() {
                inside = false;
                closeHoverSidebar();
            });

            // Close sidebar when clicking outside on desktop
            document.addEventListener('click', function(e) {
                if (!isDesktop()) return;
                if (!sidebar.contains(e.target) && !e.target.closest('.menu-toggle')) {
                    sidebar.classList.remove('open');
                }
            });
        })();

        // Scroll to top
        window.onscroll = function() {
            const btn = document.getElementById('scrollTop');
            if (btn) {
                if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
                    btn.classList.add('show');
                } else {
                    btn.classList.remove('show');
                }
            }
        };

        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Initialize tooltips and popovers if any
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });
    </script>
</body>
</html>
