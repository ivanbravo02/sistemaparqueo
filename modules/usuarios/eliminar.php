<?php
include '../../includes/header.php';
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Verificar que se recibió el ID
if (!isset($_GET['id'])) {
    setMessage('error', 'ID de usuario no proporcionado');
    redirect('modules/usuarios/listar.php');
}

$id = $_GET['id'];

// Verificar que no sea el usuario actual
if ($id == $_SESSION['user_id']) {
    setMessage('error', 'No puedes eliminarte a ti mismo');
    redirect('modules/usuarios/listar.php');
}

try {
    // ELIMINACIÓN TOTAL de la base de datos
    $query = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    
    // Verificar si se eliminó realmente
    if ($stmt->rowCount() > 0) {
        setMessage('success', "✅ Usuario eliminado permanentemente de la base de datos");
    } else {
        setMessage('error', "❌ No se pudo eliminar el usuario o no existe");
    }
    
} catch (Exception $e) {
    setMessage('error', "Error al eliminar usuario: " . $e->getMessage());
}

// Redirigir al listado
redirect('modules/usuarios/listar.php');
?>