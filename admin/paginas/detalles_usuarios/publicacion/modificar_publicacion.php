<?php
//Archivos de configuracion y funciones necesarias
include("../../../../config/consultas_bd/conexion_bd.php");
include("../../../../config/consultas_bd/consultas_publicaciones.php");
include("../../../../config/consultas_bd/consultas_usuario.php");
include("../../../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../../../config/consultas_bd/consultas_usuario_admin.php");
include("../../../../config/funciones/funciones_verificaciones.php");
include("../../../../config/funciones/funciones_generales.php");
include("../../../../config/funciones/funciones_session.php");
include("../../../../config/funciones/funciones_token.php");
include("../../../../config/config_define.php");

//Configuracion de archivos permitidos
$archivo = [
    'cant_min' => 1,
    'cant_max' => 5,
    'extensiones' => ['png', 'jpeg', 'jpg', 'mp4', 'mkv', 'avi'],
    'extensiones_imagen' => ['png', 'jpeg', 'jpg'],
    'extensiones_video' => ['mp4', 'mkv', 'avi'],
];


// Campos esperados en la solicitud POST
$campo_esperados = ['id_publicacion_modificar', 'txt_descripcion_modificar', 'fecha_modificada'];


//Limite de caracteres para la descripcion 
$descripcion_datos = [
    'cant_min' => 1,
    'cant_max' => 255,
];

$respuesta = [];

$estado = 'danger';

//Inicializacion de variables obtenidas de la URL
$id_usuario = isset($_GET['id']) ? $_GET['id'] : '';
$id_usuario_token = isset($_GET['token']) ? $_GET['token'] : '';

