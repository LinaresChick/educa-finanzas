<?php
namespace Controllers;
// DEBUG: Verificar que el archivo se carga sin errores
error_log("=== EstudianteController CARGADO ===");
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../core/Sesion.php';
require_once __DIR__ . '/../core/Vista.php';
require_once __DIR__ . '/../vendor/autoload.php';
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

        error_log("DEBUG GET: " . print_r($_GET, true));
error_log("DEBUG POST: " . print_r($_POST, true));

        parent::__construct();
        $this->estudianteModel = new EstudianteModel();
        $this->usuarioModel = new UsuarioModel();

        error_log("EstudianteController initialized.");
        
        // Verificación normal de roles (soportar nuevos roles: Administrador, Contador, Director, Secretario)
        $rolesPermitidos = ['Superadmin', 'Administrador', 'Colaborador', 'Contador', 'Director', 'Secretario'];
if (!$this->sesion->tieneRol($rolesPermitidos)) {

    // Permitir acceso SOLO a procesarImportacion e importarSalon
    $accion = $_REQUEST['action'] ?? '';

if (in_array($accion, ['procesarImportacion', 'importarSalon'])) {
    return; // permitir SIN sesión
}


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
        error_log("DEBUG: procesarImportacion INICIADA");
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("DEBUG: REQUEST_METHOD no es POST");
            $this->redireccionar('estudiantes');
            return;
        }

        $docente_id = $_POST['docente_id'] ?? null;
        $id_seccion = $_POST['id_seccion'] ?? null;
        $monto = $_POST['monto'] ?? null;

        error_log("DEBUG: POST data - docente_id=$docente_id, id_seccion=$id_seccion, monto=$monto");

        // PASO 2: Si viene confirmación, guardar estudiantes
        if (!empty($_POST['confirm']) && $_POST['confirm'] == '1') {
            error_log("DEBUG: Confirmación detectada, llamando confirmarImportacion");
            $this->confirmarImportacion();
            return;
        }

        // PASO 1: Validar y mostrar preview
        if (empty($docente_id) || empty($id_seccion)) {
            error_log("DEBUG: Falta docente_id o id_seccion");
            $this->sesion->setFlash('error', 'Debe seleccionar docente y sección.');
            $this->redireccionar('estudiantes/importarSalon');
            return;
        }

        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            error_log("DEBUG: Archivo no válido. FILES: " . print_r($_FILES, true));
            $this->sesion->setFlash('error', 'Debe subir un archivo válido.');
            $this->redireccionar('estudiantes/importarSalon');
            return;
        }

        // Guardar archivo en temp
        $uploaded = $_FILES['archivo'];
        $tmpDir = __DIR__ . '/../temp';
        if (!is_dir($tmpDir)) @mkdir($tmpDir, 0755, true);
        
        $tmpPath = $tmpDir . '/' . time() . '_' . basename($uploaded['name']);
        if (!move_uploaded_file($uploaded['tmp_name'], $tmpPath)) {
            error_log("DEBUG: No se pudo mover el archivo a: $tmpPath");
            $this->sesion->setFlash('error', 'No se pudo mover el archivo subido.');
            $this->redireccionar('estudiantes/importarSalon');
            return;
        }
        
        error_log("DEBUG: Archivo movido a: $tmpPath");

        // Parsear archivo (CSV o Excel)
        $ext = strtolower(pathinfo($tmpPath, PATHINFO_EXTENSION));
        error_log("DEBUG: Extensión detectada: $ext");
        
        $rows = [];
        $allowed = ['nombres','apellidos','dni','fecha_nacimiento','direccion','telefono','mencion'];

        if (in_array($ext, ['xlsx','xls','xlsm','xlsb','ods'])) {
            error_log("DEBUG: Procesando como archivo Excel");
            
            if (!class_exists('ZipArchive')) {
                @unlink($tmpPath);
                error_log("DEBUG: ZipArchive no está habilitado");
                $this->sesion->setFlash('error', 'Debe habilitar la extensión ZipArchive en PHP (php.ini) para leer archivos Excel.');
                $this->redireccionar('estudiantes/importarSalon');
                return;
            }
            if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
                @unlink($tmpPath);
                error_log("DEBUG: PhpSpreadsheet no está instalado");
                $this->sesion->setFlash('error', 'Para importar archivos Excel, instale phpoffice/phpspreadsheet.');
                $this->redireccionar('estudiantes/importarSalon');
                return;
            }

            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmpPath);
                $sheet = $spreadsheet->getActiveSheet();
                $rowsData = $sheet->toArray(null, true, true, true);
                
                error_log("DEBUG: Datos Excel leídos. Filas totales: " . count($rowsData));
                error_log("DEBUG: Datos Excel (primeras 3 filas): " . print_r(array_slice($rowsData, 0, 3), true));
                
                // Filtrar filas vacías
                $rowsData = array_filter($rowsData, function($row) {
                    return !empty(array_filter($row, function($cell) {
                        return $cell !== null && $cell !== '';
                    }));
                });
                
                error_log("DEBUG: Después de filtrar filas vacías: " . count($rowsData));
                
                if (empty($rowsData) || count($rowsData) < 2) {
                    @unlink($tmpPath);
                    error_log("DEBUG: No hay suficientes filas en el Excel");
                    $this->sesion->setFlash('error', 'El archivo Excel está vacío o no contiene filas válidas.');
                    $this->redireccionar('estudiantes/importarSalon');
                    return;
                }

                // Obtener encabezados de la primera fila
                $rowsArray = array_values($rowsData);
                $header = array_map(function($c){ 
                    return trim(strtolower(preg_replace('/\s+/', '', $c))); 
                }, array_values($rowsArray[0]));
                
                error_log("DEBUG: Header normalizado: " . print_r($header, true));
                
                // build rows starting from row 2
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
                
                error_log("DEBUG: Filas procesadas del Excel: " . count($rows));
                
            } catch (\Exception $e) {
                @unlink($tmpPath);
                error_log("DEBUG: Excepción Excel: " . $e->getMessage());
                $this->sesion->setFlash('error', 'Error al leer el archivo Excel: ' . $e->getMessage());
                $this->redireccionar('estudiantes/importarSalon');
                return;
            }

        } else {
            error_log("DEBUG: Procesando como archivo CSV");
            
            // CSV parsing
            $fh = fopen($tmpPath, 'r');
            if (!$fh) {
                error_log("DEBUG: No se puede abrir el archivo CSV");
                $this->sesion->setFlash('error', 'No se puede leer el archivo subido.');
                $this->redireccionar('estudiantes/importarSalon');
                return;
            }

            $header = fgetcsv($fh);
            error_log("DEBUG: Header CSV: " . print_r($header, true));
            
            if (!$header) {
                fclose($fh);
                error_log("DEBUG: Header CSV vacío");
                $this->sesion->setFlash('error', 'El archivo CSV está vacío o tiene formato incorrecto.');
                $this->redireccionar('estudiantes/importarSalon');
                return;
            }

            // Normalizar encabezados
            $cols = array_map(function($c){ return trim(strtolower($c)); }, $header);
            error_log("DEBUG: Cols normalizadas: " . print_r($cols, true));
            
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
            
            error_log("DEBUG: Filas procesadas del CSV: " . count($rows));
        }

        error_log("DEBUG: Total de filas válidas encontradas: " . count($rows));
        
        if (empty($rows)) {
            @unlink($tmpPath);
            error_log("DEBUG: No hay filas válidas para mostrar");
            $this->sesion->setFlash('error', 'No se encontraron filas válidas (se requieren "nombres" y "apellidos").');
            $this->redireccionar('estudiantes/importarSalon');
            return;
        }

        // Mostrar vista previa
        error_log("DEBUG: Mostrando vista previa con " . count($rows) . " filas");
        $data['titulo'] = 'Previsualización de importación';
        $data['rows'] = $rows;
        $data['tmp_path'] = $tmpPath;
        $data['docente_id'] = $docente_id;
        $data['id_seccion'] = $id_seccion;
        $data['monto'] = $monto;
        $this->vista->mostrar('estudiantes/importar_preview', $data);
    }

    /**
     * Confirmar y guardar la importación de estudiantes
     */
    private function confirmarImportacion() {
        $docente_id = $_POST['docente_id'] ?? null;
        $id_seccion = $_POST['id_seccion'] ?? null;
        $monto = $_POST['monto'] ?? null;
        $tmpPathConfirm = $_POST['tmp_path'] ?? null;

        if (!$tmpPathConfirm || !file_exists($tmpPathConfirm)) {
            $this->sesion->setFlash('error', 'El archivo temporal no existe.');
            $this->redireccionar('estudiantes/importarSalon');
            return;
        }

        // Parsear archivo nuevamente desde el archivo temporal
        $ext = strtolower(pathinfo($tmpPathConfirm, PATHINFO_EXTENSION));
        $rowsConfirm = [];
        $allowed = ['nombres','apellidos','dni','fecha_nacimiento','direccion','telefono','mencion'];

        if (in_array($ext, ['xlsx','xls','xlsm','xlsb','ods'])) {
            if (!class_exists('ZipArchive')) {
                @unlink($tmpPathConfirm);
                $this->sesion->setFlash('error', 'Debe habilitar la extensión ZipArchive en PHP (php.ini) para leer archivos Excel.');
                $this->redireccionar('estudiantes/importarSalon');
                return;
            }
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmpPathConfirm);
                $sheet = $spreadsheet->getActiveSheet();
                $rowsData = $sheet->toArray(null, true, true, true);
                
                // Filtrar filas vacías
                $rowsData = array_filter($rowsData, function($row) {
                    return !empty(array_filter($row, function($cell) {
                        return $cell !== null && $cell !== '';
                    }));
                });
                
                $rowsArray = array_values($rowsData);
                $header = array_map(function($c){ 
                    return trim(strtolower(preg_replace('/\s+/', '', $c))); 
                }, array_values($rowsArray[0]));
                
                for ($i = 1; $i < count($rowsArray); $i++) {
                    $vals = array_values($rowsArray[$i]);
                    $row = [];
                    foreach ($header as $j => $col) {
                        if (in_array($col, $allowed)) {
                            $row[$col] = isset($vals[$j]) ? trim($vals[$j]) : '';
                        }
                    }
                    if (!empty($row['nombres']) && !empty($row['apellidos'])) $rowsConfirm[] = $row;
                }
            } catch (\Exception $e) {
                @unlink($tmpPathConfirm);
                $this->sesion->setFlash('error', 'Error al procesar archivo Excel.');
                $this->redireccionar('estudiantes/importarSalon');
                return;
            }
        } else {
            $fh = fopen($tmpPathConfirm, 'r');
            if (!$fh) {
                @unlink($tmpPathConfirm);
                $this->sesion->setFlash('error', 'No se puede leer el archivo temporal.');
                $this->redireccionar('estudiantes/importarSalon');
                return;
            }
            $header = fgetcsv($fh);
            $cols = array_map(function($c){ return trim(strtolower($c)); }, $header ?: []);
            while (($data = fgetcsv($fh)) !== false) {
                $row = [];
                foreach ($cols as $i => $col) {
                    if (in_array($col, $allowed)) {
                        $row[$col] = isset($data[$i]) ? trim($data[$i]) : '';
                    }
                }
                if (!empty($row['nombres']) && !empty($row['apellidos'])) {
                    $rowsConfirm[] = $row;
                }
            }
            fclose($fh);
        }

        if (empty($rowsConfirm)) {
            @unlink($tmpPathConfirm);
            $this->sesion->setFlash('error', 'No hay datos para importar.');
            $this->redireccionar('estudiantes/importarSalon');
            return;
        }

        // Preparar monto
        $monto_value = ($monto === null || $monto === '') ? 0.00 : floatval(str_replace(',', '.', $monto));

        // Crear salón con docente
        $cupo = count($rowsConfirm);
        try {
            $nuevoSalonId = $this->estudianteModel->crearSalonConDocente(
                intval($id_seccion),
                intval($docente_id),
                $cupo
            );
        } catch (\Exception $e) {
            @unlink($tmpPathConfirm);
            $this->sesion->setFlash('error', 'Error al crear el salón: ' . $e->getMessage());
            $this->redireccionar('estudiantes/importarSalon');
            return;
        }

        if (!$nuevoSalonId) {
            @unlink($tmpPathConfirm);
            $this->sesion->setFlash('error', 'No se pudo crear el salón.');
            $this->redireccionar('estudiantes/importarSalon');
            return;
        }

        // Insertar cada estudiante
        $inserted = 0;
        $errors = [];
        foreach ($rowsConfirm as $r) {
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
                'monto' => $monto_value
            ];

            try {
                $this->estudianteModel->insertar($datos);
                $inserted++;
            } catch (\Exception $e) {
                error_log("Error insertando estudiante: " . $e->getMessage());
                $errors[] = "No se guardó '" . ($r['nombres'] ?? '') . " " . ($r['apellidos'] ?? '') . "': " . $e->getMessage();
            }
        }

        @unlink($tmpPathConfirm);
        error_log("DEBUG confirmarImportacion: inserted={$inserted}, errores=" . count($errors) . ", total=" . count($rowsConfirm));
        
        if ($inserted > 0) {
            $msg = "✅ Importación exitosa: {$inserted} estudiante(s) guardado(s).";
            if (!empty($errors)) {
                $msg .= " " . count($errors) . " no se guardaron. Ejemplo: " . $errors[0];
            }
            $this->sesion->setFlash('exito', $msg);
        } elseif (!empty($errors)) {
            $detalle = implode(' | ', array_slice($errors, 0, 3));
            $this->sesion->setFlash('error', "❌ No se insertó ningún estudiante. Motivos: " . $detalle);
        } else {
            $this->sesion->setFlash('error', '❌ No se pudo importar ningún estudiante.');
        }
        
        $this->redireccionar('estudiantes');
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
        if (!$this->sesion->tieneRol(['Superadmin', 'Administrador', 'Director', 'Secretario'])) {
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
