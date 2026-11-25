<?php
require_once __DIR__ . '/../config.php';
require_role('admin');
$pageTitle = 'All Parcels';

// Delete parcel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_check();
    $pdo->prepare('DELETE FROM parcels WHERE id = ?')->execute([(int)$_POST['id']]);
    flash('success', 'Parcel deleted permanently.');
    redirect('admin/parcels.php');
}

$search  = trim($_GET['q'] ?? '');
$status  = $_GET['status'] ?? '';
$service = $_GET['service'] ?? '';
$rider   = $_GET['rider'] ?? '';
$from    = $_GET['from'] ?? '';
$to      = $_GET['to'] ?? '';
$page    = max(1, (int)($_GET['page'] ?? 1));
$per     = 12;

$w = [];
$params = [];
if ($search !== '') {
    $w[] = '(p.tracking_no LIKE ? OR p.receiver_name LIKE ? OR p.receiver_phone LIKE ? OR p.sender_name LIKE ? OR u.name LIKE ?)';
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like, $like, $like);
}
if (in_array($status, parcel_statuses(), true)) { $w[] = 'p.status = ?'; $params[] = $status; }
if (isset(service_types()[$service])) { $w[] = 'p.service_type = ?'; $params[] = $service; }
if ($rider === 'none') { $w[] = 'p.rider_id IS NULL'; }
elseif ((int)$rider > 0) { $w[] = 'p.rider_id = ?'; $params[] = (int)$rider; }
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) { $w[] = 'DATE(p.created_at) >= ?'; $params[] = $from; }
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   { $w[] = 'DATE(p.created_at) <= ?'; $params[] = $to; }
$where = $w ? 'WHERE ' . implode(' AND ', $w) : '';

$cnt = $pdo->prepare("SELECT COUNT(*) FROM parcels p JOIN users u ON u.id = p.user_id $where");
$cnt->execute($params);
$total = (int)$cnt->fetchColumn();

$off = ($page - 1) * $per;
$st = $pdo->prepare("SELECT p.*, u.name AS cust, r.name AS rider_name
                     FROM parcels p JOIN users u ON u.id = p.user_id
                     LEFT JOIN users r ON r.id = p.rider_id
                     $where ORDER BY p.created_at DESC LIMIT $per OFFSET $off");
$st->execute($params);
$rows = $st->fetchAll();
$riderList = $pdo->query("SELECT id, name FROM users WHERE role='rider' ORDER BY name")->fetchAll();

include __DIR__ . '/../includes/panel_header.php';
?>
<div class="card-box mb-4">
    <div class="card-body-x">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3"><label class="form-label small mb-1">Search</label><input type="text" name="q" value="<?= e($search) ?>" class="form-control form-control-sm" placeholder="Tracking, name, phone"></div>
            <div class="col-md-2"><label class="form-label small mb-1">Status</label>
                <select name="status" class="form-select form-select-sm"><option value="">All</option><?php foreach (parcel_statuses() as $x): ?><option <?= $status === $x ? 'selected' : '' ?>><?= e($x) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2"><label class="form-label small mb-1">Service</label>
                <select name="service" class="form-select form-select-sm"><option value="">All</option><?php foreach (service_types() as $k => $v): ?><option value="<?= $k ?>" <?= $service === $k ? 'selected' : '' ?>><?= e($v['label']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2"><label class="form-label small mb-1">Rider</label>
                <select name="rider" class="form-select form-select-sm"><option value="">All</option><option value="none" <?= $rider === 'none' ? 'selected' : '' ?>>Unassigned</option>
                    <?php foreach ($riderList as $r): ?><option value="<?= $r['id'] ?>" <?= (string)$rider === (string)$r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-1"><label class="form-label small mb-1">From</label><input type="date" name="from" value="<?= e($from) ?>" class="form-control form-control-sm"></div>
            <div class="col-md-1"><label class="form-label small mb-1">To</label><input type="date" name="to" value="<?= e($to) ?>" class="form-control form-control-sm"></div>
            <div class="col-md-1 d-flex gap-1"><button class="btn btn-sm btn-orange w-100"><i class="bi bi-search"></i></button><a href="<?= url('admin/parcels.php') ?>" class="btn btn-sm btn-light" title="Reset"><i class="bi bi-x-lg"></i></a></div>
        </form>
    </div>
</div>

<div class="card-box">
    <div class="card-header-x">Parcels <span class="badge bg-secondary ms-2"><?= $total ?></span></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Tracking</th><th>Customer</th><th>Receiver</th><th>Service</th><th>Charge</th><th>Rider</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $p): ?>
                <tr>
                    <td class="fw-semibold"><?= e($p['tracking_no']) ?><br><small class="badge bg-<?= $p['payment_method'] === 'cod' ? 'info' : 'light text-dark' ?>"><?= $p['payment_method'] === 'cod' ? 'COD ' . money($p['cod_amount']) : 'Prepaid' ?></small></td>
                    <td><?= e($p['cust']) ?></td>
                    <td><?= e($p['receiver_name']) ?><br><small class="text-muted"><?= e($p['receiver_city']) ?></small></td>
                    <td><small><?= e(service_types()[$p['service_type']]['label']) ?></small></td>
                    <td><?= money($p['delivery_charge']) ?></td>
                    <td><?= $p['rider_name'] ? e($p['rider_name']) : '<span class="badge bg-warning text-dark">Unassigned</span>' ?></td>
                    <td><small><?= date('d M Y', strtotime($p['created_at'])) ?></small></td>
                    <td><?= status_badge($p['status']) ?></td>
                    <td class="text-nowrap">
                        <a href="<?= url('admin/parcel_view.php?id=' . $p['id']) ?>" class="btn btn-sm btn-outline-orange" title="Manage"><i class="bi bi-pencil-square"></i></a>
                        <a href="<?= url('user/invoice.php?id=' . $p['id']) ?>" class="btn btn-sm btn-outline-secondary" title="Invoice"><i class="bi bi-receipt"></i></a>
                        <form method="post" class="d-inline" data-confirm="Delete this parcel permanently?">
                            <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="9" class="text-center text-muted py-5">No parcels match your filters.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="p-3"><?= paginate_links($total, $per, $page, ['q' => $search, 'status' => $status, 'service' => $service, 'rider' => $rider, 'from' => $from, 'to' => $to]) ?></div>
</div>
<?php include __DIR__ . '/../includes/panel_footer.php'; ?>