try {
    //Verifica si la solicitud es POST y no esta vacia
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {

        //Se verifica que los datos recibidos de la URL sean validos
        verificarUrlTokenId($id_usuario, $id_usuario_token);

        //Establecer la sesion
        session_start();

        //Verificar los datos de sesion del usuario administrador
        if (!verificarEntradaDatosSession(['id_usuario_administrador', 'tipo_usuario_admin'])) {
            throw new Exception("Debe iniciar sesion para poder modificar la publicacion");
        }

        //Se obtiene los datos de sesion
        $id_usuario_administrador = $_SESSION['id_usuario_administrador'];
        $tipo_usuario_admin = $_SESSION['tipo_usuario_admin'];

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Verifica si el usuario administrador es valido
        if (!verificarSiEsUsuarioAdminValido($conexion, $id_usuario_administrador, $tipo_usuario_admin)) {
            throw new Exception("No se puede modificar la publicaciones por que no es usuario administrador valido");
        }

        //Verifica si la cuenta del usuario sigue existe
        if (!laCuentaDelUsuarioExiste($conexion, $id_usuario)) {
            throw new Exception("Esta cuenta fue eliminada previamente. Por favor regrese a la página anterior");
        }


        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosArray($campo_esperados, $_POST);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }

        //Se obtiene datos del usuario emprendedor por el id del usuario
        $usuario_emprendedor = obtenerDatosUsuarioEmprendedorPorIDUsuario($conexion, $id_usuario);
        if (empty($usuario_emprendedor)) {
            throw new Exception("No se pudo obtener la informacion del usuario emprendedor. por favor intente mas tarde");
        }
        $id_usuario_emprendedor = $usuario_emprendedor['id_usuario_emprendedor'];


        $id_publicacion_modificar = $_POST['id_publicacion_modificar'];
        //Verifica si la publicacion existe
        if (!verificarSiLaPublicacionExiste($conexion, $id_publicacion_modificar)) {
            throw new Exception("La publicacion ya fue eliminado previamente por lo que no puede ser modificada. Por favor actualiza la pagina para ver los cambios");
        }


        //Se obtiene datos de la publicacion por el ID de la publicacion y por ID del usuario emprendedor
        $publicacion = obtenerPublicacionDelUsuarioEmprendedor($conexion, $id_publicacion_modificar, $id_usuario_emprendedor);
        if (empty($publicacion)) {
            throw new Exception("No se puede modificar la publicacion que no le pertenecen al mismo usuario");
        }

        //Se obtiene los datos de la solicitud POST
        $fecha_modificada = $_POST['fecha_modificada'];
        $descripcion = $_POST['txt_descripcion_modificar'];
        $files = (isset($_FILES['files'])) ? $_FILES['files'] : [];
        $map_latitud = (isset($_POST['latitud']) && $_POST['latitud'] != "null") ? (float)$_POST['latitud'] : null;
        $map_longitud = (isset($_POST['longitud']) && $_POST['longitud'] != "null") ? (float)$_POST['longitud'] : null;
        $nombres_files_bd = isset($_POST["nombres_files_bd"]) ? $_POST["nombres_files_bd"] : [];
        $nombre_archivos = [];
        $archivos_eliminados = [];

        //Se obtiene el nombre de la carpeta donde se guardan los archivos de la publicacion
        $nombre_carpeta = str_replace([" ", ":"], ["_", "-"], $publicacion['fecha_publicacion']);


        //Se obtiene los datos de los archivos de la publicacion
        $archivo_publicacion = obtenerListaArchivosPublicaciones($conexion, $id_publicacion_modificar);
        // Verifica si hay archivos asociados a la publicación original
        if (!empty($archivo_publicacion)) {

            // Extrae los nombres de los archivos de la publicación en un array
            $nombre_archivos = array_column($archivo_publicacion, 'nombre_archivo');

            // Si nombres_files_bd esta vacio se considera que todos los archivos fueron eliminados
            if (count($nombres_files_bd) == 0) {
                $archivos_eliminados = $nombre_archivos;
            } else {
                // Si hay nombres de archivos en la base de datos, se determina qué archivos han sido eliminados
                // Compara la lista actual de archivos con la recibida por el usuario
                $archivos_eliminados = array_diff($nombre_archivos, $nombres_files_bd);

                // Reindexa el array resultante para asegurar que los índices sean consecutivos
                $archivos_eliminados = array_values($archivos_eliminados);
            }
        }

        //Verifica que los nuevos datos recibidos no sean iguales a la publicacion original
        if ($fecha_modificada == $publicacion['fecha_publicacion'] && $descripcion == $publicacion['descripcion'] && $map_latitud == $publicacion['map_latitud'] && $map_longitud == $publicacion['map_longitud'] && empty($files) && empty($archivos_eliminados)) {
            $estado = 'info';
            throw new Exception("No hubo cambios en la publicacion");
        }

        //Verifica que la descripcion no tenga espacios al inicio o al final de la cadena
        if (tieneEspaciosInicioFinCadena($descripcion)) {
            throw new Exception("El campo descripción no puede tener espacios en blanco al inicio o al final");
        }


        //Verifica que la longitud de caracteres sea valida 
        if (verificarLongitudTexto($descripcion, $descripcion_datos['cant_min'], $descripcion_datos['cant_max'])) {
            throw new Exception("El campo descripción debe tener entre 1 y 255 caracteres");
        }



        //Verifica y maneja la carga de archivos en caso que se reciba uno o se halla eliminado un archivo
        if (!empty($files) || !empty($nombres_files_bd)) {

            //Verifica que la cantidad de total de archivos eliminados y los nuevos archivos sea valido
            if (!validarCantTotalArchivosConBD('files', 'nombres_files_bd', $archivo['cant_min'], $archivo['cant_max'])) {
                throw new Exception("La cantidad de archivos no cumplen con los requisitos.Debe ser al menos " . $archivo['cant_min'] . " imagen/video y como máximo " . $archivo['cant_max'] . " imagenes/videos");
            }

            //Verifica que la subida de archivos por parte del usuario sea correcta
            if (!empty($files)) {

                //Verifica que la subida de archivos por parte del usuario sea correcta
                if (!verificarSubidaArchivos('files')) {
                    throw new Exception("Error al subir archivos.");
                }

                $cantidad_archivos = count($_FILES['files']['name']);
                for ($i = 0; $i < $cantidad_archivos; $i++) {

                    $extension_archivo = pathinfo($_FILES['files']['name'][$i], PATHINFO_EXTENSION);

                    //Se verifica que la extension del archivo sea uno valido
                    if (!verificarExtensionesValidasArchivos($extension_archivo, $archivo['extensiones'])) {
                        throw new Exception("El formato del archivo " . $_FILES['files']['name'][$i] . " no es valido. Formatos permitidos: JPEG, JPG, PNG, MP4, MKV, AVI");
                    }
                    $tamanio_archivo = $_FILES['files']['size'][$i];

                    //Se verifica si la extension del archivo sea un imagen
                    if (verificarExtensionesValidasArchivos($extension_archivo, $archivo['extensiones_imagen'])) {

                        //Se verifica que la imagen tenga un tamaño valido
                        if (!validarTamanioImagen($tamanio_archivo)) {
                            throw new Exception("La imagen:" . $_FILES['files']['name'][$i] . " pesa mas de 10MB");
                        }
                    } else {
                        //Se verifica que la extension del archivo sea un video
                        if (verificarExtensionesValidasArchivos($extension_archivo, $archivo['extensiones_video'])) {

                            //Se verifica que el video tenga un tamaño valido 
                            if (!validarTamanioVideo($tamanio_archivo)) {
                                throw new Exception("El video:" . $_FILES['files']['name'][$i] . " pesa mas de 100MB");
                            }
                        }
                    }
                }
            }
        }



        //Verifica la latitude y longitud del mapa 
        verificarLatitudLongitud($map_latitud, $map_longitud);

        //Se guarda las modificaciones de la publicacion en la cuenta usuario emprendedor
        modificarPublicacionInformacionPorAdmin($conexion, $fecha_modificada, $descripcion, $map_latitud, $map_longitud, $id_usuario_emprendedor, $id_publicacion_modificar, $files, $archivos_eliminados, $nombre_carpeta);


        $respuesta['estado'] = 'success';
        $respuesta['mensaje'] = 'La informacion de la publicacion fue modificada correctamente';
    } else {
        throw new Exception("No se recibio una solicitud POST.");
    }
} catch (Exception $e) {

    //Capturar cualquier excepción y guardar el mensaje de error en la respuesta
    $respuesta['mensaje'] = $e->getMessage();
    $respuesta['estado'] = $estado;
}

//Devolver la respuesta en formato JSON
echo json_encode($respuesta);
