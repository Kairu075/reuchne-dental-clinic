<?php
$pageTitle = 'Medical History';
require_once __DIR__ . '/../includes/auth.php';
requireRole('patient');
$db = getDB();
$uid = $_SESSION['user_id'];
$profile = getPatientProfile($uid);

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medical_conditions  = sanitize($_POST['medical_conditions'] ?? '');
    $current_medications = sanitize($_POST['current_medications'] ?? '');
    $dental_history      = sanitize($_POST['dental_history'] ?? '');
    $allergies           = sanitize($_POST['allergies'] ?? '');
    $insurance_provider  = sanitize($_POST['insurance_provider'] ?? '');
    $insurance_number    = sanitize($_POST['insurance_number'] ?? '');

    if ($profile) {
        $stmt = $db->prepare("UPDATE patient_profiles SET medical_conditions=?,current_medications=?,dental_history=?,allergies=?,insurance_provider=?,insurance_number=? WHERE user_id=?");
        $stmt->bind_param("ssssssi",$medical_conditions,$current_medications,$dental_history,$allergies,$insurance_provider,$insurance_number,$uid);
    } else {
        $stmt = $db->prepare("INSERT INTO patient_profiles (medical_conditions,current_medications,dental_history,allergies,insurance_provider,insurance_number,user_id,full_name) VALUES (?,?,?,?,?,?,?,?)");
        $fn = $_SESSION['username'];
        $stmt->bind_param("sssssssi",$medical_conditions,$current_medications,$dental_history,$allergies,$insurance_provider,$insurance_number,$uid,$fn);
    }
    $stmt->execute();
    $success = 'Medical history updated!';
    $profile = getPatientProfile($uid);
}

require_once __DIR__ . '/../includes/patient-sidebar.php';
?>

<div class="page-header">
    <div class="breadcrumb"><a href="index.php">Dashboard</a> <i class="fas fa-chevron-right" style="font-size:0.7rem"></i> Medical History</div>
    <h1>Medical History</h1>
    <p>This information helps your doctor provide the best possible care.</p>
</div>

<?php if ($success): ?><div class="alert alert-success" data-dismiss="4000"><i class="fas fa-check-circle"></i><?= $success ?></div><?php endif; ?>

<div class="alert alert-info" style="margin-bottom:1.5rem">
    <i class="fas fa-shield-alt"></i>
    <div>Your medical information is kept strictly confidential and only shared with your treating doctor.</div>
</div>

<form method="POST">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem" class="responsive-grid-1">
    <div class="table-card" style="padding:1.75rem">
        <h4 style="margin-bottom:1.25rem;padding-bottom:0.75rem;border-bottom:1px solid var(--gray-100)"><i class="fas fa-heartbeat" style="color:var(--pink-400);margin-right:0.5rem"></i>Medical Conditions</h4>
        <div class="form-group">
            <label class="form-label">Current Medical Conditions</label>
            <textarea name="medical_conditions" class="form-control" rows="4" placeholder="e.g., Diabetes, Hypertension, Heart Disease, Asthma..."><?= sanitize($profile['medical_conditions'] ?? '') ?></textarea>
            <small style="color:var(--gray-400);font-size:0.78rem">List any current or chronic conditions that may affect dental treatment.</small>
        </div>
        <div class="form-group mb-0">
            <label class="form-label">Current Medications</label>
            <textarea name="current_medications" class="form-control" rows="4" placeholder="List all medications you are currently taking..."><?= sanitize($profile['current_medications'] ?? '') ?></textarea>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:1.5rem">
        <div class="table-card" style="padding:1.75rem">
            <h4 style="margin-bottom:1.25rem;padding-bottom:0.75rem;border-bottom:1px solid var(--gray-100)"><i class="fas fa-tooth" style="color:var(--pink-400);margin-right:0.5rem"></i>Dental History</h4>
            <div class="form-group mb-0">
                <label class="form-label">Previous Dental Treatments</label>
                <textarea name="dental_history" class="form-control" rows="4" placeholder="Previous extractions, braces, root canals, implants..."><?= sanitize($profile['dental_history'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="table-card" style="padding:1.75rem">
            <h4 style="margin-bottom:1.25rem;padding-bottom:0.75rem;border-bottom:1px solid var(--gray-100)"><i class="fas fa-allergies" style="color:var(--pink-400);margin-right:0.5rem"></i>Allergies</h4>
            <div class="form-group mb-0">
                <label class="form-label">Known Allergies (medications, materials, etc.)</label>
                <textarea name="allergies" class="form-control" rows="3" placeholder="Penicillin, latex, anesthesia..."><?= sanitize($profile['allergies'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="table-card" style="padding:1.75rem">
            <h4 style="margin-bottom:1.25rem;padding-bottom:0.75rem;border-bottom:1px solid var(--gray-100)"><i class="fas fa-id-card" style="color:var(--pink-400);margin-right:0.5rem"></i>Insurance Information</h4>
            <div class="form-group">
                <label class="form-label">Insurance Provider</label>
                <input type="text" name="insurance_provider" class="form-control" placeholder="e.g., Maxicare, Intellicare, PhilHealth" value="<?= sanitize($profile['insurance_provider'] ?? '') ?>">
            </div>
            <div class="form-group mb-0">
                <label class="form-label">Insurance / Member Number</label>
                <input type="text" name="insurance_number" class="form-control" placeholder="Your insurance ID number" value="<?= sanitize($profile['insurance_number'] ?? '') ?>">
            </div>
        </div>
    </div>
</div>

<div style="margin-top:1.5rem;display:flex;justify-content:flex-end">
    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Medical History</button>
</div>
</form>

<style>@media(max-width:768px){.responsive-grid-1{grid-template-columns:1fr !important}}</style>
<?php require_once __DIR__ . '/../includes/dashboard-footer.php'; ?>
