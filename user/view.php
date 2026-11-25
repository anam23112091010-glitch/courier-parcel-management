<?php
require_once __DIR__ . '/../config.php';
require_role('user');
$pageTitle = 'Parcel Details';
$uid = current_user()['id'];
$id  = (int)($_GET['id'] ?? 0);

$st = $pdo->prepare('SELECT p.*, r.name AS rider_name, r.phone AS rider_phone, b.name AS branch_name
                     FROM parcels p
                     LEFT JOIN users r ON r.id = p.rider_id
                     LEFT JOIN branches b ON b.id = p.branch_id
                     WHERE p.id = ? AND p.user_id = ?');
$st->execute([$id, $uid]);
$p = $st->fetch();
if (!$p) {
    flash('danger', 'Parcel not found.');
    redirect('user/parcels.php');
}
$h = $pdo->prepare('SELECT * FROM tracking_history WHERE parcel_id = ? ORDER BY created_at DESC, id DESC');
$h->execute([$id]);
$history = $h->fetchAll();
$canCancel = in_array($p['status'], ['Pending', 'Confirmed'], true);

include __DIR__ . '/../includes/panel_header.php';
?>
<div class="d-flex flex-wrap gap-2 mb-3 no-print">
    <a href="<?= url('user/parcels.php') ?>" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
    <a href="<?= url('user/invoice.php?id=' . $p['id']) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-receipt"></i> Invoice</a>
    <a href="<?= url('track.php?tn=' . urlencode($p['tracking_no'])) ?>" class="btn btn-outline-orange btn-sm"><i class="bi bi-geo-alt"></i> Public Tracking</a>
    <?php if ($canCancel): ?>
        <form method="post" action="<?= url('user/cancel.php') ?>" data-confirm="Are you sure you want to cancel this booking?" class="ms-auto">
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= $p['id'] ?>">
            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-x-circle"></i> Cancel Booking</button>
        </form>
    <?php endif; ?>
</div>

<div class="card-box p-4 mb-4 d-md-flex justify-content-between align-items-center">
    <div><small class="text-muted">Tracking Number</small><h4 class="fw-bold mb-0"><?= e($p['tracking_no']) ?></h4></div>
    <div class="fs-5 mt-2 mt-md-0"><?= status_badge($p['status']) ?></div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="row g-4">
            <div class="col-md-6"><div class="card-box h-100"><div class="card-header-x">Sender</div><div class="card-body-x small">
                <b><?= e($p['sender_name']) ?></b><br><?= e($p['sender_phone']) ?><br><span class="text-muted"><?= e($p['sender_address']) ?></span></div></div></div>
            <div class="col-md-6"><div class="card-box h-100"><div class="card-header-x">Receiver</div><div class="card-body-x small">
                <b><?= e($p['receiver_name']) ?></b><br><?= e($p['receiver_phone']) ?><br><span class="text-muted"><?= e($p['receiver_address']) ?>, <?= e($p['receiver_city']) ?></span></div></div></div>
            <div class="col-12"><div class="card-box"><div class="card-header-x">Shipment &amp; Payment</div><div class="card-body-x">
                <div class="row small g-3">
                    <div class="col-6 col-md-4"><span class="text-muted d-block">Parcel Type</span><b><?= e($p['parcel_type']) ?></b></div>
                    <div class="col-6 col-md-4"><span class="text-muted d-block">Weight</span><b><?= e($p['weight']) ?> kg</b></div>
                    <div class="col-6 col-md-4"><span class="text-muted d-block">Service</span><b><?= e(service_types()[$p['service_type']]['label']) ?></b></div>
                    <div class="col-6 col-md-4"><span class="text-muted d-block">Payment Method</span><b><?= $p['payment_method'] === 'cod' ? 'Cash on Delivery' : 'Prepaid' ?></b></div>
                    <div class="col-6 col-md-4"><span class="text-muted d-block">Delivery Charge</span><b><?= money($p['delivery_charge']) ?></b></div>
                    <div class="col-6 col-md-4"><span class="text-muted d-block">COD Amount</span><b><?= money($p['cod_amount']) ?></b></div>
                    <div class="col-6 col-md-4"><span class="text-muted d-block">Total</span><b class="text-orange"><?= money($p['total_amount']) ?></b></div>
                    <div class="col-6 col-md-4"><span class="text-muted d-block">Payment Status</span><span class="badge bg-<?= $p['payment_status'] === 'Paid' ? 'success' : 'warning' ?>"><?= e($p['payment_status']) ?></span></div>
                    <div class="col-6 col-md-4"><span class="text-muted d-block">Booked On</span><b><?= date('d M Y, h:i A', strtotime($p['created_at'])) ?></b></div>
                    <?php if ($p['rider_name']): ?>
                        <div class="col-12"><span class="text-muted d-block">Assigned Rider</span><b><?= e($p['rider_name']) ?></b> &middot; <?= e($p['rider_phone']) ?></div>
                    <?php endif; ?>
                    <?php if ($p['note']): ?><div class="col-12"><span class="text-muted d-block">Note</span><?= e($p['note']) ?></div><?php endif; ?>
                </div>
            </div></div></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card-box"><div class="card-header-x">Tracking History</div><div class="card-body-x">
            <div class="timeline">
                <?php foreach ($history as $x): ?>
                    <div class="timeline-item">
                        <div class="d-flex justify-content-between flex-wrap"><b><?= e($x['status']) ?></b><small class="text-muted"><?= date('d M, h:i A', strtotime($x['created_at'])) ?></small></div>
                        <div class="small text-muted"><i class="bi bi-geo-alt"></i> <?= e($x['location']) ?></div>
                        <?php if ($x['remarks']): ?><div class="small"><?= e($x['remarks']) ?></div><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div></div>
    </div>
</div>
<?php include __DIR__ . '/../includes/panel_footer.php'; ?>
