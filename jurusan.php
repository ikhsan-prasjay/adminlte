<?php
// jurusan.php
$page_title = 'Data Jurusan';
$breadcrumb = 'Data Jurusan';
require_once 'config/database.php';
require_once 'config/session.php';
check_login();

// Proses CRUD
if ($_POST) {
    $action = $_POST['action'];

    if ($action == 'add') {
        $kode_jurusan = escape_string($conn, $_POST['kode_jurusan']);
        $nama_jurusan = escape_string($conn, $_POST['nama_jurusan']);

        $check_sql = "SELECT id FROM jurusan WHERE kode_jurusan = '$kode_jurusan'";
        $check_result = query($conn, $check_sql);

        if ($check_result === false) { // Query check gagal
            set_alert('error', 'Gagal memverifikasi kode jurusan. Silakan coba lagi.');
        } elseif ($check_result->num_rows > 0) {
            set_alert('error', 'Gagal menambahkan! Kode Jurusan sudah ada.');
        } else {
            $sql = "INSERT INTO jurusan (kode_jurusan, nama_jurusan)
                    VALUES ('$kode_jurusan', '$nama_jurusan')";
            if (query($conn, $sql)) {
                set_alert('success', 'Data Jurusan berhasil ditambahkan!');
            } else {
                set_alert('error', 'Gagal menambahkan data Jurusan!');
            }
        }
        header("Location: jurusan.php");
        exit();
    }

    if ($action == 'edit') {
        $id = escape_string($conn, $_POST['id']);
        $kode_jurusan = escape_string($conn, $_POST['kode_jurusan']);
        $nama_jurusan = escape_string($conn, $_POST['nama_jurusan']);

        $check_sql = "SELECT id FROM jurusan WHERE kode_jurusan = '$kode_jurusan' AND id != '$id'";
        $check_result = query($conn, $check_sql);

        if ($check_result === false) { // Query check gagal
            set_alert('error', 'Gagal memverifikasi kode jurusan untuk update. Silakan coba lagi.');
        } elseif ($check_result->num_rows > 0) {
            set_alert('error', 'Gagal mengupdate! Kode Jurusan sudah digunakan oleh data lain.');
        } else {
            $sql = "UPDATE jurusan SET
                    kode_jurusan = '$kode_jurusan',
                    nama_jurusan = '$nama_jurusan',
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = '$id'";

            $query_update_result = query($conn, $sql); // Simpan hasil query
            if ($query_update_result) {
                if ($conn->affected_rows > 0) {
                    set_alert('success', 'Data Jurusan berhasil diupdate!');
                } else {
                    set_alert('info', 'Tidak ada perubahan data pada Jurusan.');
                }
            } else {
                set_alert('error', 'Gagal mengupdate data Jurusan!');
            }
        }
        header("Location: jurusan.php");
        exit();
    }
}

