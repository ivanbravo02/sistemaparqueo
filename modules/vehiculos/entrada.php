<?php 
include '../../includes/header.php'; 
require_once '../../config/database.php';

$error = null;

// Procesar formulario
if ($_POST) {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        $db->beginTransaction();
        
        // Validar y sanitizar datos
        $placa = strtoupper(trim($_POST['placa']));
        $marca = trim($_POST['marca'] ?? '');
        $modelo = trim($_POST['modelo'] ?? '');
        $color = trim($_POST['color'] ?? '');
        $tipo = $_POST['tipo'];
        
        // Validaciones
        if (empty($placa)) {
            throw new Exception("La placa es obligatoria");
        }
        
        if (empty($tipo)) {
            throw new Exception("El tipo de vehículo es obligatorio");
        }
        
        // Verificar si el vehículo ya existe
        $query = "SELECT id FROM vehiculos WHERE placa = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$placa]);
        $vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$vehiculo) {
            // Crear nuevo vehículo
            $query = "INSERT INTO vehiculos (placa, marca, modelo, color, tipo) VALUES (?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([$placa, $marca, $modelo, $color, $tipo]);
            $vehiculo_id = $db->lastInsertId();
        } else {
            $vehiculo_id = $vehiculo['id'];
        }
        
        // Verificar si ya tiene un registro activo
        $query = "SELECT id FROM registros WHERE vehiculo_id = ? AND estado = 'activo'";
        $stmt = $db->prepare($query);
        $stmt->execute([$vehiculo_id]);
        
        if ($stmt->rowCount() > 0) {
            throw new Exception("El vehículo con placa <strong>{$placa}</strong> ya tiene un registro activo en el estacionamiento");
        }
        
        // Crear registro de entrada
        $query = "INSERT INTO registros (vehiculo_id, usuario_id) VALUES (?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$vehiculo_id, $_SESSION['user_id']]);
        $registro_id = $db->lastInsertId();
        
        $db->commit();
        
        // Redireccionar con mensaje de éxito
        setMessage('success', "✅ Entrada registrada correctamente para el vehículo con placa: <strong>{$placa}</strong> (Registro #{$registro_id})");
        redirect();
        
    } catch (Exception $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
}

