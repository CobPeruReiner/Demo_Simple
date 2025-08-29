<?php

declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');
date_default_timezone_set('America/Lima');

ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? '1' : '0');
ini_set('session.cookie_domain', '');

if (function_exists('ob_start')) {
    ob_start();
}
session_name('geocampo');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

require_once 'config.php';
require_once 'horario.php';

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $usuario = $_POST['usuario'];
//     $contrasena = $_POST['contrasena'];

//     // Verificar si está en el horario permitido
//     if (!validarHorario()) {
//         $response = ['success' => false, 'message' => 'Acceso bloqueado, fuera de horario.'];
//         echo json_encode($response);
//         exit();
//     }

//     // Llama al procedimiento almacenado
//     $query = "CALL GetLogin2(?, ?)";
//     $stmt = $mysqli->prepare($query);
//     $stmt->bind_param('ss', $usuario, $contrasena);
//     $stmt->execute();
//     $stmt->bind_result($id, $nombreCompleto, $doc, $cartera);
//     $stmt->fetch();
//     $stmt->close();

//     if ($id) {
//         $response = ['success' => true];
//         session_start();
//         $_SESSION['id'] = $id;
//         $_SESSION['nombreCompleto'] = $nombreCompleto;
//         $_SESSION['doc'] = $doc;
//         $_SESSION['cartera'] = $cartera;
//     } else {
//         $response = ['success' => false, 'message' => 'Credenciales incorrectas'];
//     }

//     echo json_encode($response);
// }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (function_exists('ob_get_length') && ob_get_length()) {
        ob_clean();
    }
    echo json_encode(['success' => false, 'message' => 'Método no permitido'], JSON_UNESCAPED_UNICODE);
    exit;
}

$usuario    = $_POST['usuario'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';

if (!validarHorario()) {
    if (function_exists('ob_get_length') && ob_get_length()) {
        ob_clean();
    }
    echo json_encode(['success' => false, 'message' => 'Acceso bloqueado, fuera de horario.'], JSON_UNESCAPED_UNICODE);
    exit;
}

/* --- Autenticación --- */
$id = $user = $cargo = $pass = $nombres = $apellidos = $doc = $cartera = null;

$stmt = $mysqli->prepare(
    "SELECT IDPERSONAL, USUARIO, CARGO, PASSWORD, NOMBRES, APELLIDOS, DOC, id_cartera
     FROM personal
     WHERE USUARIO = ? AND PASSWORD = MD5(?) AND IDESTADO = 1"
);

if (!$stmt) {
    error_log('Fallo prepare login: ' . $mysqli->error);
    if (function_exists('ob_get_length') && ob_get_length()) {
        ob_clean();
    }
    echo json_encode(['success' => false, 'message' => 'Error de servidor.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt->bind_param('ss', $usuario, $contrasena);
$stmt->execute();
$stmt->bind_result($id, $user, $cargo, $pass, $nombres, $apellidos, $doc, $cartera);
$stmt->fetch();
$stmt->close();

if ($id) {
    /* Obtener token anterior (opcional, para saber si hubo reemplazo) */
    $tokenAnterior = null;

    if ($stmtToken = $mysqli->prepare("SELECT api_token FROM personal WHERE IDPERSONAL = ?")) {
        $stmtToken->bind_param('i', $id);
        $stmtToken->execute();
        $result = $stmtToken->get_result();
        if ($row = $result->fetch_assoc()) {
            $tokenAnterior = $row['api_token'];
        }
        $stmtToken->close();
    }

    /* Logs de depuración al log (no al output) */
    error_log("======= DEPURACIÓN DE LOGIN =======");
    error_log("Usuario: $usuario");
    error_log("Token anterior: " . var_export($tokenAnterior, true));

    /* Generar y guardar nuevo token */
    $nuevoToken = bin2hex(random_bytes(16));
    error_log("Nuevo token generado: $nuevoToken");

    if ($stmtUpdate = $mysqli->prepare("UPDATE personal SET api_token = ? WHERE IDPERSONAL = ?")) {
        $stmtUpdate->bind_param('si', $nuevoToken, $id);
        $stmtUpdate->execute();
        $stmtUpdate->close();
    } else {
        error_log('Fallo update token: ' . $mysqli->error);
    }

    /* Variables de sesión */
    $_SESSION['id']             = $id;
    $_SESSION['nombreCompleto'] = "$apellidos, $nombres";
    $_SESSION['doc']            = $doc;
    $_SESSION['cartera']        = $cartera;
    $_SESSION['token']          = $nuevoToken;

    $reemplazada = !empty($tokenAnterior);
    error_log("¿Sesión reemplazada?: " . ($reemplazada ? 'Sí' : 'No'));
    error_log("===================================");

    /* Cerrar sesión para escribir y liberar lock ANTES del siguiente request */
    session_write_close();

    /* Responder JSON puro */
    if (function_exists('ob_get_length') && ob_get_length()) {
        ob_clean();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Inicio de sesión exitoso.',
        'sesionReemplazada' => $reemplazada
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/* Credenciales inválidas */
if (function_exists('ob_get_length') && ob_get_length()) {
    ob_clean();
}
echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas'], JSON_UNESCAPED_UNICODE);
exit;
