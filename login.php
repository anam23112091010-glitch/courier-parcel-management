<?php
require_once __DIR__ . '/config.php';
if (is_logged_in()) {
    redirect(dashboard_url());
}
$pageTitle = 'Login';
$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    $st = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $st->execute([$email]);
    $user = $st->fetch();

    if (!$user || !password_verify($pass, $user['password'])) {
        $error = 'Invalid email or password.';
    } elseif ($user['status'] === 'blocked') {
        $error = 'Your account has been blocked. Please contact support.';
    } else {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'    => (int)$user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ];
        flash('success', 'Welcome back, ' . $user['name'] . '!');
        redirect(dashboard_url($user['role']));
    }
}
include __DIR__ . '/includes/header.php';
?>
<section class="auth-wrap">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card auth-card">
                    <div class="row g-0">
                        <div class="col-md-5 auth-side d-none d-md-flex">
                            <i class="bi bi-box-seam-fill text-orange display-3"></i>
                            <h3 class="fw-bold mt-3">Welcome Back!</h3>
                            <p class="text-secondary">Login to manage your parcels, track deliveries and view invoices.</p>
                            <hr class="border-secondary">
                            <small class="text-secondary">
                                <b class="text-white">Demo accounts</b><br>
                                Admin: admin@courier.com / admin123<br>
                                User: user@courier.com / user123<br>
                                Rider: rider@courier.com / rider123
                            </small>
                        </div>
                        <div class="col-md-7 p-4 p-md-5 bg-white">
                            <h4 class="fw-bold mb-1">Sign in</h4>
                            <p class="text-muted small mb-4">Enter your account details below</p>
                            <?php if ($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
                            <form method="post">
                                <?= csrf_field() ?>
                                <div class="mb-3">
                                    <label class="form-label">Email address</label>
                                    <div class="input-group"><span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" value="<?= e($email) ?>" required autofocus></div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Password</label>
                                    <div class="input-group"><span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" class="form-control" required></div>
                                </div>
                                <button class="btn btn-orange w-100 py-2">Login</button>
                            </form>
                            <p class="text-center small mt-4 mb-0">Don't have an account? <a href="<?= url('register.php') ?>" class="text-orange fw-semibold">Register now</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