// Obtener tarifas para mostrar
$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM tarifas";
$stmt = $db->prepare($query);
$stmt->execute();
$tarifas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Registrar Entrada</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo url(); ?>">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo url('modules/vehiculos/registros.php'); ?>">Vehículos</a></li>
                    <li class="breadcrumb-item active">Registrar Entrada</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Formulario -->
            <div class="col-md-8">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Datos del Vehículo</h3>
                    </div>
                    <form method="post" id="formEntrada">
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
                                        <label for="placa">Placa *</label>
                                        <input type="text" name="placa" id="placa" class="form-control" required 
                                               placeholder="Ej: ABC123" 
                                               value="<?php echo $_POST['placa'] ?? ''; ?>"
                                               pattern="[A-Z0-9]{3,10}"
                                               title="Solo letras mayúsculas y números (3-10 caracteres)"
                                               style="text-transform: uppercase;">
                                        <small class="form-text text-muted">Formato: Letras y números, 3-10 caracteres</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tipo">Tipo de Vehículo *</label>
                                        <select name="tipo" id="tipo" class="form-control" required>
                                            <option value="">Seleccionar tipo</option>
                                            <option value="moto" <?php echo ($_POST['tipo'] ?? '') == 'moto' ? 'selected' : ''; ?>>Moto</option>
                                            <option value="auto" <?php echo ($_POST['tipo'] ?? '') == 'auto' ? 'selected' : ''; ?>>Auto</option>
                                            <option value="camioneta" <?php echo ($_POST['tipo'] ?? '') == 'camioneta' ? 'selected' : ''; ?>>Camioneta</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="marca">Marca</label>
                                        <input type="text" name="marca" id="marca" class="form-control" 
                                               value="<?php echo $_POST['marca'] ?? ''; ?>" 
                                               placeholder="Ej: Toyota">
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="modelo">Modelo</label>
                                        <input type="text" name="modelo" id="modelo" class="form-control" 
                                               value="<?php echo $_POST['modelo'] ?? ''; ?>" 
                                               placeholder="Ej: Corolla">
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="color">Color</label>
                                        <input type="text" name="color" id="color" class="form-control" 
                                               value="<?php echo $_POST['color'] ?? ''; ?>" 
                                               placeholder="Ej: Rojo">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Información de registro -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h6><i class="icon fas fa-info-circle"></i> Información de Registro</h6>
                                        <strong>Fecha y Hora de Entrada:</strong> <?php echo date('d/m/Y H:i:s');?><br>
                                        <strong>Usuario Registrador:</strong> <?php echo $_SESSION['user_name']; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Registrar Entrada
                            </button>
                            <a href="<?php echo url(); ?>" class="btn btn-default">Cancelar</a>
                            <button type="reset" class="btn btn-secondary">Limpiar Formulario</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Información Lateral -->
            <div class="col-md-4">
                <!-- Tarifas -->
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-tags mr-2"></i>Tarifas Vigentes</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($tarifas): ?>
                            <?php foreach ($tarifas as $tarifa): ?>
                                <div class="callout callout-info mb-3">
                                    <h5>
                                        <i class="fas fa-<?php 
                                            echo $tarifa['tipo_vehiculo'] == 'moto' ? 'motorcycle' : 
                                                 ($tarifa['tipo_vehiculo'] == 'auto' ? 'car' : 'truck'); 
                                        ?> mr-2"></i>
                                        <?php echo ucfirst($tarifa['tipo_vehiculo']); ?>
                                    </h5>
                                    <p class="mb-1">
                                        <strong><?php echo formatMoney($tarifa['precio_por_hora']); ?></strong> por hora
                                    </p>
                                    <p class="mb-0 text-muted">
                                        <small><?php echo formatMoney($tarifa['precio_por_minuto']); ?> por minuto</small>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No hay tarifas configuradas</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Instrucciones -->
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Instrucciones</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <h6><i class="icon fas fa-exclamation-triangle"></i> Campos Obligatorios</h6>
                            <ul class="mb-0 pl-3">
                                <li><strong>Placa</strong> - Identificación del vehículo</li>
                                <li><strong>Tipo de vehículo</strong> - Para calcular tarifa</li>
                            </ul>
                        </div>
                        <small class="text-muted">
                            Los campos de marca, modelo y color son opcionales pero recomendados para mejor identificación.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Convertir placa a mayúsculas automáticamente
    const placaInput = document.getElementById('placa');
    if (placaInput) {
        placaInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });
    }
    
    // Validación del formulario
    const form = document.getElementById('formEntrada');
    if (form) {
        form.addEventListener('submit', function(e) {
            const placa = document.getElementById('placa').value.trim();
            const tipo = document.getElementById('tipo').value;
            
            if (!placa) {
                e.preventDefault();
                alert('❌ Por favor ingrese la placa del vehículo');
                document.getElementById('placa').focus();
                return false;
            }
            
            if (!tipo) {
                e.preventDefault();
                alert('❌ Por favor seleccione el tipo de vehículo');
                document.getElementById('tipo').focus();
                return false;
            }
            
            // Validar formato de placa
            const placaRegex = /^[A-Z0-9]{3,10}$/;
            if (!placaRegex.test(placa)) {
                e.preventDefault();
                alert('❌ Formato de placa inválido. Use solo letras mayúsculas y números (3-10 caracteres)');
                document.getElementById('placa').focus();
                return false;
            }
            
            // Confirmación final
            if (!confirm(`¿Está seguro de registrar la entrada del vehículo con placa: ${placa}?`)) {
                e.preventDefault();
                return false;
            }
            
            // Mostrar loading
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando...';
            submitBtn.disabled = true;
        });
    }
});
</script>

<?php include '../../includes/footer.php'; ?>