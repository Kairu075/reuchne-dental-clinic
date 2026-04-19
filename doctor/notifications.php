<?php
$pageTitle = 'Notifications';
require_once __DIR__ . '/../includes/auth.php';
requireRole('doctor');
$db = getDB();
$uid = $_SESSION['user_id'];

$stmt = $db->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?");
if ($stmt) {
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $stmt->close();
}

$notifs = $db->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC");
if ($notifs) {
    $notifs->bind_param("i", $uid);
    $notifs->execute();
    $notifications = $notifs->get_result()->fetch_all(MYSQLI_ASSOC);
    $notifs->close();
} else {
    $notifications = [];
}

require_once __DIR__ . '/../includes/doctor-sidebar.php';
?>
<div class="page-header">
    <h1>Notifications</h1>
</div>

<div style="max-width:680px">
<?php if (empty($notifications)): ?>
<div class="card" style="padding:3rem;text-align:center;color:var(--gray-400)">
    <div style="font-size:3rem;margin-bottom:1rem">🔔</div>
    <p>No notifications yet.</p>
</div>
<?php else: foreach ($notifications as $n):
    $icons  = ['appointment'=>'fas fa-calendar','payment'=>'fas fa-credit-card','announcement'=>'fas fa-bullhorn','system'=>'fas fa-info-circle'];
    $colors = ['appointment'=>'#3b82f6','payment'=>'#22c55e','announcement'=>'#f59e0b','system'=>'#8b5cf6'];
    $type   = $n['type'] ?? 'system';
?>
<div class="card" style="padding:1.25rem;margin-bottom:0.75rem;display:flex;gap:1rem;align-items:flex-start;<?= !$n['is_read'] ? 'border-left:3px solid var(--pink-400)' : '' ?>">
    <div style="width:40px;height:40px;border-radius:50%;background:<?= str_replace('#','rgba(',substr($colors[$type]??'#8b5cf6',1)).', 0.12)' ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <i class="<?= $icons[$type] ?? 'fas fa-bell' ?>" style="color:<?= $colors[$type] ?? '#8b5cf6' ?>"></i>
    </div>
    <div style="flex:1">
        <div style="font-weight:600;font-size:0.9rem;margin-bottom:0.25rem"><?= sanitize($n['title']) ?></div>
        <div style="font-size:0.85rem;color:var(--gray-600)"><?= sanitize($n['message']) ?></div>
        <div style="font-size:0.75rem;color:var(--gray-400);margin-top:0.4rem"><?= date('M d, Y g:i A', strtotime($n['created_at'])) ?></div>
    </div>
</div>
<?php endforeach; endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/dashboard-footer.php'; ?>
