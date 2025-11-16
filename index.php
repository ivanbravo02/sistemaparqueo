<?php 
include 'includes/header.php'; 
require_once 'config/database.php';

// Manejar mensajes de sesión
$success = getMessage('success');
$error = getMessage('error');

// Obtener estadísticas
$database = new Database();
$db = $database->getConnection();

// Obtener estadísticas manualmente
$stats = [];
try {
    // Vehículos activos
    $query = "SELECT COUNT(*) as total FROM registros WHERE estado = 'activo'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['activos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Ingresos del día
    $query = "SELECT COALESCE(SUM(monto), 0) as ingresos FROM registros 
             WHERE DATE(fecha_salida) = CURDATE() AND estado = 'finalizado'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['ingresos_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['ingresos'];
    
    // Ingresos del mes
    $query = "SELECT COALESCE(SUM(monto), 0) as ingresos_mes FROM registros 
             WHERE MONTH(fecha_salida) = MONTH(CURDATE()) 
             AND YEAR(fecha_salida) = YEAR(CURDATE())
             AND estado = 'finalizado'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['ingresos_mes'] = $stmt->fetch(PDO::FETCH_ASSOC)['ingresos_mes'];
    
    // Total de registros hoy
    $query = "SELECT COUNT(*) as total_registros FROM registros 
             WHERE DATE(fecha_entrada) = CURDATE()";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['registros_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_registros'];
    
} catch (Exception $e) {
    // Si hay error, establecer valores por defecto
    $stats = [
        'activos' => 0,
        'ingresos_hoy' => 0,
        'ingresos_mes' => 0,
        'registros_hoy' => 0
    ];
}
?>

<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Dashboard</h1>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Alertas -->
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

        <!-- Métricas Principales -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?php echo $stats['activos']; ?></h3>
                        <p>Vehículos Activos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-car"></i>
                    </div>
                    <a href="<?php echo url('modules/vehiculos/registros.php?estado=activo'); ?>" class="small-box-footer">
                        Más info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?php echo formatMoney($stats['ingresos_hoy']); ?></h3>
                        <p>Ingresos Hoy</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <a href="<?php echo url('modules/vehiculos/registros.php?fecha=hoy'); ?>" class="small-box-footer">
                        Más info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?php echo formatMoney($stats['ingresos_mes']); ?></h3>
                        <p>Ingresos Este Mes</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <a href="<?php echo url('modules/vehiculos/registros.php'); ?>" class="small-box-footer">
                        Más info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?php echo $stats['registros_hoy']; ?></h3>
                        <p>Registros Hoy</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <a href="<?php echo url('modules/vehiculos/registros.php?fecha=hoy'); ?>" class="small-box-footer">
                        Más info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Bienvenido al Sistema de Estacionamiento</h3>
                    </div>
                    <div class="card-body text-center">
                        <p class="lead">Sistema funcionando correctamente</p>
                        <a href="<?php echo url('modules/vehiculos/entrada.php'); ?>" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Comenzar a Registrar Entradas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>