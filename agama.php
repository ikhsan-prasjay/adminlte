<?php
// Agama.php
$page_title = 'Data Agama';
$breadcrumb = 'Data Agama';
require_once 'config/database.php';
require_once 'config/session.php';
check_login();
// Proses CRUD
if ($_POST) {
    $action = $_POST['action'];

    if ($action == 'add') {
        $nama_agama = escape_string($conn, $_POST['nama_agama']);

        // Perbaikan NAMA TABEL dari nama_agama menjadi agama
        $sql = "INSERT INTO agama (nama_agama)
                VALUES ('$nama_agama')";

        if (query($conn, $sql)) {
            set_alert('success', 'Data Agama berhasil ditambahkan!');
        } else {
            set_alert('error', 'Gagal menambahkan data Agama!');
        }
        header("Location: agama.php");
        exit();
    }

    if ($action == 'edit') {
        $id = escape_string($conn, $_POST['id']);
        $nama_agama = escape_string($conn, $_POST['nama_agama']);

        $sql = "UPDATE agama SET
                nama_agama = '$nama_agama',
                updated_at = CURRENT_TIMESTAMP
                WHERE id = '$id'";

        $query_result = query($conn, $sql);
        if ($query_result) {
            // Optional: Cek apakah ada baris yang benar-benar terupdate
            if ($conn->affected_rows > 0) {
                set_alert('success', 'Data Agama berhasil diupdate!');
            } else {
                set_alert('info', 'Tidak ada perubahan data pada Agama.');
            }
        } else {
            set_alert('error', 'Gagal mengupdate data Agama!');
        }
        header("Location: agama.php");
        exit();
    }
}

// Proses Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = escape_string($conn, $_GET['id']);
    $sql = "DELETE FROM agama WHERE id = '$id'";

    if (query($conn, $sql)) {
        if ($conn->affected_rows > 0) {
            set_alert('success', 'Data Agama berhasil dihapus!');
        } else {
            set_alert('info', 'Data Agama tidak ditemukan atau sudah dihapus.');
        }
    } else {
        set_alert('error', 'Gagal menghapus data Agama!');
    }
    header("Location: agama.php");
    exit();
}
$agama_data_result = query($conn, "SELECT * FROM agama ORDER BY nama_agama"); // Ganti nama variabel
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
                            Data Agama
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalTambah">
                                <i class="fas fa-plus"></i> Tambah Agama
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tableAgama" class="table table-bordered table-striped table-data">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Agama</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($agama_data_result && $agama_data_result->num_rows > 0): // Cek hasil query
                                        $no = 1;
                                        while ($row = fetch_assoc($agama_data_result)):
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_agama']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm" onclick="editAgama(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars(addslashes($row['nama_agama'])); ?>', 'agama.php')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php
                                        endwhile;
                                    else: ?>
                                        <tr><td colspan="3" class="text-center">
                                            <?php echo $agama_data_result ? 'Tidak ada data agama.' : 'Gagal memuat data agama.'; ?>
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

<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-plus"></i> Tambah Data Agama
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Nama Agama <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama_agama" required>
                            </div>
                        </div>
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

<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-edit"></i> Edit Data Agama
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="post" id="formEdit">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Nama Agama <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama_agama" id="edit_nama_agama" required>
                            </div>
                        </div>
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
function editAgama(data) {
    $('#edit_id').val(data.id);
    $('#edit_nama_agama').val(data.nama_agama);
    $('#modalEdit').modal('show');
}
</script>

<?php include 'includes/footer.php'; ?>
