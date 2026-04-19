<?php
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');
$db = getDB();

// Key stats
$totalPatients = $db->query("SELECT COUNT(*) c FROM users WHERE role='patient'")->fetch_assoc()['c'];
$totalDoctors  = $db->query("SELECT COUNT(*) c FROM users WHERE role='doctor'")->fetch_assoc()['c'];
$todayAppts    = $db->query("SELECT COUNT(*) c FROM appointments WHERE appointment_date=CURDATE()")->fetch_assoc()['c'];
$pendingAppts  = $db->query("SELECT COUNT(*) c FROM appointments WHERE status='pending'")->fetch_assoc()['c'];
$monthRevenue  = $db->query("SELECT COALESCE(SUM(amount),0) total FROM payments WHERE payment_status='paid' AND MONTH(paid_at)=MONTH(CURDATE()) AND YEAR(paid_at)=YEAR(CURDATE())")->fetch_assoc()['total'];
$totalRevenue  = $db->query("SELECT COALESCE(SUM(amount),0) total FROM payments WHERE payment_status='paid'")->fetch_assoc()['total'];
$todayRevenue  = $db->query("SELECT COALESCE(SUM(amount),0) total FROM payments WHERE payment_status='paid' AND DATE(paid_at)=CURDATE()")->fetch_assoc()['total'];

// Monthly revenue for chart (last 6 months)
$revenueData = $db->query("SELECT DATE_FORMAT(paid_at,'%b %Y') month_label, DATE_FORMAT(paid_at,'%Y-%m') month_key, SUM(amount) total FROM payments WHERE payment_status='paid' AND paid_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY month_key ORDER BY month_key")->fetch_all(MYSQLI_ASSOC);

// Revenue by service
$serviceRevenue = $db->query("SELECT s.name, SUM(p.amount) total FROM payments p JOIN appointments a ON p.appointment_id=a.id JOIN services s ON a.service_id=s.id WHERE p.payment_status='paid' GROUP BY s.id ORDER BY total DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// Recent appointments
$recentAppts = $db->query("SELECT a.*, s.name svc_name, dp.full_name doc_name, pp.full_name pat_name FROM appointments a JOIN services s ON a.service_id=s.id JOIN doctor_profiles dp ON a.doctor_id=dp.user_id JOIN patient_profiles pp ON a.patient_id=pp.user_id ORDER BY a.created_at DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/admin-sidebar.php';
?>

<div class="page-header">
    <h1>Admin Dashboard</h1>
    <p>Overview of clinic operations — <?= date('F d, Y') ?></p>
</div>

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr))">
    <div class="stat-card">
        <div class="stat-icon" style="background:var(--pink-100)"><i class="fas fa-users" style="color:var(--pink-500)"></i></div>
        <div class="stat-value"><?= $totalPatients ?></div>
        <div class="stat-label">Total Patients</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#dbeafe"><i class="fas fa-calendar-day" style="color:#2563eb"></i></div>
        <div class="stat-value"><?= $todayAppts ?></div>
        <div class="stat-label">Today's Appointments</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fef9c3"><i class="fas fa-hourglass-half" style="color:#d97706"></i></div>
        <div class="stat-value"><?= $pendingAppts ?></div>
        <div class="stat-label">Pending Approval</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#dcfce7"><i class="fas fa-peso-sign" style="color:#16a34a"></i></div>
        <div class="stat-value" style="font-size:1.5rem">₱<?= number_format($todayRevenue,0) ?></div>
        <div class="stat-label">Today's Revenue</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f3e8ff"><i class="fas fa-chart-bar" style="color:#7c3aed"></i></div>
        <div class="stat-value" style="font-size:1.5rem">₱<?= number_format($monthRevenue,0) ?></div>
        <div class="stat-label">This Month's Revenue</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fee2e2"><i class="fas fa-coins" style="color:#dc2626"></i></div>
        <div class="stat-value" style="font-size:1.5rem">₱<?= number_format($totalRevenue,0) ?></div>
        <div class="stat-label">Total Revenue</div>
    </div>
</div>

<!-- Charts -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem" class="responsive-grid-1">
    <div class="chart-card">
        <div class="chart-title">Monthly Revenue (Last 6 Months)</div>
        <canvas id="revenueChart" height="200"></canvas>
    </div>
    <div class="chart-card">
        <div class="chart-title">Top Services by Revenue</div>
        <canvas id="serviceChart" height="200"></canvas>
    </div>
</div>

<!-- Recent Appointments -->
<div class="table-card">
    <div class="table-header">
        <h3>Recent Appointments</h3>
        <a href="appointments.php" class="btn-secondary btn-sm">View All</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Patient</th><th>Doctor</th><th>Service</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($recentAppts as $a): ?>
            <tr>
                <td><?= sanitize($a['pat_name']) ?></td>
                <td><?= sanitize($a['doc_name']) ?></td>
                <td><?= sanitize($a['svc_name']) ?></td>
                <td><?= date('M d, Y', strtotime($a['appointment_date'])) ?> <small style="color:var(--gray-400)"><?= date('g:i A', strtotime($a['appointment_time'])) ?></small></td>
                <td><span class="badge badge-<?= strtolower($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
                <td>
                    <?php if ($a['status']==='pending'): ?>
                    <a href="appointments.php?approve=<?= $a['id'] ?>" class="btn-success btn-sm" style="font-size:0.75rem">Approve</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>@media(max-width:768px){.responsive-grid-1{grid-template-columns:1fr !important}}</style>

<?php
$revLabels = array_column($revenueData, 'month_label');
$revValues = array_column($revenueData, 'total');
$svcLabels = array_column($serviceRevenue, 'name');
$svcValues = array_column($serviceRevenue, 'total');
$extraJS = '<script>
const revCtx = document.getElementById("revenueChart");
if(revCtx){new Chart(revCtx,{type:"bar",data:{labels:'.json_encode($revLabels).',datasets:[{label:"Revenue (₱)",data:'.json_encode($revValues).',backgroundColor:"rgba(240,78,125,0.7)",borderColor:"#f04e7d",borderWidth:2,borderRadius:8}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{callback:v=>"₱"+Number(v).toLocaleString()}}}}});}

const svcCtx = document.getElementById("serviceChart");
if(svcCtx){new Chart(svcCtx,{type:"doughnut",data:{labels:'.json_encode($svcLabels).',datasets:[{data:'.json_encode($svcValues).',backgroundColor:["#f04e7d","#f9739a","#ff99b8","#ffc0d3","#ffe0e9"],borderWidth:0}]},options:{responsive:true,plugins:{legend:{position:"right"}}}});}
</script>';
?>
<?php require_once __DIR__ . '/../includes/dashboard-footer.php'; ?>
