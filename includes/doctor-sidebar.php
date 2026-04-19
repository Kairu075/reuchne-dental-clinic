<?php require_once __DIR__ . '/auth.php'; requireRole('doctor'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' — ' : '' ?>Reuchne Tooth Fairy Clinic</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
    <?= isset($extraCSS) ? $extraCSS : '' ?>
</head>
<body>
<nav class="navbar scrolled">
    <div class="nav-container">
        <a href="<?= BASE_URL ?>/index.php" class="nav-logo">
            <div class="logo-fallback" style="display:flex">
                <img src="<?= BASE_URL ?>/assets/images/placeholders/rb-logo-transparent.png" alt="Logo" class="logo-icon" style="height:4rem;width:auto;flex-shrink:0;">
                <div class="logo-text"><span class="logo-name">Reuchne</span><span class="logo-sub">Tooth Fairy Clinic</span></div>
            </div>
        </a>
        <div style="display:flex;align-items:center;gap:1rem">
            <button id="sidebarToggle" style="background:none;border:none;cursor:pointer;font-size:1.2rem;color:var(--gray-700);display:none"><i class="fas fa-bars"></i></button>
            <?php $unc = getUnreadNotifications($_SESSION['user_id']); if ($unc > 0): ?>
            <a href="<?= BASE_URL ?>/doctor/notifications.php" class="notif-bell"><i class="fas fa-bell"></i><span class="notif-badge"><?= $unc ?></span></a>
            <?php endif; ?>
            <div class="dropdown">
                <button class="dropdown-trigger"><i class="fas fa-user-md"></i> Dr. <?= sanitize($_SESSION['username']) ?> <i class="fas fa-chevron-down"></i></button>
                <div class="dropdown-menu">
                    <a href="#" data-modal="logoutModal"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>
</nav>
<div class="dashboard-layout">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand"><span>👩‍⚕️</span> Doctor Portal</div>
        <nav class="sidebar-nav">
            <div class="sidebar-section">
                <?php $cp = basename($_SERVER['PHP_SELF']); ?>
                <div class="sidebar-section-label">Main</div>
                <a href="<?= BASE_URL ?>/doctor/index.php"         class="sidebar-link <?= $cp==='index.php' ? 'active' : '' ?>"><i class="fas fa-home"></i> Dashboard</a>
                <a href="<?= BASE_URL ?>/doctor/appointments.php"  class="sidebar-link <?= $cp==='appointments.php' ? 'active' : '' ?>"><i class="fas fa-calendar"></i> Appointments</a>
                <a href="<?= BASE_URL ?>/doctor/patients.php"      class="sidebar-link <?= $cp==='patients.php' ? 'active' : '' ?>"><i class="fas fa-users"></i> My Patients</a>
                <a href="<?= BASE_URL ?>/doctor/schedule.php"      class="sidebar-link <?= $cp==='schedule.php' ? 'active' : '' ?>"><i class="fas fa-clock"></i> My Schedule</a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-section-label">Communication</div>
                <a href="<?= BASE_URL ?>/doctor/announcements.php" class="sidebar-link <?= $cp==='announcements.php' ? 'active' : '' ?>"><i class="fas fa-bullhorn"></i> Announcements</a>
                <a href="<?= BASE_URL ?>/doctor/notifications.php" class="sidebar-link <?= $cp==='notifications.php' ? 'active' : '' ?>"><i class="fas fa-bell"></i> Notifications</a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-section-label">Account</div>
                <a href="#" data-modal="logoutModal" class="sidebar-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>
    </aside>
        <?php include __DIR__ . '/logout-modal.php'; ?>

    <main class="dashboard-main">



