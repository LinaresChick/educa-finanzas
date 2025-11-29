<?php
namespace Controllers;
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../core/Sesion.php';
require_once __DIR__ . '/../core/Vista.php';
require_once __DIR__ . '/../models/EstudianteModel.php';
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../models/SeccionModel.php';
require_once __DIR__ . '/../models/DocenteModel.php';

use Core\BaseController;
use Core\Sesion;
use Core\Vista;
use Exception;
use Models\EstudianteModel;
use Models\UsuarioModel;
use Models\SeccionModel;
use Models\DocenteModel;

class EstudianteController extends BaseController {
    private $estudianteModel;
    private $usuarioModel;

    public function __construct() {
        parent::__construct();
        $this->estudianteModel = new EstudianteModel();
        $this->usuarioModel = new UsuarioModel();

        error_log("EstudianteController initialized.");
        
        // Verificación normal de roles
        $rolesPermitidos = ['Superadmin', 'Administrador', 'Colaborador'];
        if (!$this->sesion->tieneRol($rolesPermitidos)) {
            $this->redireccionar('auth/acceso_denegado');
        }
    }

    public function index() {
        $data['titulo'] = 'Listado de Estudiantes';
        $data['estudiantes'] = $this->estudianteModel->obtenerEstudiantesConInfo();
        $this->vista->mostrar('estudiantes/listado', $data);
    }

    public function crear() {
        try {
            $data['titulo'] = 'Registrar Nuevo Estudiante';
            $data['salones'] = $this->estudianteModel->obtenerSalonesDisponibles();
            // Añadir lista de secciones para que la vista pueda mostrar nombres desde la tabla `secciones`
            $seccionModel = new SeccionModel();
            $data['secciones'] = $seccionModel->obtenerTodas();
            
            $this->vista->mostrar('estudiantes/crear', $data);
            
        } catch (Exception $e) {
            error_log("Error en EstudianteController::crear: " . $e->getMessage());
            $this->sesion->setFlash('error', 'Error al cargar el formulario: ' . $e->getMessage());
            $this->redireccionar('estudiantes');
        }
    }

    /**
     * Mostrar formulario para importar un salón (seleccionar docente, sección, monto y archivo CSV)
     */
    public function importarSalon() {
        try {
            $data['titulo'] = 'Importar Salón - Cargar Estudiantes desde CSV';
            $docenteModel = new DocenteModel();
            $seccionModel = new SeccionModel();
            $data['docentes'] = $docenteModel->obtenerTodosActivos();
            $data['secciones'] = $seccionModel->obtenerTodas();
            $this->vista->mostrar('estudiantes/importar', $data);
        } catch (\Exception $e) {
            error_log('Error importarSalon: ' . $e->getMessage());
            $this->sesion->setFlash('error', 'Error al cargar el formulario de importación.');
            $this->redireccionar('estudiantes');
        }
    }