// Proses Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = escape_string($conn, $_GET['id']);
    $check_siswa_sql = "SELECT COUNT(*) as count FROM siswa WHERE id_jurusan = '$id'";
    $check_siswa_query_result = query($conn, $check_siswa_sql); // Ganti nama variabel

    if ($check_siswa_query_result) {
        $check_siswa_data = fetch_assoc($check_siswa_query_result); // Ganti nama variabel
        if ($check_siswa_data && $check_siswa_data['count'] > 0) {
            set_alert('error', 'Gagal menghapus! Jurusan masih digunakan oleh data siswa.');
        } else {
            // Jika count adalah 0 atau query untuk check_siswa_data gagal tapi tidak false (misal fetch_assoc gagal)
            // Sebaiknya pastikan $check_siswa_data ada
            if ($check_siswa_data) { // Hanya lanjut jika data count valid
                 $sql_delete = "DELETE FROM jurusan WHERE id = '$id'"; // Ganti nama variabel
                if (query($conn, $sql_delete)) {
                    if ($conn->affected_rows > 0) {
                        set_alert('success', 'Data Jurusan berhasil dihapus!');
                    } else {
                         set_alert('info', 'Data Jurusan tidak ditemukan atau sudah dihapus sebelumnya.');
                    }
                } else {
                    set_alert('error', 'Gagal menghapus data Jurusan!');
                }
            } else if ($check_siswa_data === null && $check_siswa_query_result->num_rows === 0 && !$conn->error){
                 // Kasus COUNT(*) mengembalikan 0 row, tidak mungkin terjadi jika SQL benar, tapi untuk jaga-jaga.
                 // Atau fetch_assoc mengembalikan null padahal query berhasil.
                 // Lanjutkan hapus karena berarti tidak ada siswa terkait (atau gagal fetch data count)
                $sql_delete_fallback = "DELETE FROM jurusan WHERE id = '$id'";
                if (query($conn, $sql_delete_fallback)) {
                    if ($conn->affected_rows > 0) {
                        set_alert('success', 'Data Jurusan berhasil dihapus! (count check issue)');
                    } else {
                         set_alert('info', 'Data Jurusan tidak ditemukan (count check issue).');
                    }
                } else {
                    set_alert('error', 'Gagal menghapus data Jurusan! (count check issue)');
                }
            } else { // fetch_assoc gagal
                 set_alert('error', 'Gagal memproses data relasi siswa. Penghapusan dibatalkan.');
            }

        }
    } else { // Gagal menjalankan query $check_siswa_sql
        set_alert('error', 'Gagal memeriksa relasi siswa dengan jurusan. Penghapusan dibatalkan.');
    }
    header("Location: jurusan.php");
    exit();
}

$jurusan_data_result = query($conn, "SELECT * FROM jurusan ORDER BY nama_jurusan"); // Ganti nama variabel
include_once 'includes/header.php';
?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-book mr-1"></i>
                            Data Jurusan
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalTambahJurusan">
                                <i class="fas fa-plus"></i> Tambah Jurusan
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tableJurusan" class="table table-bordered table-striped table-data">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode Jurusan</th>
                                        <th>Nama Jurusan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    // Menggunakan $jurusan_data_result yang sudah didefinisikan
                                    if ($jurusan_data_result && $jurusan_data_result->num_rows > 0):
                                        while ($row = fetch_assoc($jurusan_data_result)):
                                        ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($row['kode_jurusan']); ?></td>
                                            <td><?php echo htmlspecialchars($row['nama_jurusan']); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-info btn-sm" onclick="editJurusan(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars(addslashes($row['nama_jurusan'])); ?>', 'jurusan.php')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php
                                        endwhile;
                                    else: ?>
                                        <tr><td colspan="4" class="text-center">
                                            <?php echo $jurusan_data_result ? 'Tidak ada data jurusan.' : 'Gagal memuat data jurusan.'; ?>
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

<div class="modal fade" id="modalTambahJurusan" tabindex="-1" role="dialog" aria-labelledby="modalTambahJurusanLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalTambahJurusanLabel">
                    <i class="fas fa-plus"></i> Tambah Data Jurusan
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>
            <form method="post" action="jurusan.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="kode_jurusan">Kode Jurusan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="kode_jurusan" name="kode_jurusan" required maxlength="10">
                    </div>
                    <div class="form-group">
                        <label for="nama_jurusan">Nama Jurusan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_jurusan" name="nama_jurusan" required maxlength="100">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditJurusan" tabindex="-1" role="dialog" aria-labelledby="modalEditJurusanLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalEditJurusanLabel">
                    <i class="fas fa-edit"></i> Edit Data Jurusan
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>
            <form method="post" id="formEditJurusan" action="jurusan.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_jurusan_id">
                    <div class="form-group">
                        <label for="edit_kode_jurusan">Kode Jurusan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="kode_jurusan" id="edit_kode_jurusan" required maxlength="10">
                    </div>
                    <div class="form-group">
                        <label for="edit_nama_jurusan">Nama Jurusan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_jurusan" id="edit_nama_jurusan" required maxlength="100">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editJurusan(data) {
    $('#edit_jurusan_id').val(data.id);
    $('#edit_kode_jurusan').val(data.kode_jurusan);
    $('#edit_nama_jurusan').val(data.nama_jurusan);
    $('#modalEditJurusan').modal('show');
}
</script>

<?php include 'includes/footer.php'; ?>
