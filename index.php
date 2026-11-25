<?php
require_once __DIR__ . '/config.php';
$pageTitle = 'Fast & Reliable Parcel Delivery';

$totalParcels   = (int)$pdo->query('SELECT COUNT(*) FROM parcels')->fetchColumn();
$totalDelivered = (int)$pdo->query("SELECT COUNT(*) FROM parcels WHERE status='Delivered'")->fetchColumn();
$totalCustomers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$totalBranches  = (int)$pdo->query('SELECT COUNT(*) FROM branches')->fetchColumn();

include __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <span class="badge bg-orange px-3 py-2 mb-3"><i class="bi bi-lightning-charge-fill"></i> Nationwide delivery in 64 districts</span>
                <h1>We Deliver Your Parcel <span class="text-orange">Fast, Safe</span> &amp; On Time</h1>
                <p class="lead mt-3 mb-4">Book a pickup in under two minutes, track every step in real time and get cash on delivery support for your online business.</p>
                <form action="<?= url('track.php') ?>" method="get" class="track-box d-flex">
                    <input type="text" name="tn" class="form-control form-control-lg" placeholder="Enter your tracking number (e.g. CPM-DEMO-0001)" required>
                    <button class="btn btn-orange btn-lg px-4 ms-2"><i class="bi bi-search"></i> Track</button>
                </form>
                <div class="mt-4 d-flex flex-wrap gap-3">
                    <a href="<?= url('user/book.php') ?>" class="btn btn-orange px-4"><i class="bi bi-box-seam"></i> Book a Parcel</a>
                    <a href="<?= url('price.php') ?>" class="btn btn-outline-light px-4"><i class="bi bi-calculator"></i> Check Price</a>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block text-center">
                <i class="bi bi-truck text-orange" style="font-size: 14rem; opacity:.9"></i>
            </div>
        </div>
    </div>
</section>

<!-- Stats -->
<section class="stats-strip">
    <div class="container">
        <div class="row g-3">
            <div class="col-6 col-md-3"><div class="stat-box"><h3 data-count="<?= $totalParcels + 12000 ?>" data-suffix="+">0</h3><small class="text-muted">Parcels Handled</small></div></div>
            <div class="col-6 col-md-3"><div class="stat-box"><h3 data-count="<?= $totalDelivered + 11500 ?>" data-suffix="+">0</h3><small class="text-muted">Delivered Successfully</small></div></div>
            <div class="col-6 col-md-3"><div class="stat-box"><h3 data-count="<?= $totalCustomers + 850 ?>" data-suffix="+">0</h3><small class="text-muted">Happy Customers</small></div></div>
            <div class="col-6 col-md-3"><div class="stat-box"><h3 data-count="<?= $totalBranches + 59 ?>">0</h3><small class="text-muted">Branches &amp; Hubs</small></div></div>
        </div>
    </div>
</section>

<!-- Services -->
<section class="section-pad">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Our <span>Services</span></h2>
            <p class="section-sub mt-2">Whatever you want to send, we have a delivery option that matches your budget and deadline.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3"><div class="feature-card"><div class="icon-circle"><i class="bi bi-truck"></i></div><h5>Standard Delivery</h5><p class="text-muted small mb-0">Affordable delivery to any district within 3-5 working days.</p></div></div>
            <div class="col-md-6 col-lg-3"><div class="feature-card"><div class="icon-circle"><i class="bi bi-lightning-charge"></i></div><h5>Express Delivery</h5><p class="text-muted small mb-0">Priority handling with delivery in just 1-2 working days.</p></div></div>
            <div class="col-md-6 col-lg-3"><div class="feature-card"><div class="icon-circle"><i class="bi bi-stopwatch"></i></div><h5>Same Day Delivery</h5><p class="text-muted small mb-0">Urgent parcels inside Dhaka reach the receiver within 24 hours.</p></div></div>
            <div class="col-md-6 col-lg-3"><div class="feature-card"><div class="icon-circle"><i class="bi bi-cash-coin"></i></div><h5>Cash on Delivery</h5><p class="text-muted small mb-0">We collect the product price from your customer and hand it to you.</p></div></div>
        </div>
        <div class="text-center mt-4"><a href="<?= url('services.php') ?>" class="btn btn-outline-orange px-4">View All Services</a></div>
    </div>
</section>

<!-- How it works -->
<section class="section-pad bg-soft">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">How It <span>Works</span></h2>
            <p class="section-sub mt-2">Sending a parcel is as easy as one, two, three, four.</p>
        </div>
        <div class="row g-4 text-center">
            <div class="col-6 col-lg-3"><div class="step-num">1</div><h6>Create Account</h6><p class="small text-muted">Register for free and open your customer dashboard.</p></div>
            <div class="col-6 col-lg-3"><div class="step-num">2</div><h6>Book Parcel</h6><p class="small text-muted">Fill in sender and receiver details and get an instant price.</p></div>
            <div class="col-6 col-lg-3"><div class="step-num">3</div><h6>We Pick Up</h6><p class="small text-muted">Our rider collects the parcel from your doorstep.</p></div>
            <div class="col-6 col-lg-3"><div class="step-num">4</div><h6>Track &amp; Deliver</h6><p class="small text-muted">Follow the parcel live until it reaches the receiver.</p></div>
        </div>
    </div>
