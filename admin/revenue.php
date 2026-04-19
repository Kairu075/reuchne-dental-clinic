<?php
$pageTitle = 'Revenue Dashboard';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');
$db = getDB();

$month = sanitize($_GET['month'] ?? date('Y-m'));
$year  = substr($month, 0, 4);
$mon   = substr($month, 5, 2);

$totalMonth = $db->query("SELECT COALESCE(SUM(amount),0) t FROM payments WHERE payment_status='paid' AND YEAR(paid_at)='$year' AND MONTH(paid_at)='$mon'")->fetch_assoc()['t'];
$totalYear  = $db->query("SELECT COALESCE(SUM(amount),0) t FROM payments WHERE payment_status='paid' AND YEAR(paid_at)='$year'")->fetch_assoc()['t'];
$totalAll   = $db->query("SELECT COALESCE(SUM(amount),0) t FROM payments WHERE payment_status='paid'")->fetch_assoc()['t'];
$totalTx    = $db->query("SELECT COUNT(*) c FROM payments WHERE payment_status='paid'")->fetch_assoc()['c'];

// Daily revenue for this month
$dailyData = $db->query("SELECT DAY(paid_at) day_num, SUM(amount) total FROM payments WHERE payment_status='paid' AND YEAR(paid_at)='$year' AND MONTH(paid_at)='$mon' GROUP BY day_num ORDER BY day_num")->fetch_all(MYSQLI_ASSOC);

// Revenue by service
$bySvc = $db->query("SELECT s.name, COUNT(p.id) cnt, SUM(p.amount) total FROM payments p JOIN appointments a ON p.appointment_id=a.id JOIN services s ON a.service_id=s.id WHERE p.payment_status='paid' GROUP BY s.id ORDER BY total DESC")->fetch_all(MYSQLI_ASSOC);

// Revenue by doctor
$byDoc = $db->query("SELECT dp.full_name, COUNT(p.id) cnt, SUM(p.amount) total FROM payments p JOIN appointments a ON p.appointment_id=a.id JOIN doctor_profiles dp ON a.doctor_id=dp.user_id WHERE p.payment_status='paid' GROUP BY dp.user_id ORDER BY total DESC")->fetch_all(MYSQLI_ASSOC);

// Payments list for selected month
$payments = $db->query("SELECT p.*, pp.full_name pat_name, s.name svc_name, dp.full_name doc_name, a.appointment_date FROM payments p JOIN appointments a ON p.appointment_id=a.id JOIN patient_profiles pp ON p.patient_id=pp.user_id JOIN services s ON a.service_id=s.id JOIN doctor_profiles dp ON a.doctor_id=dp.user_id WHERE YEAR(p.created_at)='$year' AND MONTH(p.created_at)='$mon' ORDER BY p.created_at DESC LIMIT 20")->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/admin-sidebar.php';
?>
<div class="page-header">
    <h1>Revenue Dashboard</h1>
    <p>Track earnings and financial performance.</p>
</div>

<!-- Month selector -->
<div style="margin-bottom:1.5rem;display:flex;align-items:center;gap:1rem">
    <label class="form-label" style="margin:0;white-space:nowrap">View Month:</label>
    <input type="month" id="monthPicker" class="form-control" style="max-width:200px" value="<?= $month ?>">
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:var(--pink-100)"><i class="fas fa-calendar-alt" style="color:var(--pink-500)"></i></div>
        <div class="stat-value" style="font-size:1.6rem">₱<?= number_format($totalMonth,2) ?></div>
        <div class="stat-label">This Month</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#dbeafe"><i class="fas fa-chart-line" style="color:#2563eb"></i></div>
        <div class="stat-value" style="font-size:1.6rem">₱<?= number_format($totalYear,2) ?></div>
        <div class="stat-label">This Year (<?= $year ?>)</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#dcfce7"><i class="fas fa-coins" style="color:#16a34a"></i></div>
        <div class="stat-value" style="font-size:1.6rem">₱<?= number_format($totalAll,2) ?></div>
        <div class="stat-label">All-Time Revenue</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f3e8ff"><i class="fas fa-receipt" style="color:#7c3aed"></i></div>
        <div class="stat-value"><?= $totalTx ?></div>
        <div class="stat-label">Total Transactions</div>
    </div>
