<?php
/**
 * Script para generar archivo Excel de ejemplo para importar estudiantes
 * Ejecutar: php generar_excel_ejemplo.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

try {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Estudiantes');

    // Encabezados
    $headers = ['nombres', 'apellidos', 'dni', 'fecha_nacimiento', 'direccion', 'telefono', 'mencion'];
    $sheet->fromArray($headers, null, 'A1');

    // Estilo de encabezados
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
            'size' => 12
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '4472C4']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER
        ]
    ];
    $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

    // Datos de ejemplo
    $datos = [
        ['Juan Carlos', 'Pérez López', '12345678', '2010-05-15', 'Av. Principal 123', '987654321', 'Ciencias'],
        ['María Elena', 'García Torres', '23456789', '2010-08-20', 'Jr. Los Olivos 456', '987654322', 'Letras'],
        ['Pedro José', 'Martínez Ruiz', '34567890', '2010-03-10', 'Calle Las Flores 789', '987654323', 'Ciencias'],
        ['Ana Sofía', 'Rodríguez Silva', '45678901', '2010-11-25', 'Av. Los Pinos 321', '987654324', 'Letras'],
        ['Carlos Alberto', 'Fernández Gómez', '56789012', '2010-07-08', 'Jr. San Martín 654', '987654325', 'Ciencias'],
        ['Lucía Isabel', 'Sánchez Vargas', '67890123', '2010-09-14', 'Calle Comercio 987', '987654326', 'Humanidades'],
        ['Miguel Ángel', 'Ramírez Castro', '78901234', '2010-04-22', 'Av. La Paz 147', '987654327', 'Ciencias'],
        ['Gabriela Rosa', 'Torres Mendoza', '89012345', '2010-12-30', 'Jr. Unión 258', '987654328', 'Letras'],
        ['Daniel Eduardo', 'Flores Jiménez', '90123456', '2010-06-18', 'Calle Lima 369', '987654329', 'Ciencias'],
        ['Valentina Sofía', 'Cruz Morales', '01234567', '2010-10-05', 'Av. Arequipa 741', '987654330', 'Humanidades']
    ];

    $sheet->fromArray($datos, null, 'A2');

    // Ajustar ancho de columnas
    foreach(range('A','G') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Guardar archivo
    $writer = new Xlsx($spreadsheet);
    $filename = __DIR__ . '/ejemplo_importar_estudiantes.xlsx';
    $writer->save($filename);

    echo "✅ Archivo Excel generado exitosamente: ejemplo_importar_estudiantes.xlsx\n";
    echo "📊 Contiene 10 estudiantes de ejemplo con todas las columnas\n";
    echo "📁 Ubicación: " . $filename . "\n";

} catch (Exception $e) {
    echo "❌ Error al generar el archivo: " . $e->getMessage() . "\n";
    exit(1);
}
