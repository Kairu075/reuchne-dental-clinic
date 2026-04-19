<?php
$pageTitle = 'Receipt';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$db = getDB();
$uid = $_SESSION['user_id'];
$pay_id = (int)($_GET['pay_id'] ?? 0);

// Fetch payment (patients can only see their own)
$q = "SELECT p.*, a.appointment_date, a.appointment_time, a.diagnosis,
    s.name svc_name, s.duration_minutes,
    dp.full_name doc_name, dp.specialty,
    pp.full_name pat_name, pp.phone pat_phone, pp.address pat_address
    FROM payments p
    JOIN appointments a ON p.appointment_id=a.id
    JOIN services s ON a.service_id=s.id
    JOIN doctor_profiles dp ON a.doctor_id=dp.user_id
    JOIN patient_profiles pp ON p.patient_id=pp.user_id
    WHERE p.id=?";
if (getRole() === 'patient') $q .= " AND p.patient_id=$uid";
$stmt = $db->prepare($q);
$stmt->bind_param("i",$pay_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

if (!$payment) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?= $pay_id ?> — Reuchne Tooth Fairy Clinic</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
    <style>
        body{background:var(--gray-50);min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:flex-start;padding:2rem}
        .receipt-wrapper{max-width:520px;width:100%}
        .no-print{margin-bottom:1.5rem;display:flex;gap:1rem}
        @media print{.no-print{display:none}body{background:white;padding:0}}
    </style>
</head>
<body>
<div class="receipt-wrapper">
    <div class="no-print">
        <button onclick="window.print()" class="btn-primary"><i class="fas fa-print"></i> Print Receipt</button>
        <a href="<?= BASE_URL ?>/patient/payments.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Go Back</a>
    </div>

    <div class="receipt">
        <div class="receipt-header">
            <div class="receipt-logo"><img src="<?= BASE_URL ?>/assets/images/placeholders/rb-logo-transparent.png" alt="Reuchne Tooth Fairy Clinic Logo" style="height: 6rem; width: auto; margin-bottom: 0.5rem; display: block; margin-left: auto; margin-right: auto;"></div>
            <div class="receipt-title">Reuchne Tooth Fairy Clinic</div>
            <div style="font-size:0.78rem;color:var(--gray-500);margin-top:0.25rem">123 Smile Street, Your City, Philippines</div>
            <div style="font-size:0.78rem;color:var(--gray-500)">+63 912 345 6789</div>
            <div style="margin-top:1rem;background:var(--pink-50);padding:0.6rem 1rem;border-radius:50px;display:inline-block">
                <div style="font-size:0.7rem;color:var(--gray-500);letter-spacing:0.1em;text-transform:uppercase">Receipt Number</div>
                <div style="font-weight:700;color:var(--pink-600)">RTC-<?= str_pad($pay_id,6,'0',STR_PAD_LEFT) ?></div>
            </div>
        </div>

        <div class="receipt-row">
            <span style="color:var(--gray-500)">Patient</span>
            <span style="font-weight:600"><?= sanitize($payment['pat_name']) ?></span>
        </div>
        <div class="receipt-row">
            <span style="color:var(--gray-500)">Doctor</span>
            <span><?= sanitize($payment['doc_name']) ?></span>
        </div>
        <div class="receipt-row">
            <span style="color:var(--gray-500)">Service</span>
            <span style="font-weight:600"><?= sanitize($payment['svc_name']) ?></span>
        </div>
        <div class="receipt-row">
            <span style="color:var(--gray-500)">Appointment Date</span>
            <span><?= date('F d, Y', strtotime($payment['appointment_date'])) ?></span>
        </div>
        <div class="receipt-row">
            <span style="color:var(--gray-500)">Appointment Time</span>
            <span><?= date('g:i A', strtotime($payment['appointment_time'])) ?></span>
        </div>
        <div class="receipt-row">
            <span style="color:var(--gray-500)">Payment Method</span>
            <span class="badge badge-pink"><?= ucfirst(str_replace('_',' ',$payment['payment_method'])) ?></span>
        </div>
        <?php if ($payment['reference_number']): ?>
        <div class="receipt-row">
            <span style="color:var(--gray-500)">Reference No.</span>
            <span><?= sanitize($payment['reference_number']) ?></span>
        </div>
        <?php endif; ?>
        <div class="receipt-row">
            <span style="color:var(--gray-500)">Date Paid</span>
            <span><?= $payment['paid_at'] ? date('F d, Y g:i A', strtotime($payment['paid_at'])) : '—' ?></span>
        </div>
        <div class="receipt-row total">
            <span>TOTAL PAID</span>
            <span>₱<?= number_format($payment['amount'],2) ?></span>
        </div>

        <div class="receipt-footer">
            <div style="font-size:1.5rem;margin-bottom:0.5rem">✨</div>
            <p>Thank you for choosing Reuchne Tooth Fairy Clinic!</p>
            <p style="margin-top:0.25rem">Magical Care for Your Perfect Smile</p>
            <div style="margin-top:1rem;padding-top:1rem;border-top:1px dashed var(--pink-200)">
                <p style="font-size:0.72rem">This is an official receipt. Please keep for your records.</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>

