<?php

session_name('geocampo');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


// Obtén los parámetros id_tabla e identificador
$id_tabla = isset($_POST['id_tabla']) ? $_POST['id_tabla'] : $_SESSION['id_tabla'];
$dni = isset($_POST['dni']) ? $_POST['dni'] : '';
$id_cartera = isset($_POST['idCartera']) ? $_POST['idCartera'] : 0;
// Agregando variable global dni cliente (new)
// session_start();

// =========================================================
$id_usuario = isset($_SESSION['id']) ? $_SESSION['id'] : 0;
// =========================================================

$_SESSION['dni'] = $dni;

// Incluye el archivo de configuración para obtener la conexión a la base de datos
include('config.php');

// Verificar la conexión (esto no es necesario si config.php ya lo hace)
if ($mysqli->connect_error) {
    die('Error de conexión: ' . $mysqli->connect_error);
}

// Preparar la llamada al procedimiento almacenado
if ($id_tabla == 'C_PICHINCHA_DINERS_REFINANCIADOS' || $id_tabla == 'C_PICHINCHA_DINERS_NO_REFINANCIADOS' || $id_tabla == 'C_EFECTIVA_VENTA') {
    $stmt = $mysqli->prepare("CALL GetCuentasGeneral(?,?)");
    $stmt->bind_param('ss', $id_tabla, $dni);
} else if ($id_tabla == 'C_FINANCIERA_EFECTIVA_CAMPO') {
    $stmt = $mysqli->prepare("CALL GetCuentasFECampo(?)");
    $stmt->bind_param('s', $dni);
} else {
    $stmt = $mysqli->prepare("CALL GetCuentas2(?, ?)");
    $stmt->bind_param('ss', $id_tabla, $dni);
}
// $stmt = $mysqli->prepare("CALL GetCuentas2(?, ?)");

// Ejecutar el procedimiento almacenado
$stmt->execute();

// Obtener el resultado de la consulta
$result = $stmt->get_result();

// Cerrar el statement
$stmt->close();

