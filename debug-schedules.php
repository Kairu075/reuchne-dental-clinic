<?php
require_once 'includes/config.php';
$db = getDB();
$doctor_id = 2; // Dr. Bermas
echo "<h2>Schedules for doctor_id=$doctor_id</h2><pre>";
$res = $db->query("SELECT * FROM schedules WHERE doctor_id=$doctor_id ORDER BY day_of_week");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
echo "\nBlocked dates:\n";
$res = $db->query("SELECT * FROM blocked_dates WHERE doctor_id=$doctor_id");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
<a href="debug-schedules.php?clear=1" onclick="return confirm('Clear all schedules for this doctor?')">Clear Schedules</a>
