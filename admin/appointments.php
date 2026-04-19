<?php
$pageTitle = 'Manage Appointments';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');
$db = getDB();

// Approve action
if (isset($_GET['approve'])) {
    $aid = (int)$_GET['approve'];
    $db->query("UPDATE appointments SET status='approved' WHERE id=$aid");
    $r = $db->query("SELECT patient_id, appointment_date FROM appointments WHERE id=$aid")->fetch_assoc();
    if ($r) createNotification($r['patient_id'],'Appointment Approved','Your appointment on '.date('M d, Y',strtotime($r['appointment_date'])).' is confirmed!','appointment');
    header('Location: appointments.php?msg=approved'); exit;
}
if (isset($_GET['cancel'])) {
    $aid = (int)$_GET['cancel'];
    $db->query("UPDATE appointments SET status='cancelled' WHERE id=$aid");
    header('Location: appointments.php?msg=cancelled'); exit;
}

$status = sanitize($_GET['status'] ?? '');
$date   = sanitize($_GET['date'] ?? '');
$search = sanitize($_GET['search'] ?? '');

$where = "WHERE 1=1";
if ($status) $where .= " AND a.status='{$db->real_escape_string($status)}'";
if ($date)   $where .= " AND a.appointment_date='{$db->real_escape_string($date)}'";
if ($search) $where .= " AND (pp.full_name LIKE '%{$db->real_escape_string($search)}%' OR dp.full_name LIKE '%{$db->real_escape_string($search)}%')";

$appointments = $db->query("SELECT a.*, s.name svc_name, s.price, dp.full_name doc_name, pp.full_name pat_name FROM appointments a JOIN services s ON a.service_id=s.id JOIN doctor_profiles dp ON a.doctor_id=dp.user_id JOIN patient_profiles pp ON a.patient_id=pp.user_id $where ORDER BY a.appointment_date DESC, a.appointment_time")->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/admin-sidebar.php';
?>
<div class="page-header">
    <h1>Manage Appointments</h1>
    <p>View, approve, and manage all clinic appointments.</p>
</div>

<?php if (isset($_GET['msg'])): ?>
<div class="alert alert-success" data-dismiss="3000"><i class="fas fa-check-circle"></i> Appointment <?= sanitize($_GET['msg']) ?> successfully!</div>
<?php endif; ?>

<!-- Filters -->
<div style="background:white;padding:1.25rem;border-radius:var(--radius-md);border:1px solid var(--gray-100);margin-bottom:1.5rem">
    <form method="GET" style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:flex-end">
        <div class="form-group mb-0" style="flex:1;min-width:180px">
            <label class="form-label" style="font-size:0.78rem">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Patient or doctor name..." value="<?= sanitize($_GET['search'] ?? '') ?>">
        </div>
        <div class="form-group mb-0">
            <label class="form-label" style="font-size:0.78rem">Status</label>
            <select name="status" class="form-control">
                <option value="">All</option>
                <?php foreach (['pending','approved','completed','cancelled'] as $s): ?>
                <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group mb-0">
            <label class="form-label" style="font-size:0.78rem">Date</label>
            <input type="date" name="date" class="form-control" value="<?= $date ?>">
        </div>
        <div style="display:flex;gap:0.5rem;align-items:flex-end">
            <button type="submit" class="btn-primary btn-sm">Filter</button>
            <a href="appointments.php" class="btn-secondary btn-sm">Reset</a>
        </div>
    </form>
</div>

<div class="table-card">
    <div class="table-header"><h3><?= count($appointments) ?> Appointments</h3></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Patient</th><th>Doctor</th><th>Service</th><th>Date & Time</th><th>Status</th><th>Fee</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($appointments)): ?>
            <tr><td colspan="7" style="text-align:center;padding:3rem;color:var(--gray-400)">No appointments found.</td></tr>
            <?php else: foreach ($appointments as $a): ?>
            <tr>
                <td style="font-weight:600"><?= sanitize($a['pat_name']) ?></td>
                <td><?= sanitize($a['doc_name']) ?></td>
                <td><?= sanitize($a['svc_name']) ?></td>
                <td><?= date('M d, Y', strtotime($a['appointment_date'])) ?><br><small style="color:var(--gray-400)"><?= date('g:i A', strtotime($a['appointment_time'])) ?></small></td>
                <td><span class="badge badge-<?= strtolower($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
                <td>₱<?= number_format($a['price'],0) ?></td>
                <td>
                    <div style="display:flex;gap:0.4rem;flex-wrap:wrap">
                        <?php if ($a['status']==='pending'): ?>
                        <a href="?approve=<?= $a['id'] ?>" class="btn-success btn-sm" style="font-size:0.73rem">Approve</a>
                        <?php endif; ?>
                        <?php if (!in_array($a['status'],['cancelled','completed'])): ?>
                        <a href="?cancel=<?= $a['id'] ?>" class="btn-secondary btn-sm" style="font-size:0.73rem;color:#ef4444;border-color:#ef4444" data-confirm="Cancel this appointment?">Cancel</a>
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
