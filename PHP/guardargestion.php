<?php
require_once 'horario.php';
session_start();

if (!isset($_SESSION['id'])) {
  header('Location: index.php');
  exit;
}

$id_tabla = isset($_GET['id_tabla']) ? $_GET['id_tabla'] : $_SESSION['id_tabla'];
$identificador = isset($_GET['identificador']) ? $_GET['identificador'] : '';

date_default_timezone_set('America/Lima');

// Incluye el archivo de configuración para obtener la conexión a la base de datos
include('config.php');

// Verificar la conexión
if ($mysqli->connect_error) {
  die('Error de conexión: ' . $mysqli->connect_error);
}

if (!validarHorario()) {
  $response = ['success' => false, 'message' => 'Gestión no permitida fuera del horario.'];
  echo json_encode($response);
  exit();
}

// Obtener los datos del formulario
$fecha = date('Y-m-d H:i:s'); // Fecha y hora actual
$idaccion   = $_POST['idaccion'];
$idefecto   = $_POST['idefecto'];
$horaVisita = $_POST['hora_visita'] . ':00';

// Obtener los valores seleccionados, si están disponibles
$idmotivo   = !empty($_POST['idmotivo'])   ? $_POST['idmotivo']   : 0;
$idcontacto = !empty($_POST['idcontacto']) ? $_POST['idcontacto'] : 0;

$observacion = $_POST['observacion'];
$iddireccion = !empty($_POST['iddireccion']) ? $_POST['iddireccion'] : 0;
$idpersonal  = $_SESSION['id'];
$nomcontacto = $_POST['nomcontacto'];
$pisos       = $_POST['pisos'];
$puerta      = $_POST['puerta'];
$fachada     = $_POST['fachada'];

$fecha_promesa = !empty($_POST['fecha_promesa']) ? "'{$_POST['fecha_promesa']}'" : "NULL";
$monto_promesa = !empty($_POST['monto_promesa']) ? $_POST['monto_promesa'] : "NULL";
if ($monto_promesa != "NULL") {
  $monto_promesa = "'$monto_promesa'";
}
$latitud  = $_POST['latitud'];   // Obtener de la ubicación geográfica
$longitud = $_POST['longitud'];  // Obtener de la ubicación geográfica
$txt      = $_POST['txt'];       // Obtener de la ubicación geográfica

$idefecto = intval($idefecto);

// Validaciones básicas
if (!isset($idefecto) || $idefecto === null || empty($idefecto) || !isset($iddireccion) || $iddireccion === null || $iddireccion == 0 || empty($iddireccion)) {
  $response1 = array();
  $response1['success'] = false;
  $response1['message'] = "Error en registro, compruebe conexión, actualice la página e ingrese gestión nuevamente" . $mysqli->error;
  header('Content-Type: application/json');
  echo json_encode($response1);
  exit;
}

if ($horaVisita < "07:00:00" || $horaVisita > "20:00:00") {
  $response = array(
    "success" => false,
    "message" => "La hora de visita debe estar entre las 07:00 y las 20:00"
  );
  header('Content-Type: application/json');
  echo json_encode($response);
  exit;
}

/* =========================
   Subida de imágenes (opcional)
   ========================= */

$defaultBase = '/var/www/html/fotos';
if (!empty($_SERVER['DOCUMENT_ROOT'])) {
  $defaultBase = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/fotos';
}
$UPLOAD_BASE = rtrim(getenv('GEOCAMPO_UPLOAD_DIR') ?: $defaultBase, '/');

// La carpeta por registro (antes: substr($id_tabla, 2)) y sanitizada
$baseCarpeta  = substr($id_tabla ?? '', 2);
$carpeta_ruta = preg_replace('/[^A-Za-z0-9_\-]/', '', $baseCarpeta);
if ($carpeta_ruta === '') {
  $carpeta_ruta = 'default';
}

$destDir = $UPLOAD_BASE . '/' . $carpeta_ruta;

// Crear carpeta si no existe
if (!is_dir($destDir)) {
  if (!mkdir($destDir, 0775, true) && !is_dir($destDir)) {
    error_log("No se pudo crear directorio: $destDir");
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No se pudo preparar carpeta de imágenes.']);
    exit;
  }
}