// Muestra los datos en una tabla principal
if ($result && $result->num_rows > 0) {
    echo '<table class="table table-bordered table-striped tabla-principal">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>ID</th>';
    echo '<th>PRODUCTO</th>';
    echo '<th>OK</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    while ($row = $result->fetch_assoc()) {


        echo '<tr>';
        if ($id_tabla == 'C_PICHINCHA_DINERS_REFINANCIADOS' || $id_tabla == 'C_PICHINCHA_DINERS_NO_REFINANCIADOS' || $id_tabla == 'C_EFECTIVA_VENTA') {
            $row = array_change_key_case($row, CASE_LOWER);
            echo '<td>' . $row['identificador'] . '</td>';
            echo '<td>' . $row['producto'] . '</td>';
        } else {
            echo '<td>' . $row['identificador'] . '</td>';
            echo '<td>' . $row['PRODUCTO'] . '</td>';
        }
        echo '<td><button class="btn btn-info ver-detalles-btn" data-id="' . $row['identificador'] . '">OK</button></td>';
        echo '</tr>';

        // Agregar una fila de detalles debajo de la estructura inicial
        echo '<tr class="detalle-cuenta" style="display: none;">';
        echo '<td colspan="3" class="detalle-celda">';

        echo '<table class="table table-bordered">';
        echo '<tbody>';

        // PICHINCHAS
        if ($id_tabla == 'C_PICHINCHA_DINERS_REFINANCIADOS' || $id_tabla == 'C_PICHINCHA_DINERS_NO_REFINANCIADOS' || $id_tabla == 'C_EFECTIVA_VENTA') {
            // Normalizar claves en minúsculas

            // Definir columnas a excluir
            $columnas_excluidas =
                [
                    'id',
                    'numero_cuenta',
                    'producto',
                    'subproducto',
                    'moneda',
                    'saldoporpagar',
                    'montocampana',
                    'pordescuento',
                    'nomcampana',
                    'anocastigo',
                    'fechacastigo',
                    'rango',
                    'fechaultpa',
                    'agencia',
                    'riesgo',
                    'estado',
                    'calsbs',
                    'entireportadas',
                    'edad',
                    'estadociv',
                    'ingresos',
                    'fechaulti',
                    'cuopac',
                    'cuopag',
                    'cuoven',
                    'cuopen'
                ];

            // Inicializar un contador para alternar los colores
            $contador_filas = 0;

            // Recorrer los valores de $row dinámicamente
            foreach ($row as $campo => $valor) {
                // Omitir las columnas que se quieren excluir
                if (!in_array($campo, $columnas_excluidas)) {
                    // Verificar si el valor es numérico y aplicar formato si es necesario
                    // if (is_numeric($valor)) {
                    if ($campo == 'montoacobrar' || $campo == 'deudatotal' || $campo == 'capital') {
                        $valor = number_format($valor, 2, '.', ',');
                    }

                    // Definir el color de fondo alternado
                    $color_fondo = ($contador_filas % 2 == 0) ? '#f2f2f2' : '#e0e4ec'; // grid y marrón claro (color Tanned Leather)

                    // Determinar el ícono a usar para cada campo (puedes personalizar estos íconos)
                    // $icono = '<i class="fas fa-info-circle"></i>'; // Un ícono genérico
                    $icono = '<i class="fas fa-check-circle" style="color: green;"></i>'; // Un ícono genérico

                    // Mostrar la fila con el color de fondo correspondiente
                    echo '<tr style="background-color: ' . $color_fondo . ';">';
                    echo '<td style="font-weight: bold;">' . $icono . ' ' . ucfirst($campo) . '</td>';
                    echo '<td>' . $valor . '</td>';
                    echo '</tr>';

                    // Incrementar el contador para alternar el color en la siguiente fila
                    $contador_filas++;
                }
            }
        }
        /* FE CAMPO ROWS */ else if ($id_tabla == 'C_FINANCIERA_EFECTIVA_CAMPO') {
            echo '<tr style="background-color: #ffffff;"><td style="font-weight: bold;">🪪 Doc</td><td>' . $row['documento'] . '</td></tr>';
            echo '<tr style="background-color: #ffffff;"><td style="font-weight: bold;">🧛 Nombre</td><td>' . $row['NOMBRE'] . '</td></tr>';
            echo '<tr style="background-color: #ffffff;"><td style="font-weight: bold;">📌 Dpto</td><td>' . $row['DPTO'] . '</td></tr>';
            echo '<tr style="background-color: #eff7a8;"><td style="font-weight: bold;">🥇 Campaña</td><td>' . number_format($row['MONTOCAMPANA'], 2, '.', ',') . '</td></tr>';
            echo '<tr style="background-color: #f2f2f2;"><td style="font-weight: bold;">👀 Cluster</td><td>';
            echo $row['TRAMOINICIAL'];
            echo '</td></tr>';
            // echo '<tr style="background-color: #f2f2f2;"><td style="font-weight: bold;">⚠️ DiasMora</td><td>' . $row['DIASATRASO'] . '</td></tr>';
            echo '<tr style="background-color: #f2f2f2;"><td style="font-weight: bold;">🕘 Tipo Producto</td><td>' . $row['PRODUCTO'] . '</td></tr>';
            echo '<tr style="background-color: #f2f2f2;"><td style="font-weight: bold;">🕘 Detalle Producto</td><td>' . $row['SUBPRODUCTO'] . '</td></tr>';
            // echo '<tr style="background-color: #f2f2f2;"><td style="font-weight: bold;">🕘 CuoVen</td><td>' . $row['CUOVEN'] . '</td></tr>';
            // echo '<tr style="background-color: #f2f2f2;"><td style="font-weight: bold;">🕘 CuoPen</td><td>' . $row['CUOPEN'] . '</td></tr>';
            echo '<tr style="background-color: #f2f2f2;"><td style="font-weight: bold;">📅 Fecha Castigo </td><td>' . $row['FECHACASTIGO'] . '</td></tr>';
            echo '<tr style="background-color: #dddddd;"><td style="font-weight: bold;">⚡ Moneda</td><td>' . $row['MONEDA'] . '</td></tr>';
            // echo '<tr style="background-color: #dddddd;"><td style="font-weight: bold;">💸 Cuota</td><td>' . $row['VALORCUOTA'] . '</td></tr>';
            // echo '<tr style="background-color: #dddddd;"><td style="font-weight: bold;">📅 UltPago</td><td>' . $row['FECHAULTPA'] . '</td></tr>';
            echo '<tr style="background-color: #dddddd;"><td style="font-weight: bold;">💸 Saldo</td><td>' . number_format($row['SALDOPORPAGAR'], 2, '.', ',') . '</td></tr>';
            echo '<tr style="background-color: #dddddd;"><td style="font-weight: bold;">💸 DeuTot</td><td>' . number_format($row['DEUDATOTAL'], 2, '.', ',') . '</td></tr>';
            echo '<tr style="background-color: #dddddd;"><td style="font-weight: bold;">💸 Capital</td><td>' . number_format($row['CAPITAL'], 2, '.', ',') . '</td></tr>';
        } else {
            // DEFAULT

            echo '<tr style="background-color: #ffffff;"><td style="font-weight: bold;">🪪 Doc</td><td>' . $row['documento'] . '</td></tr>';
            echo '<tr style="background-color: #ffffff;"><td style="font-weight: bold;">🧛 Nombre</td><td>' . $row['NOMBRE'] . '</td></tr>';
            echo '<tr style="background-color: #ffffff;"><td style="font-weight: bold;">📌 Dpto</td><td>' . $row['DPTO'] . '</td></tr>';
            echo '<tr style="background-color: #eff7a8;"><td style="font-weight: bold;">🥇 Campaña</td><td>' . number_format($row['CAMPANA'], 2, '.', ',') . '</td></tr>';
            echo '<tr style="background-color: #f2f2f2;"><td style="font-weight: bold;">👀 Tramo</td><td>';
            // if ($id_tabla == 'C_FINANCIERA_EFECTIVA_CAMPO') {
            //     echo $row['TRAMOINICIAL'];
            // } else {
            //     echo $row['TRAMOFINAL'];
            // }
            echo $row['TRAMOFINAL'];
            echo '</td></tr>';
            // echo '<tr style="background-color: #f2f2f2;"><td style="font-weight: bold;">👀 Tramo</td><td>' . $row['TRAMOFINAL'] . '</td></tr>';
            echo '<tr style="background-color: #f2f2f2;"><td style="font-weight: bold;">⚠️ DiasMora</td><td>' . $row['DIASATRASO'] . '</td></tr>';
            echo '<tr style="background-color: #f2f2f2;"><td style="font-weight: bold;">🕘 CuoPac</td><td>' . $row['CUOPAC'] . '</td></tr>';
            echo '<tr style="background-color: #f2f2f2;"><td style="font-weight: bold;">🕘 CuoPag</td><td>' . $row['CUOPAG'] . '</td></tr>';
            echo '<tr style="background-color: #f2f2f2;"><td style="font-weight: bold;">🕘 CuoVen</td><td>' . $row['CUOVEN'] . '</td></tr>';
            echo '<tr style="background-color: #f2f2f2;"><td style="font-weight: bold;">🕘 CuoPen</td><td>' . $row['CUOPEN'] . '</td></tr>';
            echo '<tr style="background-color: #f2f2f2;"><td style="font-weight: bold;">📅 FechaVen</td><td>' . $row['FECHAVEN'] . '</td></tr>';
            echo '<tr style="background-color: #dddddd;"><td style="font-weight: bold;">⚡ Moneda</td><td>' . $row['MONEDA'] . '</td></tr>';
            echo '<tr style="background-color: #dddddd;"><td style="font-weight: bold;">💸 Cuota</td><td>' . $row['VALORCUOTA'] . '</td></tr>';
            echo '<tr style="background-color: #dddddd;"><td style="font-weight: bold;">📅 UltPago</td><td>' . $row['FECHAULTPA'] . '</td></tr>';
            echo '<tr style="background-color: #dddddd;"><td style="font-weight: bold;">💸 Saldo</td><td>' . number_format($row['SALDO'], 2, '.', ',') . '</td></tr>';
            echo '<tr style="background-color: #dddddd;"><td style="font-weight: bold;">💸 DeuTot</td><td>' . number_format($row['DEUDATOTAL'], 2, '.', ',') . '</td></tr>';
            echo '<tr style="background-color: #dddddd;"><td style="font-weight: bold;">💸 Capital</td><td>' . number_format($row['CAPITAL'], 2, '.', ',') . '</td></tr>';
        }

        // Puedes agregar más detalles aquí si es necesario
        echo '</tbody>';

        //echo '<tr><td colspan="3"><a class="btn btn-primary" href="agregargestion.php?id_tabla=' . $id_tabla . '&identificador=' . $row['identificador'] . '">✅ Agregar Gestion</a></td></tr>'; 


        // Verifica el valor de 'id_tabla' y crea el enlace correspondiente (USED)
        // echo '<tr><td colspan="3"><a class="btn btn-primary" href="agregargestion.php?id_tabla=' . $id_tabla . '&identificador=' . $row['identificador'] . '">✅ Agregar Gestion</a></td></tr>';

        $url_destino = ($id_usuario == 1391)
            ? "agregargestion2.php?id_tabla={$id_tabla}&identificador=" . $row['identificador'] . "&id_cartera={$id_cartera}"
            : "agregargestion2.php?id_tabla={$id_tabla}&identificador=" . $row['identificador'] . "&id_cartera={$id_cartera}";

        // ==========================================================================================================
        echo "<tr><td colspan=3><a class=btn btn-primary href='$url_destino'>✅ Agregar Gestión</a></td></tr>";
        // ==========================================================================================================

        echo '</table>';

        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody>';

    echo '</table>';
} else {
    echo '<p>No se encontraron resultados.</p>';
}

// Cerrar la conexión a la base de datos
if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close(); // Cierra la conexión a la base de datos
}
