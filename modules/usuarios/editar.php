<?php 
// PROCESAMIENTO PRIMERO - ANTES DE CUALQUIER HTML
include '../../includes/header.php'; 
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Obtener datos del usuario - PRIMERO
$query = "SELECT * FROM usuarios WHERE id = ? AND activo = 1";
$stmt = $db->prepare($query);
$stmt->execute([$_GET['id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    setMessage('error', "Usuario no encontrado");
    redirect('modules/usuarios/listar.php');
}

$error = null;
$success = null;

// Procesar formulario - ANTES DE HTML
if ($_POST) {
    try {
        // Construir query de actualización
        $params = [$_POST['nombre'], $_POST['email'], $_POST['rol'], $_GET['id']];
        $query = "UPDATE usuarios SET nombre = ?, email = ?, rol = ? WHERE id = ?";
        
        // Si se proporcionó una nueva contraseña
        if (!empty($_POST['password'])) {
            if ($_POST['password'] !== $_POST['confirm_password']) {
                $error = "Las contraseñas no coinciden";
            } else {
                $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $query = "UPDATE usuarios SET nombre = ?, email = ?, rol = ?, password = ? WHERE id = ?";
                $params = [$_POST['nombre'], $_POST['email'], $_POST['rol'], $password_hash, $_GET['id']];
            }
        }
        
        if (!isset($error)) {
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            
            // ÉXITO - pero NO redirigir inmediatamente
            $success = "✅ Usuario actualizado correctamente. Redirigiendo al listado...";
            
            // JavaScript para redirección después de mostrar mensaje
            echo "<script>
                setTimeout(function() {
                    window.location.href = '" . url('modules/usuarios/listar.php') . "';
                }, 2000);
            </script>";
        }
    } catch (Exception $e) {
        $error = "Error al actualizar el usuario: " . $e->getMessage();
    }
}
?>

<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Editar Usuario</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo url(); ?>">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo url('modules/usuarios/listar.php'); ?>">Usuarios</a></li>
                    <li class="breadcrumb-item active">Editar Usuario</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Editar Datos del Usuario</h3>
                    </div>
                    <form method="post" id="formEditar">
                        <div class="card-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                    <i class="icon fas fa-ban"></i> <?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($success): ?>
                                <div class="alert alert-success alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                    <i class="icon fas fa-check"></i> <?php echo $success; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nombre Completo *</label>
                                        <input type="text" name="nombre" class="form-control" required 
                                               value="<?php echo $usuario['nombre']; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email *</label>
                                        <input type="email" name="email" class="form-control" required 
                                               value="<?php echo $usuario['email']; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nueva Contraseña</label>
                                        <input type="password" name="password" class="form-control" 
                                               minlength="6" placeholder="Mínimo 6 caracteres">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Confirmar Contraseña</label>
                                        <input type="password" name="confirm_password" class="form-control" 
                                               minlength="6" placeholder="Repita la nueva contraseña">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Rol *</label>
                                        <select name="rol" class="form-control" required>
                                            <option value="operador" <?php echo $usuario['rol'] == 'operador' ? 'selected' : ''; ?>>Operador</option>
                                            <option value="admin" <?php echo $usuario['rol'] == 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Fecha de Creación</label>
                                        <input type="text" class="form-control" readonly 
                                               value="<?php echo date('d/m/Y H:i', strtotime($usuario['fecha_creacion'])); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary" id="btnSubmit">
                                <i class="fas fa-save"></i> Actualizar Usuario
                            </button>
                            <a href="<?php echo url('modules/usuarios/listar.php'); ?>" class="btn btn-default">Cancelar</a>
                            <button type="reset" class="btn btn-secondary">Restablecer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formEditar');
    const btnSubmit = document.getElementById('btnSubmit');
    const password = document.querySelector('input[name="password"]');
    const confirmPassword = document.querySelector('input[name="confirm_password"]');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            // Validaciones
            let isValid = true;
            
            // Validar contraseñas si se ingresaron
            if (password.value || confirmPassword.value) {
                if (password.value !== confirmPassword.value) {
                    alert('❌ Las contraseñas no coinciden');
                    password.focus();
                    isValid = false;
                } else if (password.value.length < 6) {
                    alert('❌ La contraseña debe tener al menos 6 caracteres');
                    password.focus();
                    isValid = false;
                }
            }
            
            // Si hay errores, prevenir envío
            if (!isValid) {
                e.preventDefault();
                return false;
            }
            
            // Si todo está bien, mostrar confirmación
            if (!confirm('¿Está seguro de actualizar este usuario?\n\nSerá redirigido al listado de usuarios después de guardar.')) {
                e.preventDefault();
                return false;
            }
            
            // Mostrar loading
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
            btnSubmit.disabled = true;
        });
    }
    
    // Mostrar/ocultar contraseñas
    const togglePassword = (input) => {
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
    };
    
    // Agregar botones para mostrar contraseña
    if (password) {
        password.insertAdjacentHTML('afterend', '<button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="togglePassword(this.previousElementSibling)"><i class="fas fa-eye"></i></button>');
    }
    if (confirmPassword) {
        confirmPassword.insertAdjacentHTML('afterend', '<button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="togglePassword(this.previousElementSibling)"><i class="fas fa-eye"></i></button>');
    }
});
</script>

<?php include '../../includes/footer.php'; ?>