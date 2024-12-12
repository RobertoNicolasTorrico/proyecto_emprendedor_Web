<?php
//Archivos de configuracion y funciones necesarias

include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_seguimiento.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/funciones/funciones_session.php");
include("../../config/funciones/funciones_usuario.php");
include("../../config/funciones/funciones_token.php");
include("../../config/config_define.php");


// Se obtiene la pagina actual de seguidor y el campo busqueda del usuario
$pagina_actual = isset($_POST['numero_pagina']) ? (int)$_POST['numero_pagina'] : 1;
$campo_buscar_seguidor = isset($_POST['campo_buscar_seguidor']) ? $_POST['campo_buscar_seguidor'] : '';


// Limite de seguidores por pagina
$limite_seguidores = 8;

$respuesta = [];


try {
    //Establecer la sesion
    session_start();

    //Verificar los datos de sesion del usuario
    if (!verificarEntradaDatosSession(['id_usuario', 'tipo_usuario'])) {
        throw new Exception("Debe iniciar sesion para poder a sus seguidores");
    }

    //Se obtiene los datos de sesion
    $id_usuario = $_SESSION["id_usuario"];
    $tipo_usuario = $_SESSION["tipo_usuario"];

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Verifica si el usuario es valido
    if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
        throw new Exception("No se puede ver la lista de seguidores debido a que su cuenta no esta activa o esta baneado");
    }


    //Verifica que el usuario sea un usuario emprendedor
    if (!verificarSiEsUsuarioEmprendedor($conexion, $id_usuario)) {
        throw new Exception("No se puede ver la informacion de los seguidores ya que no es un usuario emprendedor");
    }

    //Se obtiene la condicion WHERE para la consulta en funcion de las condiciones de busqueda del usuario
    $condicion_where =  obtenerCondicionWhereBuscadorUsuarioSeguidor($campo_buscar_seguidor);

    //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y la cantidad de registros
    $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $limite_seguidores);

    //Se obtiene lista de seguidores que cumplen con las condiciones de busqueda
    $lista_seguidores = obtenerListasSeguidoresDeEmprendedores($conexion, $id_usuario, $condicion_limit, $condicion_where);

    //Determina si la busqueda esta activa
    $busqueda_activa = !empty($campo_buscar_seguidor);
    $respuesta["busqueda_activa"] =  $busqueda_activa;

    //Verifica si es necesario cargar mas elementos
    $cantidad_seguidores = count($lista_seguidores);
    $cargar_mas = cargarMasElementos($cantidad_seguidores, $limite_seguidores);
    $respuesta["cargar_mas_seguidores"] =  $cargar_mas;

    //Se guarda la cantidad actual de seguidores
    $respuesta["cant_seguidores"] = $cantidad_seguidores;


    //Se obtiene la cantidad de seguidores del usuario
    $cant_total_seguidores = cantTotalSeguidoresUsuarioEmprendedor($conexion, $id_usuario);

    //Genera las cards HTML para los seguidores 
    $respuesta["cards_seguidores"] = cargarCardSeguidores($lista_seguidores, $pagina_actual, $limite_seguidores, $cargar_mas, $cant_total_seguidores, $busqueda_activa);
} catch (Exception $e) {

     //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    $respuesta['estado'] = 'danger';
    $respuesta['mensaje'] = $e->getMessage();
}



