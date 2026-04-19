<?php
$pageTitle = 'My Appointments';
require_once __DIR__ . '/../includes/auth.php';
requireRole('patient');
$db = getDB();
$uid = $_SESSION['user_id'];

$status = sanitize($_GET['status'] ?? '');
$where = $status ? "AND a.status='{$db->real_escape_string($status)}'" : '';

$stmt = $db->prepare("SELECT a.*, s.name svc_name, s.price, dp.full_name doc_name, p.payment_status, p.id pay_id
    FROM appointments a
    JOIN services s ON a.service_id=s.id
    JOIN doctor_profiles dp ON a.doctor_id=dp.user_id
    LEFT JOIN payments p ON p.appointment_id=a.id
    WHERE a.patient_id=? $where
    ORDER BY a.appointment_date DESC, a.appointment_time DESC");
$stmt->bind_param("i", $uid);
$stmt->execute();
$appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/patient-sidebar.php';
?>

<div class="page-header">
    <div class="breadcrumb"><a href="index.php">Dashboard</a> <i class="fas fa-chevron-right" style="font-size:0.7rem"></i> My Appointments</div>
    <h1>My Appointments</h1>
    <p>Track and manage all your dental appointments.</p>
</div>

<!-- Filter Tabs -->
<div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1.5rem">
    <?php foreach ([''=>'All','pending'=>'Pending','approved'=>'Approved','completed'=>'Completed','cancelled'=>'Cancelled'] as $val=>$label): ?>
    <a href="?status=<?= $val ?>" class="<?= $status===$val ? 'btn-primary btn-sm' : 'btn-secondary btn-sm' ?>"><?= $label ?></a>
    <?php endforeach; ?>
    <a href="<?= BASE_URL ?>/pages/book.php" class="btn-primary btn-sm" style="margin-left:auto"><i class="fas fa-plus"></i> New Appointment</a>
</div>

<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Service</th><th>Doctor</th><th>Date & Time</th><th>Status</th><th>Payment</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($appointments)): ?>
            <tr><td colspan="6" style="text-align:center;padding:3rem;color:var(--gray-400)">
                <div style="font-size:2.5rem;margin-bottom:0.75rem">📅</div>
                No appointments found. <a href="<?= BASE_URL ?>/pages/book.php" style="color:var(--pink-500)">Book your first one!</a>
            </td></tr>
            <?php else: foreach ($appointments as $a): ?>
            <tr>
                <td>
                    <div style="font-weight:600;font-size:0.9rem"><?= sanitize($a['svc_name']) ?></div>
                    <div style="font-size:0.78rem;color:var(--gray-400)">₱<?= number_format($a['price'],2) ?></div>
                </td>
                <td><?= sanitize($a['doc_name']) ?></td>
                <td>
                    <div style="font-weight:500"><?= date('M d, Y', strtotime($a['appointment_date'])) ?></div>
                    <div style="font-size:0.8rem;color:var(--gray-400)"><?= date('g:i A', strtotime($a['appointment_time'])) ?></div>
                </td>
                <td><span class="badge badge-<?= strtolower($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
                <td>
                    <?php if ($a['payment_status']): ?>
                    <span class="badge badge-<?= $a['payment_status'] === 'paid' ? 'approved' : 'pending' ?>"><?= ucfirst($a['payment_status']) ?></span>
                    <?php else: ?><span style="color:var(--gray-300);font-size:0.8rem">—</span><?php endif; ?>
                </td>
                <td>
                    <div style="display:flex;gap:0.5rem;flex-wrap:wrap">
                        <?php if (in_array($a['status'],['pending','approved']) && strtotime($a['appointment_date']) > time()): ?>
                        <a href="cancel-appointment.php?id=<?= $a['id'] ?>" class="btn-secondary btn-sm" style="font-size:0.75rem;color:#ef4444;border-color:#ef4444" data-confirm="Are you sure you want to cancel this appointment?">Cancel</a>
                        <?php endif; ?>
                        <?php if ($a['status']==='completed' && $a['pay_id']): ?>
                        <a href="<?= BASE_URL ?>/patient/receipt.php?pay_id=<?= $a['pay_id'] ?>" class="btn-secondary btn-sm" style="font-size:0.75rem" target="_blank"><i class="fas fa-receipt"></i> Receipt</a>
                        <?php endif; ?>
                        <?php if ($a['diagnosis']): ?>
                        <button class="btn-secondary btn-sm" style="font-size:0.75rem" data-modal="diag-<?= $a['id'] ?>"><i class="fas fa-notes-medical"></i> Notes</button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <!-- Diagnosis Modal -->
            <?php if ($a['diagnosis']): ?>
            <div class="modal-backdrop" id="diag-<?= $a['id'] ?>">
                <div class="modal">
                    <div class="modal-header">
                        <h3>Doctor's Notes — <?= date('M d, Y', strtotime($a['appointment_date'])) ?></h3>
                        <button class="modal-close"><i class="fas fa-times"></i></button>
                    </div>
                    <?php if ($a['diagnosis']): ?>
                    <div style="margin-bottom:1rem">
                        <div class="form-label">Diagnosis</div>
                        <div style="background:var(--gray-50);padding:1rem;border-radius:var(--radius-sm);font-size:0.9rem"><?= nl2br(sanitize($a['diagnosis'])) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if ($a['prescription']): ?>
                    <div>
                        <div class="form-label">Prescription</div>
                        <div style="background:var(--gray-50);padding:1rem;border-radius:var(--radius-sm);font-size:0.9rem"><?= nl2br(sanitize($a['prescription'])) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/dashboard-footer.php'; ?>
