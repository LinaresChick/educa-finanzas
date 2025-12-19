<?php
// Script de prueba para diagnosticar problemas con Excel
require_once __DIR__ . '/../vendor/autoload.php';

$filePath = __DIR__ . '/../ejemplo_importar_estudiantes.xlsx';

echo "=== TEST DE IMPORTACIÓN EXCEL ===\n\n";

// 1. Verificar si el archivo existe
echo "1. ¿Existe el archivo? " . (file_exists($filePath) ? "SÍ" : "NO") . "\n";
if (!file_exists($filePath)) {
    echo "ERROR: Archivo no encontrado en: $filePath\n";
    exit;
}

// 2. Verificar ZipArchive
echo "2. ¿ZipArchive disponible? " . (class_exists('ZipArchive') ? "SÍ" : "NO") . "\n";

// 3. Verificar PhpSpreadsheet
echo "3. ¿PhpSpreadsheet disponible? " . (class_exists('\PhpOffice\PhpSpreadsheet\IOFactory') ? "SÍ" : "NO") . "\n";

if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
    echo "ERROR: PhpSpreadsheet no está instalado\n";
    exit;
}

// 4. Intentar cargar el archivo
try {
    echo "4. Cargando archivo...\n";
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
    echo "   ✓ Archivo cargado exitosamente\n";
    
    $sheet = $spreadsheet->getActiveSheet();
    echo "5. Hoja activa: " . $sheet->getTitle() . "\n";
    
    $rowsData = $sheet->toArray(null, true, true, true);
    echo "6. Total de filas en el Excel: " . count($rowsData) . "\n";
    
    // Mostrar primeras 3 filas
    echo "7. Contenido (primeras 3 filas):\n";
    for ($i = 0; $i < min(3, count($rowsData)); $i++) {
        echo "   Fila " . ($i+1) . ": " . json_encode($rowsData[$i]) . "\n";
    }
    
    // Procesar como el controlador
    $rowsData = array_filter($rowsData, function($row) {
        return !empty(array_filter($row, function($cell) {
            return $cell !== null && $cell !== '';
        }));
    });
    
    echo "8. Filas después de filtrar vacías: " . count($rowsData) . "\n";
    
    if (count($rowsData) >= 2) {
        $rowsArray = array_values($rowsData);
        $header = array_map(function($c){ 
            return trim(strtolower(preg_replace('/\s+/', '', $c))); 
        }, array_values($rowsArray[0]));
        
        echo "9. Header normalizado: " . json_encode($header) . "\n";
        
        $allowed = ['nombres','apellidos','dni','fecha_nacimiento','direccion','telefono','mencion'];
        $rows = [];
        
        for ($i = 1; $i < count($rowsArray); $i++) {
            $vals = array_values($rowsArray[$i]);
            $row = [];
            foreach ($header as $j => $col) {
                if (in_array($col, $allowed)) {
                    $row[$col] = isset($vals[$j]) ? trim($vals[$j]) : '';
                }
            }
            if (!empty($row['nombres']) && !empty($row['apellidos'])) {
                $rows[] = $row;
            }
        }
        
        echo "10. Filas válidas procesadas: " . count($rows) . "\n";
        if (count($rows) > 0) {
            echo "    Primera fila: " . json_encode($rows[0]) . "\n";
        }
    }
    
    echo "\n✓ TODO OK - El archivo puede ser importado\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
