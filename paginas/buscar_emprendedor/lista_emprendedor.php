<?php

//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_seguimiento.php");
include("../../config/funciones/funciones_session.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/funciones/funciones_emprendedores.php");
include("../../config/funciones/funciones_token.php");
include("../../config/config_define.php");

//Inicializacion de variables obtenidas de la solicitud POST
$campo_buscar_emprendedor = isset($_POST['campo_buscar_emprendedor']) ? $_POST['campo_buscar_emprendedor'] : '';
$pagina_actual = isset($_POST['numero_pagina']) ? (int)$_POST['numero_pagina'] : 1;
$num_ordenamiento = isset($_POST['num_ordenamiento']) ? (int)$_POST['num_ordenamiento'] : 1;
$calificacion = isset($_POST['calificacion']) ? $_POST['calificacion'] : 'todos_calificacion';


// Limite de emprendedores por pagina
$limite_emprendedor = 9;


$respuesta['registro'] = '';
$respuesta['pagina'] = '';
$respuesta['paginacion'] = '';


try {

    //Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Se obtiene la condicion WHERE para la consulta en funcion de las condiciones de busqueda del usuario
    $condicion_where =  obtenerCondicionWhereBuscadorEmprendedor($campo_buscar_emprendedor, $calificacion);

    //Se obtiene la condicion ASC y DESC para la consulta en funcion del numero de ordenamiento 
    $condicion_ASC_DESC = obtenerCondicionASCDESCFiltroEmprendedor($num_ordenamiento);

    //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y la cantidad de registros
    $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $limite_emprendedor);

    //Se obtiene lista de publicaciones que cumplen con las condiciones de busqueda
    $lista_emprendedores = obtenerListaBusquedaEmprendedoresWhereASCDESCLimit($conexion, $condicion_where, $condicion_ASC_DESC, $condicion_limit);

    //Determina si la busqueda esta activa
    $busqueda_activa = (!empty($campo_buscar_emprendedor) || $calificacion !== "todos_calificacion");

    //Se obtiene la cantidad de emprendedores disponibles
    $cantidad_emprendedores = count($lista_emprendedores);

    //Se obtiene la cantidad total de emprendedores segun las condiciones de busqueda
    $cantidad_total_emprendedores  = cantTotalListaBusquedaEmprendedoresWhere($conexion, $condicion_where);


    //Genera las cards HTML para las emprendedores 
    $respuesta['cards'] = cargarListaCardEmprendedores($conexion, $lista_emprendedores, $cantidad_total_emprendedores, $busqueda_activa);
    if ($cantidad_emprendedores >= 1) {

        //Genera la paginacion  
        $respuesta['paginacion'] = generarPaginacion($cantidad_total_emprendedores, $limite_emprendedor, $pagina_actual, "nextPageEmprendedor");

        //Calcula el total de paginas
        $totalPaginas = ceil($cantidad_total_emprendedores / $limite_emprendedor);


        //Se guarda en respuesta un mensaje indicando la pagina donde se encuentra el usuario y la cantidad de paginas que hay en total
        $respuesta['pagina'] = "Pagina {$pagina_actual} de {$totalPaginas}";
    }
} catch (Exception $e) {
    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    $respuesta['estado'] = 'danger';
    $respuesta['mensaje'] = $e->getMessage();
}



