<?php 
include '../../includes/header.php'; 
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Parámetros de filtro
$filtro_estado = $_GET['estado'] ?? '';
$filtro_fecha = $_GET['fecha'] ?? '';
$filtro_placa = $_GET['placa'] ?? '';
$filtro_tipo = $_GET['tipo'] ?? '';

// Construir consulta base
$query = "SELECT r.*, v.placa, v.marca, v.modelo, v.color, v.tipo, u.nombre as usuario 
          FROM registros r 
          JOIN vehiculos v ON r.vehiculo_id = v.id 
          JOIN usuarios u ON r.usuario_id = u.id 
          WHERE 1=1";

$params = [];

// Aplicar filtros
if ($filtro_estado) {
    $query .= " AND r.estado = ?";
    $params[] = $filtro_estado;
}

if ($filtro_fecha == 'hoy') {
    $query .= " AND DATE(r.fecha_entrada) = CURDATE()";
} elseif ($filtro_fecha == 'ayer') {
    $query .= " AND DATE(r.fecha_entrada) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
} elseif ($filtro_fecha) {
    $query .= " AND DATE(r.fecha_entrada) = ?";
    $params[] = $filtro_fecha;
}

if ($filtro_placa) {
    $query .= " AND v.placa LIKE ?";
    $params[] = "%$filtro_placa%";
}

if ($filtro_tipo) {
    $query .= " AND v.tipo = ?";
    $params[] = $filtro_tipo;
}

$query .= " ORDER BY r.fecha_entrada DESC";

// Ejecutar consulta
$stmt = $db->prepare($query);
$stmt->execute($params);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estadísticas
$query_estadisticas = "SELECT 
    COUNT(*) as total,
    COUNT(CASE WHEN estado = 'activo' THEN 1 END) as activos,
    COUNT(CASE WHEN estado = 'finalizado' THEN 1 END) as finalizados,
    COALESCE(SUM(monto), 0) as ingresos_totales
    FROM registros WHERE 1=1";

$params_estadisticas = [];

if ($filtro_fecha == 'hoy') {
    $query_estadisticas .= " AND DATE(fecha_entrada) = CURDATE()";
} elseif ($filtro_fecha == 'ayer') {
    $query_estadisticas .= " AND DATE(fecha_entrada) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
} elseif ($filtro_fecha) {
    $query_estadisticas .= " AND DATE(fecha_entrada) = ?";
    $params_estadisticas[] = $filtro_fecha;
}

