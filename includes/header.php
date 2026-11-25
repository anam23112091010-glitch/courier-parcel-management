<?php
// Public site header. Set $pageTitle before including.
$pageTitle = $pageTitle ?? 'Home';
$self = basename($_SERVER['PHP_SELF']);
function nav_active($file, $self) { return $file === $self ? 'active' : ''; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | <?= e(get_setting('company_name', 'SwiftParcel Courier')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark main-nav sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= url('index.php') ?>">
            <i class="bi bi-box-seam-fill text-orange"></i> <?= e(get_setting('company_name', 'SwiftParcel Courier')) ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link <?= nav_active('index.php', $self) ?>" href="<?= url('index.php') ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link <?= nav_active('services.php', $self) ?>" href="<?= url('services.php') ?>">Services</a></li>
                <li class="nav-item"><a class="nav-link <?= nav_active('price.php', $self) ?>" href="<?= url('price.php') ?>">Pricing</a></li>
                <li class="nav-item"><a class="nav-link <?= nav_active('track.php', $self) ?>" href="<?= url('track.php') ?>">Track Parcel</a></li>
                <li class="nav-item"><a class="nav-link <?= nav_active('about.php', $self) ?>" href="<?= url('about.php') ?>">About</a></li>
                <li class="nav-item"><a class="nav-link <?= nav_active('contact.php', $self) ?>" href="<?= url('contact.php') ?>">Contact</a></li>
            </ul>
            <div class="d-flex gap-2">
                <?php if (is_logged_in()): ?>
                    <a href="<?= dashboard_url() ?>" class="btn btn-orange btn-sm px-3"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    <a href="<?= url('logout.php') ?>" class="btn btn-outline-light btn-sm px-3">Logout</a>
                <?php else: ?>
                    <a href="<?= url('login.php') ?>" class="btn btn-outline-light btn-sm px-3">Login</a>
                    <a href="<?= url('register.php') ?>" class="btn btn-orange btn-sm px-3">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<?php if (!empty($_SESSION['flash'])): ?>
<div class="container mt-3"><?php render_flash(); ?></div>
<?php endif; ?>
