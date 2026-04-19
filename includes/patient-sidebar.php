<?php require_once __DIR__ . '/auth.php'; requireRole('patient'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' — ' : '' ?>Reuchne Tooth Fairy Clinic</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
    <?= isset($extraCSS) ? $extraCSS : '' ?>
</head>
<body>

<!-- Top Navbar -->
<nav class="navbar scrolled">
    <div class="nav-container">
        <a href="<?= BASE_URL ?>/index.php" class="nav-logo">
            <div class="logo-fallback" style="display:flex">
                <img src="<?= BASE_URL ?>/assets/images/placeholders/rb-logo-transparent.png" alt="Logo" class="logo-icon" style="height:4rem;width:auto;flex-shrink:0;">
                <div class="logo-text">
                    <span class="logo-name">Reuchne</span>
                    <span class="logo-sub">Tooth Fairy Clinic</span>
                </div>
            </div>
        </a>
        <div style="display:flex;align-items:center;gap:1rem">
            <button id="sidebarToggle" style="background:none;border:none;cursor:pointer;font-size:1.2rem;color:var(--gray-700);display:none"><i class="fas fa-bars"></i></button>
            <?php $unc = getUnreadNotifications($_SESSION['user_id']); if ($unc > 0): ?>
            <a href="<?= BASE_URL ?>/patient/notifications.php" class="notif-bell"><i class="fas fa-bell"></i><span class="notif-badge"><?= $unc ?></span></a>
            <?php endif; ?>
            <div class="dropdown">
                <button class="dropdown-trigger"><i class="fas fa-user-circle"></i> <?= sanitize($_SESSION['username']) ?> <i class="fas fa-chevron-down"></i></button>
                <div class="dropdown-menu">
                    <a href="<?= BASE_URL ?>/patient/profile.php"><i class="fas fa-user"></i> My Profile</a>
                    <a href="<?= BASE_URL ?>/index.php"><i class="fas fa-home"></i> Home</a>
                    <a href="#" data-modal="logoutModal"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>
</nav>

<div class="dashboard-layout">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <img src="<?= BASE_URL ?>/assets/images/placeholders/rb-logo-transparent.png" alt="Reuchne Clinic Logo" style="height:3rem;width:auto;margin-right:0.5rem;flex-shrink:0;">Patient Portal
        <nav class="sidebar-nav">
            <div class="sidebar-section">
                <div class="sidebar-section-label">Main</div>
                <?php $cp = basename($_SERVER['PHP_SELF']); ?>
                <a href="<?= BASE_URL ?>/patient/index.php" class="sidebar-link <?= $cp==='index.php' ? 'active' : '' ?>"><i class="fas fa-home"></i> Dashboard</a>
                <a href="<?= BASE_URL ?>/pages/book.php" class="sidebar-link"><i class="fas fa-calendar-plus"></i> Book Appointment</a>
                <a href="<?= BASE_URL ?>/patient/appointments.php" class="sidebar-link <?= $cp==='appointments.php' ? 'active' : '' ?>"><i class="fas fa-calendar"></i> My Appointments</a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-section-label">Account</div>
                <a href="<?= BASE_URL ?>/patient/payments.php" class="sidebar-link <?= $cp==='payments.php' ? 'active' : '' ?>"><i class="fas fa-credit-card"></i> Payments</a>
                <a href="<?= BASE_URL ?>/patient/profile.php" class="sidebar-link <?= $cp==='profile.php' ? 'active' : '' ?>"><i class="fas fa-user-edit"></i> My Profile</a>
                <a href="<?= BASE_URL ?>/patient/medical-history.php" class="sidebar-link <?= $cp==='medical-history.php' ? 'active' : '' ?>"><i class="fas fa-notes-medical"></i> Medical History</a>
                <a href="<?= BASE_URL ?>/patient/notifications.php" class="sidebar-link <?= $cp==='notifications.php' ? 'active' : '' ?>"><i class="fas fa-bell"></i> Notifications</a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-section-label">Quick Links</div>
                <a href="<?= BASE_URL ?>/pages/services.php" class="sidebar-link"><i class="fas fa-tooth"></i> Services</a>
                <a href="<?= BASE_URL ?>/pages/doctor-profile.php" class="sidebar-link"><i class="fas fa-user-md"></i> Our Doctor</a>
                <a href="#" data-modal="logoutModal" class="sidebar-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>
    </aside>

        <?php include __DIR__ . '/logout-modal.php'; ?>

    <main class="dashboard-main">



