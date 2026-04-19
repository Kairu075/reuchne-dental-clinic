<?php
$pageTitle = 'Add Clinical Notes';
require_once __DIR__ . '/../includes/auth.php';
requireRole('doctor');
$db = getDB();
$uid = $_SESSION['user_id'];
$id  = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT a.*, s.name svc_name, s.price, pp.full_name pat_name, pp.medical_conditions, pp.allergies, pp.dental_history FROM appointments a JOIN services s ON a.service_id=s.id JOIN patient_profiles pp ON a.patient_id=pp.user_id WHERE a.id=? AND a.doctor_id=?");
$stmt->bind_param("ii",$id,$uid);
$stmt->execute();
$appt = $stmt->get_result()->fetch_assoc();

if (!$appt) { header('Location: appointments.php'); exit; }

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diagnosis    = sanitize($_POST['diagnosis'] ?? '');
    $prescription = sanitize($_POST['prescription'] ?? '');
    $notes        = sanitize($_POST['notes'] ?? '');
    $complete     = isset($_POST['mark_complete']);

    $newStatus = $complete ? 'completed' : $appt['status'];
    $upd = $db->prepare("UPDATE appointments SET diagnosis=?,prescription=?,notes=?,status=? WHERE id=?");
    $upd->bind_param("ssssi",$diagnosis,$prescription,$notes,$newStatus,$id);
    $upd->execute();

    if ($complete) {
        createNotification($appt['patient_id'],'Appointment Completed','Your appointment on '.date('M d, Y',strtotime($appt['appointment_date'])).' has been marked complete.','appointment');
        header('Location: appointments.php?completed=1'); exit;
    }
    $success = 'Notes saved successfully!';
    $appt = array_merge($appt, ['diagnosis'=>$diagnosis,'prescription'=>$prescription,'notes'=>$notes]);
}

require_once __DIR__ . '/../includes/doctor-sidebar.php';
?>
<div class="page-header">
    <div class="breadcrumb"><a href="index.php">Dashboard</a> <i class="fas fa-chevron-right" style="font-size:0.7rem"></i> <a href="appointments.php">Appointments</a> <i class="fas fa-chevron-right" style="font-size:0.7rem"></i> Clinical Notes</div>
    <h1>Clinical Notes</h1>
</div>

<?php if ($success): ?><div class="alert alert-success" data-dismiss="3000"><i class="fas fa-check-circle"></i><?= $success ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem" class="responsive-grid-1">
    <div>
        <form method="POST">
            <div class="table-card" style="padding:1.75rem">
                <h4 style="margin-bottom:1.25rem;padding-bottom:0.75rem;border-bottom:1px solid var(--gray-100)">
                    <i class="fas fa-notes-medical" style="color:var(--pink-400);margin-right:0.5rem"></i>Clinical Notes for <?= sanitize($appt['pat_name']) ?>
                </h4>
                <div class="form-group">
                    <label class="form-label">Diagnosis</label>
                    <textarea name="diagnosis" class="form-control" rows="4" placeholder="Enter diagnosis..."><?= sanitize($appt['diagnosis'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Prescription / Treatment Plan</label>
                    <textarea name="prescription" class="form-control" rows="4" placeholder="Medications, follow-up instructions..."><?= sanitize($appt['prescription'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Doctor's Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Internal notes..."><?= sanitize($appt['notes'] ?? '') ?></textarea>
                </div>
                <div style="display:flex;gap:1rem;margin-top:0.5rem">
                    <button type="submit" name="save" class="btn-secondary"><i class="fas fa-save"></i> Save Notes</button>
                    <?php if ($appt['status']==='approved'): ?>
                    <button type="submit" name="mark_complete" class="btn-primary" style="background:#22c55e" onclick="return confirm('Mark this appointment as completed?')">
                        <i class="fas fa-check-circle"></i> Mark as Completed
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- Patient Info -->
    <div>
        <div class="table-card" style="padding:1.5rem;margin-bottom:1rem">
            <h4 style="margin-bottom:1rem;font-size:0.9rem">Appointment Details</h4>
            <div style="font-size:0.85rem">
                <div style="display:flex;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid var(--gray-50)"><span style="color:var(--gray-500)">Service</span><strong><?= sanitize($appt['svc_name']) ?></strong></div>
                <div style="display:flex;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid var(--gray-50)"><span style="color:var(--gray-500)">Date</span><strong><?= date('M d, Y', strtotime($appt['appointment_date'])) ?></strong></div>
                <div style="display:flex;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid var(--gray-50)"><span style="color:var(--gray-500)">Time</span><strong><?= date('g:i A', strtotime($appt['appointment_time'])) ?></strong></div>
                <div style="display:flex;justify-content:space-between;padding:0.5rem 0"><span style="color:var(--gray-500)">Status</span><span class="badge badge-<?= strtolower($appt['status']) ?>"><?= ucfirst($appt['status']) ?></span></div>
            </div>
        </div>
        <?php if ($appt['medical_conditions'] || $appt['allergies'] || $appt['dental_history']): ?>
        <div class="table-card" style="padding:1.5rem">
            <h4 style="margin-bottom:1rem;font-size:0.9rem"><i class="fas fa-heartbeat" style="color:var(--pink-400);margin-right:0.4rem"></i>Medical History</h4>
            <?php if ($appt['allergies']): ?>
            <div style="background:#fee2e2;padding:0.75rem;border-radius:var(--radius-sm);margin-bottom:0.75rem">
                <div style="font-size:0.75rem;font-weight:700;color:#991b1b;letter-spacing:0.06em;margin-bottom:0.25rem">⚠️ ALLERGIES</div>
                <div style="font-size:0.83rem;color:#991b1b"><?= sanitize($appt['allergies']) ?></div>
            </div>
            <?php endif; ?>
            <?php if ($appt['medical_conditions']): ?>
            <div style="margin-bottom:0.75rem">
                <div style="font-size:0.75rem;font-weight:700;color:var(--gray-500);margin-bottom:0.25rem">CONDITIONS</div>
                <div style="font-size:0.83rem;color:var(--gray-700)"><?= sanitize($appt['medical_conditions']) ?></div>
            </div>
            <?php endif; ?>
            <?php if ($appt['dental_history']): ?>
            <div>
                <div style="font-size:0.75rem;font-weight:700;color:var(--gray-500);margin-bottom:0.25rem">DENTAL HISTORY</div>
                <div style="font-size:0.83rem;color:var(--gray-700)"><?= sanitize($appt['dental_history']) ?></div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>@media(max-width:768px){.responsive-grid-1{grid-template-columns:1fr !important}}</style>
<?php require_once __DIR__ . '/../includes/dashboard-footer.php'; ?>
