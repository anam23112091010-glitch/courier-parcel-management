<?php
require_once __DIR__ . '/../config.php';
require_role('admin');
$pageTitle = 'Riders';
$errors = [];
$old = ['name' => '', 'email' => '', 'phone' => '', 'address' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        foreach ($old as $k => $_) $old[$k] = trim($_POST[$k] ?? '');
        $pass = $_POST['password'] ?? '';
        if (strlen($old['name']) < 3) $errors[] = 'Rider name is too short.';
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email.';
        if (!preg_match('/^01[3-9]\d{8}$/', $old['phone'])) $errors[] = 'Enter a valid mobile number.';
        if (strlen($pass) < 6) $errors[] = 'Password must be at least 6 characters.';
        if (!$errors) {
            $chk = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $chk->execute([$old['email']]);
            if ($chk->fetch()) $errors[] = 'This email is already in use.';
        }
        if (!$errors) {
            $pdo->prepare("INSERT INTO users (name, email, phone, password, address, role) VALUES (?,?,?,?,?, 'rider')")
                ->execute([$old['name'], $old['email'], $old['phone'], password_hash($pass, PASSWORD_DEFAULT), $old['address']]);
            flash('success', 'Rider account created.');
            redirect('admin/riders.php');
        }
    } elseif ($action === 'toggle') {
        $pdo->prepare("UPDATE users SET status = IF(status='active','blocked','active') WHERE id = ? AND role = 'rider'")->execute([(int)$_POST['id']]);
        flash('success', 'Rider status updated.');
        redirect('admin/riders.php');
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'rider'")->execute([(int)$_POST['id']]);
        flash('success', 'Rider deleted. Their parcels are now unassigned.');
        redirect('admin/riders.php');
    }
}

$rows = $pdo->query("SELECT u.*,
        (SELECT COUNT(*) FROM parcels p WHERE p.rider_id = u.id AND p.status IN ('Confirmed','Picked Up','In Transit','Out for Delivery')) AS active_jobs,
        (SELECT COUNT(*) FROM parcels p WHERE p.rider_id = u.id AND p.status = 'Delivered') AS delivered
        FROM users u WHERE u.role = 'rider' ORDER BY u.created_at DESC")->fetchAll();

include __DIR__ . '/../includes/panel_header.php';
?>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card-box">
            <div class="card-header-x"><i class="bi bi-person-plus text-orange me-2"></i> Add New Rider</div>
            <div class="card-body-x">
                <form method="post">
                    <?= csrf_field() ?><input type="hidden" name="action" value="add">
                    <div class="mb-3"><label class="form-label">Full Name</label><input type="text" name="name" class="form-control" value="<?= e($old['name']) ?>" required></div>
                    <div class="mb-3"><label class="form-label">Email (login)</label><input type="email" name="email" class="form-control" value="<?= e($old['email']) ?>" required></div>
                    <div class="mb-3"><label class="form-label">Mobile</label><input type="text" name="phone" class="form-control" value="<?= e($old['phone']) ?>" required></div>
                    <div class="mb-3"><label class="form-label">Address</label><input type="text" name="address" class="form-control" value="<?= e($old['address']) ?>"></div>
                    <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
                    <button class="btn btn-orange w-100">Create Rider</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card-box">
            <div class="card-header-x">Delivery Riders <span class="badge bg-secondary ms-2"><?= count($rows) ?></span></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Rider</th><th>Phone</th><th>Active Jobs</th><th>Delivered</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><b><?= e($r['name']) ?></b><br><small class="text-muted"><?= e($r['email']) ?></small></td>
                            <td><?= e($r['phone']) ?></td>
                            <td><a href="<?= url('admin/parcels.php?rider=' . $r['id']) ?>"><?= (int)$r['active_jobs'] ?></a></td>
                            <td><?= (int)$r['delivered'] ?></td>
                            <td><span class="badge bg-<?= $r['status'] === 'active' ? 'success' : 'danger' ?>"><?= e(ucfirst($r['status'])) ?></span></td>
                            <td class="text-nowrap">
                                <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="toggle">
                                    <button class="btn btn-sm btn-outline-<?= $r['status'] === 'active' ? 'warning' : 'success' ?>"><?= $r['status'] === 'active' ? 'Block' : 'Unblock' ?></button></form>
                                <form method="post" class="d-inline" data-confirm="Delete this rider?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="delete">
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$rows): ?><tr><td colspan="6" class="text-center text-muted py-5">No riders yet.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/panel_footer.php'; ?>
