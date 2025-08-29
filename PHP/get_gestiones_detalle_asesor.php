<?php
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? '1' : '0');
ini_set('session.cookie_domain', '');

session_name('geocampo');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


// Muestra los datos en una tabla principal
if (isset($_POST)) {
    // $id_tabla = isset($_SESSION['id_tabla']) ? $_SESSION['id_tabla'] : 0;
    $id_tabla = isset($_GET['carteraFile']) ? $_GET['carteraFile'] : 0;
    // error_log('Variable de _GET: ' . $_GET['carteraFile']);
    // error_log('nombre tabla: ' . $id_tabla);
    // if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['row'])) {
    // if (true) {
    $data = file_get_contents("php://input");
    $row = json_decode($data, true);
    // echo $row;
    // $htmlDetails = '<ul class="list-group">';
    // $htmlDetails .= '<li class="list-group-item">' . $row['FECHA'] . '</li>';
    // $htmlDetails .= '<li class="list-group-item">' . $row['IDENTIFICADOR'] . '</li>';
    // $htmlDetails .= '<li class="list-group-item">' . $row['IDEFECTO'] . '</li>';
    // $htmlDetails .= '<li class="list-group-item">' . $row['IDMOTIVO'] . '</li>';
    // $htmlDetails .= '<li class="list-group-item">' . $row['OBSERVACION'] . '</li>';
    // $htmlDetails .= '</ul>';

    $htmlDetails = '<div class="modal fade" id="modalData" tabindex="-1" role="dialog" aria-labelledby="modalDataTitle" aria-hidden="true">';
    $htmlDetails .= '  <div class="modal-dialog modal-lg" role="document">';
    $htmlDetails .= '    <div class="modal-content">';
    $htmlDetails .= '      <div class="modal-header">';
    $htmlDetails .= '        <h5 class="modal-title" id="exampleModalLongTitle">Detalle de la gestión</h5>';
    $htmlDetails .= '        <button type="button" class="close" data-dismiss="modal" aria-label="Close">';
    $htmlDetails .= '          <span aria-hidden="true">&times;</span>';
    $htmlDetails .= '        </button>';
    $htmlDetails .= '      </div>';
    $htmlDetails .= '      <div class="list-container">';
    $htmlDetails .= '       <ul class="list-group">';
    $htmlDetails .= '                <li class="list-group-item">📅 FECHA :    ' . $row['FECHA'] . '</li>';
    $htmlDetails .= '                <li class="list-group-item">📌 IDENTIFICADOR :    ' . $row['IDENTIFICADOR'] . '</li>';
    // $htmlDetails .= '                <li class="list-group-item">I DEFECTO  : ' . $row['IDEFECTO'] . '</li>';
    $htmlDetails .= '                <li class="list-group-item">⚡ EFECTO  : ' . $row['EFECTO'] . '</li>';
    // $htmlDetails .= '                <li class="list-group-item">I DMOTIVO  : ' . $row['IDMOTIVO'] . '</li>';
    $htmlDetails .= '                <li class="list-group-item">⚡ MOTIVO  : ' . $row['MOTIVO'] . '</li>';
    $htmlDetails .= '                <li class="list-group-item">👀 OBSERVACION   :  ' . $row['OBSERVACION'] . '</li>';
    // $htmlDetails .= '                <li class="list-group-item">I DDIRECCION   :  ' . $row['IDDIRECCION'] . '</li>';
    $htmlDetails .= '                <li class="list-group-item">🗺 DIRECCION   :  ' . $row['DIRECCION_DEPURADA'] . '</li>';
    $htmlDetails .= '        </ul>';
    $htmlDetails .= '       <ul class="list-group">';
    $htmlDetails .= '                <li class="list-group-item">🙍‍♂️ NOMCONTACTO   :  ' . $row['NOMCONTACTO'] . '</li>';
    $htmlDetails .= '                <li class="list-group-item">🏠 PISOS :    ' . $row['PISOS'] . '</li>';
    $htmlDetails .= '                <li class="list-group-item">🚪 PUERTA    :   ' . $row['PUERTA'] . '</li>';
    $htmlDetails .= '                <li class="list-group-item">💒 FACHADA   :  ' . $row['FACHADA'] . '</li>';
    $htmlDetails .= '                <li class="list-group-item">📅 FECHA_PROMESA :    ' . $row['FECHA_PROMESA'] . '</li>';
    $htmlDetails .= '                <li class="list-group-item">💸 MONTO_PROMESA :    ' . $row['MONTO_PROMESA'] . '</li>';
    $htmlDetails .= '                <li class="list-group-item current-latitud" hidden>' . $row['latitud'] . '</li>';
    $htmlDetails .= '                <li class="list-group-item current-longitud" hidden>' . $row['longitud'] . '</li>';
    $htmlDetails .= '        </ul>';
    $htmlDetails .= '      </div>';


    /************************** CARRUSEL *****************/
    // $primeraImagen = "./fotos/" . $row['imagen1'];
    // $primeraImagen = "./fotos/{$row['imagen1']}";

    // $carpeta_ruta = '';
    // if (isset($row['IDCARTERA'])) {
    //     if ($row['IDCARTERA'] == 59) {
    //         $carpeta_ruta = 'FC_CAMPO';
    //     } elseif ($row['IDCARTERA'] == 24){
    //         $carpeta_ruta = 'SANTANDER_CAMPO';
    //     } elseif ($row['IDCARTERA'] == 52){
    //         $carpeta_ruta = 'FC_JUDICIAL_VIGENTE';
    //     } elseif ($row['IDCARTERA'] == 53){
    //         $carpeta_ruta = 'FC_JUDICIAL_CASTIGO';
    //     } elseif ($row['IDCARTERA'] == 61){
    //         $carpeta_ruta = 'DERRAMA_CAMPO';
    //     } elseif ($row['IDCARTERA'] == 63){
    //         $carpeta_ruta = 'EFECTIVA_CAMPO';
    //     } else {
    //         $carpeta_ruta = 'OTROS';
    //     }
    // }

    $carpeta_ruta = substr($id_tabla, 2);

    $primeraImagen = "./fotos/" . $carpeta_ruta . "/" . $row['imagen1'];

    if (file_exists($primeraImagen)) {

        // $primeraImagen = "./fotos/" . $row['imagen1'];

        $htmlDetails .= '    <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">';
        $htmlDetails .= '    <div class="carousel-inner">';

        $firstImage = true;  // Variable para rastrear la primera imagen

        for ($i = 1; $i <= 3; $i++) {
            $nombreImagen = $row["imagen$i"]; // Asegúrate de obtener el nombre correcto de la columna
            // $rutaImagen = "./fotos/" . $nombreImagen;
            // $rutaImagen = "./fotos/{$row[$nombreImagen]}";
            // $rutaImagen = "./fotos/FC_CAMPO/" . $nombreImagen;
            $rutaImagen = "./fotos/" . $carpeta_ruta . "/" . $nombreImagen;
            if (!empty($nombreImagen) && file_exists($rutaImagen)) {
                // Añade la clase "active" solo a la primera imagen
                $claseActive = $firstImage ? 'active' : '';
                // $htmlDetails .= '<img src="' . $rutaImagen . '" alt="Imagen ' . $i . '" class="img-fluid">';
                $htmlDetails .= '            <div class="carousel-item ' . $claseActive . '">';
                $htmlDetails .= '<img src="' . $rutaImagen . '" alt="Imagen ' . $i . '" class="d-block w-100 image_asesor" >';
                $htmlDetails .= '            </div>';
                // Desactiva la variable $firstImage después de la primera iteración
                $firstImage = false;
            }
        }

        $htmlDetails .= '    </div>';
        $htmlDetails .= '    <button class="carousel-control-prev" type="button" data-target="#carouselExampleControls" data-slide="prev">';
        $htmlDetails .= '        <span class="carousel-control-prev-icon" aria-hidden="true"></span>';
        $htmlDetails .= '        <span class="sr-only">Previous</span>';
        $htmlDetails .= '    </button>';
        $htmlDetails .= '    <button class="carousel-control-next" type="button" data-target="#carouselExampleControls" data-slide="next">';
        $htmlDetails .= '        <span class="carousel-control-next-icon" aria-hidden="true"></span>';
        $htmlDetails .= '        <span class="sr-only">Next</span>';
        $htmlDetails .= '    </button>';
        $htmlDetails .= '    </div>';
    }
    /*************************************************************/
    $htmlDetails .= '      <div id="map"></div>';
    $htmlDetails .= '      <div class="modal-footer">';
    $htmlDetails .= '        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>';
    $htmlDetails .= '        <button type="button" class="btn btn-primary currentMap" onclick="verMapa()">Ver mapa</button>';
    $htmlDetails .= '      </div>';
    $htmlDetails .= '    </div>';
    $htmlDetails .= '  </div>';
    $htmlDetails .= '</div>';

    // Devuelve los detalles como una cadena JSON
    $responseData = array('html' => $htmlDetails, 'otro_dato' => 'valor');
    echo json_encode($responseData);
} else {
    echo 'No hay datos';
}

// Cerrar la conexión a la base de datos
if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close(); // Cierra la conexión a la base de datos
}