</div>

<!-- Charts -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;margin-bottom:1.5rem" class="responsive-grid-1">
    <div class="chart-card">
        <div class="chart-title">Daily Revenue — <?= date('F Y', mktime(0,0,0,$mon,1,$year)) ?></div>
        <canvas id="dailyChart" height="160"></canvas>
    </div>
    <div class="chart-card">
        <div class="chart-title">Revenue by Service</div>
        <canvas id="svcPie" height="160"></canvas>
    </div>
</div>

<!-- By Service Table -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem" class="responsive-grid-1">
    <div class="table-card">
        <div class="table-header"><h3>Revenue by Service</h3></div>
        <table>
            <thead><tr><th>Service</th><th>Transactions</th><th>Total</th></tr></thead>
            <tbody>
            <?php foreach ($bySvc as $r): ?>
            <tr>
                <td><?= sanitize($r['name']) ?></td>
                <td style="text-align:center"><?= $r['cnt'] ?></td>
                <td style="color:var(--pink-500);font-weight:600">₱<?= number_format($r['total'],2) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="table-card">
        <div class="table-header"><h3>Revenue by Doctor</h3></div>
        <table>
            <thead><tr><th>Doctor</th><th>Transactions</th><th>Total</th></tr></thead>
            <tbody>
            <?php foreach ($byDoc as $r): ?>
            <tr>
                <td><?= sanitize($r['full_name']) ?></td>
                <td style="text-align:center"><?= $r['cnt'] ?></td>
                <td style="color:var(--pink-500);font-weight:600">₱<?= number_format($r['total'],2) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Transactions -->
<div class="table-card">
    <div class="table-header"><h3>Recent Transactions</h3><a href="payments.php" class="btn-secondary btn-sm">Manage All Payments</a></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Patient</th><th>Service</th><th>Doctor</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
            <?php foreach ($payments as $p): ?>
            <tr>
                <td><?= sanitize($p['pat_name']) ?></td>
                <td><?= sanitize($p['svc_name']) ?></td>
                <td><?= sanitize($p['doc_name']) ?></td>
                <td style="font-weight:700;color:var(--pink-500)">₱<?= number_format($p['amount'],2) ?></td>
                <td><span class="badge badge-pink"><?= ucfirst(str_replace('_',' ',$p['payment_method'])) ?></span></td>
                <td><span class="badge badge-<?= $p['payment_status']==='paid'?'approved':'pending' ?>"><?= ucfirst($p['payment_status']) ?></span></td>
                <td><?= $p['paid_at'] ? date('M d g:i A', strtotime($p['paid_at'])) : date('M d', strtotime($p['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>@media(max-width:768px){.responsive-grid-1{grid-template-columns:1fr !important}}</style>
<?php
$dDays = array_column($dailyData,'day_num');
$dVals = array_column($dailyData,'total');
$sNames = array_column($bySvc,'name');
$sVals  = array_column($bySvc,'total');
$extraJS = '<script>
document.getElementById("monthPicker").addEventListener("change",function(){
    window.location.href="?month="+this.value;
});
const dCtx=document.getElementById("dailyChart");
if(dCtx)new Chart(dCtx,{type:"line",data:{labels:'.json_encode($dDays).',datasets:[{label:"Daily Revenue",data:'.json_encode($dVals).',borderColor:"#f04e7d",backgroundColor:"rgba(240,78,125,0.08)",tension:0.4,fill:true,pointBackgroundColor:"#f04e7d",pointRadius:4}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{callback:v=>"₱"+Number(v).toLocaleString()}}}}});
const sCtx=document.getElementById("svcPie");
if(sCtx)new Chart(sCtx,{type:"doughnut",data:{labels:'.json_encode($sNames).',datasets:[{data:'.json_encode($sVals).',backgroundColor:["#f04e7d","#f9739a","#ff99b8","#ffc0d3","#ffe0e9","#dc3060"],borderWidth:0}]},options:{responsive:true,plugins:{legend:{position:"bottom",labels:{font:{size:11}}}}}});
</script>';
?>
<?php require_once __DIR__ . '/../includes/dashboard-footer.php'; ?>
