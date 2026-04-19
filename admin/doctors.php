<?php
$pageTitle = 'Manage Doctors';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');
$db = getDB();

$success = ''; $error = '';

// Create doctor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_doctor'])) {
    $username  = sanitize($_POST['username'] ?? '');
    $email     = sanitize($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? 'Doctor@123';
    $full_name = sanitize($_POST['full_name'] ?? '');
    $specialty = sanitize($_POST['specialty'] ?? '');
    $education = sanitize($_POST['education'] ?? '');
    $bio       = sanitize($_POST['bio'] ?? '');

    $chk = $db->prepare("SELECT id FROM users WHERE email=? OR username=?");
    $chk->bind_param("ss",$email,$username); $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
        $error = 'Email or username already exists.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username,email,password,role) VALUES (?,?,?,'doctor')");
        $stmt->bind_param("sss",$username,$email,$hash);
        if ($stmt->execute()) {
            $uid = $db->insert_id;
            $dp = $db->prepare("INSERT INTO doctor_profiles (user_id,full_name,specialty,education,bio) VALUES (?,?,?,?,?)");
            $dp->bind_param("issss",$uid,$full_name,$specialty,$education,$bio);
            $dp->execute();
            $success = 'Doctor account created!';
        } else { $error = 'Failed to create account.'; }
    }
}

// Toggle active
if (isset($_GET['toggle'])) {
    $did = (int)$_GET['toggle'];
    $db->query("UPDATE users SET is_active=NOT is_active WHERE id=$did AND role='doctor'");
    header('Location: doctors.php'); exit;
}

$doctors = $db->query("SELECT u.id, u.email, u.is_active, u.created_at, dp.full_name, dp.specialty, dp.experience_years, dp.is_featured, COUNT(a.id) total_appts
    FROM users u
    LEFT JOIN doctor_profiles dp ON u.id=dp.user_id
    LEFT JOIN appointments a ON u.id=a.doctor_id
    WHERE u.role='doctor'
    GROUP BY u.id ORDER BY u.created_at")->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/admin-sidebar.php';
?>
<div class="page-header">
    <h1>Manage Doctors</h1>
</div>

<?php if ($success): ?><div class="alert alert-success" data-dismiss="3000"><i class="fas fa-check-circle"></i><?= $success ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i><?= $error ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1.5fr;gap:1.5rem" class="responsive-grid-1">
    <!-- Create Form -->
    <div class="table-card" style="padding:1.75rem">
        <h4 style="margin-bottom:1.25rem;padding-bottom:0.75rem;border-bottom:1px solid var(--gray-100)">➕ Add New Doctor</h4>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input type="text" name="full_name" class="form-control" placeholder="Dr. Full Name" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <input type="text" name="password" class="form-control" value="Doctor@123">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Specialty</label>
                <input type="text" name="specialty" class="form-control" placeholder="e.g., General Dentistry, Orthodontics">
            </div>
            <div class="form-group">
                <label class="form-label">Education</label>
                <input type="text" name="education" class="form-control" placeholder="e.g., DMD — Centro Escolar University">
            </div>
            <div class="form-group">
                <label class="form-label">Bio</label>
                <textarea name="bio" class="form-control" rows="3" placeholder="Short biography..."></textarea>
            </div>
            <button type="submit" name="create_doctor" class="btn-primary">➕ Add Doctor</button>
        </form>
    </div>

    <!-- Doctors List -->
    <div class="table-card">
        <div class="table-header"><h3><?= count($doctors) ?> Doctor<?= count($doctors)!=1?'s':'' ?></h3></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Doctor</th><th>Specialty</th><th>Appts</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($doctors as $d): ?>
                <tr>
                    <td>
                        <div style="font-weight:600"><?= sanitize($d['full_name'] ?? '—') ?></div>
                        <div style="font-size:0.78rem;color:var(--gray-400)"><?= sanitize($d['email']) ?></div>
                        <?php if ($d['is_featured']): ?><span class="badge badge-pink" style="margin-top:2px">⭐ Featured</span><?php endif; ?>
                    </td>
                    <td style="font-size:0.85rem"><?= sanitize($d['specialty'] ?? '—') ?></td>
                    <td style="text-align:center"><span class="badge badge-pink"><?= $d['total_appts'] ?></span></td>
                    <td>
                        <span class="badge <?= $d['is_active'] ? 'badge-approved' : '' ?>" style="<?= !$d['is_active'] ? 'background:var(--gray-100);color:var(--gray-500)' : '' ?>">
                            <?= $d['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td>
                        <a href="?toggle=<?= $d['id'] ?>" class="btn-secondary btn-sm" style="font-size:0.75rem" data-confirm="<?= $d['is_active'] ? 'Deactivate' : 'Activate' ?> this doctor?">
                            <?= $d['is_active'] ? '🔒' : '🔓' ?>
                        </a>
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
