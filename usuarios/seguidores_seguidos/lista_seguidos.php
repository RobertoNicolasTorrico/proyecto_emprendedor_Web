<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_seguimiento.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/funciones/funciones_token.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/funciones/funciones_emprendedores.php");
include("../../config/funciones/funciones_session.php");
include("../../config/config_define.php");


// Se obtiene la pagina actual de seguidor y el campo busqueda de emprendedor
$pagina_actual = isset($_POST['numero_pagina']) ? (int)$_POST['numero_pagina'] : 1;
$campo_buscar_emprendedor = isset($_POST['campo_buscar_emprendedor']) ? $_POST['campo_buscar_emprendedor'] : '';


// Limite de seguidos por pagina
$limite_seguidos = 8;

$respuesta = [];


try {




    //Establecer la sesion
    session_start();

    //Verificar los datos de sesion del usuario
    if (!verificarEntradaDatosSession(['id_usuario', 'tipo_usuario'])) {
        throw new Exception("Debe iniciar sesion para poder va lista de emprendedores");
    }

    //Se obtiene los datos de sesion
    $id_usuario_seguidor = $_SESSION["id_usuario"];
    $tipo_usuario = $_SESSION["tipo_usuario"];

    // Establecer conexión con la base de datos
    $conexion = obtenerConexionBD();

    //Se obtiene la condicion WHERE para la consulta en funcion de las condiciones de busqueda del usuario
    $condicion_where =  obtenerCondicionWhereBuscadorEmprendedorSeguidos($campo_buscar_emprendedor);

    //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y la cantidad de registros
    $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $limite_seguidos);

    //Se obtiene lista de seguidores que cumplen con las condiciones de busqueda
    $lista_seguidos = obtenerListaEmprendedoresSeguidos($conexion, $id_usuario_seguidor, $condicion_limit, $condicion_where);

    //Determina si la busqueda esta activa
    $busqueda_activa = !empty($campo_buscar_emprendedor);
    $respuesta["busqueda_activa"] =  $busqueda_activa;


    //Verifica si es necesario cargar mas elementos
    $cantidad_seguidos = count($lista_seguidos);
    $cargar_mas=cargarMasElementos($cantidad_seguidos,$limite_seguidos);
    $respuesta["cargar_mas_seguidos"] =  $cargar_mas;

    //Se guarda la cantidad actual de seguidos
    $respuesta["cant_seguidos"] = $cantidad_seguidos;
    
    //Se obtiene la cantidad de seguidores del usuario
    $cant_total_seguidos = cantTotalSeguimientoUsuario($conexion, $id_usuario_seguidor);

    $respuesta["cards_seguidos"] = cargarCardSeguidos($lista_seguidos, $pagina_actual, $limite_seguidos, $cargar_mas, $cant_total_seguidos, $busqueda_activa);
} catch (Exception $e) {
    $respuesta['estado'] = 'danger';
    $respuesta['mensaje'] = $e->getMessage();
}

