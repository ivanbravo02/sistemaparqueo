<?php
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!isset($_GET['registro_id'])) {
    die("Registro no especificado");
}

$query = "SELECT r.*, v.placa, v.marca, v.modelo, v.color, v.tipo, u.nombre as usuario
         FROM registros r 
         JOIN vehiculos v ON r.vehiculo_id = v.id 
         JOIN usuarios u ON r.usuario_id = u.id 
         WHERE r.id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_GET['registro_id']]);
$registro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$registro) {
    die("Registro no encontrado");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ticket de Estacionamiento</title>
    <style>
        /* TAMAÑO PARA TICKET DE 80mm (impresoras térmicas) */
        @page {
            size: 80mm auto;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            width: 78mm;
            margin: 0 auto;
            padding: 3px;
            background: white;
            line-height: 1.2;
        }

        .ticket {
            width: 100%;
            border: 1px solid #000;
            padding: 8px;
            box-sizing: border-box;
        }

        .header { 
            text-align: center; 
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }

        .header h2 {
            margin: 0;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header p {
            margin: 2px 0;
            font-size: 9px;
        }

        .info { 
            margin: 4px 0; 
            padding: 2px 0;
        }

        .info strong {
            display: block;
            font-size: 10px;
            margin-bottom: 1px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
            padding-top: 6px;
        }

        .total {
            font-weight: bold;
            font-size: 13px;
            border-top: 2px solid #000;
            padding-top: 8px;
            margin-top: 8px;
            text-align: center;
            background: #f0f0f0;
            padding: 8px;
            margin: 10px -8px -8px -8px;
        }

        .center { 
            text-align: center; 
        }

        .barcode-area {
            text-align: center;
            margin: 8px 0;
            padding: 5px;
            border: 1px dashed #ccc;
            font-family: 'Courier New', monospace;
            font-size: 10px;
        }

        .footer {
            font-size: 9px;
            text-align: center;
            margin-top: 10px;
            border-top: 1px dashed #000;
            padding-top: 6px;
        }

        /* Estilos para impresión */
        @media print {
            body {
                margin: 0;
                padding: 2mm;
                width: 76mm;
            }
            
            .ticket {
                border: 1px solid #000;
            }
            
            .no-print {
                display: none !important;
            }
        }

        /* Estilos para pantalla */
        @media screen {
            body {
                background: #f0f0f0;
                margin: 20px auto;
            }
            
            .ticket {
                background: white;
                box-shadow: 0 0 10px rgba(0,0,0,0.2);
            }
            
            .print-btn {
                text-align: center;
                margin-top: 15px;
            }
        }
    </style>
</head>
<body onload="window.print(); setTimeout(() => window.close(), 1000);">
    <div class="ticket">
        <!-- Encabezado -->
        <div class="header">
            <h2>SISTEMA DE ESTACIONAMIENTO</h2>
            <p>Ciudad: Tehuacán, Puebla, México</p>
            <p>Tel: (238) 123-4567</p>
        </div>
        
        <!-- Información del ticket -->
        <div class="info center"><strong>TICKET DE SALIDA</strong></div>
        <div class="info center">N°: <?php echo str_pad($registro['id'], 6, '0', STR_PAD_LEFT); ?></div>
        
        <!-- Área de código (simulado) -->
        <div class="barcode-area">
            *<?php echo $registro['id'] . date('dmYHis'); ?>*
        </div>
        
        <div class="divider"></div>
        
        <!-- Información del vehículo -->
        <div class="info"><strong>INFORMACIÓN DEL VEHÍCULO</strong></div>
        <div class="info">PLACA: <strong><?php echo $registro['placa']; ?></strong></div>
        <div class="info">TIPO: <?php echo strtoupper($registro['tipo']); ?></div>
        <div class="info">MARCA: <?php echo $registro['marca'] ?: 'N/A'; ?></div>
        <div class="info">MODELO: <?php echo $registro['modelo'] ?: 'N/A'; ?></div>
        <div class="info">COLOR: <?php echo $registro['color'] ?: 'N/A'; ?></div>
        
        <div class="divider"></div>
        
        <!-- Tiempo de estadía -->
        <div class="info"><strong>TIEMPO DE ESTADÍA</strong></div>
        <div class="info">ENTRADA: <?php echo date('d/m/Y H:i', strtotime($registro['fecha_entrada'])); ?></div>
        <div class="info">SALIDA: <?php echo date('d/m/Y H:i', strtotime($registro['fecha_salida'])); ?></div>
        <div class="info">DURACIÓN: <?php echo $registro['tiempo_estadia']; ?></div>
        
        <div class="divider"></div>
        
        <!-- Total a pagar -->
        <div class="total">
            TOTAL A PAGAR: $<?php echo number_format($registro['monto'], 2); ?>
        </div>
        
        <!-- Pie de página -->
        <div class="footer">
            <p>¡Gracias por su preferencia!</p>
            <p>Vuelva pronto</p>
            <p>--------------------------------</p>
            <p><?php echo date('d/m/Y H:i'); ?></p>
            <p>Operador: <?php echo $registro['usuario']; ?></p>
        </div>
    </div>

    <!-- Botones para navegador (solo se ven en pantalla) -->
    <div class="no-print print-btn">
        <button onclick="window.print()" style="padding: 8px 16px; margin: 5px; font-size: 12px;">
            🖨️ Imprimir Ticket
        </button>
        <button onclick="window.close()" style="padding: 8px 16px; margin: 5px; font-size: 12px;">
            ❌ Cerrar Ventana
        </button>
    </div>
</body>
</html>