function cargarCardSeguidores($lista_seguidos, $pagina_actual, $limite_seguidores, $cargar_mas, $cant_total_seguidores, $busqueda_activa)
{
    $datos = "";
    $cant_seguidos = count($lista_seguidos);
    $mensaje = "";

     //Verifica si hay seguidores en la lista
    if ($cant_seguidos > 0) {

         //Si es la primera pagina se agrega un titulo para la seccion de seguidores 
        if ($pagina_actual == 1) {
            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12 mb-2'>";
            //Si el emprendedor esta buscando a un seguidor tiene un diferente titulo
            if ($busqueda_activa) {
                $datos .= "<span>Resultados: <span id='cant_resultados_seguidores'>{$cant_seguidos}</span></span>";
            } else {
                $datos .= "<span>Cantidad de usuarios que te siguen: <span id='cant_actual_seguidores'>{$cant_total_seguidores}</span></span>";
            }
            $datos .= "</div>";
        }

        for ($i = 0; $i < $cant_seguidos; $i++) {
            $datos .= "<div class='col-11 col-sm-11 col-md-11 col-lg-11 mb-3' id='div_seguidor_{$lista_seguidos[$i]["id_usuario"]}'>";
                $datos .= "<div class='card overflow-x-auto'>";//Card Inicio
                    $datos .= "<div class='card-body'>";//Card Body Inicio
                        $datos .= "<div class='row'>";

                            $datos .= "<div class='col-8 col-sm-8 col-md-8 col-lg-9'>";//Div Inicio que contiene la informacion del usuario
                                $datos .= "<div class='d-flex align-items-center'>";
                                    $datos .= "<div>";
                                        $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12'>";
                                            $datos .= "<p class='card-text text-break'>{$lista_seguidos[$i]['nombre_usuario']}</p>";
                                        $datos .= "</div>";
                                        $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12'>";
                                            $datos .= "<span class='text-break text-secondary'>{$lista_seguidos[$i]['nombre_completo']}</span>";
                                        $datos .= "</div>";
                                    $datos .= "</div>";
                                $datos .= "</div>";
                            $datos .= "</div>";//Div Fin que contiene la informacion del usuario


                         
                            $datos .= "<div class='col-4 col-sm-4 col-md-4 col-lg-3'>"; //Div Inicio que contiene el boton para eliminar al seguidor
                                $datos .= "<div class='col-auto mb-md-2' id='div_boton_seguimiento-{$lista_seguidos[$i]["id_usuario"]}'>";
                                        $datos .= "<button type='button' class='btn btn-outline-danger'  data-bs-toggle='modal' data-bs-target='#eliminarSeguidor' data-bs-nombre_usuario='{$lista_seguidos[$i]["nombre_usuario"]}' data-bs-id_seguidor='{$lista_seguidos[$i]["id_usuario"]}'>Eliminar</button>";
                                $datos .= "</div>";
                            $datos .= "</div>";//Div Fin que contiene el boton para eliminar al seguidor

                        $datos .= "</div>";
                    $datos .= "</div>";//Card Body Fin
                $datos .= "</div>";//Card Fin
            $datos .= "</div>";
        }
    }


    $titulo_mensaje=true;

    //Si el emprendedor esta buscando a un seguidor tiene un diferente titulo
    if (!$busqueda_activa) {
            //Verifica si no hay mas usuario disponibles para cargar
        if (!$cargar_mas) {
            if ($cant_seguidos >= 0 || $cant_seguidos <= $limite_seguidores) {
                $titulo_mensaje=false;
                $mensaje = "No hay mas usuarios que te sigan";
            }
        }
            //Verifica si no hay seguidores disponibles para cargar
        if ($pagina_actual == 1 && $cant_seguidos == 0) {
            $titulo_mensaje=false;
            $mensaje = "Por el momento no hay usuarios que te sigan";
        }
    }

    //Si el emprendedor esta buscando a un seguidor y no hay resultados
    if ($cant_seguidos == 0 && $busqueda_activa) {
        $titulo_mensaje=true;
        $mensaje = "Sin resultados";
    }


    if (!empty($mensaje)) {
        $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12 mt-3'>";
        if($titulo_mensaje){
            $datos .= "<h3 class='text-center'>{$mensaje}</h3>";
        }else{
            $datos .= "<p class='text-center'>{$mensaje}</p>";
        }
        $datos .= "</div>";
    }

    return $datos;
}


//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
