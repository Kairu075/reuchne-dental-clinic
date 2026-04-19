<?php
$pageTitle = 'Doctor Dashboard';
require_once __DIR__ . '/../includes/auth.php';
requireRole('doctor');
$db = getDB();
$uid = $_SESSION['user_id'];

// Stats
$todayAppts = $db->prepare("SELECT COUNT(*) c FROM appointments WHERE doctor_id=? AND appointment_date=CURDATE() AND status NOT IN ('cancelled')"); $todayAppts->bind_param("i",$uid); $todayAppts->execute(); $todayCount = $todayAppts->get_result()->fetch_assoc()['c'];
$pending = $db->prepare("SELECT COUNT(*) c FROM appointments WHERE doctor_id=? AND status='pending'"); $pending->bind_param("i",$uid); $pending->execute(); $pendingCount = $pending->get_result()->fetch_assoc()['c'];
$totalPatients = $db->prepare("SELECT COUNT(DISTINCT patient_id) c FROM appointments WHERE doctor_id=?"); $totalPatients->bind_param("i",$uid); $totalPatients->execute(); $patientCount = $totalPatients->get_result()->fetch_assoc()['c'];
$completed = $db->prepare("SELECT COUNT(*) c FROM appointments WHERE doctor_id=? AND status='completed'"); $completed->bind_param("i",$uid); $completed->execute(); $completedCount = $completed->get_result()->fetch_assoc()['c'];

// Today's appointments
$todayList = $db->prepare("SELECT a.*, s.name svc_name, pp.full_name pat_name, pp.phone pat_phone FROM appointments a JOIN services s ON a.service_id=s.id JOIN patient_profiles pp ON a.patient_id=pp.user_id WHERE a.doctor_id=? AND a.appointment_date=CURDATE() AND a.status NOT IN ('cancelled') ORDER BY a.appointment_time");
$todayList->bind_param("i",$uid); $todayList->execute();
$todayAppointments = $todayList->get_result()->fetch_all(MYSQLI_ASSOC);

// Upcoming (next 7 days)
$upcoming = $db->prepare("SELECT a.*, s.name svc_name, pp.full_name pat_name FROM appointments a JOIN services s ON a.service_id=s.id JOIN patient_profiles pp ON a.patient_id=pp.user_id WHERE a.doctor_id=? AND a.appointment_date > CURDATE() AND a.appointment_date <= DATE_ADD(CURDATE(),INTERVAL 7 DAY) AND a.status NOT IN ('cancelled') ORDER BY a.appointment_date,a.appointment_time LIMIT 8");
$upcoming->bind_param("i",$uid); $upcoming->execute();
$upcomingAppointments = $upcoming->get_result()->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/doctor-sidebar.php';
?>

