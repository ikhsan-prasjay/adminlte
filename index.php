<?php
// index.php
$page_title = 'Dashboard';
$breadcrumb = 'Dashboard';
require_once 'config/database.php';
require_once 'config/session.php';
check_login(); // Pastikan check_login dipanggil sebelum penggunaan $_SESSION

// Mengambil statistik data
$stats = [
    'siswa' => 0, // Default values
    'jurusan' => 0,
    'agama' => 0,
    'users' => 0
];
$query_error_occurred = false;

// Total Siswa
$result_siswa = query($conn, "SELECT COUNT(*) as total FROM siswa WHERE status = 'aktif'");
if ($result_siswa) {
    $data = fetch_assoc($result_siswa);
    if ($data) $stats['siswa'] = $data['total'];
} else {
    $query_error_occurred = true;
}

// Total Jurusan
$result_jurusan = query($conn, "SELECT COUNT(*) as total FROM jurusan");
if ($result_jurusan) {
    $data = fetch_assoc($result_jurusan);
    if ($data) $stats['jurusan'] = $data['total'];
} else {
    $query_error_occurred = true;
}

// Total Agama
$result_agama_count = query($conn, "SELECT COUNT(*) as total FROM agama"); // Renamed variable
if ($result_agama_count) {
    $data = fetch_assoc($result_agama_count);
    if ($data) $stats['agama'] = $data['total'];
} else {
    $query_error_occurred = true;
}

// Total Users (hanya jika admin)
if ($_SESSION['role'] == 'admin') {
    $result_users = query($conn, "SELECT COUNT(*) as total FROM users WHERE status = 'aktif'");
    if ($result_users) {
        $data = fetch_assoc($result_users);
        if ($data) $stats['users'] = $data['total'];
    } else {
        $query_error_occurred = true;
    }
}

if ($query_error_occurred) {
    // set_alert('warning', 'Beberapa statistik gagal dimuat karena masalah database.');
    // Pemberitahuan ini mungkin terlalu mengganggu untuk setiap load halaman jika ada masalah sementara.
    // Cukup dengan nilai default 0 dan error log dari database.php
}


// Data siswa per jurusan
$siswa_jurusan = [];
$sql_siswa_jurusan = "SELECT j.nama_jurusan, COUNT(s.id) as jumlah
        FROM jurusan j
        LEFT JOIN siswa s ON j.id = s.id_jurusan AND s.status = 'aktif'
        GROUP BY j.id, j.nama_jurusan
        ORDER BY j.nama_jurusan";
$result_siswa_jurusan = query($conn, $sql_siswa_jurusan);
if ($result_siswa_jurusan) {
    while ($row = fetch_assoc($result_siswa_jurusan)) {
        $siswa_jurusan[] = $row;
    }
} else {
    // set_alert('warning', 'Data chart siswa per jurusan gagal dimuat.');
}

// Data siswa per jenis kelamin
$siswa_gender = [];
$sql_siswa_gender = "SELECT jenis_kelamin, COUNT(*) as jumlah
        FROM siswa
        WHERE status = 'aktif'
        GROUP BY jenis_kelamin";
$result_siswa_gender = query($conn, $sql_siswa_gender);
if ($result_siswa_gender) {
    while ($row = fetch_assoc($result_siswa_gender)) {
        $siswa_gender[] = $row;
    }
} else {
    // set_alert('warning', 'Data chart siswa per jenis kelamin gagal dimuat.');
}

// Siswa Terbaru
$siswa_terbaru = [];
$sql_siswa_terbaru = "SELECT s.nis, s.nama_lengkap, s.jenis_kelamin, j.nama_jurusan, s.created_at
                       FROM siswa s
                       LEFT JOIN jurusan j ON s.id_jurusan = j.id
                       WHERE s.status = 'aktif'
                       ORDER BY s.created_at DESC
                       LIMIT 5";
$result_siswa_terbaru = query($conn, $sql_siswa_terbaru);
if ($result_siswa_terbaru) {
    while ($row = fetch_assoc($result_siswa_terbaru)) {
        $siswa_terbaru[] = $row;
    }
} else {
    // set_alert('warning', 'Data siswa terbaru gagal dimuat.');
}


