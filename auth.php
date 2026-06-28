<?php
/**
 * Hevan Booking - المصادقة وجلسات المستخدم
 */
require_once __DIR__ . '/config.php';

session_start();

/**
 * هل المستخدم مسجل دخول كإداري؟
 */
function is_admin() {
    return !empty($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

/**
 * حماية الصفحات - لازم إداري عشان يشوفها
 */
function require_admin() {
    if (!is_admin()) {
        redirect('login.php');
    }
}

/**
 * تسجيل دخول (اختبار بسيط)
 */
function login_user($username, $password) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin' LIMIT 1");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) {
        $_SESSION['user'] = [
            'id'       => (int)$user['id'],
            'name'     => $user['name'],
            'username' => $user['username'],
            'role'     => $user['role'],
        ];
        return true;
    }
    return false;
}

/**
 * تسجيل الخروج
 */
function logout_user() {
    unset($_SESSION['user']);
    session_destroy();
}
