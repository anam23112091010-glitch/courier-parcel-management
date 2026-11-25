<?php
// Dashboard layout (admin / user / rider). Set $pageTitle before including.
$pageTitle = $pageTitle ?? 'Dashboard';
$u    = current_user();
$role = $u['role'];
$self = basename($_SERVER['PHP_SELF']);

$menus = [
    'admin' => [
        ['dashboard.php', 'bi-speedometer2', 'Dashboard'],
        ['parcels.php',   'bi-box-seam',     'All Parcels'],
        ['users.php',     'bi-people',       'Customers'],
        ['riders.php',    'bi-bicycle',      'Riders'],
        ['branches.php',  'bi-shop',         'Branches'],
        ['messages.php',  'bi-envelope',     'Messages'],
        ['reports.php',   'bi-graph-up',     'Reports'],
        ['settings.php',  'bi-gear',         'Settings'],
    ],
    'user' => [
        ['dashboard.php', 'bi-speedometer2', 'Dashboard'],
        ['book.php',      'bi-plus-circle',  'Book a Parcel'],
        ['parcels.php',   'bi-box-seam',     'My Parcels'],
        ['profile.php',   'bi-person-gear',  'My Profile'],
    ],
    'rider' => [
        ['dashboard.php', 'bi-speedometer2', 'My Deliveries'],
        ['history.php',   'bi-clock-history', 'Delivery History'],
        ['profile.php',   'bi-person-gear',  'My Profile'],
    ],
];
$unread = $role === 'admin' ? unread_messages() : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | <?= e(ucfirst($role)) ?> Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body class="panel-body">
<div class="panel-wrap">
    <aside class="sidebar" id="sidebar">
        <a href="<?= url('index.php') ?>" class="sidebar-brand">
            <i class="bi bi-box-seam-fill text-orange"></i>
            <span><?= e(get_setting('company_name', 'SwiftParcel Courier')) ?></span>
        </a>
        <div class="sidebar-role"><?= e(strtoupper($role)) ?> PANEL</div>
        <ul class="sidebar-menu">
            <?php foreach ($menus[$role] as $m): ?>
                <li>
                    <a href="<?= url($role . '/' . $m[0]) ?>" class="<?= $self === $m[0] ? 'active' : '' ?>">
                        <i class="bi <?= $m[1] ?>"></i> <?= $m[2] ?>
                        <?php if ($m[0] === 'messages.php' && $unread > 0): ?>
                            <span class="badge bg-danger ms-auto"><?= $unread ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
            <li class="mt-3"><a href="<?= url('track.php') ?>"><i class="bi bi-geo-alt"></i> Track Parcel</a></li>
            <li><a href="<?= url('index.php') ?>"><i class="bi bi-house"></i> Visit Website</a></li>
            <li><a href="<?= url('logout.php') ?>" class="text-danger-emphasis"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
    </aside>

    <div class="panel-main">
        <header class="topbar">
            <button class="btn btn-light d-lg-none" id="sidebarToggle"><i class="bi bi-list fs-5"></i></button>
            <h5 class="mb-0 fw-semibold"><?= e($pageTitle) ?></h5>
            <div class="ms-auto d-flex align-items-center gap-3">
                <span class="d-none d-md-inline text-muted small"><i class="bi bi-calendar3"></i> <?= date('D, d M Y') ?></span>
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                        <span class="avatar"><?= e(strtoupper(substr($u['name'], 0, 1))) ?></span>
                        <span class="ms-2 d-none d-md-inline text-dark"><?= e($u['name']) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <?php if ($role !== 'admin'): ?>
                            <li><a class="dropdown-item" href="<?= url($role . '/profile.php') ?>"><i class="bi bi-person"></i> My Profile</a></li>
                        <?php endif; ?>
                        <li><a class="dropdown-item" href="<?= url('logout.php') ?>"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>
        <main class="panel-content">
            <?php render_flash(); ?>
