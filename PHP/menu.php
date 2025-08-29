<?php
// Mantén el mismo nombre y política de cookie
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? '1' : '0');
ini_set('session.cookie_domain', '');

session_name('geocampo');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['id'])) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300&display=swap" rel="stylesheet">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="rutaAsesor.css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>

<body>
    <div class="menu-container">
        <h3 style="color: #a32d2d;">Hola,💪</h3>
        <h4>🏍️<?php echo $_SESSION['nombreCompleto']; ?></h4>
        <!--<h3>DNI: <?php echo $_SESSION['doc']; ?></h3> -->
        <marquee behavior="scroll" direction="left" style="color: #383838; background-color: #eff7a8; font-size: 18px;">
            ⚠️Advertencia: Todo pago debe realizarse en las agencias autorizadas. Está prohibido recibir dinero de los
            clientes.⚠️
        </marquee>
        <!-- ComboBox para seleccionar la cartera -->
        <div class="form-group">

            <select class="form-control" id="id_tabla" name="id_tabla">

            </select>
        </div>
        <!-- Menú con opciones -->
        <div class="custom-list-group">
            <a href="#" class="custom-list-group-item list-group-item-action consulta-informacion"><i
                    class="fas fa-info"></i> Consulta de Informacion</a>
            <a href="#" class="custom-list-group-item list-group-item-action ruta-asesor"><i
                    class="fas fa-map-marker"></i> Ruta Asesor</a>
            <a href="#" class="custom-list-group-item list-group-item-action ruta-supervisor hide-html-element"><i
                    class="fas fa-map-marker"></i> Control gestión campo</a>
        </div>
        <!-- <div class="dropdown show">
            <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Ruta Vista
            </a>

            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                <a class="dropdown-item ruta-asesor" href="#"> <i class="fas fa-map-marker"></i> Asesor</a>
                <a class="dropdown-item ruta-supervisor" href="#"> <i class="fas fa-map-marker"></i> Supervisor</a>
            </div>
        </div> -->

        <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
    </div>

    <!-- MODAL DE NUEVO INICIO DE SESION -->
    <?php include 'MSsesionExpirada.html'; ?>


    <!-- Agregar FontAwesome y Bootstrap JavaScript (si es necesario) -->
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- JavaScript para cargar las opciones del ComboBox y actualizar id_tabla -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Obtener el ID del usuario de la sesión (reemplaza esto con tu lógica de sesión)
            const userId = <?php echo $_SESSION['id']; ?>;

            // Obtener una referencia al ComboBox
            const carteraSelect = document.getElementById('id_tabla');
            // const idTablaInput = document.querySelector('input[name="id_tabla"]');

            // Función para cargar las opciones del ComboBox
            function cargarOpciones() {
                // Llamar al procedimiento almacenado "GetAsignacion" usando Fetch API
                fetch(`get_asignacion.php?userId=${userId}`)
                    .then(response => response.json())
                    .then(data => {
                        // Llenar el ComboBox con las opciones
                        data.forEach(item => {
                            // console.log(data)
                            const option = document.createElement('option');
                            option.value = item.id_tabla;
                            option.textContent = item.cartera;
                            option.dataset.idCartera = item.id_cartera;
                            carteraSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }

            // Llamar a la función para cargar las opciones cuando se carga la página
            cargarOpciones();

            // Agrega un evento clic al enlace "Consulta de Informacion"
            const consultaInformacionLink = document.querySelector('.consulta-informacion');
            consultaInformacionLink.addEventListener('click', function(e) {
                e.preventDefault();

                // Obtén el valor seleccionado en el ComboBox (idcartera y nomCartera)
                // const selectedValue = carteraSelect.value;
                // const selectedIndex = carteraSelect.selectedIndex;
                // const selectedText = carteraSelect.options[selectedIndex].text;
                //
                // Obtener el option seleccionado
                const selectedOption = carteraSelect.options[carteraSelect.selectedIndex];

                // Acceder al value y dataset del option seleccionado
                const value = selectedOption.value;
                const textContent = selectedOption.textContent;
                const dataset = selectedOption.dataset.idCartera;

                // Redirige a la página de consulta de cuentas con el parámetro id_tabla
                window.location.href = `consulta_cuentas.php?id_tabla=${value}&id_cartera=${dataset}`;
            });

            // Agregar un evento de cambio al ComboBox para actualizar el campo oculto
            // carteraSelect.addEventListener('change', function() {
            //     // Obtener el valor seleccionado en el ComboBox
            //     const selectedValue = carteraSelect.value;

            //     // Actualizar el campo oculto id_tabla
            //     idTablaInput.value = selectedValue;
            // });


            /*************************** RUTA DE ASESOR (NEW) OK ******************************/
            const rutaAsesorLink = document.querySelector('.ruta-asesor');
            const idPersonal = <?php echo $_SESSION['id']; ?>;
            rutaAsesorLink.addEventListener('click', function(e) {
                e.preventDefault();

                // Redirige a la página de consulta de cuentas con el parámetro id_tabla

                window.location.href = `ruta_asesor.php?id_usuario=${idPersonal}`;
            });
            /*************************** FIN RUTA DE ASESOR ******************************/

            /*************************** RUTA DE ASESOR (VISTA SUPERVISOR) (NEW) ******************************/
            const adminIds = [14, 17, 21, 22, 25, 27, 38, 44, 49, 71, 78, 173, 185, 186, 202, 203, 207, 240, 297, 348, 352, 410, 411, 413, 470, 478, 480, 482, 515, 647, 655, 668, 735, 765, 769, 788, 793, 802, 813, 820, 861, 1275, 1316, 1391]
            const rutaSupervisorLink = document.querySelector('.ruta-supervisor');
            // console.log(adminIds)
            // console.log(idPersonal)
            // console.log(int(idPersonal))
            // console.log(adminIds.includes(idPersonal))
            if (adminIds.includes(idPersonal)) {
                rutaSupervisorLink.classList.remove('hide-html-element')
                rutaSupervisorLink.addEventListener('click', function(e) {
                    e.preventDefault();

                    window.location.href = `ruta_supervisor.php?id_usuario=${idPersonal}`;
                });
            }
            /*************************** FIN RUTA DE ASESOR (VISTA SUPERVISOR) ******************************/

        });
    </script>

    <!-- VERIFICAR TOKEN -->
    <script src="verificarToken.js"></script>
</body>

</html>