function cargarCardSeguidos($lista_seguidos, $pagina_actual, $limite_seguidos, $cargar_mas, $numero_seguidos, $busqueda_activa)
{
    $datos = "";
    $cant_seguidos = count($lista_seguidos);
    $mensaje = "";
    $token = "";
    global $url_base_archivos;
    global $url_foto_perfil_predeterminada;
    if ($cant_seguidos > 0) {

        //Si es la primera pagina se agrega un titulo para la seccion de seguidos 
        if ($pagina_actual == 1 ) {
            $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12 mb-2'>";

            //Si el usuario esta buscando a un emprendedor tiene un diferente titulo
            if($busqueda_activa){
                $datos .= "<span>Resultados: <span id='cant_resultados_seguidos'>{$cant_seguidos}</span></span>";
            }else{
                $datos .= "<span>Cantidad de emprendedores que seguis: <span id='cant_actual_seguidos'>{$numero_seguidos}</span></span>";
            }
     
            $datos .= "</div>";
        }


        for ($i = 0; $i < $cant_seguidos; $i++) {
            $datos .= "<div class='col-11 col-sm-11 col-md-11 col-lg-11 mb-3' id='div_seguido_emprendedor_{$lista_seguidos[$i]["id_usuario_emprendedor"]}'>";
                $datos .= "<div class='card overflow-x-auto'>";//Card Inicio
                    $datos .= "<div class='card-body'>";//Card Body Inicio
                        $datos .= "<div class='row'>";
                            $datos .= "<div class='col-8 col-sm-8 col-md-8 col-lg-8'>";//Contenedor inicio contiene la informacion del emprendedor
                                $datos .= "<div class='d-flex align-items-center'>";

                                    //Se verifica que el emprendedor tiene una imagen de perfil en caso que no se agrega una imagen predeterminada del sistema
                                    if (is_null($lista_seguidos[$i]["foto_perfil_nombre"])) {
                                        $ruta_archivo = $url_foto_perfil_predeterminada;
                                    } else {
                                        $ruta_archivo = "{$url_base_archivos}/uploads/{$lista_seguidos[$i]["id_usuario_emprendedor"]}/foto_perfil/{$lista_seguidos[$i]["foto_perfil_nombre"]}";
                                    }
                                    $datos .= "<img class='mini_imagen_perfil' src='{$ruta_archivo}' alt='Foto de perfil'>";
                                    $datos .= "<div>";
                                        $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12'>";
                                            $token = hash_hmac("sha1", $lista_seguidos[$i]["id_usuario_emprendedor"], KEY_TOKEN);
                                            $datos .= "<a class='link-dark link-offset-2 link-underline link-underline-opacity-0 link-opacity-50-hover' href='{$url_base_archivos}/usuarios/usuario_emprendedor/seccion/perfil/pagina_perfil.php?id={$lista_seguidos[$i]['id_usuario_emprendedor']}&token={$token}'>{$lista_seguidos[$i]['nombre_emprendimiento']}</a>";
                                        $datos .= "</div>";
                        
                                        $datos .= "<div class='col-12 col-sm-12 col-md-12 col-lg-12'>";
                                            $datos .= "<span class='text-break text-secondary'>{$lista_seguidos[$i]['nombre_usuario']}</span>";
                                        $datos .= "</div>";
                                    $datos .= "</div>";
                                $datos .= "</div>";
                            $datos .= "</div>"; //Contenedor fin contiene la informacion del emprendedor

                        

                            $datos .= "<div class='col-4 col-sm-4 col-md-4 col-lg-4'>";//Div Inicio que contiene el boton para dejar de seguir
                                $datos .= "<div class='col-auto mb-md-2' id='div_boton_seguimiento-{$lista_seguidos[$i]["id_usuario_emprendedor"]}'>";
                                    $datos .= "<button type='button' class='btn btn-outline-danger m-1'  data-bs-toggle='modal' data-bs-target='#eliminarSeguidorEmprendedor' data-bs-nombre_emprendimiento='{$lista_seguidos[$i]["nombre_emprendimiento"]}' data-bs-id_usuario_emprendedor='{$lista_seguidos[$i]["id_usuario_emprendedor"]}' data-perfil-token='{$token}'>Dejar de seguir</button>";
                                $datos .= "</div>";
                            $datos .= "</div>";//Div Fin que contiene el boton para dejar de seguir

                        $datos .= "</div>";
                    $datos .= "</div>";//Card Body Fin
                $datos .= "</div>";//Card Fin
            $datos .= "</div>";
        }
    }


    $titulo_mensaje=true;


    //Si el usuario esta buscando a un emprendedor tiene un diferente titulo
    if(!$busqueda_activa){

        //Verifica si no hay mas emprendedores disponibles para cargar
        if(!$cargar_mas){
            if($cant_seguidos >= 0 && $cant_seguidos <= $limite_seguidos){
                $titulo_mensaje=false;
                $mensaje = "No seguis a mas emprendedores por el momento";
            }
        }

        //Verifica si no hay emprendedores disponibles para cargar
        if ($pagina_actual == 1 && $cant_seguidos == 0) {
            $titulo_mensaje=false;
            $mensaje = "No seguis a algun emprendedor por el momento";
        }
    }

    //Si el usuario esta buscando a un emprendedor y no hay resultados
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


echo json_encode($respuesta);
