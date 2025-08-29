<?php
require_once 'config.php';

if (isset($_GET['idPersonal']) && isset($_GET['fecha']) && isset($_GET['idCartera'])) {
    $idPersonal = $_GET['idPersonal'];
    $fecha = $_GET['fecha'];
    $idCartera = $_GET['idCartera'];

    if (isset($_GET['fecha2'])) {
        $fecha2 = $_GET['fecha2'];
        $query = "CALL GetCampoGestiones3(?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('iiss', $idPersonal, $idCartera, $fecha, $fecha2);
    } else {
        $query = "CALL GetCampoGestiones3(?, ?, ?, NULL)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('iis', $idPersonal, $idCartera, $fecha);
    }

    // Llamar al procedimiento almacenado "GetAsignacion" con el ID del usuario
    // $query = "CALL GetCampoGestiones(?, ?, NULL)";

    $stmt->execute();
    $result = $stmt->get_result();

    $stmt->close();
} else {
    echo 'Id usuario no especificado.';
}

// Almacenar los datos en una variable
$rows = [];

// Iniciar el buffer de salida
ob_start();

// Muestra los datos en una tabla principal
if ($result && $result->num_rows > 0) {
    echo '<table class="table table-bordered table-striped table-hover">';
    echo '<thead class="thead-dark">';
    echo '<tr>';
    echo '<th class="text-center">Fecha</th>';
    echo '<th class="text-center">Hora</th>';
    echo '<th class="text-center">Identificador</th>';
    echo '<th class="text-center">Efecto</th>';
    echo '<th class="text-center">Observación</th>';
    // echo '<th class="text-center">MOTIVO</th>';
    echo '<th class="text-center">ACCION</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    while ($row = $result->fetch_assoc()) {
        $fechaFormateada = date("d/m/Y H:i:s", strtotime($row['FECHA']));
        $row['FECHA'] = $fechaFormateada;
        $rows[] = $row;  // Almacena la fila en el array
        echo '<tr class="p-0 m-0">';
        echo '<td class="p-1 m-0">' . explode(' ', $row['FECHA'])[0] . '</td>';
        echo '<td class="p-1 m-0">' . explode(' ', $row['FECHA'])[1] . '</td>';
        // echo '<td class="p-1 m-0">' . explode(' ', $fechaFormateada)[0] . '</td>';
        // echo '<td class="p-1 m-0">' . explode(' ', $fechaFormateada)[1] . '</td>';
        echo '<td class="p-1 m-0">' . $row['IDENTIFICADOR'] . '</td>';
        // echo '<td class="p-1 m-0">' . $row ['IDEFECTO'] . '</td>';
        echo '<td class="p-1 m-0">' . $row['EFECTO'] . '</td>';
        echo '<td class="p-1 m-0">' . $row['OBSERVACION'] . '</td>';
        // echo '<td class="p-1 m-0">' . $row ['IDMOTIVO'] . '</td>';
        // echo '<td class="p-1 m-0">' . $row ['MOTIVO'] . '</td>';
        echo "<td class='hide-html-element'}>" . $row['latitud'] . '</td>';
        echo "<td class='hide-html-element'}>" . $row['longitud'] . '</td>';
        // echo '<td class="text-center p-0 m-0"><button type="button" id="btn-test" class="btn btn-primary btn-test h6 text-sm p-1" data-row="' . htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') . '"> Más </button></td>';
        echo '<td role="button" class="text-center p-1 m-0 btn-test" data-row="' . htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') . '"><i class="fa-solid fa-eye"></i></td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
} else {
    echo "<p class='text-center'>No se encontraron resultados.</p>";
}

// Obtener el contenido del buffer de salida y limpiarlo
$html = ob_get_clean();

$response = [
    'html' => $html,           // Obtener el HTML generado
    'result' => $rows             // Inicializar el array result
];

// Enviar la respuesta como JSON
echo json_encode($response);

// Cerrar la conexión a la base de datos
if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close(); // Cierra la conexión a la base de datos
}
