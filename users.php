<?php
// users.php
$page_title = 'Data Pengguna';
$breadcrumb = 'Data Pengguna';
require_once 'config/database.php';
require_once 'config/session.php';
check_login();
check_admin();

// Proses CRUD
if ($_POST) {
    $action = $_POST['action'];

    if ($action == 'add') {
        $username = escape_string($conn, $_POST['username'] ?? '');
        $password = $_POST['password'] ?? ''; // Tidak di-escape langsung karena akan di-hash
        $nama_lengkap = escape_string($conn, $_POST['nama_lengkap'] ?? '');
        $email = escape_string($conn, $_POST['email'] ?? '');
        $role = escape_string($conn, $_POST['role'] ?? '');
        $status = escape_string($conn, $_POST['status'] ?? '');

        if (empty($username) || empty($password) || empty($nama_lengkap) || empty($email) || empty($role) || empty($status)) {
            set_alert('error', 'Semua field yang ditandai * wajib diisi!');
        } else {
            $check_sql = "SELECT id FROM users WHERE username = '$username' OR email = '$email'";
            $check_result = query($conn, $check_sql);

            if ($check_result === false) {
                set_alert('error', 'Gagal memverifikasi data pengguna. Silakan coba lagi.');
            } elseif ($check_result->num_rows > 0) {
                set_alert('error', 'Gagal menambahkan! Username atau Email sudah terdaftar.');
            } else {
                $hashed_password = md5($password);
                $sql = "INSERT INTO users (username, password, nama_lengkap, email, role, status)
                        VALUES ('$username', '$hashed_password', '$nama_lengkap', '$email', '$role', '$status')";
                if (query($conn, $sql)) {
                    set_alert('success', 'Data Pengguna berhasil ditambahkan!');
                } else {
                    set_alert('error', 'Gagal menambahkan data Pengguna!');
                }
            }
        }
        header("Location: users.php");
        exit();
    }

    if ($action == 'edit') {
        $id = escape_string($conn, $_POST['id'] ?? '');
        $username = escape_string($conn, $_POST['username'] ?? '');
        $nama_lengkap = escape_string($conn, $_POST['nama_lengkap'] ?? '');
        $email = escape_string($conn, $_POST['email'] ?? '');
        $role = escape_string($conn, $_POST['role'] ?? '');
        $status = escape_string($conn, $_POST['status'] ?? '');
        $password = $_POST['password'] ?? ''; // Tidak di-escape langsung

        if (empty($username) || empty($nama_lengkap) || empty($email) || empty($role) || empty($status)) {
             set_alert('error', 'Field Username, Nama Lengkap, Email, Role, dan Status wajib diisi!');
        } else {
            $check_sql = "SELECT id FROM users WHERE (username = '$username' OR email = '$email') AND id != '$id'";
            $check_result = query($conn, $check_sql);

            if ($check_result === false) {
                 set_alert('error', 'Gagal memverifikasi data pengguna untuk update. Silakan coba lagi.');
            } elseif ($check_result->num_rows > 0) {
                set_alert('error', 'Gagal mengupdate! Username atau Email sudah digunakan oleh pengguna lain.');
            } else {
                $sql_update_parts = []; // Untuk membangun query update secara dinamis
                $sql_update_parts[] = "username = '$username'";
                $sql_update_parts[] = "nama_lengkap = '$nama_lengkap'";
                $sql_update_parts[] = "email = '$email'";
                $sql_update_parts[] = "role = '$role'";
                $sql_update_parts[] = "status = '$status'";
                if (!empty($password)) {
                    $hashed_password = md5($password);
                    $sql_update_parts[] = "password = '$hashed_password'";
                }
                $sql_update_parts[] = "updated_at = CURRENT_TIMESTAMP";

                $sql = "UPDATE users SET " . implode(', ', $sql_update_parts) . " WHERE id = '$id'";

                $query_edit_result = query($conn, $sql);
                if ($query_edit_result) {
                    if ($conn->affected_rows > 0) {
                        set_alert('success', 'Data Pengguna berhasil diupdate!');
                    } else {
                        set_alert('info', 'Tidak ada perubahan data pada Pengguna.');
                    }
                } else {
                    set_alert('error', 'Gagal mengupdate data Pengguna!');
                }
            }
        }
        header("Location: users.php");
        exit();
    }
}

