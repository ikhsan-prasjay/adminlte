<?php
// config/session.php
session_start();

// Fungsi untuk cek login
function check_login() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
        header("Location: login.php");
        exit();
    }
}

// Fungsi untuk cek role admin
function check_admin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: index.php");
        exit();
    }
}

// Fungsi untuk login
function login_user($user_data) {
    $_SESSION['user_id'] = $user_data['id'];
    $_SESSION['username'] = $user_data['username'];
    $_SESSION['nama_lengkap'] = $user_data['nama_lengkap'];
    $_SESSION['email'] = $user_data['email'];
    $_SESSION['role'] = $user_data['role'];
}

// Fungsi untuk logout
function logout_user() {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Fungsi untuk mendapatkan pesan alert
function get_alert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return $alert;
    }
    return null;
}

// Fungsi untuk set pesan alert
function set_alert($type, $message) {
    $_SESSION['alert'] = [
        'type' => $type,
        'message' => $message
    ];
}
?>
