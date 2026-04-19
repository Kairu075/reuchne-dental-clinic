<?php
$pageTitle = 'Patient Detail';
require_once __DIR__ . '/../includes/auth.php';
requireRole('doctor');
$db = getDB();
$uid_doc = $_SESSION['user_id'];
$pid = (int)($_GET['id'] ?? 0);

// Verify this patient belongs to doctor
$chk = $db->prepare("SELECT COUNT(*) c FROM appointments WHERE doctor_id=? AND patient_id=?");
$chk->bind_param("ii",$uid_doc,$pid); $chk->execute();
if ($chk->get_result()->fetch_assoc()['c'] == 0) { header('Location: patients.php'); exit; }

$profile = getPatientProfile($pid);
$user    = $db->prepare("SELECT email,created_at FROM users WHERE id=?"); $user->bind_param("i",$pid); $user->execute(); $userdata = $user->get_result()->fetch_assoc();

$appts = $db->prepare("SELECT a.*,s.name svc_name FROM appointments a JOIN services s ON a.service_id=s.id WHERE a.doctor_id=? AND a.patient_id=? ORDER BY a.appointment_date DESC");
$appts->bind_param("ii",$uid_doc,$pid); $appts->execute();
$appointments = $appts->get_result()->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/doctor-sidebar.php';
?>
<div class="page-header">
    <div class="breadcrumb"><a href="index.php">Dashboard</a> <i class="fas fa-chevron-right" style="font-size:0.7rem"></i> <a href="patients.php">Patients</a> <i class="fas fa-chevron-right" style="font-size:0.7rem"></i> <?= sanitize($profile['full_name'] ?? '') ?></div>
    <h1><?= sanitize($profile['full_name'] ?? 'Patient') ?></h1>
</div>

<div style="display:grid;grid-template-columns:320px 1fr;gap:1.5rem" class="responsive-grid-1">
    <!-- Profile Card -->
    <div>
        <div class="table-card" style="padding:1.75rem;margin-bottom:1rem">
            <div style="text-align:center;margin-bottom:1.5rem">
                <div style="width:72px;height:72px;border-radius:50%;background:var(--pink-100);display:flex;align-items:center;justify-content:center;font-size:2rem;margin:0 auto 0.75rem">👤</div>
                <div style="font-weight:700;font-size:1.1rem"><?= sanitize($profile['full_name'] ?? '—') ?></div>
                <div style="font-size:0.82rem;color:var(--gray-400)"><?= sanitize($userdata['email'] ?? '') ?></div>
            </div>
            <?php $fields = [
                ['label'=>'Phone','value'=>$profile['phone'] ?? '—'],
                ['label'=>'Gender','value'=>$profile['gender'] ? ucfirst($profile['gender']) : '—'],
                ['label'=>'Date of Birth','value'=>$profile['date_of_birth'] ? date('M d, Y',strtotime($profile['date_of_birth'])) : '—'],
                ['label'=>'Blood Type','value'=>$profile['blood_type'] ?? '—'],
                ['label'=>'Patient Since','value'=>date('M Y', strtotime($userdata['created_at'] ?? 'now'))],
            ];
            foreach ($fields as $f): ?>
            <div style="display:flex;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid var(--gray-50);font-size:0.85rem">
                <span style="color:var(--gray-500)"><?= $f['label'] ?></span>
                <span style="font-weight:500"><?= sanitize($f['value']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Medical Alert Box -->
        <?php if ($profile['allergies']): ?>
        <div style="background:#fee2e2;padding:1.25rem;border-radius:var(--radius-md);border-left:4px solid #ef4444;margin-bottom:1rem">
            <div style="font-weight:700;color:#991b1b;margin-bottom:0.5rem;font-size:0.85rem">⚠️ ALLERGIES</div>
            <div style="font-size:0.85rem;color:#991b1b"><?= nl2br(sanitize($profile['allergies'])) ?></div>
        </div>
        <?php endif; ?>

        <?php if ($profile['medical_conditions']): ?>
        <div class="table-card" style="padding:1.25rem">
            <div style="font-weight:700;font-size:0.82rem;color:var(--gray-500);letter-spacing:0.08em;text-transform:uppercase;margin-bottom:0.5rem">Medical Conditions</div>
            <div style="font-size:0.85rem;color:var(--gray-700)"><?= nl2br(sanitize($profile['medical_conditions'])) ?></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Appointments -->
    <div class="table-card">
        <div class="table-header"><h3>Appointment History (<?= count($appointments) ?>)</h3></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Date</th><th>Service</th><th>Status</th><th>Diagnosis</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($appointments as $a): ?>
                <tr>
                    <td><?= date('M d, Y', strtotime($a['appointment_date'])) ?><br><small style="color:var(--gray-400)"><?= date('g:i A',strtotime($a['appointment_time'])) ?></small></td>
                    <td><?= sanitize($a['svc_name']) ?></td>
                    <td><span class="badge badge-<?= strtolower($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
                    <td style="max-width:200px;font-size:0.82rem;color:var(--gray-600)"><?= $a['diagnosis'] ? sanitize(substr($a['diagnosis'],0,60)).'…' : '—' ?></td>
                    <td>
                        <?php if (in_array($a['status'],['approved','completed'])): ?>
                        <a href="add-notes.php?id=<?= $a['id'] ?>" class="btn-secondary btn-sm" style="font-size:0.73rem"><i class="fas fa-edit"></i> Notes</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>@media(max-width:768px){.responsive-grid-1{grid-template-columns:1fr !important}}</style>
<?php require_once __DIR__ . '/../includes/dashboard-footer.php'; ?>
