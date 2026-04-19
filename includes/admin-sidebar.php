<?php require_once __DIR__ . '/auth.php'; requireRole('admin'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' — ' : '' ?>Admin — Reuchne Tooth Fairy Clinic</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <?= isset($extraCSS) ? $extraCSS : '' ?>
</head>
<body>
<nav class="navbar scrolled">
    <div class="nav-container">
        <a href="<?= BASE_URL ?>/index.php" class="nav-logo">
            <div class="logo-fallback" style="display:flex">
                <img src="<?= BASE_URL ?>/assets/images/placeholders/rb-logo-transparent.png" alt="Logo" class="logo-icon" style="height:4rem;width:auto;flex-shrink:0;">
                <div class="logo-text"><span class="logo-name">Reuchne</span><span class="logo-sub">Admin Panel</span></div>
            </div>
        </a>
        <div style="display:flex;align-items:center;gap:1rem">
            <button id="sidebarToggle" style="background:none;border:none;cursor:pointer;font-size:1.2rem;color:var(--gray-700);display:none"><i class="fas fa-bars"></i></button>
            <div class="dropdown">
                <button class="dropdown-trigger"><i class="fas fa-user-shield"></i> Admin <i class="fas fa-chevron-down"></i></button>
                <div class="dropdown-menu">
                    <a href="<?= BASE_URL ?>/index.php"><i class="fas fa-home"></i> View Website</a>
                    <a href="#" data-modal="logoutModal"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>
</nav>
<div class="dashboard-layout">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand" style="flex-direction:column;align-items:flex-start;gap:0.25rem">
            <span style="font-size:0.7rem;color:var(--gray-400);letter-spacing:0.1em;text-transform:uppercase">Admin Panel</span>
            <img src="<?= BASE_URL ?>/assets/images/placeholders/rb-logo-transparent.png" alt="Reuchne Clinic Logo" style="height:3rem;width:auto;margin-right:0.5rem;flex-shrink:0;">Reuchne Clinic
        </div>
        <nav class="sidebar-nav">
            <?php $cp = basename($_SERVER['PHP_SELF']); ?>
            <div class="sidebar-section">
                <div class="sidebar-section-label">Overview</div>
                <a href="<?= BASE_URL ?>/admin/index.php"       class="sidebar-link <?= $cp==='index.php'?'active':'' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="<?= BASE_URL ?>/admin/revenue.php"     class="sidebar-link <?= $cp==='revenue.php'?'active':'' ?>"><i class="fas fa-chart-line"></i> Revenue</a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-section-label">Management</div>
                <a href="<?= BASE_URL ?>/admin/appointments.php" class="sidebar-link <?= $cp==='appointments.php'?'active':'' ?>"><i class="fas fa-calendar"></i> Appointments</a>
                <a href="<?= BASE_URL ?>/admin/payments.php"     class="sidebar-link <?= $cp==='payments.php'?'active':'' ?>"><i class="fas fa-credit-card"></i> Payments</a>
                <a href="<?= BASE_URL ?>/admin/patients.php"     class="sidebar-link <?= $cp==='patients.php'?'active':'' ?>"><i class="fas fa-users"></i> Patients</a>
                <a href="<?= BASE_URL ?>/admin/doctors.php"      class="sidebar-link <?= $cp==='doctors.php'?'active':'' ?>"><i class="fas fa-user-md"></i> Doctors</a>
                <a href="<?= BASE_URL ?>/admin/services.php"     class="sidebar-link <?= $cp==='services.php'?'active':'' ?>"><i class="fas fa-tooth"></i> Services</a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-section-label">Content</div>
                <a href="<?= BASE_URL ?>/admin/testimonials.php" class="sidebar-link <?= $cp==='testimonials.php'?'active':'' ?>"><i class="fas fa-star"></i> Testimonials</a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-section-label">System</div>
                <a href="<?= BASE_URL ?>/index.php" class="sidebar-link"><i class="fas fa-globe"></i> View Website</a>
                <a href="#" data-modal="logoutModal" class="sidebar-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>
    </aside>
        <?php include __DIR__ . '/logout-modal.php'; ?>

    <main class="dashboard-main">



