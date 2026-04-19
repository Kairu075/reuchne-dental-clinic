<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('doctor');
$db = getDB();
$uid = $_SESSION['user_id'];
$id  = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: appointments.php?error=invalid');
    exit;
}

$stmt = $db->prepare("SELECT patient_id FROM appointments WHERE id=? AND doctor_id=? AND status='approved'");
if (!$stmt) {
    error_log("Complete appt prepare failed: " . $db->error);
    header('Location: appointments.php?error=1');
    exit;
}
$stmt->bind_param("ii",$id,$uid);
if (!$stmt->execute()) {
    error_log("Complete appt execute failed: " . $db->error);
    header('Location: appointments.php?error=1');
    exit;
}
$result = $stmt->get_result();
$appt = $result->fetch_assoc();
if (!$appt) {
    header('Location: appointments.php?error=no-appt');
    exit;
}

if ($appt) {
    $update = $db->prepare("UPDATE appointments SET status='completed' WHERE id=?");
    if (!$update) {
        error_log("Update status prepare failed: " . $db->error);
        header('Location: appointments.php?error=1');
        exit;
    }
    $update->bind_param("i",$id);
    if (!$update->execute()) {
        error_log("Update status execute failed: " . $db->error);
        header('Location: appointments.php?error=1');
        exit;
    }
    createNotification($appt['patient_id'],'Appointment Completed','Your recent appointment has been marked as completed. Thank you!','appointment');
}
header('Location: appointments.php?completed=1');
exit;
