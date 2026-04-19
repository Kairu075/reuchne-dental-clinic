<?php
$pageTitle = 'Manage Payments';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');
$db = getDB();

$success = '';

// Record/update payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment'])) {
    $pay_id  = (int)$_POST['pay_id'];
    $status  = sanitize($_POST['payment_status']);
    $method  = sanitize($_POST['payment_method']);
    $ref     = sanitize($_POST['reference_number'] ?? '');
    $paid_at = $status === 'paid' ? date('Y-m-d H:i:s') : null;
    $stmt = $db->prepare("UPDATE payments SET payment_status=?,payment_method=?,reference_number=?,paid_at=? WHERE id=?");
    $stmt->bind_param("ssssi",$status,$method,$ref,$paid_at,$pay_id);
    $stmt->execute();

    // Notify patient if paid
    if ($status === 'paid') {
        $pinfo = $db->prepare("SELECT patient_id FROM payments WHERE id=?"); $pinfo->bind_param("i",$pay_id); $pinfo->execute();
        $pdata = $pinfo->get_result()->fetch_assoc();
        if ($pdata) createNotification($pdata['patient_id'],'Payment Confirmed','Your payment has been confirmed. Thank you!','payment',BASE_URL.'/patient/payments.php');
    }
    $success = 'Payment updated!';
}

$status_f = sanitize($_GET['status'] ?? '');
$where = $status_f ? "WHERE p.payment_status='{$db->real_escape_string($status_f)}'" : '';
$payments = $db->query("SELECT p.*, pp.full_name pat_name, s.name svc_name, dp.full_name doc_name, a.appointment_date FROM payments p JOIN appointments a ON p.appointment_id=a.id JOIN patient_profiles pp ON p.patient_id=pp.user_id JOIN services s ON a.service_id=s.id JOIN doctor_profiles dp ON a.doctor_id=dp.user_id $where ORDER BY p.created_at DESC")->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/admin-sidebar.php';
?>
<div class="page-header">
    <h1>Manage Payments</h1>
    <p>Process and track all patient payments.</p>
</div>

<?php if ($success): ?><div class="alert alert-success" data-dismiss="3000"><i class="fas fa-check-circle"></i><?= $success ?></div><?php endif; ?>

<!-- Filter -->
<div style="display:flex;gap:0.75rem;margin-bottom:1.5rem;flex-wrap:wrap">
    <?php foreach ([''=>'All','pending'=>'Pending','paid'=>'Paid','partial'=>'Partial','refunded'=>'Refunded'] as $v=>$l): ?>
    <a href="?status=<?= $v ?>" class="<?= $status_f===$v?'btn-primary btn-sm':'btn-secondary btn-sm' ?>"><?= $l ?></a>
    <?php endforeach; ?>
</div>

<div class="table-card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Patient</th><th>Service</th><th>Appt Date</th><th>Amount</th><th>Method</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($payments as $p): ?>
            <tr>
                <td style="font-weight:600"><?= sanitize($p['pat_name']) ?></td>
                <td><?= sanitize($p['svc_name']) ?></td>
                <td><?= date('M d, Y', strtotime($p['appointment_date'])) ?></td>
                <td style="font-weight:700;color:var(--pink-500)">₱<?= number_format($p['amount'],2) ?></td>
                <td><span class="badge badge-pink"><?= ucfirst(str_replace('_',' ',$p['payment_method'])) ?></span></td>
                <td><span class="badge badge-<?= $p['payment_status']==='paid'?'approved':($p['payment_status']==='pending'?'pending':'cancelled') ?>"><?= ucfirst($p['payment_status']) ?></span></td>
                <td>
                    <button class="btn-primary btn-sm" style="font-size:0.75rem" data-modal="pay-<?= $p['id'] ?>"><i class="fas fa-edit"></i> Update</button>
                    <?php if ($p['payment_status']==='paid'): ?>
                    <a href="<?= BASE_URL ?>/patient/receipt.php?pay_id=<?= $p['id'] ?>" class="btn-secondary btn-sm" style="font-size:0.75rem" target="_blank"><i class="fas fa-receipt"></i></a>
                    <?php endif; ?>
                </td>
            </tr>
            <!-- Update Payment Modal -->
            <div class="modal-backdrop" id="pay-<?= $p['id'] ?>">
                <div class="modal">
                    <div class="modal-header">
                        <h3>Update Payment</h3>
                        <button class="modal-close"><i class="fas fa-times"></i></button>
                    </div>
                    <div style="background:var(--gray-50);padding:1rem;border-radius:var(--radius-sm);margin-bottom:1.25rem">
                        <div style="font-size:0.88rem"><strong><?= sanitize($p['pat_name']) ?></strong> — <?= sanitize($p['svc_name']) ?></div>
                        <div style="font-size:1.5rem;font-weight:700;color:var(--pink-500)">₱<?= number_format($p['amount'],2) ?></div>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="pay_id" value="<?= $p['id'] ?>">
                        <div class="form-group">
                            <label class="form-label">Payment Status</label>
                            <select name="payment_status" class="form-control" required>
                                <?php foreach (['pending','paid','partial','refunded'] as $s): ?>
                                <option value="<?= $s ?>" <?= $p['payment_status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-control">
                                <?php foreach (['cash'=>'Cash','gcash'=>'GCash','maya'=>'Maya','credit_card'=>'Credit Card','insurance'=>'Insurance'] as $v=>$l): ?>
                                <option value="<?= $v ?>" <?= $p['payment_method']===$v?'selected':'' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Reference Number (optional)</label>
                            <input type="text" name="reference_number" class="form-control" placeholder="GCash ref, card auth code..." value="<?= sanitize($p['reference_number'] ?? '') ?>">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-secondary modal-close">Cancel</button>
                            <button type="submit" name="update_payment" class="btn-primary"><i class="fas fa-save"></i> Save</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/dashboard-footer.php'; ?>
