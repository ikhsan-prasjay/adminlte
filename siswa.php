<?php
$page_title = 'Data Siswa';
$breadcrumb = 'Data Siswa';
require_once 'config/database.php';
require_once 'config/session.php';
check_login();

if ($_POST) {
    $action = $_POST['action'];

    $nis = escape_string($conn, $_POST['nis'] ?? '');
    $nama_lengkap = escape_string($conn, $_POST['nama_lengkap'] ?? '');
    $jenis_kelamin = escape_string($conn, $_POST['jenis_kelamin'] ?? '');
    $tempat_lahir = escape_string($conn, $_POST['tempat_lahir'] ?? '');
    $tanggal_lahir = escape_string($conn, $_POST['tanggal_lahir'] ?? '');
    $alamat = escape_string($conn, $_POST['alamat'] ?? '');
    $no_hp = escape_string($conn, $_POST['no_hp'] ?? '');
    $id_agama = escape_string($conn, $_POST['id_agama'] ?? '');
    $id_jurusan = escape_string($conn, $_POST['id_jurusan'] ?? '');

    if ($action == 'add') {
        if (empty($nis) || empty($nama_lengkap) || empty($jenis_kelamin) || empty($tempat_lahir) || empty($tanggal_lahir) || empty($alamat) || empty($id_agama) || empty($id_jurusan)) {
            set_alert('error', 'Semua field yang ditandai * wajib diisi!');
        } else {
            $check_nis_sql = "SELECT id FROM siswa WHERE nis = '$nis'";
            $check_nis_result = query($conn, $check_nis_sql);

            if ($check_nis_result && $check_nis_result->num_rows > 0) {
                set_alert('error', "Gagal menambahkan! NIS '$nis' sudah terdaftar.");
            } else if ($check_nis_result === false) {
                 set_alert('error', 'Terjadi kesalahan saat memeriksa NIS. Silakan coba lagi.');
            } else {
                $sql = "INSERT INTO siswa (nis, nama_lengkap, jenis_kelamin, tempat_lahir, tanggal_lahir, alamat, no_hp, id_agama, id_jurusan)
                        VALUES ('$nis', '$nama_lengkap', '$jenis_kelamin', '$tempat_lahir', '$tanggal_lahir', '$alamat', '$no_hp', '$id_agama', '$id_jurusan')";

                if (query($conn, $sql)) {
                    set_alert('success', 'Data siswa berhasil ditambahkan!');
                } else {
                    if (strpos($conn->error, "Duplicate entry") !== false && strpos($conn->error, "siswa.nis") !== false) {
                        set_alert('error', "Gagal menambahkan! NIS '$nis' sudah terdaftar.");
                    } else {
                        set_alert('error', 'Gagal menambahkan data siswa! Silakan periksa kembali data Anda.');
                    }
                }
            }
        }
        header("Location: siswa.php");
        exit();
    }

    if ($action == 'edit') {
        $id = escape_string($conn, $_POST['id'] ?? '');
        if (empty($id)) {
            set_alert('error', 'ID Siswa tidak valid untuk proses update.');
        } elseif (empty($nis) || empty($nama_lengkap) || empty($jenis_kelamin) || empty($tempat_lahir) || empty($tanggal_lahir) || empty($alamat) || empty($id_agama) || empty($id_jurusan)) {
            set_alert('error', 'Semua field yang ditandai * wajib diisi saat mengedit!');
        } else {
            $check_nis_sql = "SELECT id FROM siswa WHERE nis = '$nis' AND id != '$id'";
            $check_nis_result = query($conn, $check_nis_sql);

            if ($check_nis_result && $check_nis_result->num_rows > 0) {
                set_alert('error', "Gagal mengupdate! NIS '$nis' sudah digunakan oleh siswa lain.");
            } else if ($check_nis_result === false) {
                set_alert('error', 'Terjadi kesalahan saat memeriksa NIS untuk update. Silakan coba lagi.');
            } else {
                $sql = "UPDATE siswa SET
                        nis = '$nis',
                        nama_lengkap = '$nama_lengkap',
                        jenis_kelamin = '$jenis_kelamin',
                        tempat_lahir = '$tempat_lahir',
                        tanggal_lahir = '$tanggal_lahir',
                        alamat = '$alamat',
                        no_hp = '$no_hp',
                        id_agama = '$id_agama',
                        id_jurusan = '$id_jurusan',
                        updated_at = CURRENT_TIMESTAMP
                        WHERE id = '$id'";

                $query_edit_result = query($conn, $sql);
                if ($query_edit_result) {
                    if ($conn->affected_rows > 0) {
                        set_alert('success', 'Data siswa berhasil diupdate!');
                    } else {
                        set_alert('info', 'Tidak ada perubahan data pada siswa, atau ID siswa tidak ditemukan.');
                    }
                } else {
                     if (strpos($conn->error, "Duplicate entry") !== false && strpos($conn->error, "siswa.nis") !== false) {
                        set_alert('error', "Gagal mengupdate! NIS '$nis' sudah digunakan oleh siswa lain.");
                    } else {
                        set_alert('error', 'Gagal mengupdate data siswa! Silakan periksa kembali data Anda.');
                    }
                }
            }
        }
        header("Location: siswa.php");
        exit();
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = escape_string($conn, $_GET['id'] ?? '');
    $sql = "DELETE FROM siswa WHERE id = '$id'";

    if (query($conn, $sql)) {
        if ($conn->affected_rows > 0) {
            set_alert('success', 'Data siswa berhasil dihapus!');
        } else {
             set_alert('info', 'Data siswa tidak ditemukan atau sudah dihapus.');
        }
    } else {
        set_alert('error', 'Gagal menghapus data siswa!');
    }
    header("Location: siswa.php");
    exit();
}

$sql_display = "SELECT s.*, a.nama_agama, j.nama_jurusan, j.kode_jurusan
        FROM siswa s
        LEFT JOIN agama a ON s.id_agama = a.id
        LEFT JOIN jurusan j ON s.id_jurusan = j.id
        ORDER BY s.created_at DESC";
$siswa_data_result = query($conn, $sql_display);

$agama_data_result = query($conn, "SELECT * FROM agama ORDER BY nama_agama");
$jurusan_data_result = query($conn, "SELECT * FROM jurusan ORDER BY nama_jurusan");

include_once 'includes/header.php';
?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-users mr-1"></i>
                            Data Siswa
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalTambah">
                                <i class="fas fa-plus"></i> Tambah Siswa
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tableSiswa" class="table table-bordered table-striped table-data">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>NIS</th>
                                        <th>Nama Lengkap</th>
                                        <th>JK</th>
                                        <th>TTL</th>
                                        <th>Alamat</th>
                                        <th>No HP</th>
                                        <th>Agama</th>
                                        <th>Jurusan</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($siswa_data_result && $siswa_data_result->num_rows > 0):
                                        $no = 1;
                                        while ($row = fetch_assoc($siswa_data_result)):
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($row['nis']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $row['jenis_kelamin'] == 'L' ? 'primary' : 'secondary'; ?>">
                                                <?php echo $row['jenis_kelamin'] == 'L' ? 'L' : 'P'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['tempat_lahir']) . ', ' . date('d/m/Y', strtotime($row['tanggal_lahir'])); ?></td>
                                        <td><?php echo htmlspecialchars(substr($row['alamat'], 0, 30) . (strlen($row['alamat']) > 30 ? '...' : '')); ?></td>
                                        <td><?php echo htmlspecialchars($row['no_hp']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_agama'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_jurusan'] ?? '-'); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $row['status'] == 'aktif' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst(htmlspecialchars($row['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm" onclick="editSiswa(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars(addslashes($row['nama_lengkap'])); ?>', 'siswa.php')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php
                                        endwhile;
                                    else: ?>
                                        <tr><td colspan="11" class="text-center">
                                            <?php echo $siswa_data_result ? 'Tidak ada data siswa.' : 'Gagal memuat data siswa.';?>
                                        </td></tr>
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

<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalTambahLabel"><i class="fas fa-plus"></i> Tambah Data Siswa</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form method="post" action="siswa.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label>NIS <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="nis" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_lengkap" required>
                    </div>
                     <div class="form-group">
                        <label>Jenis Kelamin <span class="text-danger">*</span></label>
                        <select class="form-control" name="jenis_kelamin" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tempat Lahir <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="tempat_lahir" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Lahir <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="tanggal_lahir" required>
                    </div>
                    <div class="form-group">
                        <label>Alamat <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="alamat" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>No HP</label>
                        <input type="number" class="form-control" name="no_hp">
                    </div>
                    <div class="form-group">
                        <label>Agama <span class="text-danger">*</span></label>
                        <select class="form-control" name="id_agama" required>
                            <option value="">Pilih Agama</option>
                            <?php
                            if ($agama_data_result && $agama_data_result->num_rows > 0) {
                                mysqli_data_seek($agama_data_result, 0);
                                while ($agama = fetch_assoc($agama_data_result)):
                            ?>
                            <option value="<?php echo $agama['id']; ?>"><?php echo htmlspecialchars($agama['nama_agama']); ?></option>
                            <?php
                                endwhile;
                            } elseif ($agama_data_result === false) {
                                echo '<option value="" disabled>Gagal memuat data agama</option>';
                            } else {
                                echo '<option value="" disabled>Data agama kosong</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jurusan <span class="text-danger">*</span></label>
                        <select class="form-control" name="id_jurusan" required>
                            <option value="">Pilih Jurusan</option>
                             <?php
                            if ($jurusan_data_result && $jurusan_data_result->num_rows > 0) {
                                mysqli_data_seek($jurusan_data_result, 0);
                                while ($jurusan = fetch_assoc($jurusan_data_result)):
                            ?>
                            <option value="<?php echo $jurusan['id']; ?>"><?php echo htmlspecialchars($jurusan['nama_jurusan']); ?></option>
                            <?php
                                endwhile;
                            } elseif ($jurusan_data_result === false) {
                                echo '<option value="" disabled>Gagal memuat data jurusan</option>';
                            } else {
                                echo '<option value="" disabled>Data jurusan kosong</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
             <div class="modal-header">
                <h4 class="modal-title" id="modalEditLabel"><i class="fas fa-edit"></i> Edit Data Siswa</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form method="post" id="formEdit" action="siswa.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label>NIS <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="nis" id="edit_nis" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_lengkap" id="edit_nama_lengkap" required>
                    </div>
                     <div class="form-group">
                        <label>Jenis Kelamin <span class="text-danger">*</span></label>
                        <select class="form-control" name="jenis_kelamin" id="edit_jenis_kelamin" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tempat Lahir <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="tempat_lahir" id="edit_tempat_lahir" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Lahir <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="tanggal_lahir" id="edit_tanggal_lahir" required>
                    </div>
                    <div class="form-group">
                        <label>Alamat <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="alamat" rows="3" id="edit_alamat" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>No HP</label>
                        <input type="number" class="form-control" name="no_hp" id="edit_no_hp">
                    </div>
                    <div class="form-group">
                        <label>Agama <span class="text-danger">*</span></label>
                        <select class="form-control" name="id_agama" id="edit_id_agama" required>
                            <option value="">Pilih Agama</option>
                            <?php
                            if ($agama_data_result && $agama_data_result->num_rows > 0) {
                                mysqli_data_seek($agama_data_result, 0);
                                while ($agama = fetch_assoc($agama_data_result)):
                            ?>
                            <option value="<?php echo $agama['id']; ?>"><?php echo htmlspecialchars($agama['nama_agama']); ?></option>
                            <?php
                                endwhile;
                            } elseif ($agama_data_result === false) {
                                echo '<option value="" disabled>Gagal memuat data agama</option>';
                            } else {
                                echo '<option value="" disabled>Data agama kosong</option>';
                            }
                            ?>
                        </select>
                    </div>
                     <div class="form-group">
                        <label>Jurusan <span class="text-danger">*</span></label>
                        <select class="form-control" name="id_jurusan" id="edit_id_jurusan" required>
                            <option value="">Pilih Jurusan</option>
                             <?php
                            if ($jurusan_data_result && $jurusan_data_result->num_rows > 0) {
                                mysqli_data_seek($jurusan_data_result, 0);
                                while ($jurusan = fetch_assoc($jurusan_data_result)):
                            ?>
                            <option value="<?php echo $jurusan['id']; ?>"><?php echo htmlspecialchars($jurusan['nama_jurusan']); ?></option>
                            <?php
                                endwhile;
                            } elseif ($jurusan_data_result === false) {
                                echo '<option value="" disabled>Gagal memuat data jurusan</option>';
                            } else {
                                echo '<option value="" disabled>Data jurusan kosong</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editSiswa(data) {
    $('#edit_id').val(data.id);
    $('#edit_nis').val(data.nis);
    $('#edit_nama_lengkap').val(data.nama_lengkap);
    $('#edit_jenis_kelamin').val(data.jenis_kelamin);
    $('#edit_tempat_lahir').val(data.tempat_lahir);
    $('#edit_tanggal_lahir').val(data.tanggal_lahir);
    $('#edit_no_hp').val(data.no_hp);
    $('#edit_alamat').val(data.alamat);
    $('#edit_id_agama').val(data.id_agama);
    $('#edit_id_jurusan').val(data.id_jurusan);
    $('#modalEdit').modal('show');
}

function confirmDelete(id, nama, url) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data " + nama + " akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url + '?action=delete&id=' + id;
        }
    });
}
</script>

<?php
include_once 'includes/footer.php';
?>
