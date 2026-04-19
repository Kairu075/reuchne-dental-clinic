<?php
$pageTitle = 'Appointments';
require_once __DIR__ . '/../includes/auth.php';
requireRole('doctor');
$db = getDB();
$uid = $_SESSION['user_id'];
$status = sanitize($_GET['status'] ?? '');
$date   = sanitize($_GET['date'] ?? '');
$search = sanitize($_GET['search'] ?? '');

$params = [];
$types = 'i';
$where_conditions = [];
$params[] = $uid;

if ($status) {
    $where_conditions[] = 'a.status = ?';
    $types .= 's';
    $params[] = $status;
}
if ($date) {
    $where_conditions[] = 'a.appointment_date = ?';
    $types .= 's';
    $params[] = $date;
}
if ($search) {
    $where_conditions[] = 'pp.full_name LIKE ?';
    $types .= 's';
    $params[] = "%$search%";
}

$where_clause = 'WHERE a.doctor_id = ?';
if (!empty($where_conditions)) {
    $where_clause .= ' AND ' . implode(' AND ', $where_conditions);
}
$where_clause .= ' ORDER BY a.appointment_date DESC, a.appointment_time';

$query = "SELECT a.*, s.name svc_name, s.price, pp.full_name pat_name, pp.phone pat_phone FROM appointments a JOIN services s ON a.service_id=s.id JOIN patient_profiles pp ON a.patient_id=pp.user_id $where_clause";
$stmt = $db->prepare($query);
if (!$stmt) {
    error_log("Main query prepare failed: " . $db->error);
    $appointments = [];
} else {
$stmt->bind_param($types, ...$params);
$stmt->execute();
$appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
if (isset($stmt)) $stmt->close();

// Handle approve
if (isset($_GET['approve'])) {
    $aid = (int)$_GET['approve'];
    
    // Update status
    $update = $db->prepare("UPDATE appointments SET status='approved' WHERE id=? AND doctor_id=?");
    if (!$update) {
        error_log("Approve update prepare failed: " . $db->error);
        header('Location: appointments.php?error=update_failed'); exit;
    }
    $update->bind_param("ii", $aid, $uid);
    if (!$update->execute()) {
        error_log("Approve update execute failed: " . $db->error);
        header('Location: appointments.php?error=update_failed'); exit;
    }
    $update->close();
    
    // Get patient details for notification
    $r = $db->prepare("SELECT patient_id, appointment_date FROM appointments WHERE id=?");
    if (!$r) {
        error_log("Approve select prepare failed: " . $db->error);
        header('Location: appointments.php?approved=1'); exit;
    }
    $r->bind_param("i", $aid);
    $r->execute();
    $rd = $r->get_result()->fetch_assoc();
    $r->close();
    
    if ($rd && $rd['patient_id']) {
        createNotification($rd['patient_id'], 'Appointment Approved', 'Your appointment on ' . date('M d, Y', strtotime($rd['appointment_date'])) . ' has been approved!', 'appointment');
    }
    header('Location: appointments.php?approved=1'); exit;
}

require_once __DIR__ . '/../includes/doctor-sidebar.php';
?>

<div class="page-header">
    <h1>Appointments</h1>
    <p>Manage and review all patient appointments.</p>
</div>

<?php if (isset($_GET['approved'])): ?>
<div class="alert alert-success" data-dismiss="3000"><i class="fas fa-check-circle"></i> Appointment approved and patient notified!</div>
<?php elseif (isset($_GET['error'])): ?>
<div class="alert alert-danger" data-dismiss="5000"><i class="fas fa-exclamation-triangle"></i> Failed to approve appointment. Please try again.</div>
<?php endif; ?>

<!-- Filters -->
<div style="background:white;padding:1.25rem;border-radius:var(--radius-md);border:1px solid var(--gray-100);margin-bottom:1.5rem;display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end">
    <form method="GET" style="display:flex;gap:0.75rem;flex-wrap:wrap;flex:1">
        <div class="form-group mb-0" style="flex:1;min-width:180px">
            <label class="form-label" style="font-size:0.78rem">Search Patient</label>
            <input type="text" name="search" class="form-control" placeholder="Patient name..." value="<?= sanitize($_GET['search'] ?? '') ?>">
        </div>
        <div class="form-group mb-0">
            <label class="form-label" style="font-size:0.78rem">Status</label>
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <?php foreach (['pending','approved','completed','cancelled'] as $s): ?>
                <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group mb-0">
            <label class="form-label" style="font-size:0.78rem">Date</label>
            <input type="date" name="date" class="form-control" value="<?= sanitize($_GET['date'] ?? '') ?>">
        </div>
        <div style="display:flex;gap:0.5rem;align-items:flex-end">
            <button type="submit" class="btn-primary btn-sm">Filter</button>
            <a href="appointments.php" class="btn-secondary btn-sm">Reset</a>
        </div>
    </form>
</div>

<div class="table-card">
    <div class="table-header">
        <h3><?= count($appointments) ?> Appointment<?= count($appointments)!=1?'s':'' ?></h3>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Patient</th><th>Service</th><th>Date & Time</th><th>Status</th><th>Notes</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($appointments)): ?>
            <tr><td colspan="6" style="text-align:center;padding:3rem;color:var(--gray-400)">No appointments found.</td></tr>
            <?php else: foreach ($appointments as $a): ?>
            <tr>
                <td>
                    <div style="font-weight:600"><?= sanitize($a['pat_name']) ?></div>
                    <div style="font-size:0.78rem;color:var(--gray-400)"><?= sanitize($a['pat_phone'] ?? '') ?></div>
                </td>
                <td>
                    <div><?= sanitize($a['svc_name']) ?></div>
                    <div style="font-size:0.78rem;color:var(--pink-500)">₱<?= number_format($a['price'],0) ?></div>
                </td>
                <td>
                    <div style="font-weight:500"><?= date('M d, Y', strtotime($a['appointment_date'])) ?></div>
                    <div style="font-size:0.8rem;color:var(--gray-400)"><?= date('g:i A', strtotime($a['appointment_time'])) ?></div>
                </td>
                <td><span class="badge badge-<?= strtolower($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
                <td><span style="font-size:0.8rem;color:var(--gray-500)"><?= $a['patient_notes'] ? sanitize(substr($a['patient_notes'],0,40)).'…' : '—' ?></span></td>
                <td>
                    <div style="display:flex;gap:0.4rem;flex-wrap:wrap">
                        <?php if ($a['status']==='pending'): ?>
                        <a href="?approve=<?= $a['id'] ?>" class="btn-success btn-sm" style="font-size:0.75rem">Approve</a>
                        <?php endif; ?>
                        <?php if (in_array($a['status'],['approved','completed'])): ?>
                        <a href="add-notes.php?id=<?= $a['id'] ?>" class="btn-primary btn-sm" style="font-size:0.75rem"><i class="fas fa-edit"></i> Notes</a>
                        <?php endif; ?>
                        <?php if ($a['status']==='approved'): ?>
                        <a href="complete-appointment.php?id=<?= $a['id'] ?>" class="btn-secondary btn-sm" style="font-size:0.75rem;background:#dbeafe;color:#1e40af;border-color:#93c5fd">Complete</a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/dashboard-footer.php'; ?>
