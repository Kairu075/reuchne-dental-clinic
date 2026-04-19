<?php
$pageTitle = 'My Patients';
require_once __DIR__ . '/../includes/auth.php';
requireRole('doctor');
$db = getDB();
$uid = $_SESSION['user_id'];
$search = sanitize($_GET['search'] ?? '');

$where = $search ? "AND pp.full_name LIKE '%{$db->real_escape_string($search)}%'" : '';

$patients = $db->query("SELECT pp.*, u.email, u.created_at reg_date, COUNT(a.id) total_visits, MAX(a.appointment_date) last_visit
    FROM appointments a
    JOIN patient_profiles pp ON a.patient_id=pp.user_id
    JOIN users u ON pp.user_id=u.id
    WHERE a.doctor_id=$uid $where
    GROUP BY a.patient_id
    ORDER BY last_visit DESC")->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/doctor-sidebar.php';
?>
<div class="page-header">
    <h1>My Patients</h1>
    <p>View all patients and their records.</p>
</div>

<form method="GET" style="margin-bottom:1.5rem;display:flex;gap:0.75rem">
    <input type="text" name="search" class="form-control" style="max-width:320px" placeholder="Search by patient name..." value="<?= sanitize($_GET['search'] ?? '') ?>">
    <button type="submit" class="btn-primary btn-sm">Search</button>
    <?php if ($search): ?><a href="patients.php" class="btn-secondary btn-sm">Clear</a><?php endif; ?>
</form>

<div class="table-card">
    <div class="table-header">
        <h3><?= count($patients) ?> Patient<?= count($patients)!=1?'s':'' ?></h3>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Patient</th><th>Contact</th><th>Blood Type</th><th>Allergies</th><th>Total Visits</th><th>Last Visit</th><th>Action</th></tr></thead>
            <tbody>
            <?php if (empty($patients)): ?>
            <tr><td colspan="7" style="text-align:center;padding:3rem;color:var(--gray-400)">No patients found.</td></tr>
            <?php else: foreach ($patients as $p): ?>
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:0.75rem">
                        <div style="width:36px;height:36px;border-radius:50%;background:var(--pink-100);display:flex;align-items:center;justify-content:center;font-size:0.9rem;flex-shrink:0">👤</div>
                        <div>
                            <div style="font-weight:600"><?= sanitize($p['full_name']) ?></div>
                            <div style="font-size:0.78rem;color:var(--gray-400)"><?= sanitize($p['email']) ?></div>
                        </div>
                    </div>
                </td>
                <td><?= sanitize($p['phone'] ?? '—') ?></td>
                <td><?= $p['blood_type'] ? '<span class="badge badge-pink">'.$p['blood_type'].'</span>' : '—' ?></td>
                <td style="max-width:160px;font-size:0.82rem;color:<?= $p['allergies'] ? '#991b1b' : 'var(--gray-400)' ?>">
                    <?= $p['allergies'] ? '⚠️ '.sanitize(substr($p['allergies'],0,60)) : '—' ?>
                </td>
                <td style="text-align:center"><span class="badge badge-pink"><?= $p['total_visits'] ?></span></td>
                <td><?= $p['last_visit'] ? date('M d, Y', strtotime($p['last_visit'])) : '—' ?></td>
                <td><a href="patient-detail.php?id=<?= $p['user_id'] ?>" class="btn-secondary btn-sm" style="font-size:0.75rem"><i class="fas fa-eye"></i> View</a></td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/dashboard-footer.php'; ?>
