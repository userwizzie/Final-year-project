<?php
// Enhanced footer include for Lost & Found - Kyambogo University

$is_admin_page = strpos($_SERVER['PHP_SELF'] ?? '', '/admin/') !== false;
$base_path = $base_path ?? ($is_admin_page ? '../' : '');
$is_admin_user = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>

                </div>
            </div>
    </div>

    <!-- Footer -->
    <style>
        .footer-custom { background: linear-gradient(135deg, #061230 0%, #0a2b63 100%) !important; border-top: 3px solid #0d6efd; }
        .footer-accent { color: #93c5fd !important; }
        .footer-stat-card { background: rgba(13, 110, 253, 0.2); border: 1px solid rgba(147, 197, 253, 0.25); }
    </style>
    <footer class="footer-custom text-light mt-5 py-5">
        <div class="container">
            <div class="row g-4">
                <!-- University Info -->
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-university fa-2x footer-accent me-3"></i>
                        <h5 class="mb-0 fw-bold">Lost & Found</h5>
                    </div>
                    <h6 class="footer-accent mb-3">Kyambogo University</h6>
                    <p class="mb-3 opacity-75">Helping students and staff reunite with their lost belongings since 2024. Your trusted partner in recovering lost items on campus.</p>
                    <div class="d-flex">
                        <a href="#" class="text-light me-3 fs-5" title="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-light me-3 fs-5" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light me-3 fs-5" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-light fs-5" title="LinkedIn"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="footer-accent mb-3 fw-bold">Quick Links</h6>
                    <ul class="list-unstyled">
                        <?php if ($is_admin_page && $is_admin_user): ?>
                            <li class="mb-2"><a href="dashboard.php" class="text-light text-decoration-none opacity-75 hover-warning"><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</a></li>
                            <li class="mb-2"><a href="verify-claims.php" class="text-light text-decoration-none opacity-75 hover-warning"><i class="fas fa-check-circle me-2"></i>Verify Claims</a></li>
                            <li class="mb-2"><a href="reports.php" class="text-light text-decoration-none opacity-75 hover-warning"><i class="fas fa-chart-bar me-2"></i>Reports</a></li>
                            <li class="mb-2"><a href="view-items.php" class="text-light text-decoration-none opacity-75 hover-warning"><i class="fas fa-boxes me-2"></i>Manage Items</a></li>
                            <li class="mb-2"><a href="manage-users.php" class="text-light text-decoration-none opacity-75 hover-warning"><i class="fas fa-users me-2"></i>Manage Users</a></li>
                        <?php else: ?>
                            <li class="mb-2"><a href="<?php echo $base_path; ?>report-lost.php" class="text-light text-decoration-none opacity-75 hover-warning"><i class="fas fa-exclamation-triangle me-2"></i>Report Lost</a></li>
                            <li class="mb-2"><a href="<?php echo $base_path; ?>report-found.php" class="text-light text-decoration-none opacity-75 hover-warning"><i class="fas fa-search me-2"></i>Report Found</a></li>
                            <li class="mb-2"><a href="<?php echo $base_path; ?>search.php" class="text-light text-decoration-none opacity-75 hover-warning"><i class="fas fa-search-plus me-2"></i>Search Items</a></li>
                            <li class="mb-2"><a href="<?php echo $base_path; ?>my-claims.php" class="text-light text-decoration-none opacity-75 hover-warning"><i class="fas fa-clipboard-list me-2"></i>My Claims</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="footer-accent mb-3 fw-bold">Contact Us</h6>
                    <div class="d-flex align-items-start mb-2">
                        <i class="fas fa-envelope footer-accent me-3 mt-1"></i>
                        <div>
                            <a href="mailto:lostfound@kyu.ac.ug" class="text-light text-decoration-none opacity-75">lostfound@kyu.ac.ug</a>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mb-2">
                        <i class="fas fa-phone footer-accent me-3 mt-1"></i>
                        <div>
                            <a href="tel:+256414320000" class="text-light text-decoration-none opacity-75">+256 414 320 000</a>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mb-2">
                        <i class="fas fa-map-marker-alt footer-accent me-3 mt-1"></i>
                        <div>
                            <span class="text-light opacity-75">Kyambogo University<br>Kampala, Uganda</span>
                        </div>
                    </div>
                </div>

                <!-- System Stats -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="footer-accent mb-3 fw-bold">System Stats</h6>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="footer-stat-card rounded p-2 text-center">
                                <div class="h5 mb-0 footer-accent">500+</div>
                                <small class="text-light opacity-75">Items Found</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="footer-stat-card rounded p-2 text-center">
                                <div class="h5 mb-0 footer-accent">300+</div>
                                <small class="text-light opacity-75">Items Claimed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4" style="border-color: rgba(147,197,253,0.25);">

            <!-- Bottom Section -->
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 opacity-75">
                        <i class="fas fa-copyright me-2"></i>&copy; 2026 Kyambogo University. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 opacity-75">
                        <i class="fas fa-code me-2"></i>Developed by: BSc. Computer Science &mdash; 2026
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="<?php echo $base_path; ?>assets/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS for enhancements -->
    <script>
        // Sticky navbar on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('shadow-lg');
            } else {
                navbar.classList.remove('shadow-lg');
            }
        });

        // Loading spinner for forms
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
                        submitBtn.disabled = true;
                    }
                });
            });
        });

        // Alert auto-dismiss
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });

        // Add hover effect for links
        document.querySelectorAll('.hover-warning').forEach(link => {
            link.addEventListener('mouseenter', function() {
                this.classList.remove('opacity-75');
                this.style.color = '#93c5fd';
            });
            link.addEventListener('mouseleave', function() {
                this.classList.add('opacity-75');
                this.style.color = '';
            });
        });

    </script>
</body>
</html>