// Proses Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id_to_delete = escape_string($conn, $_GET['id'] ?? ''); // Ganti nama variabel

    if (empty($id_to_delete)) {
        set_alert('error', 'ID Pengguna tidak valid untuk dihapus.');
    } elseif ($id_to_delete == $_SESSION['user_id']) {
        set_alert('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
    } else {
        $can_delete = true; // Flag untuk melanjutkan penghapusan
        // Cek jika ini adalah satu-satunya admin
        $user_to_delete_result = query($conn, "SELECT role FROM users WHERE id = '$id_to_delete'");

        if ($user_to_delete_result && $user_to_delete_result->num_rows > 0) {
            $user_to_delete_data = fetch_assoc($user_to_delete_result);
            if ($user_to_delete_data && $user_to_delete_data['role'] == 'admin') {
                $admin_count_result = query($conn, "SELECT COUNT(*) as total_admins FROM users WHERE role = 'admin' AND status = 'aktif'");
                if ($admin_count_result) {
                    $admin_count_data = fetch_assoc($admin_count_result);
                    if ($admin_count_data && $admin_count_data['total_admins'] <= 1) {
                        set_alert('error', 'Tidak dapat menghapus satu-satunya admin aktif.');
                        $can_delete = false;
                    }
                } else { // Gagal query hitung admin
                    set_alert('error', 'Gagal memverifikasi jumlah admin. Penghapusan dibatalkan.');
                    $can_delete = false;
                }
            }
        } elseif ($user_to_delete_result === false) { // Gagal query ambil data user yang akan dihapus
            set_alert('error', 'Gagal memeriksa data pengguna yang akan dihapus. Penghapusan dibatalkan.');
            $can_delete = false;
        }
        // Jika user tidak ditemukan (num_rows == 0), $can_delete tetap true, dan query delete akan gagal (affected_rows = 0)

        if ($can_delete) {
            $sql_delete_user = "DELETE FROM users WHERE id = '$id_to_delete'"; // Ganti nama variabel
            if (query($conn, $sql_delete_user)) {
                if ($conn->affected_rows > 0) {
                    set_alert('success', 'Data Pengguna berhasil dihapus!');
                } else {
                    set_alert('info', 'Data Pengguna tidak ditemukan atau sudah dihapus.');
                }
            } else {
                set_alert('error', 'Gagal menghapus data Pengguna!');
            }
        }
    }
    header("Location: users.php");
    exit();
}

$users_data_result = query($conn, "SELECT * FROM users ORDER BY nama_lengkap"); // Ganti nama variabel
include_once 'includes/header.php';
?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user-cog mr-1"></i>
                            Data Pengguna
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalTambahUser">
                                <i class="fas fa-plus"></i> Tambah Pengguna
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tableUsers" class="table table-bordered table-striped table-data">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Username</th>
                                        <th>Nama Lengkap</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($users_data_result && $users_data_result->num_rows > 0): // Cek hasil
                                        $no = 1;
                                        while ($row = fetch_assoc($users_data_result)):
                                        ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                                            <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $row['role'] == 'admin' ? 'success' : 'info'; ?>">
                                                    <?php echo ucfirst(htmlspecialchars($row['role'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo $row['status'] == 'aktif' ? 'primary' : 'secondary'; ?>">
                                                    <?php echo ucfirst(htmlspecialchars($row['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-info btn-sm" onclick="editUser(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars(addslashes($row['username'])); ?>', 'users.php')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php
                                        endwhile;
                                    else: ?>
                                     <tr><td colspan="7" class="text-center">
                                        <?php echo $users_data_result ? 'Tidak ada data pengguna.' : 'Gagal memuat data pengguna.';?>
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

<div class="modal fade" id="modalTambahUser" tabindex="-1" role="dialog" aria-labelledby="modalTambahUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalTambahUserLabel">
                    <i class="fas fa-plus"></i> Tambah Data Pengguna
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>
            <form method="post" action="users.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="add_username">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add_username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="add_password">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="add_password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="add_nama_lengkap">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add_nama_lengkap" name="nama_lengkap" required>
                    </div>
                    <div class="form-group">
                        <label for="add_email">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="add_email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="add_role">Role <span class="text-danger">*</span></label>
                        <select class="form-control" id="add_role" name="role" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="add_status">Status <span class="text-danger">*</span></label>
                        <select class="form-control" id="add_status" name="status" required>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
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

<div class="modal fade" id="modalEditUser" tabindex="-1" role="dialog" aria-labelledby="modalEditUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalEditUserLabel">
                    <i class="fas fa-edit"></i> Edit Data Pengguna
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>
            <form method="post" id="formEditUser" action="users.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_user_id">
                    <div class="form-group">
                        <label for="edit_username">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="username" id="edit_username" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_nama_lengkap">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_lengkap" id="edit_nama_lengkap" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_email">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" id="edit_email" required>
                    </div>
                     <div class="form-group">
                        <label for="edit_password">Password <small>(Kosongkan jika tidak ingin mengubah)</small></label>
                        <input type="password" class="form-control" id="edit_password" name="password">
                    </div>
                    <div class="form-group">
                        <label for="edit_role">Role <span class="text-danger">*</span></label>
                        <select class="form-control" name="role" id="edit_role" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_status">Status <span class="text-danger">*</span></label>
                        <select class="form-control" name="status" id="edit_status" required>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
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
function editUser(data) {
    $('#edit_user_id').val(data.id);
    $('#edit_username').val(data.username);
    $('#edit_nama_lengkap').val(data.nama_lengkap);
    $('#edit_email').val(data.email);
    $('#edit_role').val(data.role);
    $('#edit_status').val(data.status);
    $('#edit_password').val('');
    $('#modalEditUser').modal('show');
}
</script>

<?php include 'includes/footer.php'; ?>
