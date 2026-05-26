<?php
require_once __DIR__ . '/auth.php';
requireLogin();
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Green Waste App' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top" style="background:var(--green-dark);">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2" href="dashboard.php">
            <span class="brand-icon"><i class="fa-solid fa-leaf"></i></span>
            <span><strong>Green</strong>Waste</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='dashboard.php')?'active':'' ?>" href="dashboard.php">
                        <i class="fa-solid fa-house me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='schedule.php')?'active':'' ?>" href="schedule.php">
                        <i class="fa-solid fa-calendar-check me-1"></i>Schedule
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='tracking.php')?'active':'' ?>" href="tracking.php">
                        <i class="fa-solid fa-truck-fast me-1"></i>Track Truck
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='report.php')?'active':'' ?>" href="report.php">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>Report
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='market.php')?'active':'' ?>" href="market.php">
                        <i class="fa-solid fa-recycle me-1"></i>Market
                    </a>
                </li>
                <?php if (isAdmin()): ?>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='admin/index.php')?'active':'' ?>" href="admin/index.php">
                        <i class="fa-solid fa-shield-halved me-1"></i>Admin
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <span class="text-white-50 small"><i class="fa-regular fa-user me-1"></i><?= htmlspecialchars($user['name']) ?></span>
                <a href="logout.php" class="btn btn-sm btn-outline-light"><i class="fa-solid fa-right-from-bracket"></i></a>
            </div>
        </div>
    </div>
</nav>
