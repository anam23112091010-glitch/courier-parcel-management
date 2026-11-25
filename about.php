<?php
require_once __DIR__ . '/config.php';
$pageTitle = 'About Us';
include __DIR__ . '/includes/header.php';
$branches = $pdo->query('SELECT * FROM branches ORDER BY city, name')->fetchAll();
?>
<section class="page-banner text-center">
    <div class="container"><h1>About Us</h1><p class="mb-0 text-light">Delivering trust, one parcel at a time</p></div>
</section>

<section class="section-pad">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=900&q=60" class="img-fluid rounded-4 shadow" alt="Warehouse">
            </div>
            <div class="col-lg-6">
                <h2 class="section-title">Who <span>We Are</span></h2>
                <p class="text-muted"><?= e(get_setting('company_name')) ?> is a Bangladesh-based courier and parcel service built for individuals, online sellers and growing businesses. We started with a handful of riders in Dhaka and today connect customers across the whole country.</p>
                <p class="text-muted">Our in-house management system lets customers book online, admins assign riders in a click and everyone follow each parcel from pickup to doorstep.</p>
                <div class="row g-3 mt-2">
                    <div class="col-6"><div class="feature-card p-3"><h6 class="text-orange mb-1"><i class="bi bi-bullseye"></i> Mission</h6><small class="text-muted">Make parcel delivery simple, fast and affordable for everyone.</small></div></div>
                    <div class="col-6"><div class="feature-card p-3"><h6 class="text-orange mb-1"><i class="bi bi-eye"></i> Vision</h6><small class="text-muted">Be the most trusted logistics partner in Bangladesh.</small></div></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-pad bg-soft">
    <div class="container">
        <div class="text-center mb-5"><h2 class="section-title">Our <span>Branches</span></h2></div>
        <div class="row g-4">
            <?php foreach ($branches as $b): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <h6 class="fw-bold"><i class="bi bi-shop text-orange"></i> <?= e($b['name']) ?></h6>
                        <p class="small text-muted mb-1"><i class="bi bi-geo-alt"></i> <?= e($b['address']) ?></p>
                        <p class="small text-muted mb-0"><i class="bi bi-telephone"></i> <?= e($b['phone']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
