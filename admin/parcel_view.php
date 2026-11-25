<?php
require_once __DIR__ . '/../config.php';
require_role('admin');
$pageTitle = 'Manage Parcel';
$id = (int)($_GET['id'] ?? 0);

$st = $pdo->prepare('SELECT p.*, u.name AS cust, u.email AS cust_email, u.phone AS cust_phone FROM parcels p JOIN users u ON u.id = p.user_id WHERE p.id = ?');
$st->execute([$id]);
$p = $st->fetch();
if (!$p) {
    flash('danger', 'Parcel not found.');
    redirect('admin/parcels.php');
}

$riders   = $pdo->query("SELECT id, name, phone FROM users WHERE role='rider' AND status='active' ORDER BY name")->fetchAll();
$branches = $pdo->query('SELECT id, name, city FROM branches ORDER BY name')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $status  = $_POST['status'] ?? $p['status'];
    $riderId = (int)($_POST['rider_id'] ?? 0) ?: null;
    $branchId = (int)($_POST['branch_id'] ?? 0) ?: null;
    $pay     = ($_POST['payment_status'] ?? 'Unpaid') === 'Paid' ? 'Paid' : 'Unpaid';
    $loc     = trim($_POST['location'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');

    if (!in_array($status, parcel_statuses(), true)) {
        flash('danger', 'Invalid status selected.');
        redirect('admin/parcel_view.php?id=' . $id);
    }
    if ($status === 'Delivered') {
        $pay = 'Paid';
    }

    $statusChanged = $status !== $p['status'];
    $riderChanged  = (int)$riderId !== (int)$p['rider_id'];

    $pdo->prepare('UPDATE parcels SET status=?, rider_id=?, branch_id=?, payment_status=? WHERE id=?')
        ->execute([$status, $riderId, $branchId, $pay, $id]);

    if ($statusChanged || $riderChanged || $remarks !== '') {
        if ($loc === '') {
            $loc = $p['receiver_city'];
            foreach ($branches as $b) {
                if ($b['id'] == $branchId) { $loc = $b['name']; }
            }
        }
        if ($remarks === '') {
            $remarks = $riderChanged && $riderId
                ? 'Parcel assigned to a rider'
                : ($statusChanged ? 'Status updated by admin' : 'Updated');
            if ($riderChanged && $riderId) {
                foreach ($riders as $r) { if ($r['id'] == $riderId) $remarks = 'Parcel assigned to rider ' . $r['name']; }
            }
        }
        add_history($id, $status, $loc, $remarks, current_user()['id']);
    }
    flash('success', 'Parcel updated successfully.');
    redirect('admin/parcel_view.php?id=' . $id);
}

$h = $pdo->prepare('SELECT h.*, u.name AS by_name FROM tracking_history h LEFT JOIN users u ON u.id = h.updated_by WHERE h.parcel_id = ? ORDER BY h.created_at DESC, h.id DESC');
$h->execute([$id]);
$history = $h->fetchAll();

include __DIR__ . '/../includes/panel_header.php';
?>
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="<?= url('admin/parcels.php') ?>" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i> All Parcels</a>
    <a href="<?= url('user/invoice.php?id=' . $id) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-receipt"></i> Invoice</a>
    <a href="<?= url('track.php?tn=' . urlencode($p['tracking_no'])) ?>" class="btn btn-outline-orange btn-sm" target="_blank"><i class="bi bi-geo-alt"></i> Public Tracking</a>
</div>

<div class="card-box p-4 mb-4 d-md-flex justify-content-between align-items-center">
    <div><small class="text-muted">Tracking Number</small><h4 class="fw-bold mb-0"><?= e($p['tracking_no']) ?></h4></div>
    <div class="fs-5 mt-2 mt-md-0"><?= status_badge($p['status']) ?></div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-box mb-4">
            <div class="card-header-x">Update Parcel</div>
            <div class="card-body-x">
                <form method="post">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Status</label>
                            <select name="status" class="form-select"><?php foreach (parcel_statuses() as $x): ?><option <?= $p['status'] === $x ? 'selected' : '' ?>><?= e($x) ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-6"><label class="form-label">Assign Rider</label>
                            <select name="rider_id" class="form-select"><option value="">-- Unassigned --</option>
                                <?php foreach ($riders as $r): ?><option value="<?= $r['id'] ?>" <?= $p['rider_id'] == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?> (<?= e($r['phone']) ?>)</option><?php endforeach; ?></select></div>
                        <div class="col-md-6"><label class="form-label">Current Branch / Hub</label>
                            <select name="branch_id" class="form-select"><option value="">-- None --</option>
                                <?php foreach ($branches as $b): ?><option value="<?= $b['id'] ?>" <?= $p['branch_id'] == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-6"><label class="form-label">Payment Status</label>
                            <select name="payment_status" class="form-select"><option <?= $p['payment_status'] === 'Unpaid' ? 'selected' : '' ?>>Unpaid</option><option <?= $p['payment_status'] === 'Paid' ? 'selected' : '' ?>>Paid</option></select></div>
                        <div class="col-md-6"><label class="form-label">Location <small class="text-muted">(shown in tracking)</small></label><input type="text" name="location" class="form-control" placeholder="e.g. Dhaka Sorting Hub"></div>
                        <div class="col-md-6"><label class="form-label">Remarks</label><input type="text" name="remarks" class="form-control" placeholder="e.g. Departed for Sylhet"></div>
                        <div class="col-12"><button class="btn btn-orange px-4"><i class="bi bi-check2-circle"></i> Save Update</button></div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-6"><div class="card-box h-100"><div class="card-header-x">Sender</div><div class="card-body-x small">
                <b><?= e($p['sender_name']) ?></b><br><?= e($p['sender_phone']) ?><br><span class="text-muted"><?= e($p['sender_address']) ?></span></div></div></div>
            <div class="col-md-6"><div class="card-box h-100"><div class="card-header-x">Receiver</div><div class="card-body-x small">
                <b><?= e($p['receiver_name']) ?></b><br><?= e($p['receiver_phone']) ?><br><span class="text-muted"><?= e($p['receiver_address']) ?>, <?= e($p['receiver_city']) ?></span></div></div></div>
            <div class="col-12"><div class="card-box"><div class="card-header-x">Shipment Details</div><div class="card-body-x"><div class="row small g-3">
                <div class="col-6 col-md-4"><span class="text-muted d-block">Customer</span><b><?= e($p['cust']) ?></b><br><?= e($p['cust_email']) ?></div>
                <div class="col-6 col-md-4"><span class="text-muted d-block">Type / Weight</span><b><?= e($p['parcel_type']) ?> / <?= e($p['weight']) ?> kg</b></div>
                <div class="col-6 col-md-4"><span class="text-muted d-block">Service</span><b><?= e(service_types()[$p['service_type']]['label']) ?></b></div>
                <div class="col-6 col-md-4"><span class="text-muted d-block">Payment</span><b><?= $p['payment_method'] === 'cod' ? 'Cash on Delivery' : 'Prepaid' ?></b></div>
                <div class="col-6 col-md-4"><span class="text-muted d-block">Delivery Charge</span><b><?= money($p['delivery_charge']) ?></b></div>
                <div class="col-6 col-md-4"><span class="text-muted d-block">COD Amount</span><b><?= money($p['cod_amount']) ?></b></div>
                <div class="col-6 col-md-4"><span class="text-muted d-block">Total</span><b class="text-orange"><?= money($p['total_amount']) ?></b></div>
                <div class="col-6 col-md-4"><span class="text-muted d-block">Booked</span><b><?= date('d M Y, h:i A', strtotime($p['created_at'])) ?></b></div>
                <?php if ($p['note']): ?><div class="col-12"><span class="text-muted d-block">Customer Note</span><?= e($p['note']) ?></div><?php endif; ?>
            </div></div></div></div>
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
                        <?php if ($x['by_name']): ?><div class="small text-muted fst-italic">by <?= e($x['by_name']) ?></div><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div></div>
    </div>
</div>
<?php include __DIR__ . '/../includes/panel_footer.php'; ?>
