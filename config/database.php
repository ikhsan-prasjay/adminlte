<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "sekolah";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    error_log("Database Connection Failed: " . $conn->connect_error);
    die("Koneksi ke database gagal. Silakan coba beberapa saat lagi atau hubungi administrator.");
}

$conn->set_charset("utf8");

function escape_string($conn, $string) {
    return $conn->real_escape_string($string);
}

function query($conn, $sql) {
    $result = $conn->query($sql);
    if (!$result) {
        error_log("MySQLi Error: " . $conn->error . " | SQL: " . $sql);
        return false;
    }
    return $result;
}

function fetch_assoc($result) {
    if ($result instanceof mysqli_result) {
        return $result->fetch_assoc();
    }
    return null;
}

function fetch_all($result) {
    if ($result instanceof mysqli_result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}
?>
