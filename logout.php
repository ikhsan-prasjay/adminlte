<?php
// logout.php
require_once 'config/session.php'; // Memuat file konfigurasi sesi

// Panggil fungsi logout_user yang akan menghapus session dan redirect ke login.php
logout_user();
?>
