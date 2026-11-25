<?php
require_once __DIR__ . '/config.php';
if (is_logged_in()) {
    redirect(dashboard_url());
}
$pageTitle = 'Create Account';
$errors = [];
$old = ['name' => '', 'email' => '', 'phone' => '', 'city' => 'Dhaka', 'address' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($old as $k => $_) {
        $old[$k] = trim($_POST[$k] ?? '');
    }
    $pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if (strlen($old['name']) < 3) $errors[] = 'Name must be at least 3 characters.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (!preg_match('/^01[3-9]\d{8}$/', $old['phone'])) $errors[] = 'Enter a valid Bangladeshi mobile number (e.g. 01712345678).';
    if (strlen($pass) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($pass !== $pass2) $errors[] = 'Passwords do not match.';
    if ($old['address'] === '') $errors[] = 'Address is required.';

    if (!$errors) {
        $chk = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $chk->execute([$old['email']]);
        if ($chk->fetch()) {
            $errors[] = 'This email is already registered.';
        }
    }
    if (!$errors) {
        $st = $pdo->prepare("INSERT INTO users (name, email, phone, password, address, city, role) VALUES (?,?,?,?,?,?, 'user')");
        $st->execute([$old['name'], $old['email'], $old['phone'], password_hash($pass, PASSWORD_DEFAULT), $old['address'], $old['city']]);
        flash('success', 'Account created successfully! Please login.');
        redirect('login.php');
    }
}
include __DIR__ . '/includes/header.php';
?>
<section class="auth-wrap">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card auth-card">
                    <div class="p-4 p-md-5 bg-white">
                        <h4 class="fw-bold mb-1">Create your account</h4>
                        <p class="text-muted small mb-4">Register as a customer and start sending parcels today.</p>
                        <?php if ($errors): ?>
                            <div class="alert alert-danger py-2"><ul class="mb-0 small"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
                        <?php endif; ?>
                        <form method="post">
                            <?= csrf_field() ?>
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label">Full Name</label><input type="text" name="name" class="form-control" value="<?= e($old['name']) ?>" required></div>
                                <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($old['email']) ?>" required></div>
                                <div class="col-md-6"><label class="form-label">Mobile Number</label><input type="text" name="phone" class="form-control" placeholder="01XXXXXXXXX" value="<?= e($old['phone']) ?>" required></div>
                                <div class="col-md-6"><label class="form-label">City</label>
                                    <select name="city" class="form-select">
                                        <?php foreach (bd_cities() as $c): ?><option <?= $old['city'] === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12"><label class="form-label">Address</label><input type="text" name="address" class="form-control" value="<?= e($old['address']) ?>" required></div>
                                <div class="col-md-6"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
                                <div class="col-md-6"><label class="form-label">Confirm Password</label><input type="password" name="password2" class="form-control" required></div>
                                <div class="col-12"><button class="btn btn-orange w-100 py-2">Create Account</button></div>
                            </div>
                        </form>
                        <p class="text-center small mt-4 mb-0">Already registered? <a href="<?= url('login.php') ?>" class="text-orange fw-semibold">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
