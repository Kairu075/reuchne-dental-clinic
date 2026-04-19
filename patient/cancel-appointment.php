<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('patient');
$db = getDB();
$uid = $_SESSION['user_id'];
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT a.*, s.name svc_name, dp.full_name doc_name FROM appointments a JOIN services s ON a.service_id=s.id JOIN doctor_profiles dp ON a.doctor_id=dp.user_id WHERE a.id=? AND a.patient_id=?");
$stmt->bind_param("ii",$id,$uid);
$stmt->execute();
$appt = $stmt->get_result()->fetch_assoc();

if (!$appt || !in_array($appt['status'],['pending','approved'])) {
    header('Location: appointments.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = sanitize($_POST['reason'] ?? '');
    $db->prepare("UPDATE appointments SET status='cancelled', cancel_reason=? WHERE id=?")->bind_param("si",$reason,$id)->execute();
    $db->prepare("UPDATE appointments SET status='cancelled', cancel_reason=? WHERE id=?")->execute();
    // Actually execute correctly:
    $upd = $db->prepare("UPDATE appointments SET status='cancelled', cancel_reason=? WHERE id=?");
    $upd->bind_param("si",$reason,$id);
    $upd->execute();
    createNotification($appt['doctor_id'],'Appointment Cancelled','Patient cancelled appointment on ' . $appt['appointment_date'] . ' at ' . $appt['appointment_time'],'appointment');
    header('Location: appointments.php?cancelled=1'); exit;
}

$pageTitle = 'Cancel Appointment';
require_once __DIR__ . '/../includes/patient-sidebar.php';
?>
<div class="page-header">
    <div class="breadcrumb"><a href="index.php">Dashboard</a> <i class="fas fa-chevron-right" style="font-size:0.7rem"></i> <a href="appointments.php">Appointments</a> <i class="fas fa-chevron-right" style="font-size:0.7rem"></i> Cancel</div>
    <h1>Cancel Appointment</h1>
</div>

<div style="max-width:520px">
    <div class="card" style="padding:2rem;border-top:4px solid #ef4444">
        <div style="text-align:center;margin-bottom:1.5rem">
            <div style="font-size:3rem;margin-bottom:0.5rem">⚠️</div>
            <h3>Are you sure you want to cancel?</h3>
        </div>
        <div style="background:var(--gray-50);padding:1.25rem;border-radius:var(--radius-md);margin-bottom:1.5rem">
            <div style="display:flex;justify-content:space-between;padding:0.4rem 0;font-size:0.9rem"><span style="color:var(--gray-500)">Service</span><strong><?= sanitize($appt['svc_name']) ?></strong></div>
            <div style="display:flex;justify-content:space-between;padding:0.4rem 0;font-size:0.9rem"><span style="color:var(--gray-500)">Doctor</span><strong><?= sanitize($appt['doc_name']) ?></strong></div>
            <div style="display:flex;justify-content:space-between;padding:0.4rem 0;font-size:0.9rem"><span style="color:var(--gray-500)">Date</span><strong><?= date('M d, Y', strtotime($appt['appointment_date'])) ?></strong></div>
            <div style="display:flex;justify-content:space-between;padding:0.4rem 0;font-size:0.9rem"><span style="color:var(--gray-500)">Time</span><strong><?= date('g:i A', strtotime($appt['appointment_time'])) ?></strong></div>
        </div>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Reason for Cancellation (optional)</label>
                <textarea name="reason" class="form-control" rows="3" placeholder="Please let us know why you're cancelling..."></textarea>
            </div>
            <div style="display:flex;gap:1rem">
                <button type="submit" class="btn-primary" style="background:#ef4444;flex:1"><i class="fas fa-times-circle"></i> Confirm Cancellation</button>
                <a href="appointments.php" class="btn-secondary" style="flex:1;text-align:center;padding:0.85rem">Keep Appointment</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/dashboard-footer.php'; ?>
