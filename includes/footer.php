    </div>
    <!-- /.content-wrapper -->

    <!-- Main Footer -->
    <footer class="main-footer">
        <strong>Copyright &copy; <?php echo date('Y'); ?> <a href="<?php echo url(); ?>"><?php echo SITE_NAME; ?></a>.</strong>
        Todos los derechos reservados.
        <div class="float-right d-none d-sm-inline-block">
            <b>Versión</b> 1.0.0
        </div>
    </footer>
</div>
<!-- ./wrapper -->

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<!-- Scripts globales -->
<script>
$(document).ready(function() {
    // Auto-dismiss alerts después de 5 segundos
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Confirmación para acciones importantes
    $('a.btn-danger, button.btn-danger').on('click', function(e) {
        if (!confirm('¿Está seguro de realizar esta acción? Esta acción no se puede deshacer.')) {
            e.preventDefault();
        }
    });

    // Tooltips
    $('[data-toggle="tooltip"]').tooltip();
});
</script>

</body>
</html>