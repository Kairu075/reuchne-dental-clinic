<?php
$pageTitle = 'My Payments';
require_once __DIR__ . '/../includes/auth.php';
requireRole('patient');
$db = getDB();
$uid = $_SESSION['user_id'];

$payments = $db->prepare("SELECT p.*, a.appointment_date, a.appointment_time, s.name svc_name, dp.full_name doc_name
    FROM payments p
    JOIN appointments a ON p.appointment_id=a.id
    JOIN services s ON a.service_id=s.id
    JOIN doctor_profiles dp ON a.doctor_id=dp.user_id
    WHERE p.patient_id=?
    ORDER BY p.created_at DESC");
$payments->bind_param("i",$uid);
$payments->execute();
$allPayments = $payments->get_result()->fetch_all(MYSQLI_ASSOC);

$totalPaid = array_sum(array_column(array_filter($allPayments, fn($p) => $p['payment_status']==='paid'), 'amount'));

require_once __DIR__ . '/../includes/patient-sidebar.php';
?>

<div class="page-header">
    <div class="breadcrumb"><a href="index.php">Dashboard</a> <i class="fas fa-chevron-right" style="font-size:0.7rem"></i> Payments</div>
    <h1>My Payments</h1>
    <p>View all your payment records and receipts.</p>
</div>

<div class="stats-grid" style="margin-bottom:1.5rem">
    <div class="stat-card">
        <div class="stat-icon" style="background:#dcfce7"><i class="fas fa-check-circle" style="color:#16a34a"></i></div>
        <div class="stat-value">₱<?= number_format($totalPaid,0) ?></div>
        <div class="stat-label">Total Paid</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:var(--pink-100)"><i class="fas fa-receipt" style="color:var(--pink-500)"></i></div>
        <div class="stat-value"><?= count($allPayments) ?></div>
        <div class="stat-label">Total Transactions</div>
    </div>
</div>

<div class="table-card">
    <div class="table-header"><h3>Payment History</h3></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Service</th><th>Doctor</th><th>Appointment Date</th><th>Amount</th><th>Method</th><th>Status</th><th>Receipt</th></tr></thead>
            <tbody>
            <?php if (empty($allPayments)): ?>
            <tr><td colspan="7" style="text-align:center;padding:3rem;color:var(--gray-400)">No payment records found.</td></tr>
            <?php else: foreach ($allPayments as $p): ?>
            <tr>
                <td><?= sanitize($p['svc_name']) ?></td>
                <td><?= sanitize($p['doc_name']) ?></td>
                <td><?= date('M d, Y', strtotime($p['appointment_date'])) ?><br><small style="color:var(--gray-400)"><?= date('g:i A', strtotime($p['appointment_time'])) ?></small></td>
                <td style="font-weight:600;color:var(--gray-900)">₱<?= number_format($p['amount'],2) ?></td>
                <td><span class="badge badge-pink"><?= ucfirst(str_replace('_',' ',$p['payment_method'])) ?></span></td>
                <td><span class="badge badge-<?= $p['payment_status']==='paid' ? 'approved' : ($p['payment_status']==='pending' ? 'pending' : 'cancelled') ?>"><?= ucfirst($p['payment_status']) ?></span></td>
                <td>
                    <?php if ($p['payment_status']==='paid'): ?>
                    <a href="receipt.php?pay_id=<?= $p['id'] ?>" class="btn-secondary btn-sm" target="_blank"><i class="fas fa-receipt"></i> View</a>
                    <?php else: ?>
                    <span style="color:var(--gray-300);font-size:0.8rem">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/dashboard-footer.php'; ?>
