<?php 
// INCLUIR HEADER PRIMERO - PROCESAMIENTO LUEGO
include '../../includes/header.php'; 
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$vehiculo = null;
$error = null;
$success = null;
$registro_procesado = null;

// Procesar salida - ESTO DEBE IR ANTES DE CUALQUIER HTML
if (isset($_POST['registro_id'])) {
    try {
        $db->beginTransaction();
        
        // Obtener datos del registro
        $query = "SELECT r.*, v.placa, v.marca, v.modelo, v.color, v.tipo, t.precio_por_minuto 
                 FROM registros r 
                 JOIN vehiculos v ON r.vehiculo_id = v.id 
                 JOIN tarifas t ON v.tipo = t.tipo_vehiculo 
                 WHERE r.id = ? AND r.estado = 'activo'";
        $stmt = $db->prepare($query);
        $stmt->execute([$_POST['registro_id']]);
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$registro) {
            throw new Exception("Registro no encontrado o ya finalizado");
        }
        
        // Calcular tiempo y monto
        $fecha_entrada = new DateTime($registro['fecha_entrada']);
        $fecha_salida = new DateTime();
        $interval = $fecha_entrada->diff($fecha_salida);
        
        $minutos_total = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
        $monto = $minutos_total * $registro['precio_por_minuto'];
        
        // Mínimo 1 minuto y monto mínimo
        if ($minutos_total < 1) $minutos_total = 1;
        if ($monto < $registro['precio_por_minuto']) $monto = $registro['precio_por_minuto'];
        
        // Formatear tiempo
        $tiempo_estadia = sprintf('%02d:%02d:%02d', 
            $interval->days * 24 + $interval->h, $interval->i, $interval->s);
        
        // Actualizar registro
        $query = "UPDATE registros SET 
                 fecha_salida = NOW(), 
                 tiempo_estadia = ?,
                 monto = ?,
                 estado = 'finalizado' 
                 WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$tiempo_estadia, $monto, $_POST['registro_id']]);
        
        $db->commit();
        
        // Guardar datos para mostrar y redirigir
        $registro_procesado = [
            'id' => $_POST['registro_id'],
            'placa' => $registro['placa'],
            'monto' => $monto,
            'tiempo' => $tiempo_estadia
        ];
        
        $success = "✅ Salida registrada correctamente. Monto: " . formatMoney($monto);
        
    } catch (Exception $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
}

// Buscar vehículo activo - TAMBIÉN ANTES DE HTML
if (isset($_POST['placa_buscar']) && !isset($_POST['registro_id'])) {
    $query = "SELECT r.id, v.placa, v.marca, v.modelo, v.color, v.tipo, r.fecha_entrada 
             FROM registros r 
             JOIN vehiculos v ON r.vehiculo_id = v.id 
             WHERE v.placa = ? AND r.estado = 'activo'";
    $stmt = $db->prepare($query);
    $stmt->execute([$_POST['placa_buscar']]);
    $vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$vehiculo) {
        $error = "No se encontró un vehículo activo con la placa: " . $_POST['placa_buscar'];
    }
}
?>

