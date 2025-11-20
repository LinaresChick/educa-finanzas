<?php
/**
 * Vista de comprobante de pago
 */
$modo_impresion = $modo_impresion ?? false;

if (!$modo_impresion) {
    require_once __DIR__ . '/../templates/header.php';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php if ($modo_impresion): ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Pago</title>
    <?php endif; ?>
    <style>
        .comprobante-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            border: 2px solid #333;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            background: #fff;
            font-family: Arial, sans-serif;
        }
        .comprobante-container::before {
    content: "INSTITUCIÓN EDUCATIVA PARTICULAR INDEPENDENCIA"; /* Cambiar por el nombre real */
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-30deg);
    font-size: 60px;
    color: rgba(0,0,0,0.05);
    font-weight: bold;
    white-space: nowrap;
    pointer-events: none;
    z-index: 0;
}

        .logo {
            text-align: center;
            margin-bottom: 30px;
            padding: 10px;
        }

        .logo img {
            max-width: 200px;
            height: auto;
        }

        .comprobante-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }

        .comprobante-header h2 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 24px;
            font-weight: bold;
        }

        .comprobante-header h4 {
            margin: 0;
            color: #666;
            font-size: 18px;
        }

        .info-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 5px;
        }

        .info-row {
            display: flex;
            margin-bottom: 15px;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .info-label {
            font-weight: bold;
            width: 180px;
            color: #555;
            font-size: 14px;
            text-transform: uppercase;
        }

        .info-value {
            flex: 1;
            font-size: 16px;
            color: #333;
            padding-left: 15px;
        }

        .total-section {
            margin-top: 30px;
            background: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .total-section .info-row:last-child {
            margin-top: 15px;
            border-top: 2px solid #333;
            border-bottom: none;
            padding-top: 15px;
        }

        .total-section .info-row:last-child .info-label,
        .total-section .info-row:last-child .info-value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .firma-section {
            margin-top: 60px;
            text-align: center;
        }

        .firma-linea {
            width: 250px;
            border-top: 1px solid #333;
            margin: 10px auto;
        }

        .firma-texto {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .anulado {
            position: relative;
            overflow: hidden;
        }

        .anulado::after {
            content: "ANULADO";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(255, 0, 0, 0.2);
            border: 10px solid rgba(255, 0, 0, 0.2);
            padding: 30px;
            pointer-events: none;
            z-index: 1000;
            white-space: nowrap;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                background: white;
            }
            .comprobante-container {
                box-shadow: none;
                border: 1px solid #333;
                padding: 20px;
                margin: 0 auto;
                width: 100%;
                max-width: none;
            }
            .no-print {
                display: none !important;
            }
            .content-wrapper,
            .content-header,
            .container-fluid {
                margin: 0 !important;
                padding: 0 !important;
            }
            @page {
                margin: 0.5cm;
                size: A4;
            }
        }
    </style>
</head>
<body <?= $modo_impresion ? 'onload="window.print()"' : '' ?>>

<?php if (!$modo_impresion): ?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Comprobante de Pago</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <button onclick="window.print()" class="btn btn-primary no-print">
                        <i class="fas fa-print"></i> Imprimir Comprobante
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="content">
        <div class="container-fluid">
<?php endif; ?>

            <div class="comprobante-container<?= isset($pago['estado']) && $pago['estado'] === 'anulado' ? ' anulado' : '' ?>">
                <div class="logo">
                    <img src="/educa-finanzas/public/img/image.png" alt="Logo de la Institución">
                </div>
                
                <div class="comprobante-header">
                    <h2>COMPROBANTE DE PAGO</h2>
                    <h4>N° <?= str_pad($pago['id_pago'], 8, '0', STR_PAD_LEFT) ?></h4>
                </div>

                <div class="info-section">
                    <div class="info-row">
                        <div class="info-label">Fecha</div>
                        <div class="info-value"><?= $fecha_formateada ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Estudiante</div>
                        <div class="info-value"><?= htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Concepto</div>
                        <div class="info-value"><?= htmlspecialchars($pago['concepto']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Pagador</div>
                        <div class="info-value"><?php
                            if (!empty($pagador)) {
                                if (!empty($pagador['nombres']) && !empty($pagador['apellidos'])) {
                                    echo htmlspecialchars(($pagador['nombres'] ?? '') . ' ' . ($pagador['apellidos'] ?? ''));
                                } elseif (!empty($pagador['nombres'])) {
                                    echo htmlspecialchars($pagador['nombres']);
                                }
                                if (!empty($pagador['dni'])) echo ' - DNI: ' . htmlspecialchars($pagador['dni']);
                            } else {
                                echo '—';
                            }
                        ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Método de Pago</div>
                        <div class="info-value"><?= htmlspecialchars($pago['metodo_pago']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Banco</div>
                        <div class="info-value"><?= htmlspecialchars($pago['banco']) ?></div>
                    </div>
                </div>

                <div class="total-section">
                    <div class="info-row">
                        <div class="info-label">Monto Base</div>
                        <div class="info-value">S/ <?= number_format($pago['monto'], 2) ?></div>
                    </div>
                    <?php if (!empty($pago['descuento']) && $pago['descuento'] > 0): ?>
                    <div class="info-row">
                        <div class="info-label">Descuento</div>
                        <div class="info-value">- S/ <?= number_format($pago['descuento'], 2) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($pago['aumento']) && $pago['aumento'] > 0): ?>
                    <div class="info-row">
                        <div class="info-label">Aumento</div>
                        <div class="info-value">+ S/ <?= number_format($pago['aumento'], 2) ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="info-row">
                        <div class="info-label">Total</div>
                        <div class="info-value">S/ <?= $monto_total ?></div>
                    </div>
                </div>

                <div class="firma-section">
                    <div class="firma-linea"></div>
                    <div class="firma-texto">Firma Autorizada</div>
                </div>

                <?php if (isset($pago['observaciones']) && !empty($pago['observaciones'])): ?>
                <div class="info-section" style="margin-top: 30px;">
                    <div class="info-row">
                        <div class="info-label">Observaciones</div>
                        <div class="info-value"><?= nl2br(htmlspecialchars($pago['observaciones'])) ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

<?php if (!$modo_impresion): ?>
        </div>
    </div>
</div>
<?php 
    require_once __DIR__ . '/../templates/footer.php';
endif; 
?>