$stmt_estadisticas = $db->prepare($query_estadisticas);
$stmt_estadisticas->execute($params_estadisticas);
$estadisticas = $stmt_estadisticas->fetch(PDO::FETCH_ASSOC);
?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Historial de Registros</h3>
                        <div class="card-tools">
                            <a href="entrada.php" class="btn btn-success btn-sm">
                                <i class="fas fa-plus"></i> Nueva Entrada
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <form method="get" class="mb-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Estado</label>
                                        <select name="estado" class="form-control">
                                            <option value="">Todos los estados</option>
                                            <option value="activo" <?php echo $filtro_estado == 'activo' ? 'selected' : ''; ?>>Activos</option>
                                            <option value="finalizado" <?php echo $filtro_estado == 'finalizado' ? 'selected' : ''; ?>>Finalizados</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Fecha</label>
                                        <select name="fecha" class="form-control">
                                            <option value="">Todas las fechas</option>
                                            <option value="hoy" <?php echo $filtro_fecha == 'hoy' ? 'selected' : ''; ?>>Hoy</option>
                                            <option value="ayer" <?php echo $filtro_fecha == 'ayer' ? 'selected' : ''; ?>>Ayer</option>
                                            <option value="<?php echo date('Y-m-d'); ?>" <?php echo $filtro_fecha == date('Y-m-d') ? 'selected' : ''; ?>>Fecha Específica</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tipo de Vehículo</label>
                                        <select name="tipo" class="form-control">
                                            <option value="">Todos los tipos</option>
                                            <option value="moto" <?php echo $filtro_tipo == 'moto' ? 'selected' : ''; ?>>Moto</option>
                                            <option value="auto" <?php echo $filtro_tipo == 'auto' ? 'selected' : ''; ?>>Auto</option>
                                            <option value="camioneta" <?php echo $filtro_tipo == 'camioneta' ? 'selected' : ''; ?>>Camioneta</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Placa</label>
                                        <input type="text" name="placa" class="form-control" 
                                               placeholder="Buscar por placa" value="<?php echo htmlspecialchars($filtro_placa); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter"></i> Filtrar
                                    </button>
                                    <a href="registros.php" class="btn btn-default">Limpiar</a>
                                </div>
                            </div>
                        </form>

                        <!-- Estadísticas -->
                        <div class="row mb-4">
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h3><?php echo $estadisticas['total']; ?></h3>
                                        <p>Total Registros</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-list"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h3><?php echo $estadisticas['activos']; ?></h3>
                                        <p>Vehículos Activos</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-car"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h3><?php echo $estadisticas['finalizados']; ?></h3>
                                        <p>Registros Finalizados</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-danger">
                                    <div class="inner">
                                        <h3>$<?php echo number_format($estadisticas['ingresos_totales'], 2); ?></h3>
                                        <p>Ingresos Totales</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de registros -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Placa</th>
                                        <th>Vehículo</th>
                                        <th>Entrada</th>
                                        <th>Salida</th>
                                        <th>Tiempo</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th>Usuario</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($registros): ?>
                                        <?php foreach ($registros as $registro): ?>
                                            <tr>
                                                <td><?php echo $registro['id']; ?></td>
                                                <td>
                                                    <span class="badge badge-dark"><?php echo $registro['placa']; ?></span>
                                                </td>
                                                <td>
                                                    <small>
                                                        <strong><?php echo $registro['marca'] ? $registro['marca'] : 'N/A'; ?></strong>
                                                        <?php echo $registro['modelo'] ? ' ' . $registro['modelo'] : ''; ?>
                                                        <br>
                                                        <span class="badge badge-info"><?php echo ucfirst($registro['tipo']); ?></span>
                                                        <?php if ($registro['color']): ?>
                                                            <span class="badge badge-secondary"><?php echo $registro['color']; ?></span>
                                                        <?php endif; ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <?php echo date('d/m/Y H:i', strtotime($registro['fecha_entrada'])); ?>
                                                </td>
                                                <td>
                                                    <?php if ($registro['fecha_salida']): ?>
                                                        <?php echo date('d/m/Y H:i', strtotime($registro['fecha_salida'])); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($registro['tiempo_estadia']): ?>
                                                        <span class="badge badge-primary"><?php echo $registro['tiempo_estadia']; ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($registro['monto']): ?>
                                                        <strong class="text-success">$<?php echo number_format($registro['monto'], 2); ?></strong>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php echo $registro['estado'] == 'activo' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($registro['estado']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $registro['usuario']; ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <?php if ($registro['estado'] == 'activo'): ?>
                                                            <a href="../reportes/tickets.php?registro_id=<?php echo $registro['id']; ?>" 
                                                               class="btn btn-info btn-sm" target="_blank" title="Ticket">
                                                                <i class="fas fa-receipt"></i>
                                                            </a>
                                                            <a href="salida.php?placa=<?php echo urlencode($registro['placa']); ?>" 
                                                               class="btn btn-success btn-sm" title="Registrar Salida">
                                                                <i class="fas fa-sign-out-alt"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="../reportes/tickets.php?registro_id=<?php echo $registro['id']; ?>" 
                                                               class="btn btn-info btn-sm" target="_blank" title="Ver Ticket">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="10" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-3x mb-3"></i><br>
                                                No se encontraron registros
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Información del filtro aplicado -->
                        <?php if ($filtro_estado || $filtro_fecha || $filtro_placa || $filtro_tipo): ?>
                            <div class="alert alert-info mt-3">
                                <strong>Filtros aplicados:</strong>
                                <?php
                                $filtros = [];
                                if ($filtro_estado) $filtros[] = "Estado: " . ucfirst($filtro_estado);
                                if ($filtro_fecha) {
                                    if ($filtro_fecha == 'hoy') $filtros[] = "Fecha: Hoy";
                                    elseif ($filtro_fecha == 'ayer') $filtros[] = "Fecha: Ayer";
                                    else $filtros[] = "Fecha: " . date('d/m/Y', strtotime($filtro_fecha));
                                }
                                if ($filtro_placa) $filtros[] = "Placa: " . htmlspecialchars($filtro_placa);
                                if ($filtro_tipo) $filtros[] = "Tipo: " . ucfirst($filtro_tipo);
                                
                                echo implode(' • ', $filtros);
                                ?>
                                <a href="registros.php" class="float-right text-danger">
                                    <i class="fas fa-times"></i> Quitar filtros
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit al cambiar algunos filtros
    const filtrosAuto = document.querySelectorAll('select[name="estado"], select[name="fecha"], select[name="tipo"]');
    filtrosAuto.forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });

    // Confirmación para registrar salida
    const botonesSalida = document.querySelectorAll('a[title="Registrar Salida"]');
    botonesSalida.forEach(boton => {
        boton.addEventListener('click', function(e) {
            if (!confirm('¿Está seguro de registrar la salida de este vehículo?')) {
                e.preventDefault();
            }
        });
    });
});
</script>

<?php include '../../includes/footer.php'; ?>