function cargarListaCardEmprendedores($conexion, $lista_emprendedores, $cantidad_total_emprendedores, $busqueda_activa)
{

    $es_perfil_del_usuario = false;
    $lo_sigue = false;
    $usuario_valido = false;
    $id_usuario = "";
    $tipo_usuario = "";

    //Calificacion maxima para un emprendedor
    global $calificacion_max_emprendedores;

    //URL base de archivos del sitio
    global $url_base_archivos;

    //URL de la imagen de emprendedores en que caso que no tengan imagen del perfil
    global $url_foto_perfil_predeterminada;


    //Establecer la sesion
    session_start();

    //Verificar los datos de sesion del usuario
    if (verificarEntradaDatosSession(['id_usuario', 'tipo_usuario'])) {

        //Se obtiene los datos de sesion
        $id_usuario = $_SESSION["id_usuario"];
        $tipo_usuario = $_SESSION["tipo_usuario"];

        //Se obtiene si el usuario ingresado es valido
        $usuario_valido = verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario);
    }


    $datos = '';

    if (count($lista_emprendedores) > 0) {
        if ($busqueda_activa) {
            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12 mt-2'>";
            $datos .= "<h4 class='mb-0'>Resultados:{$cantidad_total_emprendedores}</h4>";
            $datos .= "</div>";
        }
        for ($i = 0; $i < count($lista_emprendedores); $i++) {

            $es_perfil_del_usuario = false;
            $lo_sigue = false;
            if ($usuario_valido && $tipo_usuario == 2) {
                $es_perfil_del_usuario = esPerfilDelUsuario($conexion, $id_usuario, $lista_emprendedores[$i]['id_usuario_emprendedor']);
            }
            if (!$es_perfil_del_usuario) {
                $lo_sigue = verificarSiElUsuarioSigueAlEmprendedor($conexion, $lista_emprendedores[$i]['id_usuario_emprendedor'], $id_usuario);
            }

            $datos .= '<div class="col-12 col-sm-12 col-md-6 col-lg-4 mb-3 mt-2">';
            //Card Inicio
            $datos .= "<div class='card h-100'>";
            //Card Body Inicio
            $datos .= "<div class='card-body'>";
            $datos .= "<div class='row'>";

            //Div Inicio que contiene la imagen del perfil del emprendedor
            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12'>";
            //Se verifica que el emprendedor tiene una imagen de perfil en caso que no se agrega una imagen predeterminada del sistema
            if (is_null($lista_emprendedores[$i]["foto_perfil_nombre"])) {
                $ruta_archivo = $url_foto_perfil_predeterminada;
            } else {
                $ruta_archivo = $url_base_archivos . "/uploads/{$lista_emprendedores[$i]["id_usuario_emprendedor"]}/foto_perfil/{$lista_emprendedores[$i]["foto_perfil_nombre"]}";
            }

            $datos .= "<img class='card-img-top imagen_perfil' src='{$ruta_archivo}' alt='Foto de perfil'>";

            $datos .= "</div>";
            //Div Fin que contiene la imagen del perfil del emprendedor


            //Div Inicio que contiene la informacion del emprendedor
            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12'>";
            $datos .= "<div class='row'>";

            //Nombre del empredimiento
            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12'>";
            $datos .= "<p class='card-text text-break'><strong>Emprendimiento:</strong> {$lista_emprendedores[$i]["nombre_emprendimiento"]}</p>";
            $datos .= "</div>";


            //Nombre de usuario
            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12'>";
            $datos .= "<p class='card-text text-break'><strong>Nombre de usuario:</strong> {$lista_emprendedores[$i]["nombre_usuario"]}</p>";
            $datos .= "</div>";


            //Cantidad de productos disponibles
            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12'>";
            $datos .= "<p class='card-text text-break'><strong>Publicaciones de productos:</strong> {$lista_emprendedores[$i]["cant_productos_publicados"]}</p>";
            $datos .= "</div>";


            //Calificacion del emprendedor
            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12 text-center'>";
            $datos .= "<p class='card-text'><strong>Calificacion del emprendedor</strong></p>";
            $datos .= "</div>";


            $datos .= "<div class='text-center'>";
            //Verifica si el producto tiene una calificacion
            if (is_null($lista_emprendedores[$i]['calificacion_emprendedor'])) {
                $datos .= "<p class='card-text text-break'>El emprendedor aun no tiene una calificacion</p>";
            } else {

                //Agrega estrellas activas para la calificacion actual del emprendedor.
                for ($k = 0; $k < $lista_emprendedores[$i]['calificacion_emprendedor']; $k++) {
                    $datos .= "<i class='fas fa-star star-calificacion-activo'></i>";
                }


                //Calcula cúantas estrellas faltan para llegar a la calificacion máxima posible. -->
                $calificacionRestante = $calificacion_max_emprendedores - $lista_emprendedores[$i]['calificacion_emprendedor'];
                if ($calificacionRestante > 0) {

                    //Agregar estrellas vacias para las calificaciones restantes. -->
                    for ($l = 0; $l < $calificacionRestante; $l++) {
                        $datos .= "<i class='fas fa-star star-calificacion'></i>";
                    }
                }
            }
            $datos .= "</div>";

            $datos .= "</div>";
            $datos .= "</div>";
            //Div Fin que contiene la informacion del emprendedor


            $datos .= "</div>";
            $datos .= "</div>";
            //Card Body Fin

            $token = hash_hmac("sha1", $lista_emprendedores[$i]["id_usuario_emprendedor"], KEY_TOKEN);
            //Card Footer Inicio
            $datos .= "<div class='card-footer'>";
            $datos .= "<div class='row justify-content-center'>";
            //Se verifica si el perfil del emprendedor es del usuario
            if ($es_perfil_del_usuario) {
                $mensaje_boton_perfil = "Mi perfil";
            } else {

                $mensaje_boton_perfil = "Ver perfil";
                //Se verifica que el usuario que ingreso sea un usuario valido
                if ($usuario_valido) {
                    //Div que contiene un boton dependiendo si sigue o no al emprendedor
                    $datos .= "<div class='col-auto mb-md-2' id='div_boton_seguimiento-{$lista_emprendedores[$i]["id_usuario_emprendedor"]}'>";
                    if ($lo_sigue) {
                        $datos .= "<button type='button' class='btn btn-outline-danger dejar-seguir-btn' data-perfil-token='{$token}' data-perfil-id='{$lista_emprendedores[$i]["id_usuario_emprendedor"]}'>Dejar de seguir</button>";
                    } else {
                        $datos .= "<button type='button' class='btn btn-outline-success seguir-btn' data-perfil-token='{$token}' data-perfil-id='{$lista_emprendedores[$i]["id_usuario_emprendedor"]}'>Seguir</button>";
                    }
                    $datos .= "</div>";
                }
            }
            $datos .= "<div class='col-auto'>";
            $datos .= "<a class='btn btn-outline-primary' href='../../usuarios/usuario_emprendedor/seccion/perfil/pagina_perfil.php?id={$lista_emprendedores[$i]["id_usuario_emprendedor"]}&token={$token}'>{$mensaje_boton_perfil}</a>";
            $datos .= "</div>";
            $datos .= "</div>";
            $datos .= "</div>";
            //Card Footer Fin

            $datos .= "</div>";
            //Card Fin

            $datos .= "</div>";
        }
    } else {
        if ($busqueda_activa) {
            $mensaje = 'Sin resultados';
        } else {
            $mensaje = 'No hay emprendedores disponibles por el momento';
        }

        $datos .= '<div class="col-12 col-sm-12 col-md-12 col-lg-12" style="text-align: center;">';
        $datos .= "<h2 class='text-center'>{$mensaje}</h2>";
        $datos .= '</div>';
    }
    return $datos;
}


//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
