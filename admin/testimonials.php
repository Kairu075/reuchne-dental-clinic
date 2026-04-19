<?php
$pageTitle = 'Manage Testimonials';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');
$db = getDB();

if (isset($_GET['approve'])) {
    $db->query("UPDATE testimonials SET is_approved=1 WHERE id=".(int)$_GET['approve']);
    header('Location: testimonials.php?msg=approved'); exit;
}
if (isset($_GET['delete'])) {
    $db->query("DELETE FROM testimonials WHERE id=".(int)$_GET['delete']);
    header('Location: testimonials.php?msg=deleted'); exit;
}

$testimonials = $db->query("SELECT * FROM testimonials ORDER BY is_approved ASC, created_at DESC")->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/admin-sidebar.php';
?>
<div class="page-header">
    <h1>Manage Testimonials</h1>
    <p>Approve or remove patient reviews shown on the website.</p>
</div>

<?php if (isset($_GET['msg'])): ?>
<div class="alert alert-success" data-dismiss="3000"><i class="fas fa-check-circle"></i> Testimonial <?= sanitize($_GET['msg']) ?>!</div>
<?php endif; ?>

<div class="table-card">
    <div class="table-header">
        <h3><?= count($testimonials) ?> Testimonials</h3>
        <span style="font-size:0.85rem;color:var(--gray-500)"><?= count(array_filter($testimonials,fn($t)=>!$t['is_approved'])) ?> pending approval</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Patient</th><th>Rating</th><th>Review</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($testimonials as $t): ?>
            <tr style="<?= !$t['is_approved'] ? 'background:var(--pink-50)' : '' ?>">
                <td style="font-weight:600"><?= sanitize($t['patient_name'] ?? 'Anonymous') ?></td>
                <td style="color:#f59e0b"><?= str_repeat('★',$t['rating']) ?></td>
                <td style="max-width:300px;font-size:0.85rem;color:var(--gray-700)">"<?= sanitize(substr($t['content'],0,120)) ?>..."</td>
                <td><?= date('M d, Y', strtotime($t['created_at'])) ?></td>
                <td>
                    <?php if ($t['is_approved']): ?>
                    <span class="badge badge-approved">Approved</span>
                    <?php else: ?>
                    <span class="badge badge-pending">Pending</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="display:flex;gap:0.4rem">
                        <?php if (!$t['is_approved']): ?>
                        <a href="?approve=<?= $t['id'] ?>" class="btn-success btn-sm" style="font-size:0.73rem"><i class="fas fa-check"></i> Approve</a>
                        <?php endif; ?>
                        <a href="?delete=<?= $t['id'] ?>" class="btn-secondary btn-sm" style="font-size:0.73rem;color:#ef4444;border-color:#ef4444" data-confirm="Delete this testimonial?"><i class="fas fa-trash"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/dashboard-footer.php'; ?>
