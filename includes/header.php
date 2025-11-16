<?php

date_default_timezone_set('America/Mexico_City');
define('BASE_URL', 'http://localhost/sistemaparqueo/');
define('SITE_NAME', 'Sistema de Estacionamiento');

function redirect($path = '') {
    // Limpiar todos los buffers de salida
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    $url = BASE_URL . ltrim($path, '/');
    
    header("Location: " . $url);
    exit;
}

/**
 * Genera una URL absoluta
 */
function url($path = '') {
    return BASE_URL . ltrim($path, '/');
}

/**
 * Manejo de mensajes flash
 */
function setMessage($type, $message) {
    $_SESSION[$type] = $message;
}

function getMessage($type) {
    if (isset($_SESSION[$type])) {
        $message = $_SESSION[$type];
        unset($_SESSION[$type]);
        return $message;
    }
    return null;
}

/**
 * Formatea una cantidad monetaria
 */
function formatMoney($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Formatea una fecha para mostrar
 */
function formatDate($date, $includeTime = true) {
    if (!$date) return '-';
    $format = $includeTime ? 'd/m/Y H:i' : 'd/m/Y';
    return date($format, strtotime($date));
}

/**
 * Obtiene la fecha y hora actual de Mexico
 */
function now() {
    return date('Y-m-d H:i:s');
}

/**
 * Calcula el tiempo transcurrido entre dos fechas
 */
function calculateTime($start, $end = null) {
    try {
        $start = new DateTime($start);
        $end = $end ? new DateTime($end) : new DateTime();
        
        $interval = $start->diff($end);
        
        return sprintf('%02d:%02d:%02d', 
            $interval->days * 24 + $interval->h, 
            $interval->i, 
            $interval->s
        );
    } catch (Exception $e) {
        return '00:00:00';
    }
}

/**
 * Sanitiza una cadena para prevenir XSS
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Verifica si el usuario tiene un rol específico
 */
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Valida formato de placa de vehículo
 */
function isValidPlaca($placa) {
    return preg_match('/^[A-Z0-9]{3,10}$/', $placa);
}

// =============================================
// INICIAR SESIÓN Y VERIFICAR AUTENTICACIÓN
// =============================================
session_start();

if (!isset($_SESSION['user_id'])) {
    // Limpiar buffers antes de redirect
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header("Location: " . BASE_URL . "login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .brand-link {
            background: linear-gradient(45deg, #007bff, #6610f2);
        }
        .user-panel {
            border-bottom: 1px solid #4f5962;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="<?php echo url(); ?>" class="nav-link">Inicio</a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-user-circle mr-1"></i> 
                    <?php echo $_SESSION['user_name']; ?>
                    <span class="badge badge-<?php echo $_SESSION['user_role'] == 'admin' ? 'danger' : 'primary'; ?> ml-1">
                        <?php echo ucfirst($_SESSION['user_role']); ?>
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <span class="dropdown-header">
                        <i class="fas fa-user mr-2"></i><?php echo $_SESSION['user_name']; ?>
                    </span>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="<?php echo url('logout.php'); ?>">
                        <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                    </a>
                </div>
            </li>
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="<?php echo url(); ?>" class="brand-link">
            <i class="fas fa-parking brand-icon"></i>
            <span class="brand-text font-weight-light"> <b><?=$_SESSION['user_name'];?></b></span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- User Panel -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <i class="fas fa-user-circle img-circle elevation-2"></i>
                </div>
                <div class="info">
                    <a href="#" class="d-block"><?php echo $_SESSION['user_name']; ?></a>
                    <small class="text-light"><?php echo ucfirst($_SESSION['user_role']); ?></small>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a href="<?php echo url(); ?>" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <!-- Vehículos -->
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-car"></i>
                            <p>
                                Vehículos
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?php echo url('modules/vehiculos/entrada.php'); ?>" class="nav-link">
                                    <i class="fas fa-sign-in-alt nav-icon"></i>
                                    <p>Registrar Entrada</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo url('modules/vehiculos/salida.php'); ?>" class="nav-link">
                                    <i class="fas fa-sign-out-alt nav-icon"></i>
                                    <p>Registrar Salida</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo url('modules/vehiculos/registros.php'); ?>" class="nav-link">
                                    <i class="fas fa-history nav-icon"></i>
                                    <p>Historial</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Administración -->
                    <?php if ($_SESSION['user_role'] == 'admin'): ?>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>
                                Administración
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?php echo url('modules/usuarios/listar.php'); ?>" class="nav-link">
                                    <i class="fas fa-users nav-icon"></i>
                                    <p>Usuarios</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <!-- Cerrar Sesión -->
                    <li class="nav-item">
                        <a href="<?php echo url('logout.php'); ?>" class="nav-link text-danger">
                            <i class="nav-icon fas fa-sign-out-alt"></i>
                            <p>Cerrar Sesión</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">