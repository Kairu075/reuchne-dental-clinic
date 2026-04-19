<?php
$pageTitle = 'Manage Services';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');
$db = getDB();

$success = ''; $error = '';

// Create/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = (int)($_POST['id'] ?? 0);
    $name     = sanitize($_POST['name'] ?? '');
    $desc     = sanitize($_POST['description'] ?? '');
    $price    = (float)($_POST['price'] ?? 0);
    $duration = (int)($_POST['duration_minutes'] ?? 60);
    $category = sanitize($_POST['category'] ?? '');
    $icon     = sanitize($_POST['icon'] ?? '🦷');
    $active   = isset($_POST['is_active']) ? 1 : 0;

    if ($id) {
        $stmt = $db->prepare("UPDATE services SET name=?,description=?,price=?,duration_minutes=?,category=?,icon=?,is_active=? WHERE id=?");
        $stmt->bind_param("ssdisisi",$name,$desc,$price,$duration,$category,$icon,$active,$id);
    } else {
        $stmt = $db->prepare("INSERT INTO services (name,description,price,duration_minutes,category,icon,is_active) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("ssdisisi",$name,$desc,$price,$duration,$category,$icon,$active);
    }
    $stmt->execute() ? ($success = $id ? 'Service updated!' : 'Service created!') : ($error = 'Failed.');
}

// Delete
if (isset($_GET['delete'])) {
    $db->query("DELETE FROM services WHERE id=".(int)$_GET['delete']);
    header('Location: services.php?deleted=1'); exit;
}

// Toggle active
if (isset($_GET['toggle'])) {
    $sid = (int)$_GET['toggle'];
    $db->query("UPDATE services SET is_active = NOT is_active WHERE id=$sid");
    header('Location: services.php'); exit;
}

// Edit pre-fill
$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM services WHERE id=?"); $stmt->bind_param("i",(int)$_GET['edit']); $stmt->execute();
    $editing = $stmt->get_result()->fetch_assoc();
}

$services = $db->query("SELECT * FROM services ORDER BY category,name")->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/admin-sidebar.php';
?>
<div class="page-header">
    <h1>Manage Services</h1>
</div>

<?php if ($success): ?><div class="alert alert-success" data-dismiss="3000"><i class="fas fa-check-circle"></i><?= $success ?></div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert alert-info" data-dismiss="3000"><i class="fas fa-trash"></i> Service deleted.</div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem" class="responsive-grid-1">
    <!-- Form -->
    <div class="table-card" style="padding:1.75rem">
        <h4 style="margin-bottom:1.25rem;padding-bottom:0.75rem;border-bottom:1px solid var(--gray-100)">
            <?= $editing ? '✏️ Edit Service' : '➕ Add New Service' ?>
            <?php if ($editing): ?><a href="services.php" class="btn-secondary btn-sm" style="margin-left:0.75rem;float:right">Cancel</a><?php endif; ?>
        </h4>
        <form method="POST">
            <?php if ($editing): ?><input type="hidden" name="id" value="<?= $editing['id'] ?>"><?php endif; ?>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Service Name *</label>
                    <input type="text" name="name" class="form-control" required value="<?= sanitize($editing['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <input type="text" name="category" class="form-control" placeholder="e.g. Cosmetic, Preventive" value="<?= sanitize($editing['category'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= sanitize($editing['description'] ?? '') ?></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Price (₱) *</label>
                    <input type="number" name="price" class="form-control" step="0.01" min="0" required value="<?= $editing['price'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Duration (minutes)</label>
                    <input type="number" name="duration_minutes" class="form-control" min="15" value="<?= $editing['duration_minutes'] ?? 60 ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Icon (emoji)</label>
                    <input type="text" name="icon" class="form-control" value="<?= $editing['icon'] ?? '🦷' ?>">
                </div>
                <div class="form-group" style="display:flex;align-items:flex-end">
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;margin-bottom:0.5rem">
                        <input type="checkbox" name="is_active" <?= ($editing['is_active'] ?? 1) ? 'checked' : '' ?> style="accent-color:var(--pink-500)">
                        <span class="form-label" style="margin:0">Active</span>
                    </label>
                </div>
            </div>
            <button type="submit" class="btn-primary"><?= $editing ? '💾 Update Service' : '➕ Add Service' ?></button>
        </form>
    </div>

    <!-- List -->
    <div class="table-card">
        <div class="table-header"><h3><?= count($services) ?> Services</h3></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Service</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($services as $s): ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:0.5rem">
                            <span style="font-size:1.2rem"><?= $s['icon'] ?></span>
                            <div>
                                <div style="font-weight:600;font-size:0.88rem"><?= sanitize($s['name']) ?></div>
                                <div style="font-size:0.75rem;color:var(--gray-400)"><?= sanitize($s['category']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>₱<?= number_format($s['price'],0) ?></td>
                    <td><span class="badge <?= $s['is_active'] ? 'badge-approved' : '' ?>" style="<?= !$s['is_active'] ? 'background:var(--gray-100);color:var(--gray-500)' : '' ?>"><?= $s['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                    <td>
                        <a href="?edit=<?= $s['id'] ?>" class="btn-secondary btn-sm" style="font-size:0.73rem"><i class="fas fa-edit"></i></a>
                        <a href="?toggle=<?= $s['id'] ?>" class="btn-secondary btn-sm" style="font-size:0.73rem"><?= $s['is_active'] ? '🔒' : '🔓' ?></a>
                        <a href="?delete=<?= $s['id'] ?>" class="btn-secondary btn-sm" style="font-size:0.73rem;color:#ef4444;border-color:#ef4444" data-confirm="Delete this service?"><i class="fas fa-trash"></i></a>
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
