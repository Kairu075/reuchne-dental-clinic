<?php
require_once 'includes/auth.php';
if (isLoggedIn()) {
    $user = getCurrentUser();
    echo "<h2>Current Session</h2>";
    echo "<pre>";
    print_r($_SESSION);
    echo "\nUser: ";
    print_r($user);
    echo "\nDoctor profile: ";
    print_r(getDoctorProfile($_SESSION['user_id']));
    echo "\n<a href='debug-schedules.php'>Schedules</a> | <a href='doctor/schedule.php'>Schedule</a>";
} else {
    echo "Not logged in";
}
?>

