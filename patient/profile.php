<?php
$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/auth.php';
requireRole('patient');
$db = getDB();
$uid = $_SESSION['user_id'];
$profile = getPatientProfile($uid);
$user = getCurrentUser();

$success = ''; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name    = sanitize($_POST['full_name'] ?? '');
    $phone        = sanitize($_POST['phone'] ?? '');
    $gender       = sanitize($_POST['gender'] ?? '');
    $dob          = sanitize($_POST['date_of_birth'] ?? '');
    $address      = sanitize($_POST['address'] ?? '');
    $blood_type   = sanitize($_POST['blood_type'] ?? '');
    $allergies    = sanitize($_POST['allergies'] ?? '');
    $emergency_contact = sanitize($_POST['emergency_contact'] ?? '');
    $emergency_phone   = sanitize($_POST['emergency_phone'] ?? '');
    $preferred_payment = sanitize($_POST['preferred_payment'] ?? 'cash');

    if ($profile) {
        $stmt = $db->prepare("UPDATE patient_profiles SET full_name=?,phone=?,gender=?,date_of_birth=?,address=?,blood_type=?,allergies=?,emergency_contact=?,emergency_phone=?,preferred_payment=? WHERE user_id=?");
        $stmt->bind_param("ssssssssssi",$full_name,$phone,$gender,$dob,$address,$blood_type,$allergies,$emergency_contact,$emergency_phone,$preferred_payment,$uid);
    } else {
        $stmt = $db->prepare("INSERT INTO patient_profiles (full_name,phone,gender,date_of_birth,address,blood_type,allergies,emergency_contact,emergency_phone,preferred_payment,user_id) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssssssssi",$full_name,$phone,$gender,$dob,$address,$blood_type,$allergies,$emergency_contact,$emergency_phone,$preferred_payment,$uid);
    }
    if ($stmt->execute()) {
        $success = 'Profile updated successfully!';
        $profile = getPatientProfile($uid);
    } else {
        $error = 'Update failed. Please try again.';
    }

    // Update password if provided
    if (!empty($_POST['new_password'])) {
        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            $error = 'Passwords do not match.';
        } elseif (strlen($_POST['new_password']) < 8) {
            $error = 'Password must be at least 8 characters.';
        } else {
            $hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $db->prepare("UPDATE users SET password=? WHERE id=?")->bind_param("si",$hash,$uid) && $db->prepare("UPDATE users SET password=? WHERE id=?")->execute();
        }
    }
}

require_once __DIR__ . '/../includes/patient-sidebar.php';
?>

<div class="page-header">
    <div class="breadcrumb"><a href="index.php">Dashboard</a> <i class="fas fa-chevron-right" style="font-size:0.7rem"></i> My Profile</div>
    <h1>My Profile</h1>
    <p>Keep your information up to date for better care.</p>
</div>

<?php if ($success): ?><div class="alert alert-success" data-dismiss="4000"><i class="fas fa-check-circle"></i><?= $success ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-error"  ><i class="fas fa-exclamation-circle"></i><?= $error ?></div><?php endif; ?>

<form method="POST">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem" class="responsive-grid-1">
    <!-- Personal Info -->
    <div class="table-card" style="padding:1.75rem">
        <h4 style="margin-bottom:1.25rem;padding-bottom:0.75rem;border-bottom:1px solid var(--gray-100)"><i class="fas fa-user" style="color:var(--pink-400);margin-right:0.5rem"></i>Personal Information</h4>
        <div class="form-group">
            <label class="form-label">Full Name *</label>
            <input type="text" name="full_name" class="form-control" value="<?= sanitize($profile['full_name'] ?? '') ?>" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="tel" name="phone" class="form-control" value="<?= sanitize($profile['phone'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-control">
                    <option value="">-- Select --</option>
                    <?php foreach (['female','male','other'] as $g): ?>
                    <option value="<?= $g ?>" <?= ($profile['gender'] ?? '') === $g ? 'selected' : '' ?>><?= ucfirst($g) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-control" value="<?= sanitize($profile['date_of_birth'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Blood Type</label>
                <select name="blood_type" class="form-control">
                    <option value="">-- Select --</option>
                    <?php foreach (['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bt): ?>
                    <option value="<?= $bt ?>" <?= ($profile['blood_type'] ?? '') === $bt ? 'selected' : '' ?>><?= $bt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="2"><?= sanitize($profile['address'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Preferred Payment Method</label>
            <select name="preferred_payment" class="form-control">
                <?php foreach (['cash'=>'Cash','gcash'=>'GCash','maya'=>'Maya','credit_card'=>'Credit Card','insurance'=>'Insurance'] as $val=>$label): ?>
                <option value="<?= $val ?>" <?= ($profile['preferred_payment'] ?? 'cash') === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:1.5rem">
        <!-- Emergency Contact -->
        <div class="table-card" style="padding:1.75rem">
            <h4 style="margin-bottom:1.25rem;padding-bottom:0.75rem;border-bottom:1px solid var(--gray-100)"><i class="fas fa-phone-alt" style="color:var(--pink-400);margin-right:0.5rem"></i>Emergency Contact</h4>
            <div class="form-group">
                <label class="form-label">Contact Name</label>
                <input type="text" name="emergency_contact" class="form-control" value="<?= sanitize($profile['emergency_contact'] ?? '') ?>" placeholder="Full name">
            </div>
            <div class="form-group mb-0">
                <label class="form-label">Contact Phone</label>
                <input type="tel" name="emergency_phone" class="form-control" value="<?= sanitize($profile['emergency_phone'] ?? '') ?>" placeholder="+63 912 345 6789">
            </div>
        </div>

        <!-- Medical Info -->
        <div class="table-card" style="padding:1.75rem">
            <h4 style="margin-bottom:1.25rem;padding-bottom:0.75rem;border-bottom:1px solid var(--gray-100)"><i class="fas fa-notes-medical" style="color:var(--pink-400);margin-right:0.5rem"></i>Medical Information</h4>
            <div class="form-group">
                <label class="form-label">Known Allergies</label>
                <textarea name="allergies" class="form-control" rows="2" placeholder="List any known allergies..."><?= sanitize($profile['allergies'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Change Password -->
        <div class="table-card" style="padding:1.75rem">
            <h4 style="margin-bottom:1.25rem;padding-bottom:0.75rem;border-bottom:1px solid var(--gray-100)"><i class="fas fa-lock" style="color:var(--pink-400);margin-right:0.5rem"></i>Change Password <span style="font-size:0.78rem;color:var(--gray-400);font-weight:400">(leave blank to keep current)</span></h4>
            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-control" placeholder="Min. 8 characters">
            </div>
            <div class="form-group mb-0">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password">
            </div>
        </div>
    </div>
</div>

<div style="margin-top:1.5rem;display:flex;gap:1rem;justify-content:flex-end">
    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Changes</button>
</div>
</form>

<style>@media(max-width:768px){.responsive-grid-1{grid-template-columns:1fr !important}}</style>
<?php require_once __DIR__ . '/../includes/dashboard-footer.php'; ?>
