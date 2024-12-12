<?php

//Archivos de configuracion y funciones necesarias
include("../../../config/consultas_bd/conexion_bd.php");
include("../../../config/consultas_bd/consultas_usuario_admin.php");
include("../../../config/funciones/funciones_session.php");
include("../../../config/config_define.php");

$mensaje_error = "";
try {

    //Establecer la sesion
    session_start();

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Verifica que los datos de sesion sean de un usuario administrador y que sea un usuario valido
    verificarDatosSessionUsuarioAdministrador($conexion);
} catch (Exception $e) {

    // Capturar cualquier excepción y guardar el mensaje de error
    $mensaje_error = $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--Titulo de la página-->
    <title>Proyecto Emprendedor Categorias de Productos</title>

    <!--Enlace al archivo de estilos propios del proyecto-->
    <link href="../../../config/css/estilos.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de Bootstrap-->
    <link href="../../../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de FontAwesome para iconos-->
    <link href="../../../lib/fontawesome-6.5.1-web/css/all.css" rel="stylesheet">

    <!--Enlace al archivo de estilos de Fancybox-->
    <link href="../../../lib/fancyapps-5.0.33/dist/fancybox/fancybox.css" rel="stylesheet" />

</head>


<body>
    <!--Incluye el archivo de la barra de navegación para usuarios administrador.-->
    <?php include($url_navbar_usuario_admin);  ?>


    <!--Separa el contenido principal de la pagina del pie de pagina.-->
    <main>


        <!--Define el contenedor principal de la página donde se albergará todo el contenido.-->
        <div class="container">
            <?php if (empty($mensaje_error)) { ?>
                <h2 class="text-center">Lista de categorias para productos</h2>

                <div class="row">
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">

                        <div class="row">

                            <!-- Contenedor que contiene un boton para agregar una categoria -->
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                <button type='button' class='btn btn-outline-primary' data-bs-toggle='modal' data-bs-target='#ModalAgregarCategoria'><i class="fa-solid fa-circle-plus"></i> Agregar Categoria</button>
                            </div>

                            <!--Campo de busqueda por nombre de la categoria-->
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                <div class="row">
                                    <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                                            <input type="search" class="form-control" placeholder="Categoria" aria-label="Buscar Categoria" name="campo_buscar" id="campo_buscar">
                                        </div>

                                    </div>
                                </div>
                            </div>


                            <!--Selector de cantidad de categorias a mostrar-->
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text" for="cant_registro">Mostrar:</span>
                                    <select class="form-select" name="cant_registro" id="cant_registro">
                                        <option value="5">5</option>
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>


                            <!--Filtro por ordenamiento de categorias -->
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text" for="ordenar_por">Ordenar por:</span>
                                    <select class="form-select" name="ordenar_por" id="ordenar_por">
                                        <option value="alfabetico_asc">Alfabético A-Z</option>
                                        <option value="alfabetico_desc">Alfabético Z-A </option>
                                        <option value="cantidad_asc">Cantidad de productos Ascendente</option>
                                        <option value="cantidad_desc">Cantidad de productos Descendente</option>

                                    </select>
                                </div>
                            </div>

                        </div>

                        <!-- Contenedor para mostrar mensajes debido a las acciones que se realizan  -->
                        <div id="alert_notificacion_categoria"></div>
                    </div>


                    <!--Contenedor con la tabla que contiene las categorias de productos-->
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="table table-responsive" id="tabla_categorias"></div>
                    </div>


                    <!--Contenedor que contiene informacion sobre la paginacion y la cantidad de paginas disponibles-->
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="row">
                            <div class="col-6 col-sm-6 col-md-6 col-lg-6">

                                <!--Informacion sobre la cantidad de resultados que se ve en la interfaz-->
                                <p id="lbl-totalResultadoCategoria"></p>

                                <!--Informacion sobre las paginas disponibles -->
                                <p id="lbl-totalPaginaCategoria"></p>
                            </div>

                            <!--Contenedor para la navegacion de la paginacion -->
                            <div class="col-6 col-sm-6 col-md-6 col-lg-6" id="nav-paginacionCategoria"></div>
                        </div>
                    </div>
                </div>


            <?php } else { ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo ($mensaje_error) ?>
                </div>
            <?php } ?>

        </div>



    </main>
    <!-- Incluye el pie de pagina y varios scripts necesarios para el funcionamiento de la pagina.-->
    <script src="../../../config/js/funciones.js"></script>
    <script src="../../../lib/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../../lib/fancyapps-5.0.33/dist/fancybox/fancybox.umd.js"></script>

    <?php require("modal_agregar_categoria.php"); ?>
    <?php require("modal_eliminar_categoria.php"); ?>
    <?php require("modal_modificar_categoria.php"); ?>
    <?php require("../../../template/footer.php"); ?>

</body>

</html>
<script>
    // Variable de control para la paginación de categorias
    var pagina_actual_categoria = 1;


    // Variable para almacenar el valor original de una categoria que se va a modificar
    var bd_txt_tipo_categoria = "";


    var cant_actual_registros;

    var alert_notificacion_categoria = document.getElementById("alert_notificacion_categoria");


    // Carga de datos de las categoria
    getDatosCategorias();


    document.getElementById("cant_registro").addEventListener("change", function() {
        pagina_actual_categoria = 1;
        getDatosCategorias();

    });

    document.getElementById("ordenar_por").addEventListener("change", function() {
        pagina_actual_categoria = 1;
        getDatosCategorias();

    });

    document.getElementById("campo_buscar").addEventListener("input", function() {
        pagina_actual_categoria = 1;
        getDatosCategorias();

    });


    //Función para obtener y cargar los datos de la tabla de categorias
    function getDatosCategorias() {

        var cant_registro = document.getElementById("cant_registro");
        var tabla_categorias = document.getElementById("tabla_categorias");
        var campo_buscar = document.getElementById("campo_buscar");
        var ordenar_por = document.getElementById("ordenar_por");
        var pagina_categoria = pagina_actual_categoria;

        var totalResultadoCategoria = document.getElementById("lbl-totalResultadoCategoria");
        var totalPaginaCategoria = document.getElementById("lbl-totalPaginaCategoria");
        var paginacionCategoria = document.getElementById("nav-paginacionCategoria");

        // Envío del formulario usando fetch
        const formData = new FormData();
        formData.append('cant_registro', cant_registro.value);
        formData.append('campo_buscar', campo_buscar.value);
        formData.append('ordenar_por', ordenar_por.value);
        formData.append('numero_pagina_categoria', pagina_categoria);

        fetch('lista_categoria.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(datos => {
                if (datos.estado !== 'danger') {
                    tabla_categorias.innerHTML = datos.tabla;
                    cant_actual_registros = datos.cantidad_actual;
                    totalResultadoCategoria.innerHTML = datos.registro;
                    totalPaginaCategoria.innerHTML = datos.pagina;
                    paginacionCategoria.innerHTML = datos.paginacion;
                } else {
                    alert_notificacion_categoria.innerHTML = mensaje_alert_fijo(datos.estado, datos.mensaje);
                    tabla_categorias.innerHTML = "";
                    cant_actual_registros = "";
                    totalResultadoCategoria.innerHTML = "";
                    totalPaginaCategoria.innerHTML = "";
                    paginacionCategoria.innerHTML = "";
                }
            }).catch(e => {

                // Muestra un mensaje error de la solicitud
                alert_notificacion_categoria.innerHTML = mensaje_alert_fijo("danger", e);
            });
    }


    //Funcion para cambiar la pagina de categorias y recargar los datos de la tabla de categorias
    function nextPageCategorias(pagina) {
        pagina_actual_categoria = pagina;
        getDatosCategorias();
    }
</script>