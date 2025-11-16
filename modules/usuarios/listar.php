<?php 
include '../../includes/header.php'; 
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// SOLO CONSULTA - sin procesamiento
$query = "SELECT * FROM usuarios WHERE activo = 1 ORDER BY id DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener mensajes flash
$success = getMessage('success');
$error = getMessage('error');
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Gestión de Usuarios</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="crear.php" class="btn btn-success">
                        <i class="fas fa-plus"></i> Nuevo Usuario
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Mostrar mensajes -->
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="icon fas fa-check"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="icon fas fa-ban"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="row justify-content-center">
                <div class="col-xl-10 col-lg-11 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Lista de Usuarios del Sistema</h3>
                            <div class="card-tools">
                                <span class="badge badge-primary">Total: <?php echo count($usuarios); ?> usuarios</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if ($usuarios): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 5%;">ID</th>
                                                <th style="width: 25%;">Nombre</th>
                                                <th style="width: 25%;">Email</th>
                                                <th style="width: 15%;">Rol</th>
                                                <th style="width: 20%;">Fecha Creación</th>
                                                <th style="width: 10%;" class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($usuarios as $usuario): ?>
                                            <tr>
                                                <td class="text-center">
                                                    <span class="badge badge-secondary">#<?php echo $usuario['id']; ?></span>
                                                </td>
                                                <td>
                                                    <i class="fas fa-user mr-2 text-primary"></i>
                                                    <?php echo $usuario['nombre']; ?>
                                                    <?php if ($usuario['id'] == $_SESSION['user_id']): ?>
                                                        <span class="badge badge-info ml-1">Tú</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <i class="fas fa-envelope mr-2 text-info"></i>
                                                    <?php echo $usuario['email']; ?>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-<?php echo $usuario['rol'] == 'admin' ? 'danger' : 'primary'; ?>">
                                                        <?php echo ucfirst($usuario['rol']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <small class="text-muted">
                                                        <?php echo date('d/m/Y H:i', strtotime($usuario['fecha_creacion'])); ?>
                                                    </small>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a href="editar.php?id=<?php echo $usuario['id']; ?>" 
                                                           class="btn btn-sm btn-warning" 
                                                           title="Editar usuario">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php if ($usuario['id'] != $_SESSION['user_id']): ?>
                                                        <a href="eliminar.php?id=<?php echo $usuario['id']; ?>" 
                                                           class="btn btn-sm btn-danger btn-eliminar" 
                                                           title="Eliminar usuario permanentemente"
                                                           data-nombre="<?php echo $usuario['nombre']; ?>"
                                                           data-email="<?php echo $usuario['email']; ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                        <?php else: ?>
                                                        <button class="btn btn-sm btn-secondary" disabled title="No puedes eliminarte a ti mismo">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <h4 class="text-muted">No hay usuarios registrados</h4>
                                    <p class="text-muted">Comienza agregando el primer usuario al sistema.</p>
                                    <a href="crear.php" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Crear Primer Usuario
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i> 
                                        Mostrando <?php echo count($usuarios); ?> usuario(s)
                                    </small>
                                </div>
                                <div class="col-md-6 text-right">
                                    <a href="crear.php" class="btn btn-success btn-sm">
                                        <i class="fas fa-plus"></i> Agregar Usuario
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.card {
    margin: 0 auto;
    border: none;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
}

.table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
}

.btn-group .btn {
    margin: 0 2px;
}

.btn-eliminar {
    background-color: #dc3545;
    border-color: #dc3545;
}

.btn-eliminar:hover {
    background-color: #c82333;
    border-color: #bd2130;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Confirmación MÁS FUERTE para eliminar
    const deleteButtons = document.querySelectorAll('.btn-eliminar');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const nombreUsuario = this.getAttribute('data-nombre');
            const emailUsuario = this.getAttribute('data-email');
            
            const mensaje = `🚨 ELIMINACIÓN PERMANENTE\n\n¿Está ABSOLUTAMENTE seguro de eliminar al usuario:\n\n📛 Nombre: ${nombreUsuario}\n📧 Email: ${emailUsuario}\n\n⚠️  Esta acción NO se puede deshacer\n⚠️  El usuario será borrado completamente de la base de datos\n\nEscriba "ELIMINAR" para confirmar:`;
            
            const confirmacion = prompt(mensaje);
            
            if (confirmacion !== "ELIMINAR") {
                alert('❌ Eliminación cancelada. El usuario está seguro.');
                e.preventDefault();
                return false;
            }
            
            // Última confirmación
            if (!confirm(`¿CONFIRMA LA ELIMINACIÓN PERMANENTE de ${nombreUsuario}?`)) {
                e.preventDefault();
                return false;
            }
        });
    });

    // Tooltips para botones
    $('[title]').tooltip();
});
</script>

<?php include '../../includes/footer.php'; ?>