// Verifica escritura por si el volumen/permisos fallan
if (!is_writable($destDir)) {
  error_log("Directorio no escribible: $destDir");
  header('Content-Type: application/json');
  echo json_encode(['success' => false, 'message' => 'La carpeta de imágenes no es escribible.']);
  exit;
}

// Límites y validaciones
$MAX_BYTES  = 10 * 1024 * 1024; // 10MB por archivo
$permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
$finfo = new finfo(FILEINFO_MIME_TYPE);

// Sanitiza identificador para nombre de archivo
$identificadorSafe = preg_replace('/[^A-Za-z0-9_\-]/', '', $identificador ?? '');

/**
 * Sube una imagen si fue adjuntada.
 * Devuelve el nombre final (string) o null si no hubo archivo o hubo error.
 */
function subirImagen($campo, $prefix, $identificadorSafe, $destDir, $permitidos, $MAX_BYTES, $finfo)
{
  if (empty($_FILES[$campo]) || $_FILES[$campo]['error'] === UPLOAD_ERR_NO_FILE) {
    return null; // No se adjuntó
  }

  $err = $_FILES[$campo]['error'];
  if ($err !== UPLOAD_ERR_OK) {
    error_log("Error en upload $campo: code=$err");
    return null;
  }

  if ($_FILES[$campo]['size'] > $MAX_BYTES) {
    error_log("Archivo demasiado grande en $campo: " . $_FILES[$campo]['size']);
    return null;
  }

  $mime = $finfo->file($_FILES[$campo]['tmp_name']);
  if (!isset($permitidos[$mime])) {
    error_log("MIME no permitido en $campo: $mime");
    return null;
  }

  $ext = $permitidos[$mime];
  $timestamp = date('YmdHis');
  $rand = bin2hex(random_bytes(4));
  $finalName = sprintf('%s%s_%s_%s.%s', $prefix, $identificadorSafe, $timestamp, $rand, $ext);
  $destPath = $destDir . '/' . $finalName;

  if (!move_uploaded_file($_FILES[$campo]['tmp_name'], $destPath)) {
    error_log("Error al mover $campo a $destPath");
    return null;
  }
  return $finalName;
}

// Sube hasta 3 imágenes (solo si vienen)
$imagen1 = subirImagen('imagen1', 'Imagen1', $identificadorSafe, $destDir, $permitidos, $MAX_BYTES, $finfo);
$imagen2 = subirImagen('imagen2', 'Imagen2', $identificadorSafe, $destDir, $permitidos, $MAX_BYTES, $finfo);
$imagen3 = subirImagen('imagen3', 'Imagen3', $identificadorSafe, $destDir, $permitidos, $MAX_BYTES, $finfo);

// Preparar valores para SQL (NULL sin comillas si no hay archivo)
$img1Sql = is_null($imagen1) ? "NULL" : "'" . $mysqli->real_escape_string($imagen1) . "'";
$img2Sql = is_null($imagen2) ? "NULL" : "'" . $mysqli->real_escape_string($imagen2) . "'";
$img3Sql = is_null($imagen3) ? "NULL" : "'" . $mysqli->real_escape_string($imagen3) . "'";

// Escapar identificador por seguridad
$identificador_sql = $mysqli->real_escape_string($identificador);

/* =========================
   Llamada al procedimiento almacenado
   ========================= */

$idcartera = $_POST['id_cartera'];

$sql = "CALL SP_InsertarGEOCAMPO_PRUEBA(
    '$fecha',
    '$identificador_sql',
    '$id_tabla',
    '$idefecto',
    '$idmotivo',
    '$idcontacto',
    '$observacion',
    '$iddireccion',
    '$idpersonal',
    '$nomcontacto',
    '$pisos',
    '$puerta',
    '$fachada',
    $fecha_promesa,
    $monto_promesa,
    '$idcartera',
    '$latitud',
    '$longitud',
    '$txt',
    $img1Sql,
    $img2Sql,
    $img3Sql,
    '$horaVisita'
)";

$response = array();

if ($mysqli->query($sql) === TRUE) {
  $response['success'] = true;
  $response['message'] = "Gestión Ingresada correctamente.";
} else {
  $response['success'] = false;
  $response['message'] = "Error al insertar registro: " . $mysqli->error;
}

header('Content-Type: application/json');
echo json_encode($response);
exit;

// Cerrar la conexión
$mysqli->close();
