<?php
require_once __DIR__ . '/functions.php';
$admin = current_admin();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<?php if (is_logged_in()): ?>
<nav class="navbar navbar-expand-lg navbar-dark app-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= url('dashboard.php') ?>">
            <i class="bi bi-truck-front-fill me-2"></i>MAGERWA <span class="brand-light">VTS</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= url('dashboard.php') ?>"><i class="bi bi-grid me-1"></i>Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('clients/index.php') ?>"><i class="bi bi-people me-1"></i>Clients</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('vehicles/index.php') ?>"><i class="bi bi-car-front me-1"></i>Vehicles</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('vehicles/linked.php') ?>"><i class="bi bi-link-45deg me-1"></i>Linked Records</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('admins/index.php') ?>"><i class="bi bi-person-badge me-1"></i>Staff</a></li>
            </ul>
            <div class="d-flex align-items-center text-white-50">
                <span class="me-3 small"><i class="bi bi-person-circle me-1"></i><?= e($admin['names'] ?? '') ?></span>
                <a class="btn btn-sm btn-outline-light" href="<?= url('auth/logout.php') ?>"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
            </div>
        </div>
    </div>
</nav>
<?php endif; ?>
<main class="py-4">
    <div class="container">
        <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
