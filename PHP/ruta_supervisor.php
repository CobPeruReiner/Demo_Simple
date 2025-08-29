<?php
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
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ruta asesor</title>
    <script src='./fileSaver.js'></script>
    <link rel="stylesheet" href="rutaAsesor.css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

</head>

<body>
    <div class="container-fluid p-5">
        <h3 class="text-center p-3 mb-4 bg-secondary text-white">CONTROL GESTIÓN CAMPO</h3>
        <form class='mb-10' id='ruta-asesor-form'>
            <div class="form-row d-flex justify-content-between mb-4">
                <div class="form-group d-flex align-items-center col mr-4">
                    <label class="mr-2">Fecha: </label>
                    <input id='ruta-asesor-date1' type='date'
                        class="form-control mr-2 text-center ruta_asesor_datepicker" required>
                    <input id='ruta-asesor-date2' type='date' class="form-control text-center ruta_asesor_datepicker"
                        required>
                </div>
                <!-- <div id="carterasCheckbox" class='form-group' required></div> -->

                <div class="form-group d-flex align-items-center col mr-4">
                    <label for="Cartera" class="mr-2">Cartera: </label>
                    <select class="form-control" id="carteras-select" onChange="handleAsesoresCartera(this)">
                    </select>
                </div>

                <div class="form-group d-flex align-items-center col-4 mr-4">
                    <label for="asesor" class="mr-2">Asesor: </label>
                    <select class="form-control " id="asesores-cartera-select" onChange="handleAsesor(this)">
                    </select>
                </div>
                <div class="form-group d-flex align-items-center col">
                    <button type="submit" class="btn btn-info mr-2 col">Buscar</button>
                    <button type="button" class="btn btn-warning col" id='ruta_supervisor_btnGaleria'>Ver
                        Galería</button>
                </div>
            </div>

            <!-- Incluir el mapa -->

            <!-- <div id="tabla-gestiones-asesor"></div> -->
            <!-- Formulario para ingresar el DNI -->
            <!-- <div class="d-flex justify-content-center mt-2">
                <a href="menu.php" class="btn btn-secondary">Volver al Menú</a>
            </div> -->
            <!-- <input id="addressInput" type="text" placeholder="Buscar dirección..."> -->
            <div class='form-row mb-2'>
                <input type="text" class="form-control col-10 mr-2" id="addressInput" placeholder="Buscar dirección...">
                <button type="button" class="btn btn-primary col" onclick="addMarkerFromAddress()">Agregar
                    Marcador</button>
            </div>
            <!-- <button onclick="addMarkerFromAddress()">Agregar Marcador</button> -->
        </form>
        <div id="mapCanvas"></div>


        <div id="tabla-gestiones-asesor" class='table-responsive'></div>
        <!-- Formulario para ingresar el DNI -->
        <div class="d-flex justify-content-center mt-4">
            <a href="menu.php" class="btn btn-secondary">Volver al Menú</a>
        </div>
    </div>

    <!-- Incluir el modal -->
    <div id="modalContainer"></div>

    <!-- MODAL DE NUEVO INICIO DE SESION -->
    <?php include 'MSsesionExpirada.html'; ?>

    <!-- Agregar FontAwesome y Bootstrap JavaScript (si es necesario) -->
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <!-- <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script> -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <!-- Script for excel export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <script src="ruta_asesor.js"></script>

    <!-- CURRENT API KEY -->
    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCGFBIf_6mCinwpqWw2Q-lHwNmK6u2iMhE&libraries=places&callback=initMap">
    </script>
    <!-- END CURRENT API KEY -->

    <script src="https://kit.fontawesome.com/455f7ac239.js" crossorigin="anonymous"></script>
    </script>

    <!--  VISTA SUPERVISOR  -->
    <script>
        const setAsignedCarteras = e => {
            const currentSearchData = window.location.search;
            const params = new URLSearchParams(currentSearchData);
            const idPersonal = params.get('id_usuario')
            fetch(`get_asignacion.php?userId=${idPersonal}`)
                .then(res => res.json())
                .then(data => {
                    // console.log(data)
                    showCarterasCheckboxes(data)
                })
        }

        setAsignedCarteras()

        let currentIdCartera;
        let currentCartera;
        let currentCarteraFile;

        const showCarterasCheckboxes = carteras => {
            const carteraSelect = document.getElementById('carteras-select');

            currentIdCartera = carteras[0].id_cartera;
            currentCartera = carteras[0].cartera;
            currentCarteraFile = carteras[0].id_tabla;

            handleAsesoresCartera()

            carteras.forEach(e => {
                const option = document.createElement("option");
                option.value = e.id_cartera; // Valor del option
                option.text = e.cartera; // Texto visible en el option
                option.dataset.carteraFile = e.id_tabla; // Texto visible en el option
                carteraSelect.appendChild(option);
            })

            // currentCarteras.forEach(e => {
            //     const option = document.createElement("option");
            //     option.value = e.id; // Valor del option
            //     option.text = e.cartera; // Texto visible en el option
            //     carteraSelect.appendChild(option);
            // })

            // currentCarteras.forEach((e, index) => (
            //     carterasCheckbox.innerHTML += `
            //     <div class="form-check mb-2">
            //         <input class="form-check-input" type="radio" name="exampleRadios" id='exampleRadios${index}' onchange=handleAsesoresCartera(this) value=${e.id} ${e.id == '52' || e.id == '53' ? 'disabled' : ''}>
            //         <label class="form-check-label" for='exampleRadios${index}'>
            //             ${e.cartera}
            //         </label>
            //     </div>
            //     `
            // ))
        }

        const handleSort = (a, b) => {
            if (a.NOMBRE < b.NOMBRE) {
                return -1
            }
            if (a.NOMBRE > b.NOMBRE) {
                return 1
            }
            return 0
        }

        const handleAsesoresCartera = e => {

            console.log('Se muestra combo asesores')

            if (e) {
                console.log('Hay argumento e')
                const selectedIndex = e.options[e.selectedIndex];
                currentIdCartera = e.value;
                currentCartera = selectedIndex.text;
                currentCarteraFile = selectedIndex.dataset.carteraFile;
            }

            console.log('Idcartera: ', currentIdCartera)

            // else {
            //     // valor por default al iniciar página (static)
            //     currentIdCartera = 24;
            //     currentCartera = 'SANTANDER CAMPO';
            // }

            fetch(`get_asesores_by_cartera.php?idcartera=${currentIdCartera}`)
                .then(res => res.json())
                .then(data => {
                    const asesoresCarteraSelect = document.getElementById('asesores-cartera-select');
                    // Limpiar opciones existentes
                    asesoresCarteraSelect.innerHTML = "";
                    const newData = data.sort(handleSort)
                    handleAsesor(newData[0])

                    newData.forEach(e => {
                        const option = document.createElement("option");
                        option.value = e.IDPERSONAL; // Valor del option
                        option.text = e.NOMBRE; // Texto visible en el option
                        asesoresCarteraSelect.appendChild(option);
                    })
                    // set first asesor
                })
        }

        let currentIdAsesor = 0;
        let currentAsesor = '';

        const handleAsesor = e => {
            if (e.value) {
                currentIdAsesor = e.value;
                currentAsesor = e.options[e.selectedIndex].text;
            } else {
                currentIdAsesor = e.IDPERSONAL;
                currentAsesor = e.NOMBRE;
            }
        }

        /************** INSERT HTML ELEMENT BEFORE *************/
        const insertBeforeElement = (el, newEl) => {
            el.parentNode.insertBefore(newEl, el);
        };

        const insertBefore = (el, newEl) => {
            if (typeof newEl === 'string') {
                el.insertAdjacentHTML('beforebegin', newEl);
            } else {
                insertBeforeElement(el, newEl);
            }
        };

        // const insertAfter = (el, htmlString) => el.insertAdjacentHTML('afterend', htmlString);

        const createElementIfNotExists = (id, content, dataArray, cartera, asesor) => {
            let existingElement = document.getElementById(id);
            if (!existingElement) {
                const tablaGestiones = document.getElementById('tabla-gestiones-asesor');
                const newEl = document.createElement('button');
                newEl.className = 'btn btn-success';
                newEl.id = id;
                newEl.textContent = content;
                /* INSERT TO DOM */
                insertBefore(tablaGestiones, newEl);
                /* ADD EXPORT FEATURE */
                newEl.addEventListener('click', () => downloadAsExcel(dataArray, cartera, asesor))
            }
        };

        /************** END INSERT HTML ELEMENT BEFORE *************/

        const handleSearchAsesorGestiones = () => {
            const rutaSupervisorForm = document.getElementById('ruta-asesor-form');
            rutaSupervisorForm.addEventListener('submit', e => {
                e.preventDefault();
                const date1 = document.getElementById('ruta-asesor-date1')
                const date2 = document.getElementById('ruta-asesor-date2')
                const asesorSelect = document.getElementById('asesores-cartera-select')
                // currentAsesor = e.target.options[e.target.selectedIndex].text;

                // console.log(asesorSelect.value)

                fetch(`get_gestiones_asesor_2.php?idPersonal=${asesorSelect.value}&fecha=${date1.value}&fecha2=${date2.value}&idCartera=${currentIdCartera}`)
                    .then(response => response.json())
                    .then(data => {

                        // Acceder al array result (ARRAY DE GESTIONES)
                        const resultArray = data.result;

                        if (resultArray && resultArray.length) {

                            /************************ EXPORT BUTTON ************************/
                            // Delete unnecessary files
                            const newArray = resultArray.map(({
                                FECHA,
                                IDENTIFICADOR,
                                IDEFECTO,
                                EFECTO,
                                MOTIVO,
                                CONTACTO,
                                OBSERVACION,
                                DIRECCION_DEPURADA,
                                ASESOR,
                                NOMCONTACTO,
                                PISOS,
                                PUERTA,
                                FACHADA,
                                FECHA_PROMESA,
                                MONTO_PROMESA,
                                cartera,
                                latitud,
                                longitud
                            }) => ({
                                FECHA,
                                IDENTIFICADOR,
                                IDEFECTO,
                                EFECTO,
                                MOTIVO,
                                CONTACTO,
                                OBSERVACION,
                                DIRECCION_DEPURADA,
                                ASESOR,
                                NOMCONTACTO,
                                PISOS,
                                PUERTA,
                                FACHADA,
                                FECHA_PROMESA,
                                MONTO_PROMESA,
                                cartera,
                                latitud,
                                longitud
                            }))

                            const {
                                cartera,
                                ASESOR
                            } = resultArray[0];

                            createElementIfNotExists('btn-gestiones-exportar', 'Descargar', newArray, cartera, ASESOR);
                            /************************ FIN EXPORT BUTTON ************************/

                            // Mostrar la tabla de gestiones
                            const tablaGestiones = document.getElementById('tabla-gestiones-asesor');
                            tablaGestiones.innerHTML = data.html;
                            const gestionesWithLocation = resultArray.filter(e => e.latitud !== '');
                            // Show map
                            initMap(gestionesWithLocation)

                            // Agregar la lógica de modal e info a los botones
                            let botones = document.querySelectorAll('.btn-test');
                            // Agrega el evento clic a cada botón
                            botones.forEach(function(boton) {
                                boton.addEventListener('click', function() {
                                    let rowData = JSON.parse(boton.getAttribute(
                                        'data-row'));

                                    // Muestra la información en la consola
                                    fetch(`get_gestiones_detalle_asesor.php?carteraFile=${currentCarteraFile}`, {
                                            "method": 'POST',
                                            "headers": {
                                                'Content-Type': 'application/json; charset=utf-8'
                                            },
                                            "body": JSON.stringify(rowData)
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            // modal
                                            const modalContainer = document
                                                .getElementById('modalContainer');
                                            modalContainer.innerHTML = data.html

                                            // hide map
                                            const currentMap = document.querySelector(
                                                "#map")
                                            currentMap.classList.add(
                                                'hide-html-element')

                                            $('#modalData').modal('toggle');

                                        })

                                });
                            })
                        } else {
                            const currentMainMap = document.querySelector("#mapCanvas")
                            currentMainMap.classList.add('hide-html-element')
                            const tablaGestiones = document.getElementById('tabla-gestiones-asesor');
                            tablaGestiones.innerHTML = "<b>Sin registros</b>";
                        }

                    }).catch(err => {
                        console.log('Error vista super: ', err)
                    })
            })
        }

        // mostrarGestiones()
        // showCarterasCheckboxes()

        // not working 'cause of scope
        // handleAsesoresCartera()

        // add submit for the Buscar button
        handleSearchAsesorGestiones()

        /********************************************* GALERIA ************************************************/
        const idPersonal = <?php echo $_SESSION['id']; ?>;
        const date = document.getElementById('ruta-asesor-date1')
        const date2 = document.getElementById('ruta-asesor-date2')
        const btnGaleria = document.getElementById('ruta_supervisor_btnGaleria');

        const asesoresCarteraSelect = document.getElementById('asesores-cartera-select');



        btnGaleria.addEventListener('click', function(e) {

            ventanaGaleria = window.open(
                `get_galeria_asesor.php?idPersonal=${currentIdAsesor}&date1=${date.value}&date2=${date2.value}&currentIdCartera=${currentIdCartera}&asesor=${currentAsesor}&cartera=${currentCartera}&carteraFile=${currentCarteraFile}`,
                '_blank');

        });


        /********************************************* FIN GALERIA ************************************************/

        /********************************************* EXCEL EXPORT ************************************************/


        /********************************************* FINEXCEL EXPORT ************************************************/
    </script>

    <!-- VERIFICAR TOKEN -->
    <script src="verificarToken.js"></script>
</body>
<script src="./exportData.js"></script>

</html>