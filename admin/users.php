<?php
require_once __DIR__ . '/../config.php';
require_role('admin');
$pageTitle = 'Customers';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($action === 'toggle') {
        $pdo->prepare("UPDATE users SET status = IF(status='active','blocked','active') WHERE id = ? AND role = 'user'")->execute([$id]);
        flash('success', 'Customer status updated.');
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'user'")->execute([$id]);
        flash('success', 'Customer and all their parcels were deleted.');
    }
    redirect('admin/users.php');
}

$search = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per = 12;
$where = "WHERE u.role = 'user'";
$params = [];
if ($search !== '') {
    $where .= ' AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)';
    $like = '%' . $search . '%';
    $params = [$like, $like, $like];
}
$c = $pdo->prepare("SELECT COUNT(*) FROM users u $where");
$c->execute($params);
$total = (int)$c->fetchColumn();
$off = ($page - 1) * $per;
$st = $pdo->prepare("SELECT u.*, (SELECT COUNT(*) FROM parcels p WHERE p.user_id = u.id) AS parcel_count,
                            (SELECT COALESCE(SUM(delivery_charge),0) FROM parcels p WHERE p.user_id = u.id AND p.status <> 'Cancelled') AS spent
                     FROM users u $where ORDER BY u.created_at DESC LIMIT $per OFFSET $off");
$st->execute($params);
$rows = $st->fetchAll();

include __DIR__ . '/../includes/panel_header.php';
?>
<div class="card-box">
    <div class="card-header-x flex-wrap gap-2">
        <span>Registered Customers <span class="badge bg-secondary ms-1"><?= $total ?></span></span>
        <form class="ms-auto d-flex gap-2" method="get">
            <input type="text" name="q" value="<?= e($search) ?>" class="form-control form-control-sm" placeholder="Search name, email, phone">
            <button class="btn btn-sm btn-orange"><i class="bi bi-search"></i></button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>#</th><th>Customer</th><th>Phone</th><th>City</th><th>Parcels</th><th>Spent</th><th>Joined</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><b><?= e($u['name']) ?></b><br><small class="text-muted"><?= e($u['email']) ?></small></td>
                    <td><?= e($u['phone']) ?></td>
                    <td><?= e($u['city']) ?></td>
                    <td><a href="<?= url('admin/parcels.php?q=' . urlencode($u['name'])) ?>"><?= (int)$u['parcel_count'] ?></a></td>
                    <td><?= money($u['spent']) ?></td>
                    <td><small><?= date('d M Y', strtotime($u['created_at'])) ?></small></td>
                    <td><span class="badge bg-<?= $u['status'] === 'active' ? 'success' : 'danger' ?>"><?= e(ucfirst($u['status'])) ?></span></td>
                    <td class="text-nowrap">
                        <form method="post" class="d-inline">
                            <?= csrf_field() ?><input type="hidden" name="id" value="<?= $u['id'] ?>"><input type="hidden" name="action" value="toggle">
                            <button class="btn btn-sm btn-outline-<?= $u['status'] === 'active' ? 'warning' : 'success' ?>"><?= $u['status'] === 'active' ? 'Block' : 'Unblock' ?></button>
                        </form>
                        <form method="post" class="d-inline" data-confirm="Delete this customer and ALL their parcels?">
                            <?= csrf_field() ?><input type="hidden" name="id" value="<?= $u['id'] ?>"><input type="hidden" name="action" value="delete">
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="9" class="text-center text-muted py-5">No customers found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="p-3"><?= paginate_links($total, $per, $page, ['q' => $search]) ?></div>
</div>
<?php include __DIR__ . '/../includes/panel_footer.php'; ?>
