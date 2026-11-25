<?php
require_once __DIR__ . '/config.php';
$pageTitle = 'Our Services';
include __DIR__ . '/includes/header.php';

$services = [
    ['bi-truck', 'Standard Delivery', 'Our most economical option. Ideal for non-urgent parcels, gifts and regular online orders sent anywhere in Bangladesh.', ['3-5 working days', 'Doorstep pickup & delivery', 'Live tracking']],
    ['bi-lightning-charge', 'Express Delivery', 'Priority handling and faster transit between major cities for time-sensitive shipments.', ['1-2 working days', 'Priority sorting', 'SMS-style status updates']],
    ['bi-stopwatch', 'Same Day Delivery', 'Need it there today? Parcels booked before noon inside Dhaka are delivered within 24 hours.', ['Within 24 hours', 'Dedicated rider', 'Dhaka city only']],
    ['bi-cash-coin', 'Cash on Delivery', 'Perfect for e-commerce sellers. We collect the product price from the receiver on your behalf.', ['Small COD fee', 'Transparent reporting', 'Fast settlement']],
    ['bi-file-earmark-text', 'Document Delivery', 'Secure delivery of important papers, certificates, cheques and legal documents.', ['Tamper-proof handling', 'Signature on delivery', 'All districts']],
    ['bi-shield-check', 'Fragile Item Care', 'Extra-careful handling and packaging advice for glassware, electronics and delicate goods.', ['Special handling tag', 'Careful loading', 'Damage protection tips']],
];
?>
<section class="page-banner text-center">
    <div class="container">
        <h1>Our Services</h1>
        <p class="mb-0 text-light">Reliable courier solutions for individuals and businesses</p>
    </div>
</section>

<section class="section-pad">
    <div class="container">
        <div class="row g-4">
            <?php foreach ($services as $s): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="icon-circle"><i class="bi <?= $s[0] ?>"></i></div>
                        <h5><?= e($s[1]) ?></h5>
                        <p class="text-muted small"><?= e($s[2]) ?></p>
                        <ul class="list-unstyled small mb-0">
                            <?php foreach ($s[3] as $f): ?>
                                <li><i class="bi bi-check2-circle text-orange me-1"></i> <?= e($f) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="pb-5">
    <div class="container">
        <div class="cta-band text-center">
            <h3 class="fw-bold">Need a custom plan for your business?</h3>
            <p>Shipping 50+ parcels a week? Talk to us about special corporate rates.</p>
            <a href="<?= url('contact.php') ?>" class="btn btn-light px-5 fw-semibold text-orange">Contact Sales</a>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
