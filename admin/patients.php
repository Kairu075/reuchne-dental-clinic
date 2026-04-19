<?php
$pageTitle = 'Manage Patients';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');
$db = getDB();

// Toggle active
if (isset($_GET['toggle'])) {
    $uid = (int)$_GET['toggle'];
    $db->query("UPDATE users SET is_active = NOT is_active WHERE id=$uid AND role='patient'");
    header('Location: patients.php'); exit;
}

$search = sanitize($_GET['search'] ?? '');
$where = $search ? "AND (pp.full_name LIKE '%{$db->real_escape_string($search)}%' OR u.email LIKE '%{$db->real_escape_string($search)}%')" : '';

$patients = $db->query("SELECT u.id, u.email, u.is_active, u.created_at,
    pp.full_name, pp.phone, pp.gender, pp.date_of_birth,
    COUNT(a.id) total_appts
    FROM users u
    LEFT JOIN patient_profiles pp ON u.id=pp.user_id
    LEFT JOIN appointments a ON u.id=a.patient_id
    WHERE u.role='patient' $where
    GROUP BY u.id
    ORDER BY u.created_at DESC")->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/admin-sidebar.php';
?>
<div class="page-header">
    <h1>Manage Patients</h1>
    <p>View and manage all registered patients.</p>
</div>

<div style="display:flex;gap:0.75rem;margin-bottom:1.5rem;align-items:center">
    <form method="GET" style="display:flex;gap:0.75rem;flex:1">
        <input type="text" name="search" class="form-control" style="max-width:320px" placeholder="Search by name or email..." value="<?= sanitize($_GET['search'] ?? '') ?>">
        <button type="submit" class="btn-primary btn-sm">Search</button>
        <?php if ($search): ?><a href="patients.php" class="btn-secondary btn-sm">Clear</a><?php endif; ?>
    </form>
    <span style="font-size:0.85rem;color:var(--gray-500)"><?= count($patients) ?> patients total</span>
</div>

<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Patient</th><th>Contact</th><th>Gender</th><th>Appointments</th><th>Joined</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($patients)): ?>
            <tr><td colspan="7" style="text-align:center;padding:3rem;color:var(--gray-400)">No patients found.</td></tr>
            <?php else: foreach ($patients as $p): ?>
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:0.75rem">
                        <div style="width:36px;height:36px;border-radius:50%;background:var(--pink-100);display:flex;align-items:center;justify-content:center;font-size:0.9rem;flex-shrink:0">👤</div>
                        <div>
                            <div style="font-weight:600"><?= sanitize($p['full_name'] ?? 'No profile') ?></div>
                            <div style="font-size:0.78rem;color:var(--gray-400)"><?= sanitize($p['email']) ?></div>
                        </div>
                    </div>
                </td>
                <td><?= sanitize($p['phone'] ?? '—') ?></td>
                <td><?= $p['gender'] ? ucfirst($p['gender']) : '—' ?></td>
                <td style="text-align:center"><span class="badge badge-pink"><?= $p['total_appts'] ?></span></td>
                <td><?= date('M d, Y', strtotime($p['created_at'])) ?></td>
                <td>
                    <span class="badge <?= $p['is_active'] ? 'badge-approved' : '' ?>" style="<?= !$p['is_active'] ? 'background:var(--gray-100);color:var(--gray-500)' : '' ?>">
                        <?= $p['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                </td>
                <td>
                    <a href="?toggle=<?= $p['id'] ?>" class="btn-secondary btn-sm" style="font-size:0.75rem" data-confirm="<?= $p['is_active'] ? 'Deactivate' : 'Activate' ?> this patient?">
                        <?= $p['is_active'] ? '🔒 Deactivate' : '🔓 Activate' ?>
                    </a>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/dashboard-footer.php'; ?>
