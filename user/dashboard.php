<?php
require_once __DIR__ . '/../config.php';
require_role('user');
$pageTitle = 'My Dashboard';
$uid = current_user()['id'];

$q = function ($sql, $params = []) use ($pdo) {
    $st = $pdo->prepare($sql);
    $st->execute($params);
    return $st->fetchColumn();
};
$total     = (int)$q('SELECT COUNT(*) FROM parcels WHERE user_id=?', [$uid]);
$pending   = (int)$q("SELECT COUNT(*) FROM parcels WHERE user_id=? AND status IN ('Pending','Confirmed')", [$uid]);
$transit   = (int)$q("SELECT COUNT(*) FROM parcels WHERE user_id=? AND status IN ('Picked Up','In Transit','Out for Delivery')", [$uid]);
$delivered = (int)$q("SELECT COUNT(*) FROM parcels WHERE user_id=? AND status='Delivered'", [$uid]);
$spent     = (float)$q("SELECT COALESCE(SUM(delivery_charge),0) FROM parcels WHERE user_id=? AND status<>'Cancelled'", [$uid]);
$cod       = (float)$q("SELECT COALESCE(SUM(cod_amount),0) FROM parcels WHERE user_id=? AND status='Delivered' AND payment_method='cod'", [$uid]);

$recent = $pdo->prepare('SELECT * FROM parcels WHERE user_id=? ORDER BY created_at DESC LIMIT 6');
$recent->execute([$uid]);
$recent = $recent->fetchAll();

include __DIR__ . '/../includes/panel_header.php';
?>
<div class="card-box p-4 mb-4 d-md-flex align-items-center justify-content-between" style="background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff">
    <div>
        <h4 class="fw-bold mb-1">Hello, <?= e(current_user()['name']) ?> 👋</h4>
        <p class="mb-0 text-secondary">Ready to send something today? Book a parcel in under two minutes.</p>
    </div>
    <a href="<?= url('user/book.php') ?>" class="btn btn-orange px-4 mt-3 mt-md-0"><i class="bi bi-plus-circle"></i> Book New Parcel</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-icon ic-orange"><i class="bi bi-box-seam"></i></div><div><h4><?= $total ?></h4><small>Total Parcels</small></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-icon ic-blue"><i class="bi bi-hourglass-split"></i></div><div><h4><?= $pending ?></h4><small>Pending / Confirmed</small></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-icon ic-purple"><i class="bi bi-truck"></i></div><div><h4><?= $transit ?></h4><small>On the Way</small></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-icon ic-green"><i class="bi bi-check2-circle"></i></div><div><h4><?= $delivered ?></h4><small>Delivered</small></div></div></div>
    <div class="col-sm-6"><div class="stat-card"><div class="stat-icon ic-red"><i class="bi bi-wallet2"></i></div><div><h4><?= money($spent) ?></h4><small>Total Delivery Charges</small></div></div></div>
    <div class="col-sm-6"><div class="stat-card"><div class="stat-icon ic-teal"><i class="bi bi-cash-coin"></i></div><div><h4><?= money($cod) ?></h4><small>COD Collected for You</small></div></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-box">
            <div class="card-header-x">Recent Parcels <a href="<?= url('user/parcels.php') ?>" class="ms-auto small text-orange text-decoration-none">View all</a></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Tracking No</th><th>Receiver</th><th>Date</th><th>Charge</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($recent as $p): ?>
                        <tr>
                            <td class="fw-semibold"><?= e($p['tracking_no']) ?></td>
                            <td><?= e($p['receiver_name']) ?><br><small class="text-muted"><?= e($p['receiver_city']) ?></small></td>
                            <td><small><?= date('d M Y', strtotime($p['created_at'])) ?></small></td>
                            <td><?= money($p['delivery_charge']) ?></td>
                            <td><?= status_badge($p['status']) ?></td>
                            <td><a href="<?= url('user/view.php?id=' . $p['id']) ?>" class="btn btn-sm btn-outline-orange">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$recent): ?><tr><td colspan="6" class="text-center text-muted py-4">No parcels yet. Book your first one!</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card-box mb-4">
            <div class="card-header-x">Quick Track</div>
            <div class="card-body-x">
                <form action="<?= url('track.php') ?>" method="get">
                    <input type="text" name="tn" class="form-control mb-2" placeholder="Tracking number" required>
                    <button class="btn btn-orange w-100"><i class="bi bi-search"></i> Track</button>
                </form>
            </div>
        </div>
        <div class="card-box">
            <div class="card-header-x">Need Help?</div>
            <div class="card-body-x small text-muted">
                <p><i class="bi bi-telephone text-orange"></i> <?= e(get_setting('company_phone')) ?></p>
                <p><i class="bi bi-envelope text-orange"></i> <?= e(get_setting('company_email')) ?></p>
                <a href="<?= url('contact.php') ?>" class="btn btn-outline-orange btn-sm">Contact Support</a>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/panel_footer.php'; ?>
