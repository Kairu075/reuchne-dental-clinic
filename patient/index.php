<?php
$pageTitle = 'Patient Dashboard';
require_once __DIR__ . '/../includes/auth.php';
requireRole('patient');

$db = getDB();
$uid = $_SESSION['user_id'];
$profile = getPatientProfile($uid);

// Stats
$total  = $db->prepare("SELECT COUNT(*) c FROM appointments WHERE patient_id=?"); $total->bind_param("i",$uid); $total->execute(); $totalAppt = $total->get_result()->fetch_assoc()['c'];
$upcoming = $db->prepare("SELECT COUNT(*) c FROM appointments WHERE patient_id=? AND appointment_date >= CURDATE() AND status IN ('pending','approved')"); $upcoming->bind_param("i",$uid); $upcoming->execute(); $upcomingCount = $upcoming->get_result()->fetch_assoc()['c'];
$completed = $db->prepare("SELECT COUNT(*) c FROM appointments WHERE patient_id=? AND status='completed'"); $completed->bind_param("i",$uid); $completed->execute(); $completedCount = $completed->get_result()->fetch_assoc()['c'];

// Recent appointments
$recent = $db->prepare("SELECT a.*, s.name svc_name, s.price, dp.full_name doc_name FROM appointments a JOIN services s ON a.service_id=s.id JOIN doctor_profiles dp ON a.doctor_id=dp.user_id WHERE a.patient_id=? ORDER BY a.appointment_date DESC, a.appointment_time DESC LIMIT 5");
$recent->bind_param("i",$uid);
$recent->execute();
$appointments = $recent->get_result()->fetch_all(MYSQLI_ASSOC);

// Notifications
$notifs = $db->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 5"); $notifs->bind_param("i",$uid); $notifs->execute(); $notifications = $notifs->get_result()->fetch_all(MYSQLI_ASSOC);

// Announcements
$announcements = $db->query("SELECT a.*, dp.full_name FROM announcements a JOIN doctor_profiles dp ON a.doctor_id=dp.user_id WHERE a.is_published=1 AND (a.expires_at IS NULL OR a.expires_at>=CURDATE()) ORDER BY a.created_at DESC LIMIT 3")->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/patient-sidebar.php';
?>

<div class="page-header">
    <div class="breadcrumb"><a href="<?= BASE_URL ?>">Home</a> <i class="fas fa-chevron-right" style="font-size:0.7rem"></i> Dashboard</div>
    <h1>Welcome back, <?= sanitize($profile['full_name'] ?? $_SESSION['username']) ?>! 👋</h1>
    <p>Here's a summary of your dental health journey.</p>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:var(--pink-100)"><i class="fas fa-calendar" style="color:var(--pink-500)"></i></div>
        <div class="stat-value"><?= $totalAppt ?></div>
        <div class="stat-label">Total Appointments</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#dcfce7"><i class="fas fa-clock" style="color:#16a34a"></i></div>
        <div class="stat-value"><?= $upcomingCount ?></div>
        <div class="stat-label">Upcoming</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#dbeafe"><i class="fas fa-check-circle" style="color:#2563eb"></i></div>
        <div class="stat-value"><?= $completedCount ?></div>
        <div class="stat-label">Completed</div>
    </div>
    <div class="stat-card" style="cursor:pointer" onclick="window.location='<?= BASE_URL ?>/pages/book.php'">
        <div class="stat-icon" style="background:#fef9c3"><i class="fas fa-plus-circle" style="color:#d97706"></i></div>
        <div class="stat-value" style="font-size:1.5rem">Book</div>
        <div class="stat-label">New Appointment</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem;margin-top:0" class="responsive-grid-1">
    <!-- Appointments -->
    <div>
        <div class="table-card">
            <div class="table-header">
                <h3>Recent Appointments</h3>
                <a href="<?= BASE_URL ?>/patient/appointments.php" class="btn-secondary btn-sm">View All</a>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Service</th><th>Doctor</th><th>Date & Time</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php if (empty($appointments)): ?>
                    <tr><td colspan="5" style="text-align:center;color:var(--gray-400);padding:2rem">
                        No appointments yet. <a href="<?= BASE_URL ?>/pages/book.php" style="color:var(--pink-500)">Book your first one!</a>
                    </td></tr>
                    <?php else: foreach ($appointments as $appt): ?>
                    <tr>
                        <td><?= sanitize($appt['svc_name']) ?></td>
                        <td><?= sanitize($appt['doc_name']) ?></td>
                        <td><?= date('M d, Y', strtotime($appt['appointment_date'])) ?><br><small style="color:var(--gray-400)"><?= date('g:i A', strtotime($appt['appointment_time'])) ?></small></td>
                        <td><span class="badge badge-<?= strtolower($appt['status']) ?>"><?= ucfirst($appt['status']) ?></span></td>
                        <td>
                            <?php if (in_array($appt['status'],['pending','approved']) && strtotime($appt['appointment_date']) > time()): ?>
                            <a href="<?= BASE_URL ?>/patient/cancel-appointment.php?id=<?= $appt['id'] ?>" class="btn-secondary btn-sm" style="font-size:0.75rem" data-confirm="Cancel this appointment?">Cancel</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right sidebar -->
    <div>
        <!-- Quick Book -->
        <div class="card" style="padding:1.5rem;margin-bottom:1.25rem;text-align:center">
            <div style="font-size:2.5rem;margin-bottom:0.75rem">📅</div>
            <h4 style="margin-bottom:0.5rem">Ready for your next visit?</h4>
            <p style="font-size:0.85rem;color:var(--gray-500);margin-bottom:1.25rem">Book a new appointment in minutes!</p>
            <a href="<?= BASE_URL ?>/pages/book.php" class="btn-primary w-full" style="justify-content:center">
                <i class="fas fa-calendar-plus"></i> Book Now
            </a>
        </div>

        <!-- Announcements -->
        <?php if (!empty($announcements)): ?>
        <div class="table-card">
            <div class="table-header"><h3>Clinic Announcements</h3></div>
            <div style="padding:1rem">
                <?php foreach ($announcements as $ann): ?>
                <div style="padding:1rem;border-radius:var(--radius-sm);background:var(--pink-50);margin-bottom:0.75rem;border-left:3px solid var(--pink-400)">
                    <div style="font-weight:600;font-size:0.88rem;margin-bottom:0.3rem"><?= sanitize($ann['title']) ?></div>
                    <div style="font-size:0.82rem;color:var(--gray-600)"><?= sanitize(substr($ann['content'],0,120)) ?>...</div>
                    <div style="font-size:0.75rem;color:var(--gray-400);margin-top:0.4rem">by <?= sanitize($ann['full_name']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>@media(max-width:768px){.responsive-grid-1{grid-template-columns:1fr !important}}</style>
<?php require_once __DIR__ . '/../includes/dashboard-footer.php'; ?>
