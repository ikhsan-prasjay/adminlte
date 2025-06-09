</div>
    <!-- /.content-wrapper -->

    <!-- Footer -->
    <footer class="main-footer">
        <strong>Copyright &copy; <?php echo date('Y'); ?> <a href="#">Sistem Sekolah</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0.0
        </div>
    </footer>
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap4.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.4.8/sweetalert2.min.js"></script>

<script>
$(document).ready(function() {
    // DataTable initialization
    $('.table-data').DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "pageLength": 10,
        "language": {
            "search": "Cari:",
            "lengthMenu": "Tampilkan _MENU_ data per halaman",
            "zeroRecords": "Data tidak ditemukan",
            "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
            "infoEmpty": "Tidak ada data tersedia",
            "infoFiltered": "(difilter dari _MAX_ total data)",
            "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
            }
        }
    });

    // Alert notification
    <?php
    $alert = get_alert();
    if ($alert):
    ?>
    Swal.fire({
        icon: '<?php echo $alert['type']; ?>',
        title: '<?php echo ucfirst($alert['type']); ?>',
        text: '<?php echo $alert['message']; ?>',
        timer: 3000,
        showConfirmButton: false
    });
    <?php endif; ?>
});

// Fungsi untuk konfirmasi hapus
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

</body>
</html>