</section>

<!-- Pricing -->
<section class="section-pad">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Simple <span>Pricing</span></h2>
            <p class="section-sub mt-2">Starting prices for a parcel up to 1 kg inside Dhaka. Extra weight and outside-Dhaka charges apply.</p>
        </div>
        <div class="row g-4 justify-content-center">
            <?php
            $plans = [
                ['standard', 'Standard', 'bi-truck', '3-5 days', false],
                ['express', 'Express', 'bi-lightning-charge', '1-2 days', true],
                ['sameday', 'Same Day', 'bi-stopwatch', 'Within 24 hours', false],
            ];
            foreach ($plans as $p): ?>
                <div class="col-md-4">
                    <div class="price-card <?= $p[4] ? 'popular' : '' ?>">
                        <i class="bi <?= $p[2] ?> fs-1 text-orange"></i>
                        <h5 class="mt-2"><?= $p[1] ?></h5>
                        <div class="price my-3"><?= money(get_setting($p[0] . '_base')) ?></div>
                        <p class="text-muted small"><?= $p[3] ?> &middot; +<?= money(get_setting($p[0] . '_perkg')) ?> per extra kg</p>
                        <a href="<?= url('user/book.php') ?>" class="btn <?= $p[4] ? 'btn-orange' : 'btn-outline-orange' ?> w-100">Book Now</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Why us -->
<section class="section-pad bg-soft">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <h2 class="section-title">Why Choose <span>Us?</span></h2>
                <p class="text-muted">We combine a strong rider network with a simple online system so that your parcel never gets lost on the way.</p>
                <ul class="list-unstyled mt-4">
                    <li class="d-flex mb-3"><i class="bi bi-check-circle-fill text-orange fs-4 me-3"></i><div><b>Real-time tracking</b><br><small class="text-muted">Every status change is recorded with time and location.</small></div></li>
                    <li class="d-flex mb-3"><i class="bi bi-check-circle-fill text-orange fs-4 me-3"></i><div><b>Safe &amp; secure handling</b><br><small class="text-muted">Trained riders and careful packaging for fragile items.</small></div></li>
                    <li class="d-flex mb-3"><i class="bi bi-check-circle-fill text-orange fs-4 me-3"></i><div><b>Fast COD settlement</b><br><small class="text-muted">Collected cash is reported clearly in your dashboard.</small></div></li>
                    <li class="d-flex"><i class="bi bi-check-circle-fill text-orange fs-4 me-3"></i><div><b>Friendly support</b><br><small class="text-muted">Call or message us any time from 9 AM to 9 PM.</small></div></li>
                </ul>
            </div>
            <div class="col-lg-6">
                <div class="row g-3">
                    <div class="col-6"><div class="stat-box"><i class="bi bi-shield-check fs-1 text-orange"></i><h6 class="mt-2">100% Secure</h6></div></div>
                    <div class="col-6"><div class="stat-box"><i class="bi bi-geo-alt fs-1 text-orange"></i><h6 class="mt-2">64 Districts</h6></div></div>
                    <div class="col-6"><div class="stat-box"><i class="bi bi-headset fs-1 text-orange"></i><h6 class="mt-2">Daily Support</h6></div></div>
                    <div class="col-6"><div class="stat-box"><i class="bi bi-clock-history fs-1 text-orange"></i><h6 class="mt-2">On-time Delivery</h6></div></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="section-pad">
    <div class="container">
        <div class="text-center mb-5"><h2 class="section-title">What Customers <span>Say</span></h2></div>
        <div class="row g-4">
            <?php
            $reviews = [
                ['Rafiq Islam', 'Online Shop Owner', 'My customers in Chattogram get their orders in one day. The COD report in the dashboard saves me a lot of time.'],
                ['Anika Tabassum', 'Boutique Owner', 'Booking is very easy and the rider always calls before pickup. Tracking page is clear and accurate.'],
                ['Sabbir Hasan', 'Freelancer', 'I send documents to clients every week. Same day delivery inside Dhaka has never failed me.'],
            ];
            foreach ($reviews as $r): ?>
                <div class="col-md-4">
                    <div class="testimonial">
                        <div class="text-warning mb-2"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
                        <p class="text-muted">"<?= e($r[2]) ?>"</p>
                        <div class="d-flex align-items-center mt-3">
                            <span class="avatar me-2"><?= e($r[0][0]) ?></span>
                            <div><b class="d-block small"><?= e($r[0]) ?></b><small class="text-muted"><?= e($r[1]) ?></small></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="pb-5">
    <div class="container">
        <div class="cta-band d-md-flex align-items-center justify-content-between text-center text-md-start">
            <div>
                <h3 class="fw-bold mb-1">Ready to send your first parcel?</h3>
                <p class="mb-3 mb-md-0">Create a free account and get your first booking confirmed today.</p>
            </div>
            <a href="<?= url('register.php') ?>" class="btn btn-light btn-lg px-5 fw-semibold text-orange">Get Started</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
