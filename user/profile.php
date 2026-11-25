<?php
require_once __DIR__ . '/../config.php';
require_role(['user', 'rider']);
$pageTitle = 'My Profile';
$me = current_user();

$st = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$st->execute([$me['id']]);
$u = $st->fetch();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'profile') {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = $_POST['city'] ?? 'Dhaka';
        if (strlen($name) < 3) $errors[] = 'Name must be at least 3 characters.';
        if (!preg_match('/^01[3-9]\d{8}$/', $phone)) $errors[] = 'Enter a valid mobile number.';
        if ($address === '') $errors[] = 'Address is required.';
        if (!in_array($city, bd_cities(), true)) $city = 'Dhaka';
        if (!$errors) {
            $pdo->prepare('UPDATE users SET name=?, phone=?, address=?, city=? WHERE id=?')->execute([$name, $phone, $address, $city, $me['id']]);
            $_SESSION['user']['name'] = $name;
            flash('success', 'Profile updated successfully.');
            redirect($me['role'] . '/profile.php');
        }
    } elseif ($action === 'password') {
        $cur = $_POST['current'] ?? '';
        $new = $_POST['new'] ?? '';
        $new2 = $_POST['new2'] ?? '';
        if (!password_verify($cur, $u['password'])) $errors[] = 'Current password is incorrect.';
        if (strlen($new) < 6) $errors[] = 'New password must be at least 6 characters.';
        if ($new !== $new2) $errors[] = 'New passwords do not match.';
        if (!$errors) {
            $pdo->prepare('UPDATE users SET password=? WHERE id=?')->execute([password_hash($new, PASSWORD_DEFAULT), $me['id']]);
            flash('success', 'Password changed successfully.');
            redirect($me['role'] . '/profile.php');
        }
    }
}
include __DIR__ . '/../includes/panel_header.php';
?>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-box">
            <div class="card-header-x">Personal Information</div>
            <div class="card-body-x">
                <form method="post">
                    <?= csrf_field() ?><input type="hidden" name="action" value="profile">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Full Name</label><input type="text" name="name" class="form-control" value="<?= e($u['name']) ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Email <small class="text-muted">(cannot be changed)</small></label><input type="email" class="form-control" value="<?= e($u['email']) ?>" disabled></div>
                        <div class="col-md-6"><label class="form-label">Mobile</label><input type="text" name="phone" class="form-control" value="<?= e($u['phone']) ?>" required></div>
                        <div class="col-md-6"><label class="form-label">City</label>
                            <select name="city" class="form-select"><?php foreach (bd_cities() as $c): ?><option <?= $u['city'] === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select></div>
                        <div class="col-12"><label class="form-label">Address</label><input type="text" name="address" class="form-control" value="<?= e($u['address']) ?>" required></div>
                        <div class="col-12"><button class="btn btn-orange px-4">Save Changes</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card-box">
            <div class="card-header-x">Change Password</div>
            <div class="card-body-x">
                <form method="post">
                    <?= csrf_field() ?><input type="hidden" name="action" value="password">
                    <div class="mb-3"><label class="form-label">Current Password</label><input type="password" name="current" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">New Password</label><input type="password" name="new" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Confirm New Password</label><input type="password" name="new2" class="form-control" required></div>
                    <button class="btn btn-dark px-4">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/panel_footer.php'; ?>
