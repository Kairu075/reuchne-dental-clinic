<?php
$pageTitle = 'Login';
require_once __DIR__ . '/../includes/auth.php';

if (isLoggedIn()) {
    header('Location: ' . getDashboardUrl());
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $result = login($email, $pass);
    if ($result['success']) {
        header('Location: ' . getDashboardUrl());
        exit;
    } else {
        $error = $result['message'];
    }
}
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <span class="auth-icon">🦷</span>
            <h2>Welcome Back!</h2>
            <p>Sign in to your Reuchne Tooth Fairy Clinic account</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error" data-dismiss="4000"><i class="fas fa-exclamation-circle"></i><?= $error ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['registered'])): ?>
        <div class="alert alert-success" data-dismiss="4000"><i class="fas fa-check-circle"></i>Account created! Please login to continue.</div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="you@email.com" value="<?= sanitize($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <div style="position:relative">
                    <input type="password" name="password" id="passwordField" class="form-control" placeholder="Your password" required style="padding-right:3rem">
                    <button type="button" onclick="togglePass()" style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--gray-400)">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn-primary w-full" style="margin-top:0.5rem">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="<?= BASE_URL ?>/pages/register.php">Create one here</a>
        </div>
        <div style="text-align:center;margin-top:1rem">
            <a href="<?= BASE_URL ?>/index.php" style="font-size:0.85rem;color:var(--gray-400)">← Back to Homepage</a>
        </div>
    </div>
</div>

<?php $extraJS = '<script>
function togglePass(){
  const f=document.getElementById("passwordField");
  const i=document.getElementById("eyeIcon");
  if(f.type==="password"){f.type="text";i.className="fas fa-eye-slash";}
  else{f.type="password";i.className="fas fa-eye";}
}
</script>'; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
