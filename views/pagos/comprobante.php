<?php
/**
 * Vista de comprobante de pago
 */
if (!isset($modo_impresion)) {
    require_once 'views/templates/header.php';
    require_once 'views/templates/navbar.php';
    require_once 'views/templates/sidebar.php';
}
?>

<?php if (!isset($modo_impresion)): ?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Comprobante de Pago</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/panel">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="/pagos">Pagos</a></li>
                        <li class="breadcrumb-item active">Comprobante</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-12">
                    <a href="/pagos/comprobante/<?= $pago['id_pago'] ?>?imprimir=1" class="btn btn-info" target="_blank">
                        <i class="fas fa-print"></i> Imprimir Comprobante
                    </a>
                    <a href="/pagos" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
<?php else: ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Pago #<?= $pago['id_pago'] ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .comprobante-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            max-height: 80px;
        }
        .comprobante-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .comprobante-body {
            margin-bottom: 30px;
        }
        .comprobante-footer {
            text-align: center;
            margin-top: 50px;
            font-size: 0.9em;
            color: #666;
        }
        .anulado {
            position: relative;
        }
        .anulado:after {
            content: 'ANULADO';
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 100px;
            color: rgba(255, 0, 0, 0.3);
            transform: rotate(-45deg);
            transform-origin: center;
            z-index: 1000;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
            }
            .comprobante-container {
                border: none;
            }
        }
    </style>
</head>
<body onload="window.print()">
<?php endif; ?>

    <div class="comprobante-container <?= ($pago['estado'] === 'anulado') ? 'anulado' : '' ?>">
        <div class="logo">
            <img src="/img/logo.png" alt="Logo">
        </div>
        
        <div class="comprobante-header">
            <h2><?= isset($pago['tipo_comprobante']) ? (strtoupper($pago['tipo_comprobante']) === 'FACTURA' ? 'FACTURA' : 'RECIBO DE PAGO') : 'RECIBO DE PAGO' ?></h2>
            <?php if (isset($pago['numero_comprobante'])): ?>
                <h4>N° <?= htmlspecialchars($pago['numero_comprobante']) ?></h4>
            <?php else: ?>
                <h4>N° <?= str_pad($pago['id_pago'], 8, '0', STR_PAD_LEFT) ?></h4>
            <?php endif; ?>
        </div>
        
        <div class="comprobante-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Fecha:</strong> <?= isset($pago['fecha_emision_formateada']) ? $pago['fecha_emision_formateada'] : $pago['fecha_pago_formateada'] ?></p>
                </div>
                <div class="col-md-6 text-right">
                    <p><strong>Estado:</strong> 
                        <?php if ($pago['estado'] === 'completado'): ?>
                            <span class="badge badge-success">PAGADO</span>
                        <?php elseif ($pago['estado'] === 'anulado'): ?>
                            <span class="badge badge-danger">ANULADO</span>
                        <?php else: ?>
                            <span class="badge badge-info"><?= strtoupper($pago['estado']) ?></span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            
            <hr>
            
            <div class="row mb-4">
                <div class="col-md-12">
                    <h5>Datos del Estudiante</h5>
                    <p><strong>Estudiante:</strong> <?= htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']) ?></p>
                    <p><strong>DNI:</strong> <?= htmlspecialchars($estudiante['dni']) ?></p>
                    <p><strong>Grado:</strong> <?= htmlspecialchars($estudiante['grado'] . ' ' . $estudiante['seccion']) ?></p>
                </div>
            </div>
            
            <h5>Detalle del Pago</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th>Método de Pago</th>
                        <th>Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= htmlspecialchars($pago['concepto']) ?></td>
                        <td>
                            <?php
                            switch ($pago['metodo_pago']) {
                                case 'efectivo':
                                    echo 'Efectivo';
                                    break;
                                case 'transferencia':
                                    echo 'Transferencia bancaria';
                                    break;
                                case 'tarjeta':
                                    echo 'Tarjeta crédito/débito';
                                    break;
                                default:
                                    echo htmlspecialchars($pago['metodo_pago']);
                            }
                            ?>
                        </td>
                        <td class="text-right">S/ <?= number_format($pago['monto'], 2) ?></td>
                    </tr>
                    <?php if (isset($pago['subtotal']) && isset($pago['igv']) && $pago['tipo_comprobante'] === 'factura'): ?>
                        <tr>
                            <td colspan="2" class="text-right"><strong>Subtotal</strong></td>
                            <td class="text-right">S/ <?= number_format($pago['subtotal'], 2) ?></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-right"><strong>IGV (18%)</strong></td>
                            <td class="text-right">S/ <?= number_format($pago['igv'], 2) ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td colspan="2" class="text-right"><strong>TOTAL</strong></td>
                        <td class="text-right"><strong>S/ <?= number_format($pago['monto'], 2) ?></strong></td>
                    </tr>
                </tbody>
            </table>
            
            <?php if (!empty($pago['observaciones'])): ?>
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h5>Observaciones</h5>
                        <p><?= nl2br(htmlspecialchars($pago['observaciones'])) ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="comprobante-footer">
            <p>Pago registrado por: <?= isset($registrado_por) ? htmlspecialchars($registrado_por['nombre']) : 'Sistema' ?></p>
            <p>Este documento es un comprobante oficial de pago.</p>
            <p>Sistema Educativo de Finanzas - Todos los derechos reservados</p>
        </div>
    </div>

<?php if (!isset($modo_impresion)): ?>
        </div>
    </section>
</div>
<?php require_once 'views/templates/footer.php'; ?>
<?php else: ?>
</body>
</html>
<?php endif; ?>
