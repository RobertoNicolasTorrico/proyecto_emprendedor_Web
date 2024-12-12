<?php
//Archivos de configuracion y funciones necesarias
include("../../config/consultas_bd/conexion_bd.php");
include("../../config/consultas_bd/consultas_publicaciones.php");
include("../../config/consultas_bd/consultas_usuario.php");
include("../../config/consultas_bd/consultas_usuario_emprendedor.php");
include("../../config/funciones/funciones_verificaciones.php");
include("../../config/funciones/funciones_generales.php");
include("../../config/config_define.php");


//Configuracion de archivos permitidos
$archivo = [
    'cant_min' => 1,
    'cant_max' => 5,
    'extensiones' => ['png', 'jpeg', 'jpg', 'mp4', 'mkv', 'avi'],
    'extensiones_imagen' => ['png', 'jpeg', 'jpg'],
    'extensiones_video' => ['mp4', 'mkv', 'avi'],
];

$descripcion_datos = [
    'cant_min' => 1,
    'cant_max' => 255,
];

// Campos esperados en la solicitud POST
$campo_esperados = array('id_usuario', 'tipo_usuario', 'descripcion');

$respuesta = [];


try {
    //Verifica si la solicitud es POST y no esta vacia
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {

        //Verifica la entrada de datos esperados
        $mensaje = verificarEntradaDatosArray($campo_esperados, $_POST);
        if (!empty($mensaje)) {
            throw new Exception($mensaje);
        }

        // Establecer conexión con la base de datos
        $conexion = obtenerConexionBD();

        //Se obtiene los datos de la solicitud POST
        $descripcion = $_POST['descripcion'];
        $id_usuario = $_POST['id_usuario'];
        $tipo_usuario = $_POST['tipo_usuario'];
        $files = (isset($_FILES['files'])) ? $_FILES['files'] : [];

        $map_latitude = (isset($_POST['map_latitude']) && $_POST['map_latitude'] != "null") ? (float)$_POST['map_latitude'] : null;
        $map_longitude = (isset($_POST['map_longitude']) && $_POST['map_longitude'] != "null") ? (float)$_POST['map_longitude'] : null;


        //Verifica si el usuario es valido
        if (!verificarSiEsUsuarioValido($conexion, $id_usuario, $tipo_usuario)) {
            throw new Exception("No se puede hacer una nueva publicacion debido a que su cuenta no esta activa o esta baneado");
        }

        //Verifica que el usuario sea un usuario emprendedor
        if (!verificarSiEsUsuarioEmprendedor($conexion, $id_usuario)) {
            throw new Exception("No se puede hacer una publicacion debido a que no es un usuario emprendedor");
        }

        //Se obtiene los datos del usuario emprendedor por el id del usuario
        $usuario_emprendedor = obtenerDatosUsuarioEmprendedorPorIDUsuario($conexion, $id_usuario);
        if (empty($usuario_emprendedor)) {
            throw new Exception("No se pudo obtener la informacion del usuario emprendedor. por favor intente mas tarde");
        }
        $id_usuario_emprendedor = $usuario_emprendedor['id_usuario_emprendedor'];


        //Verifica que la descripcion no tenga espacios al inicio o al final de la cadena
        if (tieneEspaciosInicioFinCadena($descripcion)) {
            throw new Exception("No se permite que el campo descripcion tenga espacios en blanco al inicio o al final");
        }

        //Verifica que la longitud de caracteres sea valida 
        if (verificarLongitudTexto($descripcion, $descripcion_datos['cant_min'], $descripcion_datos['cant_max'])) {
            throw new Exception("El campo descripcion debe tener entre 1 y 255 caracteres");
        }

        //Verifica y maneja la carga de archivos en caso que se reciba uno
        if (!empty($files)) {

            //Verifica que la cantidad de archivos sea valido
            if (!validarCantArchivos('files', $archivo['cant_min'], $archivo['cant_max'])) {
                throw new Exception("La cantidad de archivos no cumplen con los requisitos.Debe ser al menos " . $archivo['cant_min'] . " archivos y como máximo " . $archivo['cant_max'] . " archivos");
            }

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

        //Verifica la latitude y longitud del mapa 
        verificarLatitudLongitud($map_latitude, $map_longitude);

        //Se guarda una nueva publicacion para el usuario emprendedor
        altaPublicacionInformacion($conexion,  $descripcion, $id_usuario_emprendedor, $map_longitude, $map_latitude, $files);

        $respuesta['estado'] = 'success';
        $respuesta['mensaje'] = 'Se publico correctamente';

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
