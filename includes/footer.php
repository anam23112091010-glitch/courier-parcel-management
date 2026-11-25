<footer class="site-footer mt-5">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5 class="text-white fw-bold"><i class="bi bi-box-seam-fill text-orange"></i> <?= e(get_setting('company_name', 'SwiftParcel Courier')) ?></h5>
                <p class="mt-3 small">Fast, safe and reliable parcel delivery across Bangladesh. From documents to bulky goods, we deliver to your doorstep with real-time tracking.</p>
                <div class="d-flex gap-3 fs-5 mt-3">
                    <a href="#"><i class="bi bi-facebook"></i></a>
                    <a href="#"><i class="bi bi-twitter-x"></i></a>
                    <a href="#"><i class="bi bi-instagram"></i></a>
                    <a href="#"><i class="bi bi-youtube"></i></a>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="text-white">Quick Links</h6>
                <ul class="list-unstyled small footer-links">
                    <li><a href="<?= url('index.php') ?>">Home</a></li>
                    <li><a href="<?= url('services.php') ?>">Services</a></li>
                    <li><a href="<?= url('price.php') ?>">Pricing</a></li>
                    <li><a href="<?= url('track.php') ?>">Track Parcel</a></li>
                    <li><a href="<?= url('contact.php') ?>">Contact</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="text-white">Account</h6>
                <ul class="list-unstyled small footer-links">
                    <li><a href="<?= url('login.php') ?>">Login</a></li>
                    <li><a href="<?= url('register.php') ?>">Register</a></li>
                    <li><a href="<?= url('user/book.php') ?>">Book a Parcel</a></li>
                    <li><a href="<?= url('about.php') ?>">About Us</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h6 class="text-white">Contact Us</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><i class="bi bi-geo-alt-fill text-orange me-2"></i><?= e(get_setting('company_address', 'Dhaka, Bangladesh')) ?></li>
                    <li class="mb-2"><i class="bi bi-telephone-fill text-orange me-2"></i><?= e(get_setting('company_phone', '+880 1700-000000')) ?></li>
                    <li class="mb-2"><i class="bi bi-envelope-fill text-orange me-2"></i><?= e(get_setting('company_email', 'info@swiftparcel.com')) ?></li>
                    <li><i class="bi bi-clock-fill text-orange me-2"></i>Sat - Thu: 9:00 AM - 9:00 PM</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer-bottom text-center py-3 small">
        &copy; <?= date('Y') ?> <?= e(get_setting('company_name', 'SwiftParcel Courier')) ?>. All rights reserved.
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= url('assets/js/main.js') ?>"></script>
<?php if (!empty($extraJs)) echo $extraJs; ?>
</body>
</html>
