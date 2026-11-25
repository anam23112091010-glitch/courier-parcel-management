<?php
require_once __DIR__ . '/../config.php';
require_role('admin');
$pageTitle = 'Settings';
$errors = [];
$vals = [];

function save_setting($k, $v)
{
    global $pdo;
    $pdo->prepare('INSERT INTO settings (skey, svalue) VALUES (?, ?) ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)')->execute([$k, $v]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'company') {
        foreach (['company_name', 'company_address', 'company_phone', 'company_email'] as $k) {
            $v = trim($_POST[$k] ?? '');
            if ($v === '') { $errors[] = 'All company fields are required.'; break; }
            $vals[$k] = $v;
        }
        if (!$errors) {
            foreach ($vals as $k => $v) save_setting($k, $v);
            flash('success', 'Company information saved.');
            redirect('admin/settings.php');
        }
    } elseif ($action === 'pricing') {
        $keys = ['standard_base', 'standard_perkg', 'express_base', 'express_perkg', 'sameday_base', 'sameday_perkg', 'outside_extra', 'cod_percent'];
        foreach ($keys as $k) {
            $v = $_POST[$k] ?? '';
            if (!is_numeric($v) || $v < 0) { $errors[] = 'All pricing values must be positive numbers.'; break; }
            $vals[$k] = (string)(float)$v;
        }
        if (!$errors) {
            foreach ($vals as $k => $v) save_setting($k, $v);
            flash('success', 'Pricing updated. New bookings will use the new rates.');
            redirect('admin/settings.php');
        }
    } elseif ($action === 'password') {
        $st = $pdo->prepare('SELECT password FROM users WHERE id = ?');
        $st->execute([current_user()['id']]);
        $hash = $st->fetchColumn();
        $new = $_POST['new'] ?? '';
        if (!password_verify($_POST['current'] ?? '', $hash)) $errors[] = 'Current password is incorrect.';
        if (strlen($new) < 6) $errors[] = 'New password must be at least 6 characters.';
        if ($new !== ($_POST['new2'] ?? '')) $errors[] = 'New passwords do not match.';
        if (!$errors) {
            $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), current_user()['id']]);
            flash('success', 'Admin password changed.');
            redirect('admin/settings.php');
        }
    }
}
include __DIR__ . '/../includes/panel_header.php';
?>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card-box mb-4">
            <div class="card-header-x"><i class="bi bi-building text-orange me-2"></i> Company Information</div>
            <div class="card-body-x">
                <form method="post">
                    <?= csrf_field() ?><input type="hidden" name="action" value="company">
                    <div class="mb-3"><label class="form-label">Company Name</label><input type="text" name="company_name" class="form-control" value="<?= e(get_setting('company_name')) ?>" required></div>
                    <div class="mb-3"><label class="form-label">Address</label><input type="text" name="company_address" class="form-control" value="<?= e(get_setting('company_address')) ?>" required></div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="company_phone" class="form-control" value="<?= e(get_setting('company_phone')) ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="text" name="company_email" class="form-control" value="<?= e(get_setting('company_email')) ?>" required></div>
                    </div>
                    <button class="btn btn-orange px-4">Save Company Info</button>
                </form>
            </div>
        </div>
        <div class="card-box">
            <div class="card-header-x"><i class="bi bi-shield-lock text-orange me-2"></i> Change Admin Password</div>
            <div class="card-body-x">
                <form method="post">
                    <?= csrf_field() ?><input type="hidden" name="action" value="password">
                    <div class="mb-3"><input type="password" name="current" class="form-control" placeholder="Current password" required></div>
                    <div class="mb-3"><input type="password" name="new" class="form-control" placeholder="New password" required></div>
                    <div class="mb-3"><input type="password" name="new2" class="form-control" placeholder="Confirm new password" required></div>
                    <button class="btn btn-dark px-4">Update Password</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card-box">
            <div class="card-header-x"><i class="bi bi-tags text-orange me-2"></i> Pricing Settings (৳)</div>
            <div class="card-body-x">
                <form method="post">
                    <?= csrf_field() ?><input type="hidden" name="action" value="pricing">
                    <table class="table align-middle">
                        <thead><tr><th>Service</th><th>Base (up to 1 kg)</th><th>Each extra kg</th></tr></thead>
                        <tbody>
                        <?php foreach (service_types() as $k => $v): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($v['label']) ?></td>
                                <td><input type="number" step="0.01" min="0" name="<?= $k ?>_base" class="form-control form-control-sm" value="<?= e(get_setting($k . '_base')) ?>"></td>
                                <td><input type="number" step="0.01" min="0" name="<?= $k ?>_perkg" class="form-control form-control-sm" value="<?= e(get_setting($k . '_perkg')) ?>"></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6"><label class="form-label">Outside Dhaka extra fee (৳)</label><input type="number" step="0.01" min="0" name="outside_extra" class="form-control" value="<?= e(get_setting('outside_extra')) ?>"></div>
                        <div class="col-md-6"><label class="form-label">COD fee (% of COD amount)</label><input type="number" step="0.01" min="0" name="cod_percent" class="form-control" value="<?= e(get_setting('cod_percent')) ?>"></div>
                    </div>
                    <button class="btn btn-orange px-4">Save Pricing</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/panel_footer.php'; ?>
