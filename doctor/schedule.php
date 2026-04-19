<?php
$pageTitle = 'My Schedule';
require_once __DIR__ . '/../includes/auth.php';
requireRole('doctor');
$db = getDB();
$uid = $_SESSION['user_id'];

$success = '';
$error = '';
// Update schedule availability
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_schedule'])) {

    foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day) {
        $avail = isset($_POST['avail_'.$day]) ? 1 : 0;
        $start = sanitize($_POST['start_'.$day] ?? '09:00') . ':00';
        $end   = sanitize($_POST['end_'.$day] ?? '17:00') . ':00';
        // Upsert
        $chk = $db->prepare("SELECT id FROM schedules WHERE doctor_id=? AND day_of_week=?");
        if (!$chk) {
            $error .= "Check query failed for $day: " . $db->error . " | ";
            continue;
        }
        $chk->bind_param("is",$uid,$day);
        if (!$chk->execute()) {
            $error .= "Check execute failed for $day: " . $chk->error . " | ";
            continue;
        }
        if ($chk->get_result()->num_rows > 0) {
            $stmt = $db->prepare("UPDATE schedules SET start_time=?,end_time=?,is_available=? WHERE doctor_id=? AND day_of_week=?");
            if (!$stmt) {
                $error .= "Update prepare failed for $day: " . $db->error . " | ";
                continue;
            }
            if (!$stmt->bind_param("ssiis",$start,$end,$avail,$uid,$day)) {
                $error .= "Bind failed for $day UPDATE: " . $stmt->error . " | ";
                continue;
            }
            if (!$stmt->execute()) {
                $error .= "Execute failed for $day UPDATE: " . $stmt->error . " | ";
                continue;
            }
        } else {
            $stmt = $db->prepare("INSERT INTO schedules (doctor_id,day_of_week,start_time,end_time,is_available) VALUES (?,?,?,?,?)");
            if (!$stmt) {
                $error .= "Insert prepare failed for $day: " . $db->error . " | ";
                continue;
            }
            if (!$stmt->bind_param("isssi",$uid,$day,$start,$end,$avail)) {
                $error .= "Bind failed for $day INSERT: " . $stmt->error . " | ";
                continue;
            }
            if (!$stmt->execute()) {
                $error .= "Execute failed for $day INSERT: " . $stmt->error . " | ";
                continue;
            }
        }
    }
    echo '<div style="background:yellow;padding:1rem;margin:2rem 0;"><h4>DEBUG POST:</h4><pre>';
    echo "UID: $uid\\n";
    echo "Error: " . htmlspecialchars($error) . "\\n";
    echo "POST keys: " . print_r(array_keys($_POST), true) . "\\n";
    foreach (['Friday'] as $testday) {
        echo "\\nTEST $testday:\\n";
        echo 'start_'. $testday . ': ' . ($_POST['start_'.$testday] ?? 'MISSING') . "\\n";
        echo 'end_'. $testday . ': ' . ($_POST['end_'.$testday] ?? 'MISSING') . "\\n";
        echo 'avail_'. $testday . ': ' . ($_POST['avail_'.$testday] ?? 'MISSING') . "\\n";
    }
    echo '</pre></div>';
    if (empty($error)) {
        $success = 'Schedule updated successfully!';
        header('Location: schedule.php?success=1'); exit;
    } else {
        $error = rtrim($error, ' | ') . '. Please check times (HH:MM) and try again.';
    }
}

// Block a date
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['block_date'])) {
    $bdate  = sanitize($_POST['blocked_date'] ?? '');
    $reason = sanitize($_POST['block_reason'] ?? '');
    if ($bdate) {
        $stmt = $db->prepare("INSERT IGNORE INTO blocked_dates (doctor_id,blocked_date,reason) VALUES (?,?,?)");
        if (!$stmt) {
            $error = "Block date query failed: " . $db->error;
        } else {
            $stmt->bind_param("iss",$uid,$bdate,$reason);
            $stmt->execute();
            $success = 'Date blocked!';
        }
    }
}

// Unblock
if (isset($_GET['unblock'])) {
    $bid = (int)$_GET['unblock'];
    $stmt = $db->prepare("DELETE FROM blocked_dates WHERE id=? AND doctor_id=?");
    if ($stmt) {
        $stmt->bind_param("ii",$bid,$uid);
        $stmt->execute();
        header('Location: schedule.php?unblocked=1'); exit;
    } else {
        $error = "Unblock query failed: " . $db->error;
    }
}

// Get schedule
$schedules = [];
$sched = $db->prepare("SELECT * FROM schedules WHERE doctor_id=? ORDER BY FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')");
if (!$sched) {
    $error = "Schedule fetch failed: " . $db->error;
    $schedules = [];
} else {
    $sched->bind_param("i",$uid); $sched->execute();
    foreach ($sched->get_result()->fetch_all(MYSQLI_ASSOC) as $s) $schedules[$s['day_of_week']] = $s;
}

// Get blocked dates
$blocked = $db->prepare("SELECT * FROM blocked_dates WHERE doctor_id=? AND blocked_date >= CURDATE() ORDER BY blocked_date");
if (!$blocked) {
    $error = "Blocked dates fetch failed: " . $db->error;
    $blockedDates = [];
} else {
    $blocked->bind_param("i",$uid); $blocked->execute();
    $blockedDates = $blocked->get_result()->fetch_all(MYSQLI_ASSOC);
}

