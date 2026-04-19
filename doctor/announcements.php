<?php
$pageTitle = 'Announcements';
require_once __DIR__ . '/../includes/auth.php';
requireRole('doctor');
$db = getDB();
$uid = $_SESSION['user_id'];

$success = $_GET['deleted'] ?? ''; 
$error = $_GET['error'] ?? '';

// Create announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $title   = sanitize($_POST['title'] ?? '');
    $content = sanitize($_POST['content'] ?? '');
    $type    = sanitize($_POST['type'] ?? 'general');
    $expires = sanitize($_POST['expires_at'] ?? '') ?: null;
    $stmt = $db->prepare("INSERT INTO announcements (doctor_id,title,content,type,expires_at) VALUES (?,?,?,?,?)");
    $stmt->bind_param("issss",$uid,$title,$content,$type,$expires);
    $stmt->execute() ? ($success='Announcement posted!') : ($error='Failed to post.');
}

// Delete
if (isset($_GET['delete'])) {
    $did = (int)sanitize($_GET['delete']);
    $stmt = $db->prepare("DELETE FROM announcements WHERE id=? AND doctor_id=?");
    if ($stmt === false) {
        $error = 'Database prepare failed: ' . $db->error;
    } else {
        $stmt->bind_param("ii", $did, $uid);
        if ($stmt->execute()) {
            header('Location: announcements.php?deleted=1'); 
            exit;
        } else {
            $error = 'Delete failed: ' . $stmt->error;
        }
    }
}

$announcements = $db->prepare("SELECT * FROM announcements WHERE doctor_id=? ORDER BY created_at DESC");
$announcements->bind_param("i",$uid); $announcements->execute();
$allAnn = $announcements->get_result()->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/doctor-sidebar.php';
?>
<div class="page-header">
    <h1>Announcements</h1>
    <p>Post updates, promos, or emergency notices to your patients.</p>
</div>

<?php if ($success): ?><div class="alert alert-success" data-dismiss="3000"><i class="fas fa-check-circle"></i><?= $success ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger" data-dismiss="5000"><i class="fas fa-exclamation-triangle"></i> <?= $error ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem" class="responsive-grid-1">
    <!-- Create Form -->
    <div class="table-card" style="padding:1.75rem">
        <h4 style="margin-bottom:1.25rem;padding-bottom:0.75rem;border-bottom:1px solid var(--gray-100)"><i class="fas fa-plus-circle" style="color:var(--pink-400);margin-right:0.5rem"></i>Post New Announcement</h4>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-control" placeholder="Announcement title..." required>
            </div>
            <div class="form-group">
                <label class="form-label">Type</label>
                <select name="type" class="form-control">
                    <option value="general">📢 General</option>
                    <option value="promo">🎉 Promo / Offer</option>
                    <option value="emergency">🚨 Emergency</option>
                    <option value="schedule_change">📅 Schedule Change</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Message *</label>
                <textarea name="content" class="form-control" rows="5" placeholder="Write your message here..." required></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Expires On (optional)</label>
                <input type="date" name="expires_at" class="form-control" min="<?= date('Y-m-d') ?>">
            </div>
            <button type="submit" name="create" class="btn-primary"><i class="fas fa-paper-plane"></i> Post Announcement</button>
        </form>
    </div>

    <!-- Existing Announcements -->
    <div>
        <h4 style="margin-bottom:1rem">Your Announcements</h4>
        <?php if (empty($allAnn)): ?>
        <div class="card" style="padding:3rem;text-align:center;color:var(--gray-400)">
            <div style="font-size:2.5rem;margin-bottom:0.75rem">📢</div>
            <p>No announcements yet. Post your first one!</p>
        </div>
        <?php else: foreach ($allAnn as $ann):
            $typeColors = ['general'=>'var(--pink-100)','promo'=>'#dcfce7','emergency'=>'#fee2e2','schedule_change'=>'#dbeafe'];
            $typeIcons  = ['general'=>'📢','promo'=>'🎉','emergency'=>'🚨','schedule_change'=>'📅'];
        ?>
        <div class="card" style="padding:1.25rem;margin-bottom:0.75rem;border-left:4px solid var(--pink-300)">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.5rem">
                <div style="display:flex;align-items:center;gap:0.5rem">
                    <span><?= $typeIcons[$ann['type']] ?? '📢' ?></span>
                    <span style="font-weight:700;font-size:0.9rem"><?= sanitize($ann['title']) ?></span>
                </div>
                <a href="?delete=<?= $ann['id'] ?>" style="color:#ef4444;font-size:0.8rem" data-confirm="Delete this announcement?"><i class="fas fa-trash"></i></a>
            </div>
            <p style="font-size:0.85rem;color:var(--gray-600);margin-bottom:0.5rem"><?= sanitize(substr($ann['content'],0,150)) ?>...</p>
            <div style="display:flex;gap:0.75rem;font-size:0.75rem;color:var(--gray-400)">
                <span><?= date('M d, Y', strtotime($ann['created_at'])) ?></span>
                <?php if ($ann['expires_at']): ?><span>Expires: <?= date('M d, Y', strtotime($ann['expires_at'])) ?></span><?php endif; ?>
                <span class="badge" style="background:<?= $typeColors[$ann['type']] ?? 'var(--pink-100)' ?>;font-size:0.7rem"><?= ucfirst(str_replace('_',' ',$ann['type'])) ?></span>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<style>@media(max-width:768px){.responsive-grid-1{grid-template-columns:1fr !important}}</style>
<?php require_once __DIR__ . '/../includes/dashboard-footer.php'; ?>