    /**
     * Procesar archivo CSV subido y mostrar vista previa, o guardar tras confirmación
     */
    public function procesarImportacion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('estudiantes');
            return;
        }

        // DEBUG: registrar POST y FILES para depuración
        try {
            $debugPath = __DIR__ . '/../storage/logs/import_debug.log';
            $dbg = "---- Import Debug: " . date('c') . " ----\n";
            $dbg .= "POST: " . print_r($_POST, true) . "\n";
            $dbg .= "FILES keys: " . print_r(array_keys($_FILES), true) . "\n";
            foreach ($_FILES as $k => $f) {
                $dbg .= "FILE {$k}: error={$f['error']} size={$f['size']} tmp=" . ($f['tmp_name'] ?? '') . " exists=" . (isset($f['tmp_name']) && file_exists($f['tmp_name']) ? 'yes' : 'no') . "\n";
            }
            file_put_contents($debugPath, $dbg, FILE_APPEND);
        } catch (\Exception $e) {
            error_log('No se pudo escribir import_debug: ' . $e->getMessage());
        }

        $docente_id = $_POST['docente_id'] ?? null;
        $id_seccion = $_POST['id_seccion'] ?? null;
        $monto = $_POST['monto'] ?? null;

        // Validaciones básicas
        if (empty($docente_id) || empty($id_seccion) || !isset($_FILES['archivo'])) {
            $this->sesion->setFlash('error', 'Debe seleccionar docente, sección y subir un archivo CSV.');
            $this->redireccionar('estudiantes/importarSalon');
            return;
        }

        // Guardar archivo en temp
        $uploaded = $_FILES['archivo'];
        if ($uploaded['error'] !== UPLOAD_ERR_OK) {
            $this->sesion->setFlash('error', 'Error al subir el archivo.');
            $this->redireccionar('estudiantes/importarSalon');
            return;
        }

        $tmpDir = __DIR__ . '/../temp';
        if (!is_dir($tmpDir)) @mkdir($tmpDir, 0755, true);
        $tmpPath = $tmpDir . '/' . time() . '_' . basename($uploaded['name']);
        if (!move_uploaded_file($uploaded['tmp_name'], $tmpPath)) {
            $this->sesion->setFlash('error', 'No se pudo mover el archivo subido.');
            $this->redireccionar('estudiantes/importarSalon');
            return;
        }
        // DEBUG: verificar que el archivo fue movido
        try {
            $dbg = "Moved uploaded file to: {$tmpPath} exists=" . (file_exists($tmpPath) ? 'yes' : 'no') . "\n";
            file_put_contents(__DIR__ . '/../storage/logs/import_debug.log', $dbg, FILE_APPEND);
        } catch (\Exception $e) {}

        // Parse CSV (esperamos encabezados: nombres, apellidos, dni, fecha_nacimiento, direccion, telefono, mencion)
        // Detect file type by extension and parse (.csv or .xlsx/.xls)
        $ext = strtolower(pathinfo($tmpPath, PATHINFO_EXTENSION));
        $rows = [];
        $allowed = ['nombres','apellidos','dni','fecha_nacimiento','direccion','telefono','mencion'];

        if (in_array($ext, ['xlsx','xls'])) {
            // Try to use PhpSpreadsheet if available
            if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
                // Log and instruct to install library
                file_put_contents(__DIR__ . '/../storage/logs/import_debug.log', "PhpSpreadsheet not installed, cannot parse Excel file.\n", FILE_APPEND);
                $this->sesion->setFlash('error', 'Para importar archivos Excel instala la librería phpoffice/phpspreadsheet: run `composer require phpoffice/phpspreadsheet` in the project root.');
                if (file_exists($tmpPath)) @unlink($tmpPath);
                $this->redireccionar('estudiantes/importarSalon');
                return;
            }

            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmpPath);
                $sheet = $spreadsheet->getActiveSheet();
                $rowsData = $sheet->toArray(null, true, true, true);
                if (empty($rowsData) || count($rowsData) < 2) {
                    @unlink($tmpPath);
                    $this->sesion->setFlash('error', 'El archivo Excel está vacío o no contiene filas válidas.');
                    $this->redireccionar('estudiantes/importarSalon');
                    return;
                }

                // header is first row
                $header = array_map(function($c){ return trim(strtolower($c)); }, array_values($rowsData[1]));
                // build rows starting from row 2
                $rowKeys = array_keys($rowsData);
                for ($i = 2; $i <= end($rowKeys); $i++) {
                    if (!isset($rowsData[$i])) continue;
                    $vals = array_values($rowsData[$i]);
                    $row = [];
                    foreach ($header as $j => $col) {
                        if (in_array($col, $allowed)) {
                            $row[$col] = isset($vals[$j]) ? trim($vals[$j]) : '';
                        }
                    }
                    if (!empty($row['nombres']) && !empty($row['apellidos'])) $rows[] = $row;
                }
            } catch (\Exception $e) {
                file_put_contents(__DIR__ . '/../storage/logs/import_debug.log', "Error parsing Excel: " . $e->getMessage() . "\n", FILE_APPEND);
                @unlink($tmpPath);
                $this->sesion->setFlash('error', 'Error al leer el archivo Excel. Revisa el archivo o instala phpoffice/phpspreadsheet.');
                $this->redireccionar('estudiantes/importarSalon');
                return;
            }

        } else {
            // CSV parsing
            $fh = fopen($tmpPath, 'r');
            if (!$fh) {
                $this->sesion->setFlash('error', 'No se puede leer el archivo subido.');
                $this->redireccionar('estudiantes/importarSalon');
                return;
            }

            $header = fgetcsv($fh);
            if (!$header) {
                fclose($fh);
                $this->sesion->setFlash('error', 'El archivo CSV está vacío o tiene formato incorrecto.');
                $this->redireccionar('estudiantes/importarSalon');
                return;
            }

            // Normalizar encabezados
            $cols = array_map(function($c){ return trim(strtolower($c)); }, $header);
            while (($data = fgetcsv($fh)) !== false) {
                $row = [];
                foreach ($cols as $i => $col) {
                    if (in_array($col, $allowed)) {
                        $row[$col] = isset($data[$i]) ? trim($data[$i]) : '';
                    }
                }
                if (!empty($row['nombres']) && !empty($row['apellidos'])) {
                    $rows[] = $row;
                }
            }
            fclose($fh);
        }

        if (empty($rows)) {
            $this->sesion->setFlash('error', 'No se encontraron filas válidas en el CSV (se requieren al menos "nombres" y "apellidos").');
            @unlink($tmpPath);
            $this->redireccionar('estudiantes/importarSalon');
            return;
        }

        // DEBUG: número de filas parseadas
        try {
            $dbg2 = "Parsed rows count: " . count($rows) . "\n";
            file_put_contents(__DIR__ . '/../storage/logs/import_debug.log', $dbg2, FILE_APPEND);
        } catch (\Exception $e) {}

        // Si viene confirmación `confirm` entonces crear salón y guardar estudiantes
        if (!empty($_POST['confirm']) && $_POST['confirm'] == '1') {
            // Si el cliente envía tmp_path lo reparseamos; si no, usamos las filas ya parseadas en $rows
            $tmpPathConfirm = $_POST['tmp_path'] ?? null;
            $rowsConfirm = [];

            if (!empty($tmpPathConfirm) && file_exists($tmpPathConfirm)) {
                $fh2 = fopen($tmpPathConfirm, 'r');
                if ($fh2) {
                    $header2 = fgetcsv($fh2);
                    $cols2 = array_map(function($c){ return trim(strtolower($c)); }, $header2 ?: []);
                    $allowed = ['nombres','apellidos','dni','fecha_nacimiento','direccion','telefono','mencion'];
                    while (($data2 = fgetcsv($fh2)) !== false) {
                        $row2 = [];
                        foreach ($cols2 as $i => $col) {
                            if (in_array($col, $allowed)) {
                                $row2[$col] = isset($data2[$i]) ? trim($data2[$i]) : '';
                            }
                        }
                        if (!empty($row2['nombres']) && !empty($row2['apellidos'])) {
                            $rowsConfirm[] = $row2;
                        }
                    }
                    fclose($fh2);
                } else {
                    $this->sesion->setFlash('error', 'No se puede leer el archivo temporal.');
                    if (file_exists($tmpPathConfirm)) @unlink($tmpPathConfirm);
                    $this->redireccionar('estudiantes/importarSalon');
                    return;
                }
            } else {
                // usar filas ya parseadas en esta petición
                $rowsConfirm = $rows;
                $tmpPathConfirm = $tmpPath; // para eliminar después
            }

            // Preparar monto (la columna `monto` es NOT NULL en la tabla)
            $monto_value = ($monto === null || $monto === '') ? 0.00 : floatval(str_replace(',', '.', $monto));

            // DEBUG: logear valores clave antes de crear el salón
            try {
                $dbg3 = "About to create salon: docente_id=" . intval($docente_id) . " id_seccion=" . intval($id_seccion) . " cupo=" . count($rowsConfirm) . " monto=" . $monto_value . "\n";
                file_put_contents(__DIR__ . '/../storage/logs/import_debug.log', $dbg3, FILE_APPEND);
            } catch (\Exception $e) {}

            // Crear salón con docente
            $cupo = count($rowsConfirm);
            try {
                $nuevoSalonId = $this->estudianteModel->crearSalonConDocente(intval($id_seccion), intval($docente_id), $cupo);
                file_put_contents(__DIR__ . '/../storage/logs/import_debug.log', "crearSalonConDocente returned: " . print_r($nuevoSalonId, true) . "\n", FILE_APPEND);
            } catch (\Exception $e) {
                file_put_contents(__DIR__ . '/../storage/logs/import_debug.log', "crearSalonConDocente exception: " . $e->getMessage() . "\n", FILE_APPEND);
                $nuevoSalonId = false;
            }

            if (!$nuevoSalonId) {
                $this->sesion->setFlash('error', 'No se pudo crear el salón.');
                if (isset($tmpPathConfirm) && file_exists($tmpPathConfirm)) @unlink($tmpPathConfirm);
                $this->redireccionar('estudiantes/importarSalon');
                return;
            }

            // Insertar cada estudiante
            $inserted = 0;
            $skipped = 0;
            foreach ($rowsConfirm as $r) {
                // Evitar duplicados por DNI si se proporcionó
                $existe = false;
                if (!empty($r['dni'])) {
                    $busq = $this->estudianteModel->buscar(['dni' => $r['dni']]);
                    if (!empty($busq)) $existe = true;
                }

                if ($existe) {
                    $skipped++;
                    continue;
                }

                $datos = [
                    'nombres' => $r['nombres'],
                    'apellidos' => $r['apellidos'],
                    'dni' => $r['dni'] ?? null,
                    'fecha_nacimiento' => $r['fecha_nacimiento'] ?? null,
                    'direccion' => $r['direccion'] ?? null,
                    'telefono' => $r['telefono'] ?? null,
                    'mencion' => $r['mencion'] ?? null,
                    'estado' => 'activo',
                    'id_salon' => $nuevoSalonId,
                ];
                // Asegurar que siempre se envíe `monto` (no es NULL en la tabla)
                $datos['monto'] = $monto_value;

                try {
                    $res = $this->estudianteModel->insertar($datos);
                    file_put_contents(__DIR__ . '/../storage/logs/import_debug.log', "Inserted student id: " . print_r($res, true) . " datos=" . print_r($datos, true) . "\n", FILE_APPEND);
                    $inserted++;
                } catch (\Exception $e) {
                    file_put_contents(__DIR__ . '/../storage/logs/import_debug.log', "Insert exception: " . $e->getMessage() . " datos=" . print_r($datos, true) . "\n", FILE_APPEND);
                }
            }

            if (isset($tmpPathConfirm) && file_exists($tmpPathConfirm)) @unlink($tmpPathConfirm);
            $this->sesion->setFlash('exito', "Importación completada. Insertados: {$inserted}. Omitidos: {$skipped}.");
            $this->redireccionar('estudiantes');
            return;
        }

        // Mostrar vista previa y botón confirmar
        $data['titulo'] = 'Previsualización de importación';
        $data['rows'] = $rows;
        $data['tmp_path'] = $tmpPath;
        $data['docente_id'] = $docente_id;
        $data['id_seccion'] = $id_seccion;
        $data['monto'] = $monto;
        $this->vista->mostrar('estudiantes/importar_preview', $data);
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('estudiantes');
            return;
        }
        
        $datosEstudiante = [
            'nombres' => $_POST['nombres'],
            'apellidos' => $_POST['apellidos'],
            'dni' => $_POST['dni'] ?? null,
            'mencion' => $_POST['mencion'] ?? null,
            'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? null,
            'direccion' => $_POST['direccion'] ?? null,
            'telefono' => $_POST['telefono'] ?? null,
            'estado' => 'activo'
        ];
        
        // Mapear id_seccion (enviado desde el formulario) a id_salon
        if (!empty($_POST['id_seccion'])) {
            $id_seccion = intval($_POST['id_seccion']);
            // Buscar un salon activo para esa seccion
            $salon = $this->estudianteModel->obtenerSalonPorSeccion($id_seccion);
            if ($salon && !empty($salon['id_salon'])) {
                $datosEstudiante['id_salon'] = $salon['id_salon'];
            } else {
                // Crear un salon placeholder y usar su id
                $nuevoSalonId = $this->estudianteModel->crearSalonPlaceholder($id_seccion);
                if ($nuevoSalonId) $datosEstudiante['id_salon'] = $nuevoSalonId;
            }
        }
        
        $datosUsuario = null;
        if (isset($_POST['crear_cuenta']) && $_POST['crear_cuenta'] == '1') {
            // ✅ CORREGIDO: Ya no necesitas verificar si existe
            $correo = $_POST['correo'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $datosUsuario = [
                'nombre' => $datosEstudiante['nombres'] . ' ' . $datosEstudiante['apellidos'],
                'correo' => $correo,
                'password' => $password,
                'rol' => 'Estudiante',
                'estado' => 'activo'
            ];
        }
        
        $resultado = $this->estudianteModel->crearEstudianteConUsuario($datosEstudiante, $datosUsuario);
        
        if ($resultado) {
            $this->sesion->setFlash('exito', 'Estudiante registrado correctamente.');
            $this->redireccionar('estudiantes/detalle/' . $resultado);
        } else {
            $this->sesion->setFlash('error', 'Error al registrar el estudiante.');
            $this->redireccionar('estudiantes/crear');
        }
    }
    /**
 * Muestra los detalles de un estudiante
 * 
 * @param int $id ID del estudiante
 */
