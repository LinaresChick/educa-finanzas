<?php
// test_pagos.php - Archivo de prueba para verificar el sistema de pagos - VERSIÓN CORREGIDA
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>TEST PAGOS - VERIFICACIÓN</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; background: white; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        pre { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .section { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    </style>
</head>";
echo "<body>";

echo "<div class='section'>";
echo "<h1>🧪 TEST SISTEMA DE PAGOS - VERIFICACIÓN COMPLETA</h1>";
echo "<p><strong>URL de acceso:</strong> http://localhost/educa-finanzas/test_pagos.php</p>";
echo "<p><strong>Directorio:</strong> " . __DIR__ . "</p>";
echo "</div>";

try {
    // ============================================
    // 1. VERIFICAR ARCHIVOS REQUERIDOS
    // ============================================
    echo "<div class='section'>";
    echo "<h2>📁 1. VERIFICACIÓN DE ARCHIVOS</h2>";
    
    $archivosRequeridos = [
        'config/database.php' => 'Configuración de base de datos',
        'core/BaseDeDatos.php' => 'Clase base de datos',
        'core/Modelo.php' => 'Clase modelo base',
        'models/PagoModel.php' => 'Modelo de pagos',
        'models/EstudianteModel.php' => 'Modelo de estudiantes'
    ];
    
    $todosArchivosOk = true;
    foreach ($archivosRequeridos as $archivo => $descripcion) {
        if (file_exists($archivo)) {
            echo "<div class='success'>✅ $descripcion ($archivo) - EXISTE</div>";
        } else {
            echo "<div class='error'>❌ $descripcion ($archivo) - NO EXISTE</div>";
            $todosArchivosOk = false;
        }
    }
    
    if ($todosArchivosOk) {
        // Incluir los archivos necesarios
        require_once 'config/database.php';
        require_once 'core/BaseDeDatos.php';
        require_once 'core/Modelo.php';
        require_once 'models/PagoModel.php';
        require_once 'models/EstudianteModel.php';
        
        echo "<div class='success'>✅ Todos los archivos incluidos correctamente</div>";
    } else {
        throw new Exception("Faltan archivos requeridos");
    }
    echo "</div>";

    // ============================================
    // 2. VERIFICAR CONEXIÓN A BASE DE DATOS
    // ============================================
    echo "<div class='section'>";
    echo "<h2>🗄️ 2. VERIFICACIÓN DE BASE DE DATOS</h2>";
    
    try {
        $database = new Core\BaseDeDatos();
        $pdo = $database->getConnection();
        echo "<div class='success'>✅ Conexión a base de datos establecida</div>";
        
        // Verificar si la tabla pagos existe
        $stmt = $pdo->query("SHOW TABLES LIKE 'pagos'");
        if ($stmt->rowCount() > 0) {
            echo "<div class='success'>✅ Tabla 'pagos' existe</div>";
        } else {
            echo "<div class='error'>❌ Tabla 'pagos' NO existe</div>";
        }
        
        // Verificar estructura de la tabla pagos
        $stmt = $pdo->query("DESCRIBE pagos");
        $estructura = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Estructura de la tabla 'pagos':</h3>";
        echo "<table>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($estructura as $campo) {
            echo "<tr>";
            echo "<td>" . $campo['Field'] . "</td>";
            echo "<td>" . $campo['Type'] . "</td>";
            echo "<td>" . $campo['Null'] . "</td>";
            echo "<td>" . $campo['Key'] . "</td>";
            echo "<td>" . $campo['Default'] . "</td>";
            echo "<td>" . $campo['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error de conexión: " . $e->getMessage() . "</div>";
        throw $e;
    }
    echo "</div>";

    // ============================================
    // 3. VERIFICAR MODELOS
    // ============================================
    echo "<div class='section'>";
    echo "<h2>🔧 3. VERIFICACIÓN DE MODELOS</h2>";
    
    try {
        $pagoModel = new Models\PagoModel();
        echo "<div class='success'>✅ Modelo PagoModel creado correctamente</div>";
        
        $estudianteModel = new Models\EstudianteModel();
        echo "<div class='success'>✅ Modelo EstudianteModel creado correctamente</div>";
        
        // Verificar allowedFields en PagoModel usando reflexión (sin necesidad de método adicional)
        echo "<h3>Campos permitidos en PagoModel:</h3>";
        $reflection = new ReflectionClass($pagoModel);
        $property = $reflection->getProperty('allowedFields');
        $property->setAccessible(true);
        $allowedFields = $property->getValue($pagoModel);
        echo "<pre>" . print_r($allowedFields, true) . "</pre>";
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error en modelos: " . $e->getMessage() . "</div>";
        throw $e;
    }
    echo "</div>";

    // ============================================
    // 4. VERIFICAR DATOS EXISTENTES
    // ============================================
    echo "<div class='section'>";
    echo "<h2>📊 4. VERIFICACIÓN DE DATOS EXISTENTES</h2>";
    
    try {
        // Verificar estudiantes activos
        $estudiantes = $estudianteModel->obtenerEstudiantesActivos();
        echo "<div class='info'>📝 Estudiantes activos encontrados: " . count($estudiantes) . "</div>";
        
        if (count($estudiantes) > 0) {
            echo "<h3>Primeros 5 estudiantes activos:</h3>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Nombres</th><th>Apellidos</th><th>DNI</th><th>Monto</th></tr>";
            $count = 0;
            foreach ($estudiantes as $estudiante) {
                if ($count++ >= 5) break;
                echo "<tr>";
                echo "<td>" . ($estudiante['id_estudiante'] ?? 'N/A') . "</td>";
                echo "<td>" . ($estudiante['nombres'] ?? 'N/A') . "</td>";
                echo "<td>" . ($estudiante['apellidos'] ?? 'N/A') . "</td>";
                echo "<td>" . ($estudiante['dni'] ?? 'N/A') . "</td>";
                echo "<td>S/ " . number_format($estudiante['monto'] ?? 0, 2) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        // Verificar pagos existentes
        $pagos = $pagoModel->obtenerPagosConEstudiantes();
        echo "<div class='info'>💰 Pagos registrados: " . count($pagos) . "</div>";
        
        if (count($pagos) > 0) {
            echo "<h3>Últimos 5 pagos:</h3>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Estudiante</th><th>Concepto</th><th>Monto</th><th>Fecha</th></tr>";
            $count = 0;
            foreach ($pagos as $pago) {
                if ($count++ >= 5) break;
                echo "<tr>";
                echo "<td>" . ($pago['id_pago'] ?? 'N/A') . "</td>";
                echo "<td>" . ($pago['estudiante_nombre_completo'] ?? 'N/A') . "</td>";
                echo "<td>" . ($pago['concepto'] ?? 'N/A') . "</td>";
                echo "<td>S/ " . number_format($pago['monto'] ?? 0, 2) . "</td>";
                echo "<td>" . ($pago['fecha_pago'] ?? 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error al obtener datos: " . $e->getMessage() . "</div>";
    }
    echo "</div>";

    // ============================================
    // 5. PRUEBA DE INSERCIÓN
    // ============================================
    echo "<div class='section'>";
    echo "<h2>🧪 5. PRUEBA DE REGISTRO DE PAGO</h2>";
    
    try {
        if (count($estudiantes) > 0) {
            $primerEstudiante = $estudiantes[0];
            
            $datosPrueba = [
                'id_estudiante' => $primerEstudiante['id_estudiante'],
                'concepto' => 'PAGO DE PRUEBA - TEST',
                'banco' => 'BCP',
                'monto' => 100.00,
                'metodo_pago' => 'efectivo',
                'fecha_pago' => date('Y-m-d'),
                'descuento' => 0,
                'aumento' => 0,
                'observaciones' => 'Pago de prueba desde test_pagos.php',
                'foto_baucher' => '',
                'usuario_registro' => 1 // Usuario de prueba
            ];
            
            echo "<h3>Datos de prueba:</h3>";
            echo "<pre>" . print_r($datosPrueba, true) . "</pre>";
            
            // Intentar insertar
            $idPago = $pagoModel->crear($datosPrueba);
            
            if ($idPago) {
                echo "<div class='success'>✅ ✅ ✅ PAGO REGISTRADO EXITOSAMENTE</div>";
                echo "<div class='success'>ID del pago: " . $idPago . "</div>";
                
                // Limpiar el pago de prueba
                $pagoModel->eliminar($idPago);
                echo "<div class='info'>🧹 Pago de prueba eliminado</div>";
            } else {
                echo "<div class='error'>❌ No se pudo registrar el pago</div>";
            }
            
        } else {
            echo "<div class='warning'>⚠ No hay estudiantes para realizar prueba</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error en prueba de inserción: " . $e->getMessage() . "</div>";
        echo "<pre>Trace: " . $e->getTraceAsString() . "</pre>";
    }
    echo "</div>";

    // ============================================
    // 6. INFORMACIÓN DEL SISTEMA
    // ============================================
    echo "<div class='section'>";
    echo "<h2>⚙️ 6. INFORMACIÓN DEL SISTEMA</h2>";
    
    echo "<table>";
    echo "<tr><th>Parámetro</th><th>Valor</th></tr>";
    echo "<tr><td>PHP Version</td><td>" . PHP_VERSION . "</td></tr>";
    echo "<tr><td>Servidor Web</td><td>" . $_SERVER['SERVER_SOFTWARE'] . "</td></tr>";
    echo "<tr><td>Extensions PDO</td><td>" . (extension_loaded('pdo') ? '✅ Activada' : '❌ Desactivada') . "</td></tr>";
    echo "<tr><td>PDO MySQL</td><td>" . (extension_loaded('pdo_mysql') ? '✅ Activada' : '❌ Desactivada') . "</td></tr>";
    echo "<tr><td>Upload Max Filesize</td><td>" . ini_get('upload_max_filesize') . "</td></tr>";
    echo "<tr><td>Post Max Size</td><td>" . ini_get('post_max_size') . "</td></tr>";
    echo "<tr><td>Memory Limit</td><td>" . ini_get('memory_limit') . "</td></tr>";
    echo "</table>";
    echo "</div>";

    echo "<div class='section success'>";
    echo "<h2>🎉 TEST COMPLETADO</h2>";
    echo "<p>Revisa los resultados arriba para identificar posibles problemas.</p>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='section error'>";
    echo "<h2>💥 ERROR CRÍTICO</h2>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "<pre><strong>Trace:</strong>\n" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "<div class='section info'>";
echo "<h2>🔗 ENLACES ÚTILES</h2>";
echo "<ul>";
echo "<li><a href='http://localhost/educa-finanzas/public/index.php?controller=Pago&action=crear' target='_blank'>📝 Formulario de Registro de Pagos</a></li>";
echo "<li><a href='http://localhost/educa-finanzas/public/index.php?controller=Pago&action=index' target='_blank'>📋 Listado de Pagos</a></li>";
echo "<li><a href='http://localhost/educa-finanzas/test_salones.php' target='_blank'>🏫 Test de Salones</a></li>";
echo "<li><a href='http://localhost/phpmyadmin' target='_blank'>🗄️ PHPMyAdmin</a></li>";
echo "</ul>";
echo "</div>";

echo "</body>";
echo "</html>";
?>