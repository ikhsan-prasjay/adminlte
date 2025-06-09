<?php
// includes/header.php
require_once 'config/database.php';
require_once 'config/session.php';
check_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset($page_title) ? $page_title : 'Sistem Sekolah'; ?></title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap4.min.css">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.4.8/sweetalert2.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="fas fa-user"></i>
                    <?php echo $_SESSION['nama_lengkap']; ?>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item-text">
                        <strong><?php echo $_SESSION['nama_lengkap']; ?></strong><br>
                        <small class="text-muted"><?php echo $_SESSION['email']; ?></small>
                    </span>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-item">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </div>
            </li>
        </ul>
    </nav>

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="index.php" class="brand-link">
            <i class="fas fa-graduation-cap brand-image img-circle elevation-3" style="margin-left: 8px;"></i>
            <span class="brand-text font-weight-light">Sistem Sekolah</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="siswa.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'siswa.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Data Siswa</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="agama.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'agama.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-pray"></i>
                            <p>Data Agama</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="jurusan.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'jurusan.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-book"></i>
                            <p>Data Jurusan</p>
                        </a>
                    </li>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                    <li class="nav-item">
                        <a href="users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-user-cog"></i>
                            <p>Data Users</p>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <?php if (isset($breadcrumb)): ?>
                            <li class="breadcrumb-item active"><?php echo $breadcrumb; ?></li>
                            <?php endif; ?>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