public function detalle($id = null) {
    // Obtener ID de GET si no viene como parámetro
    if (!$id) {
        $id = $_GET['id'] ?? null;
    }

    if (!$id) {
        $this->sesion->setFlash('error', 'ID de estudiante no especificado.');
        $this->redireccionar('estudiantes');
        return;
    }
    
    try {
        $estudiante = $this->estudianteModel->obtenerEstudianteDetalle($id);
        
        if (!$estudiante) {
            $this->sesion->setFlash('error', 'Estudiante no encontrado.');
            $this->redireccionar('estudiantes');
            return;
        }
        
        $data['titulo'] = 'Detalle de Estudiante - ' . $estudiante['nombre_completo'];
        $data['estudiante'] = $estudiante;
        $data['padres'] = $this->estudianteModel->obtenerPadresDeEstudiante($id);
        $data['deudas'] = $this->estudianteModel->obtenerDeudasPendientes($id);
        $data['pagos'] = $this->estudianteModel->obtenerHistorialPagos($id);
        
        $this->vista->mostrar('estudiantes/detalle', $data);
        
    } catch (Exception $e) {
        error_log("Error en EstudianteController::detalle: " . $e->getMessage());
        $this->sesion->setFlash('error', 'Error al cargar los detalles del estudiante.');
        $this->redireccionar('estudiantes');
    }
}

    /**
     * Retorna un JSON con los padres asociados a un estudiante
     */
    public function obtenerPadresJSON() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }

        try {
            $padres = $this->estudianteModel->obtenerPadresDeEstudiante($id);
            header('Content-Type: application/json');
            echo json_encode($padres);
            exit;
        } catch (\Exception $e) {
            error_log('Error en obtenerPadresJSON: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }
    }

/**
 * Muestra el formulario para editar un estudiante
 * 
 * @param int $id ID del estudiante
 */
public function editar($id = null) {
    // Obtener ID de GET si no viene como parámetro
    if (!$id) {
        $id = $_GET['id'] ?? null;
    }
    
    if (!$id) {
        $this->redireccionar('estudiantes');
        return;
    }
    
    $estudiante = $this->estudianteModel->obtenerEstudianteDetalle($id);
    
    if (!$estudiante) {
        $this->sesion->setFlash('error', 'Estudiante no encontrado.');
        $this->redireccionar('estudiantes');
        return;
    }
    
    $data['titulo'] = 'Editar Estudiante';
    $data['estudiante'] = $estudiante;
    $data['salones'] = $this->estudianteModel->obtenerSalonesDisponibles();
    $seccionModel = new SeccionModel();
    $data['secciones'] = $seccionModel->obtenerTodas();
    
    $this->vista->mostrar('estudiantes/editar', $data);
}

    public function actualizar($id = null) {
        // Verificar si es una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) {
            $this->redireccionar('estudiantes');
            return;
        }
        
        $estudiante = $this->estudianteModel->buscarPorId($id);
        
        if (!$estudiante) {
            $this->sesion->setFlash('error', 'Estudiante no encontrado.');
            $this->redireccionar('estudiantes');
            return;
        }
        
        // Recoger datos del formulario
        $datosEstudiante = [
            'nombres' => $_POST['nombres'],
            'apellidos' => $_POST['apellidos'],
            'dni' => $_POST['dni'] ?? null,
            'mencion' => $_POST['mencion'] ?? null,
            'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? null,
            'direccion' => $_POST['direccion'] ?? null,
            'telefono' => $_POST['telefono'] ?? null,
            'estado' => $_POST['estado']
        ];
        
        // Mapear id_seccion (si viene) a id_salon
        if (!empty($_POST['id_seccion'])) {
            $id_seccion = intval($_POST['id_seccion']);
            $salon = $this->estudianteModel->obtenerSalonPorSeccion($id_seccion);
            if ($salon && !empty($salon['id_salon'])) {
                $datosEstudiante['id_salon'] = $salon['id_salon'];
            } else {
                $nuevoSalonId = $this->estudianteModel->crearSalonPlaceholder($id_seccion);
                if ($nuevoSalonId) $datosEstudiante['id_salon'] = $nuevoSalonId;
            }
        }
        
        // Si hay un usuario asociado y se actualizó su correo
        $datosUsuario = null;
        if (!empty($estudiante['id_usuario']) && isset($_POST['correo'])) {
            // ✅ CORREGIDO: Ya no necesitas verificar si existe
            $datosUsuario = [
                'nombre' => $datosEstudiante['nombres'] . ' ' . $datosEstudiante['apellidos'],
                'correo' => $_POST['correo']
            ];
            
            // Si se proporcionó una nueva contraseña
            if (!empty($_POST['password'])) {
                $datosUsuario['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
        }
        
        // Actualizar el estudiante
        $resultado = $this->estudianteModel->actualizarEstudianteConUsuario($id, $datosEstudiante, $datosUsuario);
        
        if ($resultado) {
            $this->sesion->setFlash('exito', 'Estudiante actualizado correctamente.');
            $this->redireccionar('estudiantes/detalle/' . $id);
        } else {
            $this->sesion->setFlash('error', 'Error al actualizar el estudiante.');
            $this->redireccionar('estudiantes/editar/' . $id);
        }
    }
    
    /**
     * Elimina un estudiante
     * 
     * @param int $id ID del estudiante
     */
    public function eliminar($id = null) {
        // Solo administradores pueden eliminar estudiantes
        if (!$this->sesion->tieneRol(['Superadmin', 'Administrador'])) {
            $this->sesion->setFlash('error', 'No tienes permisos para eliminar estudiantes.');
            $this->redireccionar('estudiantes');
            return;
        }
        // Debe ser una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('estudiantes');
            return;
        }

        // Obtener el id desde el parámetro de la ruta o desde POST (formulario modal)
        if (!$id) {
            $id = $_POST['id_estudiante'] ?? null;
        }

        if (!$id) {
            $this->sesion->setFlash('error', 'ID de estudiante no especificado para eliminar.');
            $this->redireccionar('estudiantes');
            return;
        }

        $resultado = $this->estudianteModel->eliminar($id);
        
        if ($resultado) {
            $this->sesion->setFlash('exito', 'Estudiante eliminado correctamente.');
        } else {
            $this->sesion->setFlash('error', 'Error al eliminar el estudiante.');
        }
        
        $this->redireccionar('estudiantes');
    }
    
    /**
     * Asocia un estudiante a un padre
     */
    public function asociarPadre() {
        // Verificar si es una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('estudiantes');
            return;
        }
        
        $idEstudiante = $_POST['id_estudiante'] ?? null;
        $idPadre = $_POST['id_padre'] ?? null;
        $parentesco = $_POST['parentesco'] ?? 'Padre/Madre';
        
        if (!$idEstudiante || !$idPadre) {
            $this->sesion->setFlash('error', 'Faltan datos para asociar al padre.');
            $this->redireccionar('estudiantes/detalle/' . $idEstudiante);
            return;
        }
        
        $resultado = $this->estudianteModel->asociarPadre($idEstudiante, $idPadre, $parentesco);
        
        if ($resultado) {
            $this->sesion->setFlash('exito', 'Padre asociado correctamente.');
        } else {
            $this->sesion->setFlash('error', 'Error al asociar al padre.');
        }
        
        $this->redireccionar('estudiantes/detalle/' . $idEstudiante);
    }
    
    /**
     * Desasocia un estudiante de un padre
     */
    public function desasociarPadre() {
        // Verificar si es una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('estudiantes');
            return;
        }
        
        $idEstudiante = $_POST['id_estudiante'] ?? null;
        $idPadre = $_POST['id_padre'] ?? null;
        
        if (!$idEstudiante || !$idPadre) {
            $this->sesion->setFlash('error', 'Faltan datos para desasociar al padre.');
            $this->redireccionar('estudiantes/detalle/' . $idEstudiante);
            return;
        }
        
        $resultado = $this->estudianteModel->desasociarPadre($idEstudiante, $idPadre);
        
        if ($resultado) {
            $this->sesion->setFlash('exito', 'Padre desasociado correctamente.');
        } else {
            $this->sesion->setFlash('error', 'Error al desasociar al padre.');
        }
        
        $this->redireccionar('estudiantes/detalle/' . $idEstudiante);
    }
    
    /**
     * Busca estudiantes según un término
     */
    public function buscar() {
        // Verificar si es una solicitud GET
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->redireccionar('estudiantes');
            return;
        }
        
        $termino = $_GET['termino'] ?? '';
        
        if (empty($termino)) {
            $this->redireccionar('estudiantes');
            return;
        }
        
        $data['titulo'] = 'Resultados de búsqueda: ' . $termino;
        $data['termino'] = $termino;
        $data['estudiantes'] = $this->estudianteModel->buscarEstudiantes($termino);
        
        $this->vista->mostrar('estudiantes/listado', $data);
    }
}
