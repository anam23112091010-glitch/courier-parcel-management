<?php
require_once __DIR__ . '/../config.php';
require_role('rider');
$pageTitle = 'My Deliveries';
$rid = current_user()['id'];

$q = function ($sql) use ($pdo, $rid) {
    $st = $pdo->prepare($sql);
    $st->execute([$rid]);
    return $st->fetchColumn();
};
$active    = (int)$q("SELECT COUNT(*) FROM parcels WHERE rider_id=? AND status IN ('Confirmed','Picked Up','In Transit','Out for Delivery')");
$today     = (int)$q("SELECT COUNT(*) FROM parcels WHERE rider_id=? AND status='Delivered' AND DATE(updated_at)=CURDATE()");
$delivered = (int)$q("SELECT COUNT(*) FROM parcels WHERE rider_id=? AND status='Delivered'");
$codToday  = (float)$q("SELECT COALESCE(SUM(cod_amount),0) FROM parcels WHERE rider_id=? AND status='Delivered' AND payment_method='cod' AND DATE(updated_at)=CURDATE()");

$st = $pdo->prepare("SELECT * FROM parcels WHERE rider_id = ? AND status IN ('Confirmed','Picked Up','In Transit','Out for Delivery')
                     ORDER BY FIELD(status,'Out for Delivery','In Transit','Picked Up','Confirmed'), created_at");
$st->execute([$rid]);
$jobs = $st->fetchAll();

include __DIR__ . '/../includes/panel_header.php';
?>
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-icon ic-orange"><i class="bi bi-list-task"></i></div><div><h4><?= $active ?></h4><small>Active Jobs</small></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-icon ic-green"><i class="bi bi-check2-circle"></i></div><div><h4><?= $today ?></h4><small>Delivered Today</small></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-icon ic-blue"><i class="bi bi-trophy"></i></div><div><h4><?= $delivered ?></h4><small>Total Delivered</small></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-icon ic-teal"><i class="bi bi-cash-coin"></i></div><div><h4><?= money($codToday) ?></h4><small>COD Collected Today</small></div></div></div>
</div>

<h5 class="fw-bold mb-3">Assigned Parcels</h5>
<?php if (!$jobs): ?>
    <div class="card-box p-5 text-center text-muted"><i class="bi bi-emoji-smile display-4 text-orange"></i><h6 class="mt-3">No active deliveries right now. Great job!</h6></div>
<?php endif; ?>
<div class="row g-4">
<?php foreach ($jobs as $p): ?>
    <div class="col-lg-6">
        <div class="card-box h-100">
            <div class="card-header-x"><?= e($p['tracking_no']) ?> <span class="ms-auto"><?= status_badge($p['status']) ?></span></div>
            <div class="card-body-x">
                <div class="row small g-3 mb-3">
                    <div class="col-6"><span class="text-muted d-block"><i class="bi bi-person-up"></i> Pickup</span><b><?= e($p['sender_name']) ?></b><br><a href="tel:<?= e($p['sender_phone']) ?>"><?= e($p['sender_phone']) ?></a><br><span class="text-muted"><?= e($p['sender_address']) ?></span></div>
                    <div class="col-6"><span class="text-muted d-block"><i class="bi bi-person-down"></i> Deliver to</span><b><?= e($p['receiver_name']) ?></b><br><a href="tel:<?= e($p['receiver_phone']) ?>"><?= e($p['receiver_phone']) ?></a><br><span class="text-muted"><?= e($p['receiver_address']) ?>, <?= e($p['receiver_city']) ?></span></div>
                </div>
                <div class="d-flex flex-wrap gap-2 mb-3 small">
                    <span class="badge bg-light text-dark border"><?= e($p['parcel_type']) ?> &middot; <?= e($p['weight']) ?> kg</span>
                    <span class="badge bg-light text-dark border"><?= e(service_types()[$p['service_type']]['label']) ?></span>
                    <?php if ($p['payment_method'] === 'cod'): ?>
                        <span class="badge bg-danger">Collect <?= money($p['cod_amount']) ?></span>
                    <?php else: ?>
                        <span class="badge bg-success">Prepaid</span>
                    <?php endif; ?>
                </div>
                <?php if ($p['note']): ?><div class="alert alert-warning py-2 small"><i class="bi bi-sticky"></i> <?= e($p['note']) ?></div><?php endif; ?>

                <form method="post" action="<?= url('rider/update.php') ?>" class="row g-2">
                    <?= csrf_field() ?><input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <div class="col-md-5">
                        <select name="status" class="form-select form-select-sm" required>
                            <option value="">Update status...</option>
                            <option>Picked Up</option>
                            <option>In Transit</option>
                            <option>Out for Delivery</option>
                            <option>Delivered</option>
                            <option>Returned</option>
                        </select>
                    </div>
                    <div class="col-md-4"><input type="text" name="location" class="form-control form-control-sm" placeholder="Location"></div>
                    <div class="col-md-3"><button class="btn btn-sm btn-orange w-100">Update</button></div>
                    <div class="col-12"><input type="text" name="remarks" class="form-control form-control-sm" placeholder="Remarks (optional)"></div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php include __DIR__ . '/../includes/panel_footer.php'; ?>
