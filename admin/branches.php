<?php
require_once __DIR__ . '/../config.php';
require_role('admin');
$pageTitle = 'Branches';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $pdo->prepare('DELETE FROM branches WHERE id = ?')->execute([(int)$_POST['id']]);
        flash('success', 'Branch deleted.');
        redirect('admin/branches.php');
    }
    $name = trim($_POST['name'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if ($name === '' || $city === '' || $address === '' || $phone === '') {
        $errors[] = 'All fields are required.';
    }
    if (!$errors) {
        if ($action === 'edit') {
            $pdo->prepare('UPDATE branches SET name=?, city=?, address=?, phone=? WHERE id=?')->execute([$name, $city, $address, $phone, (int)$_POST['id']]);
            flash('success', 'Branch updated.');
        } else {
            $pdo->prepare('INSERT INTO branches (name, city, address, phone) VALUES (?,?,?,?)')->execute([$name, $city, $address, $phone]);
            flash('success', 'Branch added.');
        }
        redirect('admin/branches.php');
    }
}

$edit = null;
if (!empty($_GET['edit'])) {
    $st = $pdo->prepare('SELECT * FROM branches WHERE id = ?');
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch() ?: null;
}
$rows = $pdo->query('SELECT b.*, (SELECT COUNT(*) FROM parcels p WHERE p.branch_id = b.id) AS parcels FROM branches b ORDER BY b.city, b.name')->fetchAll();

include __DIR__ . '/../includes/panel_header.php';
?>
<?php if ($errors): ?><div class="alert alert-danger"><?= e($errors[0]) ?></div><?php endif; ?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card-box">
            <div class="card-header-x"><?= $edit ? 'Edit Branch' : 'Add Branch' ?></div>
            <div class="card-body-x">
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'add' ?>">
                    <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
                    <div class="mb-3"><label class="form-label">Branch Name</label><input type="text" name="name" class="form-control" value="<?= e($edit['name'] ?? '') ?>" required></div>
                    <div class="mb-3"><label class="form-label">City</label>
                        <select name="city" class="form-select"><?php foreach (bd_cities() as $c): ?><option <?= ($edit['city'] ?? '') === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select></div>
                    <div class="mb-3"><label class="form-label">Address</label><input type="text" name="address" class="form-control" value="<?= e($edit['address'] ?? '') ?>" required></div>
                    <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?= e($edit['phone'] ?? '') ?>" required></div>
                    <button class="btn btn-orange w-100"><?= $edit ? 'Update Branch' : 'Add Branch' ?></button>
                    <?php if ($edit): ?><a href="<?= url('admin/branches.php') ?>" class="btn btn-light w-100 mt-2">Cancel</a><?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card-box">
            <div class="card-header-x">All Branches <span class="badge bg-secondary ms-2"><?= count($rows) ?></span></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Branch</th><th>City</th><th>Address</th><th>Phone</th><th>Parcels</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($rows as $b): ?>
                        <tr>
                            <td class="fw-semibold"><?= e($b['name']) ?></td>
                            <td><?= e($b['city']) ?></td>
                            <td><small><?= e($b['address']) ?></small></td>
                            <td><?= e($b['phone']) ?></td>
                            <td><?= (int)$b['parcels'] ?></td>
                            <td class="text-nowrap">
                                <a href="?edit=<?= $b['id'] ?>" class="btn btn-sm btn-outline-orange"><i class="bi bi-pencil"></i></a>
                                <form method="post" class="d-inline" data-confirm="Delete this branch?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $b['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/panel_footer.php'; ?>
