<?php
require_once __DIR__ . '/config.php';

session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getRole() {
    return $_SESSION['role'] ?? null;
}

function requireLogin($redirect = '/index.php') {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/pages/login.php');
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if (getRole() !== $role) {
        header('Location: ' . BASE_URL . '/index.php?error=unauthorized');
        exit;
    }
}

function login($email, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, username, email, password, role, is_active FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user && password_verify($password, $user['password'])) {
        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'Your account has been deactivated.'];
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        return ['success' => true, 'role' => $user['role']];
    }
    return ['success' => false, 'message' => 'Invalid email or password.'];
}

function logout() {
    session_destroy();
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getPatientProfile($user_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM patient_profiles WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getDoctorProfile($user_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM doctor_profiles WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function createNotification($user_id, $title, $message, $type = 'system', $link = '') {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?,?,?,?,?)");
    $stmt->bind_param("issss", $user_id, $title, $message, $type, $link);
    $stmt->execute();
}

function getUnreadNotifications($user_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['count'];
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function generateReceiptNumber() {
    return 'RTC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

function getDashboardUrl() {
    switch (getRole()) {
        case 'admin': return BASE_URL . '/admin/index.php';
        case 'doctor': return BASE_URL . '/doctor/index.php';
        case 'patient': return BASE_URL . '/patient/index.php';
        default: return BASE_URL . '/index.php';
    }
}
