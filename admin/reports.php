<?php
require_once __DIR__ . '/../config.php';
require_role('admin');
$pageTitle = 'Reports';

$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = date('Y-m-01');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   $to = date('Y-m-d');

$range = 'DATE(p.created_at) BETWEEN ? AND ?';
$prm = [$from, $to];

// CSV export
if (isset($_GET['export'])) {
    $st = $pdo->prepare("SELECT p.tracking_no, u.name AS customer, p.receiver_name, p.receiver_city, p.service_type, p.weight, p.payment_method,
                                p.cod_amount, p.delivery_charge, p.total_amount, p.payment_status, p.status, p.created_at
                         FROM parcels p JOIN users u ON u.id = p.user_id WHERE $range ORDER BY p.created_at");
    $st->execute($prm);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="parcel-report-' . $from . '-to-' . $to . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['Tracking', 'Customer', 'Receiver', 'City', 'Service', 'Weight(kg)', 'Payment', 'COD', 'Delivery Charge', 'Total', 'Payment Status', 'Status', 'Booked At']);
    while ($r = $st->fetch(PDO::FETCH_NUM)) fputcsv($out, $r);
    fclose($out);
    exit;
}

$q = function ($sql) use ($pdo, $prm) {
    $st = $pdo->prepare($sql);
    $st->execute($prm);
    return $st->fetchAll();
};
$sum = $q("SELECT COUNT(*) total, SUM(p.status='Delivered') delivered, SUM(p.status='Cancelled') cancelled,
                  COALESCE(SUM(CASE WHEN p.status<>'Cancelled' THEN p.delivery_charge END),0) revenue,
                  COALESCE(SUM(CASE WHEN p.status='Delivered' AND p.payment_method='cod' THEN p.cod_amount END),0) cod
           FROM parcels p WHERE $range")[0];
$byStatus  = $q("SELECT p.status, COUNT(*) c, COALESCE(SUM(p.delivery_charge),0) amt FROM parcels p WHERE $range GROUP BY p.status ORDER BY c DESC");
$byService = $q("SELECT p.service_type, COUNT(*) c, COALESCE(SUM(p.delivery_charge),0) amt FROM parcels p WHERE $range AND p.status<>'Cancelled' GROUP BY p.service_type");
$byCity    = $q("SELECT p.receiver_city, COUNT(*) c, COALESCE(SUM(p.delivery_charge),0) amt FROM parcels p WHERE $range AND p.status<>'Cancelled' GROUP BY p.receiver_city ORDER BY c DESC LIMIT 8");
$daily     = $q("SELECT DATE(p.created_at) d, COUNT(*) c, COALESCE(SUM(CASE WHEN p.status<>'Cancelled' THEN p.delivery_charge END),0) amt FROM parcels p WHERE $range GROUP BY d ORDER BY d DESC");
$riderPerf = $q("SELECT u.name, COUNT(*) c FROM parcels p JOIN users u ON u.id = p.rider_id WHERE $range AND p.status='Delivered' GROUP BY u.id ORDER BY c DESC");

include __DIR__ . '/../includes/panel_header.php';
?>
<div class="card-box mb-4 no-print">
    <div class="card-body-x">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3"><label class="form-label small mb-1">From</label><input type="date" name="from" value="<?= e($from) ?>" class="form-control"></div>
            <div class="col-md-3"><label class="form-label small mb-1">To</label><input type="date" name="to" value="<?= e($to) ?>" class="form-control"></div>
            <div class="col-md-6 d-flex gap-2 flex-wrap">
                <button class="btn btn-orange"><i class="bi bi-funnel"></i> Generate</button>
                <a class="btn btn-outline-success" href="?from=<?= e($from) ?>&to=<?= e($to) ?>&export=1"><i class="bi bi-file-earmark-excel"></i> Export CSV</a>
                <button type="button" onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer"></i> Print</button>
            </div>
        </form>
    </div>
</div>

<h6 class="text-muted mb-3">Report period: <b><?= date('d M Y', strtotime($from)) ?></b> to <b><?= date('d M Y', strtotime($to)) ?></b></h6>
<div class="row g-3 mb-4">
    <div class="col-6 col-xl"><div class="stat-card"><div class="stat-icon ic-orange"><i class="bi bi-box-seam"></i></div><div><h4><?= (int)$sum['total'] ?></h4><small>Total Parcels</small></div></div></div>
    <div class="col-6 col-xl"><div class="stat-card"><div class="stat-icon ic-green"><i class="bi bi-check2-circle"></i></div><div><h4><?= (int)$sum['delivered'] ?></h4><small>Delivered</small></div></div></div>
    <div class="col-6 col-xl"><div class="stat-card"><div class="stat-icon ic-red"><i class="bi bi-x-circle"></i></div><div><h4><?= (int)$sum['cancelled'] ?></h4><small>Cancelled</small></div></div></div>
    <div class="col-6 col-xl"><div class="stat-card"><div class="stat-icon ic-teal"><i class="bi bi-cash-stack"></i></div><div><h4><?= money($sum['revenue']) ?></h4><small>Revenue</small></div></div></div>
    <div class="col-12 col-xl"><div class="stat-card"><div class="stat-icon ic-purple"><i class="bi bi-cash-coin"></i></div><div><h4><?= money($sum['cod']) ?></h4><small>COD Collected</small></div></div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4"><div class="card-box h-100"><div class="card-header-x">By Status</div>
        <table class="table mb-0"><thead><tr><th>Status</th><th>Parcels</th><th class="text-end">Amount</th></tr></thead><tbody>
            <?php foreach ($byStatus as $r): ?><tr><td><?= status_badge($r['status']) ?></td><td><?= (int)$r['c'] ?></td><td class="text-end"><?= money($r['amt']) ?></td></tr><?php endforeach; ?>
            <?php if (!$byStatus): ?><tr><td colspan="3" class="text-center text-muted">No data</td></tr><?php endif; ?>
        </tbody></table></div></div>
    <div class="col-lg-4"><div class="card-box h-100"><div class="card-header-x">By Service</div>
        <table class="table mb-0"><thead><tr><th>Service</th><th>Parcels</th><th class="text-end">Revenue</th></tr></thead><tbody>
            <?php foreach ($byService as $r): ?><tr><td><?= e(service_types()[$r['service_type']]['label']) ?></td><td><?= (int)$r['c'] ?></td><td class="text-end"><?= money($r['amt']) ?></td></tr><?php endforeach; ?>
            <?php if (!$byService): ?><tr><td colspan="3" class="text-center text-muted">No data</td></tr><?php endif; ?>
        </tbody></table></div></div>
    <div class="col-lg-4"><div class="card-box h-100"><div class="card-header-x">Top Destinations</div>
        <table class="table mb-0"><thead><tr><th>City</th><th>Parcels</th><th class="text-end">Revenue</th></tr></thead><tbody>
            <?php foreach ($byCity as $r): ?><tr><td><?= e($r['receiver_city']) ?></td><td><?= (int)$r['c'] ?></td><td class="text-end"><?= money($r['amt']) ?></td></tr><?php endforeach; ?>
            <?php if (!$byCity): ?><tr><td colspan="3" class="text-center text-muted">No data</td></tr><?php endif; ?>
        </tbody></table></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-8"><div class="card-box"><div class="card-header-x">Daily Summary</div>
        <div class="table-responsive"><table class="table mb-0"><thead><tr><th>Date</th><th>Bookings</th><th class="text-end">Revenue</th></tr></thead><tbody>
            <?php foreach ($daily as $r): ?><tr><td><?= date('D, d M Y', strtotime($r['d'])) ?></td><td><?= (int)$r['c'] ?></td><td class="text-end"><?= money($r['amt']) ?></td></tr><?php endforeach; ?>
            <?php if (!$daily): ?><tr><td colspan="3" class="text-center text-muted py-4">No bookings in this period.</td></tr><?php endif; ?>
        </tbody></table></div></div></div>
    <div class="col-lg-4"><div class="card-box"><div class="card-header-x">Rider Performance</div>
        <table class="table mb-0"><thead><tr><th>Rider</th><th class="text-end">Delivered</th></tr></thead><tbody>
            <?php foreach ($riderPerf as $r): ?><tr><td><?= e($r['name']) ?></td><td class="text-end"><?= (int)$r['c'] ?></td></tr><?php endforeach; ?>
            <?php if (!$riderPerf): ?><tr><td colspan="2" class="text-center text-muted py-4">No deliveries.</td></tr><?php endif; ?>
        </tbody></table></div></div>
</div>
<?php include __DIR__ . '/../includes/panel_footer.php'; ?>
