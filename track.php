<?php
require_once __DIR__ . '/config.php';
$pageTitle = 'Track Your Parcel';

$tn = trim($_GET['tn'] ?? '');
$parcel = null;
$history = [];
if ($tn !== '') {
    $st = $pdo->prepare('SELECT p.*, r.name AS rider_name, r.phone AS rider_phone
                         FROM parcels p LEFT JOIN users r ON r.id = p.rider_id
                         WHERE p.tracking_no = ?');
    $st->execute([$tn]);
    $parcel = $st->fetch();
    if ($parcel) {
        $h = $pdo->prepare('SELECT * FROM tracking_history WHERE parcel_id = ? ORDER BY created_at DESC, id DESC');
        $h->execute([$parcel['id']]);
        $history = $h->fetchAll();
    }
}

$steps = [
    ['Pending', 'bi-clipboard-check', 'Booked'],
    ['Confirmed', 'bi-patch-check', 'Confirmed'],
    ['Picked Up', 'bi-box-seam', 'Picked Up'],
    ['In Transit', 'bi-truck', 'In Transit'],
    ['Out for Delivery', 'bi-bicycle', 'Out for Delivery'],
    ['Delivered', 'bi-house-check', 'Delivered'],
];
$order = array_column($steps, 0);

include __DIR__ . '/includes/header.php';
?>
<section class="page-banner text-center">
    <div class="container">
        <h1>Track Your Parcel</h1>
        <form method="get" class="track-box d-flex mx-auto mt-4" style="max-width:640px">
            <input type="text" name="tn" value="<?= e($tn) ?>" class="form-control form-control-lg" placeholder="Enter tracking number" required>
            <button class="btn btn-orange btn-lg px-4 ms-2"><i class="bi bi-search"></i> Track</button>
        </form>
        <small class="d-block mt-2 text-secondary">Try demo numbers: CPM-DEMO-0001 &middot; CPM-DEMO-0002 &middot; CPM-DEMO-0003</small>
    </div>
</section>

<section class="section-pad pt-5">
    <div class="container">
    <?php if ($tn !== '' && !$parcel): ?>
        <div class="alert alert-danger text-center"><i class="bi bi-exclamation-triangle"></i> No parcel found for tracking number <b><?= e($tn) ?></b>. Please check and try again.</div>
    <?php elseif ($parcel): ?>
        <?php $cur = array_search($parcel['status'], $order); ?>
        <div class="card-box p-4 mb-4">
            <div class="d-md-flex justify-content-between align-items-center mb-3">
                <div>
                    <small class="text-muted">Tracking Number</small>
                    <h4 class="fw-bold mb-0"><?= e($parcel['tracking_no']) ?></h4>
                </div>
                <div class="mt-2 mt-md-0 fs-5"><?= status_badge($parcel['status']) ?></div>
            </div>

            <?php if (in_array($parcel['status'], ['Cancelled', 'Returned'])): ?>
                <div class="alert alert-<?= $parcel['status'] === 'Cancelled' ? 'danger' : 'secondary' ?> mb-0">
                    This parcel has been <b><?= e(strtolower($parcel['status'])) ?></b>.
                </div>
            <?php else: ?>
                <div class="progress-steps">
                    <?php foreach ($steps as $i => $s): ?>
                        <div class="p-step <?= ($cur !== false && $i <= $cur) ? 'done' : '' ?>">
                            <div class="dot"><i class="bi <?= $s[1] ?>"></i></div><?= e($s[2]) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card-box"><div class="card-header-x">Parcel Information</div>
                    <div class="card-body-x">
                        <table class="table table-sm table-borderless mb-0 small">
                            <tr><td class="text-muted">From</td><td class="fw-semibold"><?= e($parcel['sender_name']) ?></td></tr>
                            <tr><td class="text-muted">To</td><td class="fw-semibold"><?= e($parcel['receiver_name']) ?>, <?= e($parcel['receiver_city']) ?></td></tr>
                            <tr><td class="text-muted">Type</td><td><?= e($parcel['parcel_type']) ?> (<?= e($parcel['weight']) ?> kg)</td></tr>
                            <tr><td class="text-muted">Service</td><td><?= e(service_types()[$parcel['service_type']]['label']) ?></td></tr>
                            <tr><td class="text-muted">Payment</td><td><?= $parcel['payment_method'] === 'cod' ? 'Cash on Delivery' : 'Prepaid' ?></td></tr>
                            <tr><td class="text-muted">Booked on</td><td><?= date('d M Y, h:i A', strtotime($parcel['created_at'])) ?></td></tr>
                            <?php if ($parcel['rider_name']): ?>
                                <tr><td class="text-muted">Rider</td><td><?= e($parcel['rider_name']) ?> <small class="text-muted">(<?= e($parcel['rider_phone']) ?>)</small></td></tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card-box"><div class="card-header-x">Tracking History</div>
                    <div class="card-body-x">
                        <div class="timeline">
                            <?php foreach ($history as $h): ?>
                                <div class="timeline-item">
                                    <div class="d-flex justify-content-between flex-wrap">
                                        <b><?= e($h['status']) ?></b>
                                        <small class="text-muted"><?= date('d M Y, h:i A', strtotime($h['created_at'])) ?></small>
                                    </div>
                                    <div class="small text-muted"><i class="bi bi-geo-alt"></i> <?= e($h['location']) ?></div>
                                    <?php if ($h['remarks']): ?><div class="small"><?= e($h['remarks']) ?></div><?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-geo-alt display-1 text-orange"></i>
            <h5 class="mt-3">Enter your tracking number above to see where your parcel is.</h5>
        </div>
    <?php endif; ?>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
