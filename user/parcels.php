<?php
require_once __DIR__ . '/../config.php';
require_role('user');
$pageTitle = 'My Parcels';
$uid = current_user()['id'];

$search = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';
$page   = max(1, (int)($_GET['page'] ?? 1));
$per    = 10;

$where  = 'WHERE user_id = ?';
$params = [$uid];
if ($search !== '') {
    $where .= ' AND (tracking_no LIKE ? OR receiver_name LIKE ? OR receiver_phone LIKE ?)';
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like);
}
if ($status !== '' && in_array($status, parcel_statuses(), true)) {
    $where .= ' AND status = ?';
    $params[] = $status;
}

$cnt = $pdo->prepare("SELECT COUNT(*) FROM parcels $where");
$cnt->execute($params);
$total = (int)$cnt->fetchColumn();

$off = ($page - 1) * $per;
$st  = $pdo->prepare("SELECT * FROM parcels $where ORDER BY created_at DESC LIMIT $per OFFSET $off");
$st->execute($params);
$rows = $st->fetchAll();

include __DIR__ . '/../includes/panel_header.php';
?>
<div class="card-box">
    <div class="card-header-x flex-wrap gap-2">
        <span>My Parcels <span class="badge bg-secondary ms-1"><?= $total ?></span></span>
        <form class="ms-auto d-flex gap-2 flex-wrap" method="get">
            <input type="text" name="q" value="<?= e($search) ?>" class="form-control form-control-sm" style="width:200px" placeholder="Search tracking / receiver">
            <select name="status" class="form-select form-select-sm" style="width:150px">
                <option value="">All Status</option>
                <?php foreach (parcel_statuses() as $s): ?><option <?= $status === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
            </select>
            <button class="btn btn-sm btn-orange"><i class="bi bi-funnel"></i> Filter</button>
            <a href="<?= url('user/book.php') ?>" class="btn btn-sm btn-outline-orange"><i class="bi bi-plus"></i> New</a>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Tracking No</th><th>Receiver</th><th>Service</th><th>Weight</th><th>Charge</th><th>COD</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $p): ?>
                <tr>
                    <td class="fw-semibold"><?= e($p['tracking_no']) ?></td>
                    <td><?= e($p['receiver_name']) ?><br><small class="text-muted"><?= e($p['receiver_city']) ?></small></td>
                    <td><small><?= e(service_types()[$p['service_type']]['label']) ?></small></td>
                    <td><?= e($p['weight']) ?> kg</td>
                    <td><?= money($p['delivery_charge']) ?></td>
                    <td><?= $p['cod_amount'] > 0 ? money($p['cod_amount']) : '<span class="text-muted">-</span>' ?></td>
                    <td><small><?= date('d M Y', strtotime($p['created_at'])) ?></small></td>
                    <td><?= status_badge($p['status']) ?></td>
                    <td class="text-nowrap">
                        <a href="<?= url('user/view.php?id=' . $p['id']) ?>" class="btn btn-sm btn-outline-orange" title="View"><i class="bi bi-eye"></i></a>
                        <a href="<?= url('user/invoice.php?id=' . $p['id']) ?>" class="btn btn-sm btn-outline-secondary" title="Invoice"><i class="bi bi-receipt"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="9" class="text-center text-muted py-5">No parcels found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="p-3"><?= paginate_links($total, $per, $page, ['q' => $search, 'status' => $status]) ?></div>
</div>
<?php include __DIR__ . '/../includes/panel_footer.php'; ?>
