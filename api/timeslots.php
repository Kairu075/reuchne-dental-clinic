<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';

$doctor_id = (int)($_GET['doctor_id'] ?? 0);
$date_str  = $_GET['date'] ?? '';

if (!$doctor_id || !$date_str) {
    echo json_encode(['slots' => []]);
    exit;
}

$db = getDB();
$date = new DateTime($date_str);
$dayName = $date->format('l'); // Monday, Tuesday, etc.

// Get schedule for this day
$sched = $db->prepare("SELECT * FROM schedules WHERE doctor_id=? AND day_of_week=? AND is_available=1");
$sched->bind_param("is", $doctor_id, $dayName);
$sched->execute();
$schedule = $sched->get_result()->fetch_assoc();

if (!$schedule) {
    echo json_encode(['slots' => [], 'message' => 'Doctor is not available on this day.']);
    exit;
}

// Check if date is blocked
$blocked = $db->prepare("SELECT id FROM blocked_dates WHERE doctor_id=? AND blocked_date=?");
$blocked->bind_param("is", $doctor_id, $date_str);
$blocked->execute();
if ($blocked->get_result()->num_rows > 0) {
    echo json_encode(['slots' => [], 'message' => 'Doctor is not available on this date.']);
    exit;
}

// Get booked appointments for this day
$booked = $db->prepare("SELECT appointment_time FROM appointments WHERE doctor_id=? AND appointment_date=? AND status NOT IN ('cancelled')");
$booked->bind_param("is", $doctor_id, $date_str);
$booked->execute();
$bookedTimes = array_column($booked->get_result()->fetch_all(MYSQLI_ASSOC), 'appointment_time');

// Generate slots
$slots = [];
$start = new DateTime($date_str . ' ' . $schedule['start_time']);
$end   = new DateTime($date_str . ' ' . $schedule['end_time']);
$slot_duration = $schedule['slot_duration'] ?? 60;
$interval = new DateInterval('PT' . $slot_duration . 'M');
$now = new DateTime();

$current = clone $start;
while ($current < $end) {
    $timeVal = $current->format('H:i:s');
    $displayTime = $current->format('g:i A');
    $taken = in_array($timeVal, $bookedTimes);
    // Past time check
    if ($current <= $now && $date_str === $now->format('Y-m-d')) {
        $taken = true;
    }
    $slots[] = [
        'value'   => $timeVal,
        'display' => $displayTime,
        'taken'   => $taken,
    ];
    $current->add($interval);
}

echo json_encode(['slots' => $slots]);
