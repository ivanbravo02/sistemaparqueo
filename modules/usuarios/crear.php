<?php 
// PROCESAMIENTO PRIMERO - ANTES DE CUALQUIER HTML
include '../../includes/header.php'; 
require_once '../../config/database.php';

$error = null;

if ($_POST) {
    $database = new Database();
    $db = $database->getConnection();
    
    // Validar que las contraseñas coincidan
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $error = "Las contraseñas no coinciden";
    } else {
        try {
            // Verificar si el email ya existe
            $query = "SELECT id FROM usuarios WHERE email = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$_POST['email']]);
            
            if ($stmt->rowCount() > 0) {
                $error = "El email ya está registrado";
            } else {
                // Hash de la contraseña
                $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                
                $query = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
                $stmt = $db->prepare($query);
                $stmt->execute([
                    trim($_POST['nombre']),
                    trim($_POST['email']),
                    $password_hash,
                    $_POST['rol']
                ]);
                
                // REDIRECCIONAR ANTES DE CUALQUIER OUTPUT
                setMessage('success', "Usuario creado correctamente");
                redirect('modules/usuarios/listar.php');
            }
        } catch (Exception $e) {
            $error = "Error al crear el usuario: " . $e->getMessage();
        }
    }
}
?>

<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Crear Nuevo Usuario</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo url(); ?>">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo url('modules/usuarios/listar.php'); ?>">Usuarios</a></li>
                    <li class="breadcrumb-item active">Crear Usuario</li>
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
                        <h3 class="card-title">Datos del Nuevo Usuario</h3>
                    </div>
                    <form method="post">
                        <div class="card-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                    <i class="icon fas fa-ban"></i> <?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nombre Completo *</label>
                                        <input type="text" name="nombre" class="form-control" required 
                                               value="<?php echo $_POST['nombre'] ?? ''; ?>" 
                                               placeholder="Ingrese el nombre completo">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email *</label>
                                        <input type="email" name="email" class="form-control" required 
                                               value="<?php echo $_POST['email'] ?? ''; ?>" 
                                               placeholder="Ingrese el email">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Contraseña *</label>
                                        <input type="password" name="password" class="form-control" required 
                                               minlength="6" placeholder="Mínimo 6 caracteres">
                                        <small class="form-text text-muted">La contraseña debe tener al menos 6 caracteres</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Confirmar Contraseña *</label>
                                        <input type="password" name="confirm_password" class="form-control" required 
                                               minlength="6" placeholder="Repita la contraseña">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Rol *</label>
                                        <select name="rol" class="form-control" required>
                                            <option value="">Seleccionar rol</option>
                                            <option value="operador" <?php echo ($_POST['rol'] ?? '') == 'operador' ? 'selected' : ''; ?>>Operador</option>
                                            <option value="admin" <?php echo ($_POST['rol'] ?? '') == 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Fecha de Creación</label>
                                        <input type="text" class="form-control" readonly 
                                               value="<?php echo date('d/m/Y H:i:s'); ?>">
                                        <small class="form-text text-muted">Se registrará automáticamente</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Crear Usuario
                            </button>
                            <a href="<?php echo url('modules/usuarios/listar.php'); ?>" class="btn btn-default">Cancelar</a>
                            <button type="reset" class="btn btn-secondary">Limpiar Formulario</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">Información</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6><i class="icon fas fa-info-circle"></i> Campos Obligatorios</h6>
                            <ul class="mb-0">
                                <li><strong>Nombre completo</strong></li>
                                <li><strong>Email</strong> (debe ser único)</li>
                                <li><strong>Contraseña</strong> (mínimo 6 caracteres)</li>
                                <li><strong>Rol</strong> del usuario</li>
                            </ul>
                        </div>
                        
                        <div class="alert alert-warning">
                            <h6><i class="icon fas fa-exclamation-triangle"></i> Tipos de Usuario</h6>
                            <p><strong>Administrador:</strong> Acceso completo al sistema</p>
                            <p><strong>Operador:</strong> Solo puede registrar entradas/salidas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación de contraseñas
    const form = document.querySelector('form');
    const password = document.querySelector('input[name="password"]');
    const confirmPassword = document.querySelector('input[name="confirm_password"]');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            // Validar que las contraseñas coincidan
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('❌ Las contraseñas no coinciden');
                password.focus();
                return false;
            }
            
            // Validar longitud de contraseña
            if (password.value.length < 6) {
                e.preventDefault();
                alert('❌ La contraseña debe tener al menos 6 caracteres');
                password.focus();
                return false;
            }
            
            // Confirmación final
            if (!confirm('¿Está seguro de crear este usuario?')) {
                e.preventDefault();
                return false;
            }
        });
    }
    
    // Mostrar/ocultar contraseñas
    const togglePassword = (input) => {
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
    };
    
    // Agregar botones para mostrar contraseña (opcional)
    password.insertAdjacentHTML('afterend', '<button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="togglePassword(this.previousElementSibling)"><i class="fas fa-eye"></i></button>');
    confirmPassword.insertAdjacentHTML('afterend', '<button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="togglePassword(this.previousElementSibling)"><i class="fas fa-eye"></i></button>');
});
</script>

<?php include '../../includes/footer.php'; ?>