include_once 'includes/header.php';
?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-4 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?php echo $stats['siswa']; ?></h3>
                        <p>Total Siswa</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <a href="siswa.php" class="small-box-footer">
                        Selengkapnya <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-4 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?php echo $stats['jurusan']; ?></h3>
                        <p>Total Jurusan</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <a href="jurusan.php" class="small-box-footer">
                        Selengkapnya <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-4 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?php echo $stats['agama']; ?></h3>
                        <p>Total Agama</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-pray"></i>
                    </div>
                    <a href="agama.php" class="small-box-footer">
                        Selengkapnya <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'){ ?>
            <div class="col-lg-12 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?php echo $stats['users']; ?></h3>
                        <p>Total Users</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-cog"></i>
                    </div>
                    <a href="users.php" class="small-box-footer">
                        Selengkapnya <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div> </div>
            <?php } ?>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie mr-1"></i>
                            Siswa per Jurusan
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($siswa_jurusan)): ?>
                        <canvas id="chartJurusan" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        <?php else: ?>
                        <p class="text-center">Data chart siswa per jurusan tidak tersedia atau gagal dimuat.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar mr-1"></i>
                            Siswa per Jenis Kelamin
                        </h3>
                    </div>
                    <div class="card-body">
                         <?php if (!empty($siswa_gender)): ?>
                        <canvas id="chartGender" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        <?php else: ?>
                        <p class="text-center">Data chart siswa per jenis kelamin tidak tersedia atau gagal dimuat.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-users mr-1"></i>
                            Siswa Terbaru
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>NIS</th>
                                        <th>Nama Lengkap</th>
                                        <th>Jenis Kelamin</th>
                                        <th>Jurusan</th>
                                        <th>Tanggal Daftar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($siswa_terbaru)):
                                        $no = 1;
                                        foreach ($siswa_terbaru as $row): // Menggunakan data yang sudah di-fetch
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($row['nis']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $row['jenis_kelamin'] == 'L' ? 'primary' : 'secondary'; ?>">
                                                <?php echo $row['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['nama_jurusan'] ?? '-'); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                    </tr>
                                    <?php
                                        endforeach;
                                    elseif ($result_siswa_terbaru === false) : // Cek jika query gagal
                                    ?>
                                        <tr><td colspan="6" class="text-center">Gagal memuat data siswa terbaru.</td></tr>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center">Tidak ada data siswa terbaru.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
include_once 'includes/footer.php';
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function() {
    // Chart Jurusan
    <?php if (!empty($siswa_jurusan)): ?>
    var ctxJurusan = document.getElementById('chartJurusan');
    if (ctxJurusan) { // Pastikan elemen ada sebelum membuat chart
        var chartJurusan = new Chart(ctxJurusan, {
            type: 'pie',
            data: {
                labels: [
                    <?php foreach ($siswa_jurusan as $data): ?>
                    '<?php echo htmlspecialchars($data['nama_jurusan'], ENT_QUOTES, 'UTF-8'); ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    label: 'Jumlah Siswa',
                    data: [
                        <?php foreach ($siswa_jurusan as $data): ?>
                        <?php echo $data['jumlah']; ?>,
                        <?php endforeach; ?>
                    ],
                    backgroundColor: [
                        '#f56954', '#00a65a', '#f39c12', '#00c0ef',
                        '#3c8dbc', '#d2d6de', '#9b59b6', '#34495e',
                        '#16a085', '#27ae60', '#2980b9', '#8e44ad'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
                // scales Y tidak relevan untuk pie chart
            }
        });
    }
    <?php endif; ?>

    // Chart Gender
    <?php if (!empty($siswa_gender)): ?>
    var ctxGender = document.getElementById('chartGender');
    if (ctxGender) { // Pastikan elemen ada
        var chartGender = new Chart(ctxGender.getContext('2d'), {
            type: 'bar',
            data: {
                labels: [
                    <?php foreach ($siswa_gender as $data): ?>
                    '<?php echo $data['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    label: 'Jumlah Siswa',
                    data: [
                        <?php foreach ($siswa_gender as $data): ?>
                        <?php echo $data['jumlah']; ?>,
                        <?php endforeach; ?>
                    ],
                    backgroundColor: [
                        '#3c8dbc',
                        '#f56954'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            callback: function(value) { if (Number.isInteger(value)) { return value; } },
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    <?php endif; ?>
});
</script>
