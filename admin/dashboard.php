<?php
require_once __DIR__ . '/../config.php';
require_role('admin');
$pageTitle = 'Admin Dashboard';

$s = $pdo->query("SELECT COUNT(*) total,
        SUM(status='Pending') pending,
        SUM(status IN ('Confirmed','Picked Up','In Transit','Out for Delivery')) active,
        SUM(status='Delivered') delivered,
        SUM(status='Cancelled') cancelled,
        COALESCE(SUM(CASE WHEN status<>'Cancelled' THEN delivery_charge END),0) revenue,
        SUM(DATE(created_at)=CURDATE()) today
        FROM parcels")->fetch();
$customers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$riders    = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='rider' AND status='active'")->fetchColumn();
$unassigned = (int)$pdo->query("SELECT COUNT(*) FROM parcels WHERE rider_id IS NULL AND status IN ('Pending','Confirmed')")->fetchColumn();
$codPending = (float)$pdo->query("SELECT COALESCE(SUM(cod_amount),0) FROM parcels WHERE payment_method='cod' AND status='Delivered' AND payment_status='Unpaid'")->fetchColumn();

// last 7 days
$days = [];
for ($i = 6; $i >= 0; $i--) {
    $days[date('Y-m-d', strtotime("-$i day"))] = 0;
}
foreach ($pdo->query("SELECT DATE(created_at) d, COUNT(*) c FROM parcels WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY d") as $r) {
    if (isset($days[$r['d']])) $days[$r['d']] = (int)$r['c'];
}
$statusCounts = $pdo->query('SELECT status, COUNT(*) c FROM parcels GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);

$recent = $pdo->query('SELECT p.*, u.name AS cust FROM parcels p JOIN users u ON u.id = p.user_id ORDER BY p.created_at DESC LIMIT 8')->fetchAll();
$topCities = $pdo->query('SELECT receiver_city, COUNT(*) c FROM parcels GROUP BY receiver_city ORDER BY c DESC LIMIT 5')->fetchAll();

$labels = array_map(fn($d) => date('d M', strtotime($d)), array_keys($days));
$extraJs = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script><script>
new Chart(document.getElementById("lineChart"), {
    type: "bar",
    data: { labels: ' . json_encode($labels) . ', datasets: [{ label: "Bookings", data: ' . json_encode(array_values($days)) . ', backgroundColor: "#f97316", borderRadius: 8 }] },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
new Chart(document.getElementById("pieChart"), {
    type: "doughnut",
    data: { labels: ' . json_encode(array_keys($statusCounts)) . ', datasets: [{ data: ' . json_encode(array_map('intval', array_values($statusCounts))) . ',
        backgroundColor: ["#f59e0b","#0ea5e9","#3b82f6","#6366f1","#f97316","#22c55e","#ef4444","#64748b"] }] },
    options: { plugins: { legend: { position: "bottom" } }, cutout: "62%" }
});
</script>';
include __DIR__ . '/../includes/panel_header.php';
?>
<?php if ($unassigned > 0): ?>
    <div class="alert alert-warning d-flex align-items-center">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <div><b><?= $unassigned ?></b> parcel(s) are waiting for rider assignment.
        <a href="<?= url('admin/parcels.php?rider=none') ?>" class="alert-link">Assign now</a></div>
    </div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-icon ic-orange"><i class="bi bi-box-seam"></i></div><div><h4><?= (int)$s['total'] ?></h4><small>Total Parcels</small></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-icon ic-blue"><i class="bi bi-hourglass-split"></i></div><div><h4><?= (int)$s['pending'] ?></h4><small>Pending Approval</small></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-icon ic-purple"><i class="bi bi-truck"></i></div><div><h4><?= (int)$s['active'] ?></h4><small>In Progress</small></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-icon ic-green"><i class="bi bi-check2-circle"></i></div><div><h4><?= (int)$s['delivered'] ?></h4><small>Delivered</small></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-icon ic-red"><i class="bi bi-x-circle"></i></div><div><h4><?= (int)$s['cancelled'] ?></h4><small>Cancelled</small></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-icon ic-teal"><i class="bi bi-cash-stack"></i></div><div><h4><?= money($s['revenue']) ?></h4><small>Delivery Revenue</small></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-icon ic-blue"><i class="bi bi-people"></i></div><div><h4><?= $customers ?></h4><small>Customers</small></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="stat-icon ic-orange"><i class="bi bi-bicycle"></i></div><div><h4><?= $riders ?></h4><small>Active Riders</small></div></div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card-box"><div class="card-header-x">Bookings - Last 7 Days <span class="ms-auto badge bg-orange"><?= (int)$s['today'] ?> today</span></div>
            <div class="card-body-x"><canvas id="lineChart" height="110"></canvas></div></div>
    </div>
    <div class="col-lg-4">
        <div class="card-box"><div class="card-header-x">Parcels by Status</div>
            <div class="card-body-x"><canvas id="pieChart" height="210"></canvas></div></div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-box">
            <div class="card-header-x">Recent Bookings <a href="<?= url('admin/parcels.php') ?>" class="ms-auto small text-orange text-decoration-none">View all</a></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Tracking</th><th>Customer</th><th>To</th><th>Charge</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($recent as $p): ?>
                        <tr>
                            <td class="fw-semibold"><?= e($p['tracking_no']) ?></td>
                            <td><?= e($p['cust']) ?></td>
                            <td><?= e($p['receiver_city']) ?></td>
                            <td><?= money($p['delivery_charge']) ?></td>
                            <td><?= status_badge($p['status']) ?></td>
                            <td><a class="btn btn-sm btn-outline-orange" href="<?= url('admin/parcel_view.php?id=' . $p['id']) ?>">Manage</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card-box mb-4">
            <div class="card-header-x">Top Destinations</div>
            <ul class="list-group list-group-flush">
                <?php foreach ($topCities as $c): ?>
                    <li class="list-group-item d-flex justify-content-between"><span><i class="bi bi-geo-alt text-orange"></i> <?= e($c['receiver_city']) ?></span><span class="badge bg-secondary"><?= (int)$c['c'] ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="card-box">
            <div class="card-header-x">COD To Settle</div>
            <div class="card-body-x text-center">
                <h3 class="text-orange fw-bold"><?= money($codPending) ?></h3>
                <small class="text-muted">Collected by riders on delivered COD parcels, not yet marked as paid.</small>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/panel_footer.php'; ?>