<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Registrar Salida</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo url(); ?>">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo url('modules/vehiculos/registros.php'); ?>">Vehículos</a></li>
                    <li class="breadcrumb-item active">Registrar Salida</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Alertas -->
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
                
                <?php if ($registro_procesado): ?>
                <div class="mt-2">
                    <p><strong>Vehículo:</strong> <?php echo $registro_procesado['placa']; ?></p>
                    <p><strong>Tiempo de estadía:</strong> <?php echo $registro_procesado['tiempo']; ?></p>
                    <p><strong>Total a pagar:</strong> <?php echo formatMoney($registro_procesado['monto']); ?></p>
                    
                    <div class="mt-3">
                        <a href="../reportes/tickets.php?registro_id=<?php echo $registro_procesado['id']; ?>" 
                           class="btn btn-info btn-lg" target="_blank">
                            <i class="fas fa-receipt"></i> Generar Ticket
                        </a>
                        <a href="<?php echo url('modules/vehiculos/salida.php'); ?>" class="btn btn-success">
                            <i class="fas fa-plus"></i> Nueva Salida
                        </a>
                        <a href="<?php echo url('modules/vehiculos/registros.php'); ?>" class="btn btn-primary">
                            <i class="fas fa-list"></i> Ver Historial
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!$registro_procesado): ?>
        <div class="row">
            <div class="col-md-6">
                <!-- Buscar Vehículo -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Buscar Vehículo para Salida</h3>
                    </div>
                    <form method="post">
                        <div class="card-body">
                            <div class="form-group">
                                <label>Placa del Vehículo *</label>
                                <input type="text" name="placa_buscar" class="form-control" required 
                                       placeholder="Ingrese la placa del vehículo a buscar" 
                                       value="<?php echo $_POST['placa_buscar'] ?? ''; ?>"
                                       style="text-transform: uppercase;">
                                <small class="form-text text-muted">Ingrese la placa del vehículo que desea registrar su salida</small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Buscar Vehículo
                            </button>
                            <a href="<?php echo url('modules/vehiculos/registros.php?estado=activo'); ?>" class="btn btn-info">
                                <i class="fas fa-list"></i> Ver Vehículos Activos
                            </a>
                        </div>
                    </form>
                </div>
                
                <!-- Vehículo Encontrado -->
                <?php if ($vehiculo): ?>
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">Vehículo Encontrado ✅</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h5><i class="fas fa-car"></i> Información del Vehículo</h5>
                            <p><strong>Placa:</strong> <span class="badge badge-dark"><?php echo $vehiculo['placa']; ?></span></p>
                            <p><strong>Vehículo:</strong> 
                                <?php echo $vehiculo['marca'] ? $vehiculo['marca'] : 'N/A'; ?>
                                <?php echo $vehiculo['modelo'] ? ' ' . $vehiculo['modelo'] : ''; ?>
                            </p>
                            <p><strong>Color:</strong> <?php echo $vehiculo['color'] ? $vehiculo['color'] : 'N/A'; ?></p>
                            <p><strong>Tipo:</strong> <span class="badge badge-info"><?php echo ucfirst($vehiculo['tipo']); ?></span></p>
                            <p><strong>Entrada:</strong> <?php echo formatDate($vehiculo['fecha_entrada'], true); ?></p>
                        </div>
                        
                        <form method="post" id="formSalida">
                            <input type="hidden" name="registro_id" value="<?php echo $vehiculo['id']; ?>">
                            <button type="submit" class="btn btn-success btn-lg btn-block">
                                <i class="fas fa-sign-out-alt"></i> Registrar Salida
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-6">
                <!-- Información de Tarifas -->
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">Información de Tarifas</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $query = "SELECT * FROM tarifas";
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        $tarifas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        
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
                        <?php endif; ?>
                        
                        <div class="alert alert-warning">
                            <h6><i class="icon fas fa-info-circle"></i> Información Importante:</h6>
                            <ul class="mb-0">
                                <li>El sistema calcula automáticamente el monto basado en el tiempo real de estadía</li>
                                <li>Se cobra por minuto transcurrido</li>
                                <li>Mínimo de cobro: 1 minuto</li>
                                <li>El ticket se genera automáticamente después del registro</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Convertir placa a mayúsculas
    const placaInput = document.querySelector('input[name="placa_buscar"]');
    if (placaInput) {
        placaInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });
    }
    
    // Confirmación para registrar salida
    const formSalida = document.getElementById('formSalida');
    if (formSalida) {
        formSalida.addEventListener('submit', function(e) {
            if (!confirm('¿Está seguro de registrar la salida de este vehículo?\n\nSe calculará el monto a pagar y se generará el ticket.')) {
                e.preventDefault();
            }
        });
    }
});
</script>

<?php include '../../includes/footer.php'; ?>