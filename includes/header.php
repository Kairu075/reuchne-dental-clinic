<?php
require_once __DIR__ . '/auth.php';
$currentUser = getCurrentUser();
$unreadCount = isLoggedIn() ? getUnreadNotifications($_SESSION['user_id']) : 0;
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' — ' : '' ?>Reuchne Tooth Fairy Clinic</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
    <link rel="icon" href="<?= BASE_URL ?>/assets/images/placeholders/rb-logo-white-transparent.png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/images/placeholders/rb-logo-white-transparent.png">
    <?= isset($extraCSS) ? $extraCSS : '' ?>
</head>
<body>
<nav class="navbar" id="mainNav">
    <div class="nav-container">
        <a href="<?= BASE_URL ?>/index.php" class="nav-logo">
            <div class="logo-placeholder">
                <img src="<?= BASE_URL ?>/assets/images/placeholders/rb-logo-transparent.png" alt="Reuchne Tooth Fairy Clinic Logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                <div class="logo-fallback" style="display:none">
                    <span class="logo-icon">🦷</span>
                    <div class="logo-text">
                        <span class="logo-name">Reuchne</span>
                        <span class="logo-sub">Tooth Fairy Clinic</span>
                    </div>
                </div>
            </div>
        </a>

        <div class="nav-menu" id="navMenu">
<a href="<?= BASE_URL ?>/index.php" class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>">Home</a>
            <?php if (isLoggedIn()): ?>
            <a href="<?= getDashboardUrl() ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/pages/doctor-profile.php" class="nav-link <?= $currentPage === 'doctor-profile.php' ? 'active' : '' ?>">Our Doctor</a>
            <a href="<?= BASE_URL ?>/pages/services.php" class="nav-link <?= $currentPage === 'services.php' ? 'active' : '' ?>">Services</a>
            <a href="<?= BASE_URL ?>/pages/book.php" class="nav-link <?= $currentPage === 'book.php' ? 'active' : '' ?>">Book Appointment</a>
            <a href="<?= BASE_URL ?>/index.php#contact" class="nav-link">Contact</a>

            <?php if (isLoggedIn()): ?>
                <div class="nav-user-menu">
                    <?php if ($unreadCount > 0): ?>
                        <a href="<?= BASE_URL ?>/<?= getRole() ?>/notifications.php" class="notif-bell">
                            <i class="fas fa-bell"></i>
                            <span class="notif-badge"><?= $unreadCount ?></span>
                        </a>
                    <?php endif; ?>
                    <div class="dropdown">
                        <button class="dropdown-trigger">
                            <i class="fas fa-user-circle"></i>
                            <?= sanitize($_SESSION['username']) ?>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a href="<?= getDashboardUrl() ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                            <a href="#" data-modal="logoutModal"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="nav-auth">
                    <a href="<?= BASE_URL ?>/pages/login.php" class="btn-nav-outline">Login</a>
                    <a href="<?= BASE_URL ?>/pages/register.php" class="btn-nav-primary">Register</a>
                </div>
            <?php endif; ?>
        </div>

        <button class="nav-toggle" id="navToggle" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<?php if (isLoggedIn()): ?>
    <?php include __DIR__ . '/logout-modal.php'; ?>
<?php endif; ?>
