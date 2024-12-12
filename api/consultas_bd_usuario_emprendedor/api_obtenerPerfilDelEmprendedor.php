<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_categoria.php");
include("../../config/consultas_bd/consultas_seguimiento.php");
include("../../config/funciones/funciones_verificaciones.php");
include("../../config/funciones/funciones_generales.php");

$lo_sigue = false;
$es_perfil_del_usuario = false;
$usuario_valido = false;
$respuesta = [];

//Inicializacion de variables obtenidas de la solicitud GET
$id_usuario = isset($_GET['id_usuario']) ? $_GET['id_usuario'] : null;
$tipo_usuario = isset($_GET['tipo_usuario']) ? $_GET['tipo_usuario'] : null;

//Campos necesarios en la solicitud POST
$campos_necesarios = array("id_usuario_emprendedor");

try {
    //Verifica si la solicitud es GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {


        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosArray($campos_necesarios, $_GET);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();


        //Se obtiene los datos de la solicitud GET
        $id_usuario_emprendedor_perfil = $_GET['id_usuario_emprendedor'];

        //Se obtiene los datos del emprendedor
        $usuario_emprendedor_perfil = obtenerDatosUsuarioEmprendedorPorIDUsuarioEmprendedor($conexion, $id_usuario_emprendedor_perfil);
        if (empty($usuario_emprendedor_perfil)) {
            throw new Exception("No se pudo obtener la informacion del emprendedor. por favor intente mas tarde");
        }
        $respuesta['usuario_emprendedor_perfil'] = $usuario_emprendedor_perfil;


        //Se obtiene los datos de las categorias de los productos publicados
        $categorias_producto = obtenerCategoriasDeProductosPerfilEmprendedor($conexion, $id_usuario_emprendedor_perfil);
        $respuesta['categorias_producto'] = $categorias_producto;



        //Obtener cantidad de seguidores del usuario emprendedor
        $cant_total_seguidores = cantTotalSeguidoresUsuarioEmprendedor($conexion, $usuario_emprendedor_perfil['id_usuario']);
        $usuario_emprendedor_perfil["cant_seguidores"] = $cant_total_seguidores;



        //Obtener cantidad de emprendedores que sigue el propietario del perfil
        $cant_total_seguidos = cantTotalSeguimientoUsuario($conexion, $usuario_emprendedor_perfil['id_usuario']);
        $usuario_emprendedor_perfil["cant_seguidor"] =  $cant_total_seguidos;



        //Se verifica que los tados del usuario no sean NULL
        if (!is_null($id_usuario) && !is_null($tipo_usuario)) {
            $usuario_valido = verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario);

            //Verifica si el usuario es valido
            if ($usuario_valido) {

                //Se verifica que el usuario sea emprendedor
                if ($tipo_usuario == 2) {
                    //Se obtiene la informacion  para saber si el usuario que ingreso a su perfil o no
                    $es_perfil_del_usuario = esPerfilDelUsuario($conexion, $id_usuario, $id_usuario_emprendedor_perfil);
                }

                //Se verifica que no sea el perfil del usuario emprendedor
                if (!$es_perfil_del_usuario) {
                    //En caso que no sea el perfil del usuario se obtiene la informacion para saber si lo sigue o no
                    $lo_sigue = verificarSiElUsuarioSigueAlEmprendedor($conexion, $id_usuario_emprendedor_perfil, $id_usuario);
                }
            }
        }
        $respuesta['lo_sigue'] = $lo_sigue;
        $respuesta['es_perfil_del_usuario'] = $es_perfil_del_usuario;
        $respuesta['usuario_valido'] = $usuario_valido;


        //Se verifica que no sea el perfil del usuario emprendedor
        if (!$es_perfil_del_usuario) {

            //Verifica la cuenta del usuario este activa y no baneada
            if (!$usuario_emprendedor_perfil['activado'] || $usuario_emprendedor_perfil['baneado']) {
                throw new Exception("La cuenta del usuario emprendedor no esta activa o esta baneada");
            }
        }


        http_response_code(200);
    } else {
        http_response_code(405);
        throw new Exception("Metodo no permitido o datos no recibidos");
    }
} catch (Exception $e) {
    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    http_response_code(400);
    $respuesta['mensaje'] = $e->getMessage();
}
//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
