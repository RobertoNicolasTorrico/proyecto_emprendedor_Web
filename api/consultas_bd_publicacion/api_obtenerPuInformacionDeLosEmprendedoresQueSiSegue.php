<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_seguimiento.php");
include("../../config/consultas_bd/consultas_publicaciones.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/funciones/funciones_verificaciones.php");

//Inicializacion de variables obtenidas de la solicitud GET
$pagina_actual = isset($_GET['pagina_publicacion']) ? $_GET['pagina_publicacion'] : 1;

//Limite de publicacion por pagina
$limite_publicacion = 9;

// Campos esperados en la solicitud GET
$campo_esperados = array("id_usuario", "tipo_usuario");

$respuesta = [];
try {
    //Verifica si la solicitud es GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosArray($campo_esperados, $_GET);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }

        //Se obtiene los datos de la solicitud GET
        $id_usuario = $_GET['id_usuario'];
        $tipo_usuario = $_GET['tipo_usuario'];

        //Se verifica que la pagina actual sea valor numerico
        if (!is_numeric($pagina_actual)) {
            throw new Exception("La pagina de publicacion debe ser un numero");
        }

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();


        //Verifica si el usuario  es valido
        if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
            throw new Exception("La cuenta del usuario no esta activa o se cuentra baneada");
        }

        //Se obtiene la condicion LIMIT para la consulta en funcion de la pagina actual y la cantidad de registros
        $condicion_limit = obtenerCondicionLimitBuscador($pagina_actual, $limite_publicacion);




        //Se obtiene lista de publicaciones que cumplen con las condiciones de busqueda
        $lista_publicacion = obtenerPuInformacionDeLosEmprendedoresQueSiSegue($conexion, $id_usuario, $condicion_limit);

        $lista_publicaciones_archivo = array();
        for ($i = 0; $i < count($lista_publicacion); $i++) {
            $id_publicacion = $lista_publicacion[$i]['id_publicacion_informacion'];

            $lista_archivos = array();
            // Se obtiene la lista de archivos de la publicacion 
            $lista_archivos_publicacion = obtenerListaArchivosPublicaciones($conexion, $id_publicacion);

            // Recorre la lista de archivos y las agrega a $lista_archivos_publicacion
            for ($j = 0; $j < count($lista_archivos_publicacion); $j++) {
                $lista_archivos[] = $lista_archivos_publicacion[$j];
            }

            // Agrega los detalles de la publicacion y sus archivos $lista_publicaciones_archivo
            $lista_publicaciones_archivo[] = array(
                'detalles_publicaciones' => $lista_publicacion[$i],
                'archivos' => $lista_archivos
            );
        }

        $respuesta['lista_publicaciones'] = $lista_publicaciones_archivo;

        //Verifica si es necesario cargar mas elementos
        $cantidad_publicaciones = count($lista_publicacion);
        $cargar_mas = cargarMasElementos($cantidad_publicaciones, $limite_publicacion);
        $respuesta['cargar_mas_publicaciones'] = $cargar_mas;


        //Se obtiene la cantidad total de emprendedores que sigue el usuario
        $cant_seguimiento = cantTotalSeguimientoUsuario($conexion, $id_usuario);
        $respuesta['cant_seguimiento'] = $cant_seguimiento;





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
