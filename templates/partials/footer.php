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

    <!-- Screensaver Overlay -->
    <div id="screensaver">
        <div id="screensaver-container"></div>
        <div class="screensaver-text">Dairy Management System - Relaxing...</div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Screensaver Logic
        const images = <?php echo json_encode(glob("assets/images/*.{jpg,jpeg,png,gif}", GLOB_BRACE)); ?>;
        let idleTime = 0;
        const idleLimit = 60; // 60 seconds of inactivity
        let screensaverActive = false;
        let currentImageIndex = 0;
        let slideshowInterval;

        function resetIdleTimer() {
            if (screensaverActive) {
                stopScreensaver();
            }
            idleTime = 0;
        }

        // Increment the idle time counter every second
        setInterval(function() {
            idleTime++;
            if (idleTime >= idleLimit && !screensaverActive) {
                startScreensaver();
            }
        }, 1000);

        // Events to reset the idle timer
        ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(evt => {
            document.addEventListener(evt, resetIdleTimer, true);
        });

        function startScreensaver() {
            if (images.length === 0) return;
            screensaverActive = true;
            const ss = document.getElementById('screensaver');
            ss.style.display = 'block';
            
            showNextImage();
            slideshowInterval = setInterval(showNextImage, 5000); // Change image every 5 seconds
        }

        function stopScreensaver() {
            screensaverActive = false;
            const ss = document.getElementById('screensaver');
            ss.style.display = 'none';
            clearInterval(slideshowInterval);
            document.getElementById('screensaver-container').innerHTML = '';
        }

        function showNextImage() {
            const container = document.getElementById('screensaver-container');
            const imgPath = images[currentImageIndex];
            
            const img = document.createElement('img');
            img.src = imgPath;
            img.className = 'screensaver-image';
            
            container.appendChild(img);
            
            // Trigger animation
            setTimeout(() => {
                img.classList.add('active');
            }, 100);

            // Remove old images
            if (container.children.length > 1) {
                const oldImg = container.children[0];
                oldImg.classList.add('fade-out');
                setTimeout(() => {
                    if (oldImg.parentNode === container) {
                        container.removeChild(oldImg);
                    }
                }, 2000);
            }

            currentImageIndex = (currentImageIndex + 1) % images.length;
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (sidebar) sidebar.classList.add('open');
            if (overlay) overlay.classList.add('show');
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (sidebar) sidebar.classList.remove('open');
            if (overlay) overlay.classList.remove('show');
        }

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