<div class="page-header">
    <h1>Good <?= date('H') < 12 ? 'Morning' : (date('H') < 17 ? 'Afternoon' : 'Evening') ?>, Dr. <?= sanitize($_SESSION['username']) ?>! 👋</h1>
    <p><?= date('l, F d, Y') ?> • <?= $todayCount ?> appointment<?= $todayCount!=1 ? 's' : '' ?> today</p>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:var(--pink-100)"><i class="fas fa-calendar-day" style="color:var(--pink-500)"></i></div>
        <div class="stat-value"><?= $todayCount ?></div>
        <div class="stat-label">Today's Appointments</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fef9c3"><i class="fas fa-clock" style="color:#d97706"></i></div>
        <div class="stat-value"><?= $pendingCount ?></div>
        <div class="stat-label">Pending Approval</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#dbeafe"><i class="fas fa-users" style="color:#2563eb"></i></div>
        <div class="stat-value"><?= $patientCount ?></div>
        <div class="stat-label">Total Patients</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#dcfce7"><i class="fas fa-check-double" style="color:#16a34a"></i></div>
        <div class="stat-value"><?= $completedCount ?></div>
        <div class="stat-label">Completed</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem" class="responsive-grid-1">
    <!-- Today's Schedule -->
    <div>
        <div class="table-card">
            <div class="table-header">
                <h3>Today's Schedule</h3>
                <a href="appointments.php" class="btn-secondary btn-sm">All Appointments</a>
            </div>
            <?php if (empty($todayAppointments)): ?>
            <div style="text-align:center;padding:3rem;color:var(--gray-400)">
                <div style="font-size:2.5rem;margin-bottom:0.75rem">✅</div>
                <p>No appointments scheduled for today!</p>
            </div>
            <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Time</th><th>Patient</th><th>Service</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($todayAppointments as $a): ?>
                    <tr>
                        <td style="font-weight:600;color:var(--pink-500)"><?= date('g:i A', strtotime($a['appointment_time'])) ?></td>
                        <td>
                            <div style="font-weight:500"><?= sanitize($a['pat_name']) ?></div>
                            <div style="font-size:0.78rem;color:var(--gray-400)"><?= sanitize($a['pat_phone'] ?? '') ?></div>
                        </td>
                        <td><?= sanitize($a['svc_name']) ?></td>
                        <td><span class="badge badge-<?= strtolower($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
                        <td>
                            <?php if ($a['status']==='approved'): ?>
                            <a href="add-notes.php?id=<?= $a['id'] ?>" class="btn-primary btn-sm" style="font-size:0.75rem"><i class="fas fa-notes-medical"></i> Add Notes</a>
                            <?php elseif ($a['status']==='pending'): ?>
                            <a href="approve-appointment.php?id=<?= $a['id'] ?>" class="btn-success btn-sm" style="font-size:0.75rem">Approve</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- Upcoming This Week -->
        <?php if (!empty($upcomingAppointments)): ?>
        <div class="table-card" style="margin-top:1.5rem">
            <div class="table-header"><h3>Upcoming (Next 7 Days)</h3></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Date</th><th>Time</th><th>Patient</th><th>Service</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($upcomingAppointments as $a): ?>
                    <tr>
                        <td><?= date('M d', strtotime($a['appointment_date'])) ?></td>
                        <td><?= date('g:i A', strtotime($a['appointment_time'])) ?></td>
                        <td><?= sanitize($a['pat_name']) ?></td>
                        <td><?= sanitize($a['svc_name']) ?></td>
                        <td><span class="badge badge-<?= strtolower($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Quick Actions -->
    <div>
        <?php if ($pendingCount > 0): ?>
        <div class="card" style="padding:1.5rem;margin-bottom:1.25rem;border-left:4px solid #f59e0b;background:#fffbeb">
            <div style="font-weight:700;color:#92400e;margin-bottom:0.5rem"><i class="fas fa-exclamation-triangle" style="margin-right:0.5rem"></i><?= $pendingCount ?> Pending Approval</div>
            <p style="font-size:0.85rem;color:#78350f;margin-bottom:1rem"><?= $pendingCount ?> appointment<?= $pendingCount!=1?'s':'' ?> waiting for your approval.</p>
            <a href="appointments.php?status=pending" class="btn-primary btn-sm w-full" style="text-align:center;justify-content:center">Review Now</a>
        </div>
        <?php endif; ?>

        <div class="table-card">
            <div class="table-header"><h3>Quick Actions</h3></div>
            <div style="padding:1rem;display:flex;flex-direction:column;gap:0.75rem">
                <a href="appointments.php" style="display:flex;align-items:center;gap:0.75rem;padding:0.85rem;border-radius:var(--radius-sm);border:1px solid var(--gray-100);transition:all 0.2s;color:var(--gray-700)" onmouseover="this.style.borderColor='var(--pink-300)'" onmouseout="this.style.borderColor='var(--gray-100)'">
                    <i class="fas fa-calendar" style="color:var(--pink-400);width:20px"></i> All Appointments
                </a>
                <a href="patients.php" style="display:flex;align-items:center;gap:0.75rem;padding:0.85rem;border-radius:var(--radius-sm);border:1px solid var(--gray-100);transition:all 0.2s;color:var(--gray-700)" onmouseover="this.style.borderColor='var(--pink-300)'" onmouseout="this.style.borderColor='var(--gray-100)'">
                    <i class="fas fa-users" style="color:var(--pink-400);width:20px"></i> Patient Records
                </a>
                <a href="announcements.php" style="display:flex;align-items:center;gap:0.75rem;padding:0.85rem;border-radius:var(--radius-sm);border:1px solid var(--gray-100);transition:all 0.2s;color:var(--gray-700)" onmouseover="this.style.borderColor='var(--pink-300)'" onmouseout="this.style.borderColor='var(--gray-100)'">
                    <i class="fas fa-bullhorn" style="color:var(--pink-400);width:20px"></i> Post Announcement
                </a>
                <a href="schedule.php" style="display:flex;align-items:center;gap:0.75rem;padding:0.85rem;border-radius:var(--radius-sm);border:1px solid var(--gray-100);transition:all 0.2s;color:var(--gray-700)" onmouseover="this.style.borderColor='var(--pink-300)'" onmouseout="this.style.borderColor='var(--gray-100)'">
                    <i class="fas fa-clock" style="color:var(--pink-400);width:20px"></i> Manage Schedule
                </a>
            </div>
        </div>
    </div>
</div>

<style>@media(max-width:768px){.responsive-grid-1{grid-template-columns:1fr !important}}</style>
<?php require_once __DIR__ . '/../includes/dashboard-footer.php'; ?>
