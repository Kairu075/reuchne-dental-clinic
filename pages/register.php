<?php
$pageTitle = 'Create Account';
require_once __DIR__ . '/../includes/auth.php';

if (isLoggedIn()) { header('Location: ' . getDashboardUrl()); exit; }

$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = sanitize($_POST['username'] ?? '');
    $email     = sanitize($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';
    $full_name = sanitize($_POST['full_name'] ?? '');
    $phone     = sanitize($_POST['phone'] ?? '');
    $gender    = sanitize($_POST['gender'] ?? '');
    $dob       = sanitize($_POST['date_of_birth'] ?? '');

    if ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        $db = getDB();
        // Check existing
        $chk = $db->prepare("SELECT id FROM users WHERE email=? OR username=?");
        $chk->bind_param("ss",$email,$username);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $error = 'Email or username already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (username,email,password,role) VALUES (?,?,?,'patient')");
            $stmt->bind_param("sss",$username,$email,$hash);
            if ($stmt->execute()) {
                $uid = $db->insert_id;
                $p = $db->prepare("INSERT INTO patient_profiles (user_id,full_name,phone,gender,date_of_birth) VALUES (?,?,?,?,?)");
                $p->bind_param("issss",$uid,$full_name,$phone,$gender,$dob);
                $p->execute();
                header('Location: ' . BASE_URL . '/pages/login.php?registered=1');
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-page" style="padding-top:120px">
    <div class="auth-card" style="max-width:560px">
        <div class="auth-header">
            <span class="auth-icon">✨</span>
            <h2>Create Your Account</h2>
            <p>Join Reuchne Tooth Fairy Clinic and start your smile journey!</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="full_name" class="form-control" placeholder="Your full name" required value="<?= sanitize($_POST['full_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" class="form-control" placeholder="Choose a username" required value="<?= sanitize($_POST['username'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-control" placeholder="you@email.com" required value="<?= sanitize($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" class="form-control" placeholder="+63 912 345 6789" value="<?= sanitize($_POST['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-control">
                        <option value="">-- Select --</option>
                        <option value="female" <?= ($_POST['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                        <option value="male"   <?= ($_POST['gender'] ?? '') === 'male'   ? 'selected' : '' ?>>Male</option>
                        <option value="other"  <?= ($_POST['gender'] ?? '') === 'other'  ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-control" value="<?= sanitize($_POST['date_of_birth'] ?? '') ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-control" placeholder="Min. 8 characters" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password *</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
                </div>
            </div>
            <button type="submit" class="btn-primary w-full mt-2">
                <i class="fas fa-user-plus"></i> Create My Account
            </button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="<?= BASE_URL ?>/pages/login.php">Sign in here</a>
        </div>
        <div style="text-align:center;margin-top:0.75rem">
            <a href="<?= BASE_URL ?>/index.php" style="font-size:0.85rem;color:var(--gray-400)">← Back to Homepage</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