require_once __DIR__ . '/../includes/doctor-sidebar.php';
?>
<div class="page-header">
    <h1>My Schedule</h1>
    <p>Set your availability and block dates for leaves or emergencies.</p>
</div>

<?php if ($success): ?><div class="alert alert-success" data-dismiss="3000"><i class="fas fa-check-circle"></i><?= $success ?></div><?php endif; ?>
<?php if (isset($_GET['unblocked'])): ?><div class="alert alert-info" data-dismiss="3000"><i class="fas fa-unlock"></i> Date unblocked!</div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger" data-dismiss="5000"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 320px;gap:1.5rem" class="responsive-grid-1">
    <!-- Weekly Schedule -->
    <div class="table-card" style="padding:1.75rem">
        <h4 style="margin-bottom:1.25rem;padding-bottom:0.75rem;border-bottom:1px solid var(--gray-100)"><i class="fas fa-clock" style="color:var(--pink-400);margin-right:0.5rem"></i>Weekly Availability</h4>
        <form method="POST">
            <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day):
                $s = $schedules[$day] ?? null;
                $avail = $s ? (bool)$s['is_available'] : ($day !== 'Sunday');
                $start = $s['start_time'] ?? '09:00:00';
                $end   = $s['end_time']   ?? '17:00:00';
            ?>
            <div style="display:flex;align-items:center;gap:1.5rem;padding:0.85rem 0;border-bottom:1px solid var(--gray-50)">
                <label style="display:flex;align-items:center;gap:0.6rem;min-width:120px;cursor:pointer">
                    <input type="checkbox" name="avail_<?= $day ?>" <?= $avail ? 'checked' : '' ?> style="accent-color:var(--pink-500);width:16px;height:16px">
                    <span style="font-weight:600;font-size:0.9rem"><?= $day ?></span>
                </label>
                <div style="display:flex;align-items:center;gap:0.5rem;flex:1">
                    <input type="time" name="start_<?= $day ?>" value="<?= substr($start,0,5) ?>" class="form-control" style="max-width:120px">
                    <span style="color:var(--gray-400)">to</span>
                    <input type="time" name="end_<?= $day ?>" value="<?= substr($end,0,5) ?>" class="form-control" style="max-width:120px">
                </div>
                <?php if (!$avail): ?><span class="badge" style="background:var(--gray-100);color:var(--gray-500)">Off</span><?php endif; ?>
            </div>
            <?php endforeach; ?>
            <div style="margin-top:1.5rem">
                <button type="submit" name="update_schedule" class="btn-primary"><i class="fas fa-save"></i> Save Schedule</button>
            </div>
        </form>
    </div>

    <div>
        <!-- Block Date -->
        <div class="table-card" style="padding:1.5rem;margin-bottom:1.25rem">
            <h4 style="margin-bottom:1rem;font-size:0.95rem"><i class="fas fa-ban" style="color:#ef4444;margin-right:0.5rem"></i>Block a Date</h4>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Date to Block</label>
                    <input type="date" name="blocked_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Reason</label>
                    <input type="text" name="block_reason" class="form-control" placeholder="Emergency, leave, etc.">
                </div>
                <button type="submit" name="block_date" class="btn-primary" style="background:#ef4444;width:100%;justify-content:center"><i class="fas fa-ban"></i> Block Date</button>
            </form>
        </div>

        <!-- Blocked Dates List -->
        <div class="table-card" style="padding:1.5rem">
            <h4 style="margin-bottom:1rem;font-size:0.95rem">Blocked Dates</h4>
            <?php if (empty($blockedDates)): ?>
            <p style="color:var(--gray-400);font-size:0.85rem;text-align:center;padding:1rem">No upcoming blocked dates.</p>
            <?php else: foreach ($blockedDates as $bd): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:0.6rem 0;border-bottom:1px solid var(--gray-50)">
                <div>
                    <div style="font-weight:600;font-size:0.88rem"><?= date('M d, Y', strtotime($bd['blocked_date'])) ?></div>
                    <?php if ($bd['reason']): ?><div style="font-size:0.78rem;color:var(--gray-400)"><?= sanitize($bd['reason']) ?></div><?php endif; ?>
                </div>
                <a href="?unblock=<?= $bd['id'] ?>" style="color:#ef4444;font-size:0.82rem" data-confirm="Unblock this date?"><i class="fas fa-unlock"></i></a>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>

<style>@media(max-width:768px){.responsive-grid-1{grid-template-columns:1fr !important}}</style>
<script>
document.querySelectorAll('input[type=checkbox]').forEach(cb => {
    cb.addEventListener('change', function(){
        const day = this.name.replace('avail_','');
        const row = this.closest('div[style*=flex]');
        row.querySelectorAll('input[type=time]').forEach(t => t.disabled = !this.checked);
        const badge = row.querySelector('.badge');
        if (badge) badge.style.display = this.checked ? 'none' : 'inline-flex';
    });
});
</script>
<?php require_once __DIR__ . '/../includes/dashboard-footer.php'; ?>
